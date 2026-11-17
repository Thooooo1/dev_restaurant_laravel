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
            $table = Table::where('id', $tableId)->lockForUpdate()->firstOrFail();

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
                'phone' => session('customer_phone') ?: '',
                'email' => session('customer_email') ?: '',
                'address' => $table->name,
                'created_by' => auth()->id() ?? 1,
                'order_turn' => 1,
                'total_price' => 0,
            ], $attributes));

            if ($table->status != 1 || $table->locked_order_id !== $order->id) {
                $table->status = 1;
                $table->locked_order_id = $order->id;
                $table->save();
            }

            return $order;
        });
    }

    /**
     * Alias thuận tiện gọi thêm món cho bàn
     */
    public static function addItemsToTable(int|string $tableId, array $items, ?int $turn = null): Order
    {
        return static::addItemsToTableOrder($tableId, $items, $turn);
    }

    /**
     * Thêm món vào đơn hàng duy nhất của bàn (Gộp số lượng nếu món đã tồn tại)
     */
    public static function addItemsToTableOrder(int|string $tableId, array $items, ?int $turn = null): Order
    {
        return DB::transaction(function () use ($tableId, $items, $turn) {
            $order = static::getOrCreateActiveOrder($tableId);
            if ($order->isPaid() || $order->payment_status === Order::PAYMENT_PAID) {
                throw new \DomainException('Đơn hàng của bàn này đã thanh toán xong, không thể gọi thêm món.');
            }

            $hasExisting = $order->orderDetails()->exists();
            if ($hasExisting && $turn === null) {
                $order->order_turn = ($order->order_turn ?? 1) + 1;
            }

            $currentTurn = $turn ?? $order->order_turn ?? 1;

            // P1-05: Sắp xếp ID món tăng dần để tránh Deadlock khi nhiều bàn cùng đặt món đồng thời
            usort($items, function ($a, $b) {
                $idA = (int)($a['product_id'] ?? $a['id'] ?? 0);
                $idB = (int)($b['product_id'] ?? $b['id'] ?? 0);
                return $idA <=> $idB;
            });

            foreach ($items as $item) {
                $productId = $item['product_id'] ?? $item['id'];
                $qty = (int)($item['qty'] ?? $item['quantity'] ?? 1);
                if ($qty <= 0) {
                    continue;
                }

                // P1-05: Khóa sản phẩm với lockForUpdate và kiểm tra tồn kho đồng bộ
                $prod = \App\Models\Product::where('id', $productId)->lockForUpdate()->first();
                if (!$prod || (int)$prod->status !== 1) {
                    continue; // Bỏ qua món ngừng bán hoặc không tồn tại (D-09)
                }

                if ($prod->qty < $qty) {
                    throw new \DomainException("Món '{$prod->name}' không đủ số lượng phục vụ (còn {$prod->qty}, yêu cầu {$qty}).");
                }

                // Trừ tồn kho an toàn cho đơn gọi món tại bàn
                $prod->qty = max(0, $prod->qty - $qty);
                $prod->save();

                // Cập nhật giá bán mới nhất từ DB
                $price = (float)$prod->price;

                // D-03: Chỉ gộp vào dòng có trạng thái 'pending' (chưa gửi bếp).
                // Nếu món đã served/cooking/cancelled, tạo dòng mới theo đúng order_turn.
                $pendingDetail = Orderdetail::where('order_id', $order->id)
                    ->where('product_id', $productId)
                    ->where('status', 'pending')
                    ->first();

                if ($pendingDetail) {
                    $pendingDetail->qty += $qty;
                    // V4-P2-45: Đồng bộ theo giá mới nhất từ Product
                    $pendingDetail->price = $price;
                    $pendingDetail->amount = $price * $pendingDetail->qty;
                    $pendingDetail->save();
                } else {
                    Orderdetail::create([
                        'order_id' => $order->id,
                        'product_id' => $productId,
                        'qty' => $qty,
                        'price' => $price,
                        'discount' => 0,
                        'amount' => $price * $qty,
                        'order_turn' => $currentTurn,
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
