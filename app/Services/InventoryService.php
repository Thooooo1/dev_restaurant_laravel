<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InventoryService
{
    /**
     * Ghi nhận biến động tồn kho thông qua Sổ giao dịch (Inventory Ledger)
     * Đảm bảo tính nguyên tử (Atomic), khóa bi quan (lockForUpdate) và chống lặp (Idempotency)
     *
     * @param int $productId
     * @param int $delta Số lượng thay đổi (âm là xuất/bán, dương là nhập/hoàn)
     * @param string $reason Lý do: pos_sale, table_order_placed, order_cancelled, order_refunded, import_goods, etc.
     * @param string $idempotencyKey Khóa chống trùng lặp duy nhất
     * @param array $context Các trường ngữ cảnh: order_id, order_detail_id, import_receipt_id, actor_id
     * @return bool
     * @throws RuntimeException
     */
    public static function applyMovement($productOrId, int $delta, string $reason, string $idempotencyKey, array $context = []): bool
    {
        $productId = ($productOrId instanceof Product) ? $productOrId->id : (int)$productOrId;

        return DB::transaction(function () use ($productId, $delta, $reason, $idempotencyKey, $context) {
            // 1. Kiểm tra Idempotency: Nếu khóa này đã thực hiện thành công trước đó thì bỏ qua (idempotent)
            $existing = InventoryMovement::where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                return true;
            }

            // 2. Khóa sản phẩm để cập nhật tồn kho chính xác trong môi trường đồng thời
            $product = Product::where('id', $productId)->lockForUpdate()->first();
            if (!$product) {
                throw new RuntimeException("Không tìm thấy sản phẩm có ID: {$productId}");
            }

            // 3. Nếu là xuất kho (delta < 0), kiểm tra lượng hàng còn lại
            if ($delta < 0) {
                $required = abs($delta);
                if ($product->qty < $required) {
                    throw new RuntimeException("Sản phẩm '{$product->name}' không đủ tồn kho (cần: {$required}, hiện còn: {$product->qty}).");
                }
            }

            // 4. Cập nhật tồn kho sản phẩm
            $product->qty = (int)$product->qty + $delta;
            $product->save();

            // 5. Ghi nhận vào sổ giao dịch biến động tồn kho
            InventoryMovement::create([
                'product_id' => $productId,
                'delta' => $delta,
                'reason' => $reason,
                'order_id' => $context['order_id'] ?? null,
                'order_detail_id' => $context['order_detail_id'] ?? null,
                'import_receipt_id' => $context['import_receipt_id'] ?? null,
                'idempotency_key' => $idempotencyKey,
                'actor_id' => $context['actor_id'] ?? (Auth::id() ?? null),
            ]);

            return true;
        });
    }

    /**
     * Đối soát biến động tồn kho của một sản phẩm
     */
    public static function reconcile(int $productId): array
    {
        $product = Product::find($productId);
        if (!$product) {
            return ['status' => 'not_found'];
        }

        $totalMovements = (int)InventoryMovement::where('product_id', $productId)->sum('delta');
        $movementCount = InventoryMovement::where('product_id', $productId)->count();

        return [
            'product_id' => $productId,
            'product_name' => $product->name,
            'current_quantity' => (int)$product->qty,
            'net_movement_delta' => $totalMovements,
            'movement_count' => $movementCount,
        ];
    }
}
