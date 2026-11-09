<?php

namespace App\Services;

use App\Models\Orderdetail;
use Illuminate\Support\Facades\File;

class PricingService
{
    /**
     * Lấy cài đặt phí vận chuyển từ settings.json
     */
    public static function getShippingSettings(): array
    {
        $settingsPath = storage_path('app/settings.json');
        $settings = [];
        if (File::exists($settingsPath)) {
            $settings = json_decode(File::get($settingsPath), true) ?: [];
        }

        return [
            'base_fee' => (int) ($settings['shipping_base_fee'] ?? 25000),
            'free_threshold' => (int) ($settings['shipping_free_threshold'] ?? 500000),
            'max_km' => (int) ($settings['shipping_max_distance_km'] ?? 15),
        ];
    }

    /**
     * Tính phí giao hàng theo giá trị đơn hàng
     */
    public static function calculateShippingFee(int $subtotal): int
    {
        $settings = self::getShippingSettings();
        if ($subtotal >= $settings['free_threshold']) {
            return 0;
        }
        return $settings['base_fee'];
    }

    /**
     * Tính tổng hợp giỏ hàng và chiết khấu
     */
    public static function calculateCartSummary(array $cart, bool $isTableOrder = false): array
    {
        $totalQty = 0;
        $subtotal = 0;
        $items = [];

        foreach ($cart as $id => $item) {
            $price = (int) ($item['price'] ?? 0);
            $qty = (int) ($item['qty'] ?? 1);
            $itemTotal = $price * $qty;
            $totalQty += $qty;
            $subtotal += $itemTotal;

            $items[] = array_merge($item, [
                'total' => $itemTotal,
                'total_formatted' => number_format($itemTotal, 0, ',', '.') . '₫',
            ]);
        }

        $discountAmount = $isTableOrder ? (int) round($subtotal * 0.1) : 0;
        $shippingFee = $isTableOrder ? 0 : self::calculateShippingFee($subtotal);
        $finalTotal = max(0, $subtotal - $discountAmount + $shippingFee);

        return [
            'count' => $totalQty,
            'subtotal' => $subtotal,
            'subtotal_formatted' => number_format($subtotal, 0, ',', '.') . '₫',
            'discount' => $discountAmount,
            'discount_formatted' => number_format($discountAmount, 0, ',', '.') . '₫',
            'shipping_fee' => $shippingFee,
            'shipping_fee_formatted' => number_format($shippingFee, 0, ',', '.') . '₫',
            'total' => $finalTotal,
            'total_formatted' => number_format($finalTotal, 0, ',', '.') . '₫',
            'items' => $items,
        ];
    }

    /**
     * Lấy tổng tiền phụ (subtotal) các món chưa bị hủy của Order
     */
    public static function getOrderSubtotal(int $orderId): int
    {
        return (int) Orderdetail::where('order_id', $orderId)
            ->where('status', '!=', 'cancelled')
            ->sum('amount');
    }

    /**
     * Tính lại tổng tiền của Order từ các OrderDetail trong database (Đã loại trừ món hủy)
     */
    public static function recalculateOrderTotal(int $orderId, bool $isTableOrder = false): int
    {
        $subtotal = self::getOrderSubtotal($orderId);
        if ($isTableOrder) {
            $discount = (int) round($subtotal * 0.1);
            return max(0, $subtotal - $discount);
        }
        $shippingFee = self::calculateShippingFee($subtotal);
        return $subtotal + $shippingFee;
    }
}
