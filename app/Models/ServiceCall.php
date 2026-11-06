<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceCall extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'service_calls';

    protected $fillable = [
        'table_id',
        'order_id',
        'customer_name',
        'phone',
        'call_type',
        'call_type_label',
        'note',
        'status',
        'handled_by',
        'handled_at',
    ];

    const STATUS_PENDING = 0;     // Chờ phục vụ
    const STATUS_PROCESSING = 1;  // Đang phục vụ
    const STATUS_COMPLETED = 2;   // Đã phục vụ xong
    const STATUS_CANCELLED = 3;   // Đã hủy

    const TYPES = [
        'call_waiter' => [
            'label' => 'Gọi nhân viên phục vụ',
            'icon' => 'fa-bell',
            'color' => '#F47716'
        ],
        'water' => [
            'label' => 'Xin thêm đá / nước lọc',
            'icon' => 'fa-glass-water',
            'color' => '#1677FF'
        ],
        'cutlery' => [
            'label' => 'Xin chén đĩa / gia vị / khăn',
            'icon' => 'fa-utensils',
            'color' => '#52C41A'
        ],
        'bill' => [
            'label' => 'Yêu cầu thanh toán hóa đơn',
            'icon' => 'fa-receipt',
            'color' => '#722ED1'
        ],
        'clean' => [
            'label' => 'Yêu cầu dọn bàn',
            'icon' => 'fa-broom',
            'color' => '#FA8C16'
        ],
        'other' => [
            'label' => 'Hỗ trợ khác',
            'icon' => 'fa-circle-question',
            'color' => '#595959'
        ],
    ];

    public function table()
    {
        return $this->belongsTo(Table::class, 'table_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function handler()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function getTypeInfoAttribute()
    {
        return self::TYPES[$this->call_type] ?? [
            'label' => $this->call_type_label ?? 'Gọi nhân viên',
            'icon' => 'fa-bell',
            'color' => '#F47716'
        ];
    }
}
