<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Orderdetail;
use App\Models\Table;
use App\Enums\OrderStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TableOrderService
{
    /**
     * Lấy đơn hàng đang hoạt động duy nhất của bàn (status PENDING hoặc PROCESSING)
     */
    public static function getActiveOrder(int|string $tableId): ?Order
    {
        return Order::activeForTable($tableId)->latest('id')->first();
    }

    /**
     * Alias tương thích
     */
    public static function getActiveOrderForTable(int|string $tableId): ?Order
    {
        return static::getActiveOrder($tableId);
    }

    /**
     * Lấy đơn hàng đang hoạt động hoặc tạo mới nếu bàn chưa có đơn mở
     * Áp dụng Pessimistic Locking để chống race-condition tạo đơn trùng lặp
     */
    public static function getOrCreateActiveOrder(int|string $tableId, array $attributes = []): Order
    {
        return DB::transaction(function () use ($tableId, $attributes) {
            $table = Table::findOrFail($tableId);

            $order = Order::where('table_id', $tableId)
                ->whereNull('deleted_at')
                ->whereIn('status', [OrderStatus::PENDING, OrderStatus::PROCESSING])
                ->lockForUpdate()
                ->latest('id')
                ->first();

            if ($order) {
                return $order;
            }

            $order = Order::create(array_merge([
                'table_id' => $tableId,
                'user_id' => auth()->id() ?? 0,
                'channel' => Order::CHANNEL_DINE_IN,
                'orderStyle' => 0,
                'status' => OrderStatus::PENDING,
                'payment_status' => Order::PAYMENT_PENDING,
                'name' => $table->name,
                'phone' => session('customer_phone') ?? '0900000000',
                'email' => 'table' . $tableId . '@restaurant.local',
                'address' => $table->name,
                'created_by' => auth()->id() ?? 1,
                'order_turn' => 1,
                'total_price' => 0,
            ], $attributes));

            if ($table->status != 1) {
                $table->status = 1;
                $table->save();
            }

            return $order;
        });
    }

    /**
     * Thêm món vào đơn hàng duy nhất của bàn (Gộp số lượng nếu món đã tồn tại)
     */
    public static function addItemsToTableOrder(int|string $tableId, array $items, ?int $turn = null): Order
    {
        return DB::transaction(function () use ($tableId, $items, $turn) {
            $order = static::getOrCreateActiveOrder($tableId);

            foreach ($items as $item) {
                $productId = $item['product_id'] ?? $item['id'];
                $qty = (int)($item['qty'] ?? $item['quantity'] ?? 1);
                $price = (float)($item['price'] ?? 0);
                if ($price <= 0) {
                    $prod = \App\Models\Product::find($productId);
                    $price = $prod ? (float)$prod->price : 0;
                }

                $existingDetail = Orderdetail::where('order_id', $order->id)
                    ->where('product_id', $productId)
                    ->first();

                if ($existingDetail) {
                    $existingDetail->qty += $qty;
                    $existingDetail->amount = $existingDetail->price * $existingDetail->qty;
                    $existingDetail->save();
                } else {
                    Orderdetail::create([
                        'order_id' => $order->id,
                        'product_id' => $productId,
                        'qty' => $qty,
                        'price' => $price,
                        'discount' => 0,
                        'amount' => $price * $qty,
                        'order_turn' => $turn ?? $order->order_turn ?? 1,
                        'status' => 'pending',
                    ]);
                }
            }

            $order->total_price = \App\Services\PricingService::recalculateOrderTotal($order->id, true);
            $order->has_new_items = true;
            $order->save();

            return $order;
        });
    }

    /**
     * Gộp các đơn hàng trùng lặp đang mở của một bàn vào một đơn duy nhất (Primary Order)
     */
    public static function mergeDuplicateOrdersForTable(int|string $tableId, bool $apply = false): array
    {
        $activeOrders = Order::where('table_id', $tableId)
            ->whereNull('deleted_at')
            ->whereIn('status', [OrderStatus::PENDING, OrderStatus::PROCESSING])
            ->with('orderDetails')
            ->orderBy('id', 'asc')
            ->get();

        if ($activeOrders->count() <= 1) {
            return [
                'table_id' => $tableId,
                'duplicates_count' => 0,
                'merged' => false,
                'primary_order_id' => $activeOrders->first()?->id,
            ];
        }

        $primaryOrder = $activeOrders->first();
        $duplicateOrders = $activeOrders->slice(1);

        $mergedItemsCount = 0;

        if ($apply) {
            DB::transaction(function () use ($primaryOrder, $duplicateOrders, &$mergedItemsCount) {
                foreach ($duplicateOrders as $dup) {
                    foreach ($dup->orderDetails as $detail) {
                        $existing = Orderdetail::where('order_id', $primaryOrder->id)
                            ->where('product_id', $detail->product_id)
                            ->first();

                        if ($existing) {
                            $existing->qty += $detail->qty;
                            $existing->amount = $existing->price * $existing->qty;
                            $existing->save();
                            $detail->delete();
                        } else {
                            $detail->order_id = $primaryOrder->id;
                            $detail->save();
                        }
                        $mergedItemsCount++;
                    }

                    // Hủy đơn trùng và ghi nhận vào lịch sử
                    $dup->status = OrderStatus::CANCELLED;
                    $dup->delivery_note = "Đã gộp vào đơn hàng #{$primaryOrder->id}";
                    $dup->save();
                }

                // Cập nhật lại tổng tiền đơn chính
                $primaryOrder->total_price = \App\Services\PricingService::recalculateOrderTotal($primaryOrder->id, true);
                $primaryOrder->save();
            });
        }

        return [
            'table_id' => $tableId,
            'primary_order_id' => $primaryOrder->id,
            'duplicate_order_ids' => $duplicateOrders->pluck('id')->toArray(),
            'duplicates_count' => $duplicateOrders->count(),
            'merged_items_count' => $mergedItemsCount,
            'merged' => $apply,
        ];
    }

    /**
     * Quét và gộp toàn bộ các bàn đang bị trùng đơn trên hệ thống
     */
    public static function mergeAllDuplicateTableOrders(bool $apply = false): array
    {
        $tableIds = Order::whereNotNull('table_id')
            ->whereNull('deleted_at')
            ->whereIn('status', [OrderStatus::PENDING, OrderStatus::PROCESSING])
            ->select('table_id', DB::raw('count(id) as total_active'))
            ->groupBy('table_id')
            ->having('total_active', '>', 1)
            ->pluck('table_id');

        $results = [];
        foreach ($tableIds as $tid) {
            $results[] = static::mergeDuplicateOrdersForTable($tid, $apply);
        }

        return $results;
    }
}
