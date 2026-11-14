<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDeliveryLog extends Model
{
    protected $table = 'order_delivery_logs';
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'user_id',
        'from_delivery_status',
        'to_delivery_status',
        'from_order_status',
        'to_order_status',
        'shipper_name',
        'shipper_phone',
        'note',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function formatDeliveryLabel(?string $status): string
    {
        return match($status) {
            'waiting' => 'Chờ phân công',
            'picked_up' => 'Đã nhận hàng',
            'delivering' => 'Đang giao hàng',
            'delivered' => 'Đã giao thành công',
            'failed' => 'Giao hàng thất bại',
            default => $status ?: 'Khởi tạo'
        };
    }

    public function getFromDeliveryLabelAttribute(): string
    {
        return self::formatDeliveryLabel($this->from_delivery_status);
    }

    public function getToDeliveryLabelAttribute(): string
    {
        return self::formatDeliveryLabel($this->to_delivery_status);
    }
}
