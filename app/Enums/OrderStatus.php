<?php

namespace App\Enums;

class OrderStatus
{
    const PENDING = 0;    // Chờ xử lý / Tiếp nhận đơn
    const PROCESSING = 1; // Đang giao hàng / Đang thực hiện (Đơn tại bàn: Đang phục vụ / Đã thanh toán)
    const COMPLETED = 2;  // Đã giao hàng thành công / Hoàn tất dùng bữa
    const CANCELLED = 3;  // Đã hủy đơn hàng

    /**
     * Lấy mô tả chi tiết trạng thái đơn hàng (Đồng bộ toàn hệ thống: Dashboard, POS, Bếp, Chatbot, Khách hàng)
     */
    public static function getLabel(int $status, bool $isTableOrder = false): string
    {
        return match ($status) {
            self::PENDING => $isTableOrder ? 'Chờ phục vụ' : 'Chờ xử lý',
            self::PROCESSING => $isTableOrder ? 'Đang phục vụ / Đã thanh toán' : 'Đang giao hàng',
            self::COMPLETED => $isTableOrder ? 'Hoàn tất dùng bữa' : 'Đã giao hàng thành công',
            self::CANCELLED => 'Đã hủy đơn hàng',
            default => 'Không xác định',
        };
    }

    /**
     * Badge CSS class cho giao diện Ant Design Pro / Polaris
     */
    public static function getBadgeClass(int $status): string
    {
        return match ($status) {
            self::PENDING => 'polaris-badge-warning',
            self::PROCESSING => 'polaris-badge-processing',
            self::COMPLETED => 'polaris-badge-success',
            self::CANCELLED => 'polaris-badge-error',
            default => 'polaris-badge-default',
        };
    }
}
