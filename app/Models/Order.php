<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Order extends Model
{
    use SoftDeletes;
    protected $table ='order';
    
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'number_of_guests',
        'address',
        'created_by',
        'updated_by',
        'status',
        'note',
        'orderStyle',
        'table_id',
        'order_turn',
        'payment_method',
        'total_price',
    ];

    public function getStatusLabelAttribute(): string
    {
        return \App\Enums\OrderStatus::getLabel((int)$this->status, (bool)$this->table_id);
    }

    public function orderDetails()
    {
        return $this->hasMany(Orderdetail::class, 'order_id');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function table()
    {
        return $this->belongsTo(Table::class, 'table_id');
    }

    public function itemLogs()
    {
        return $this->hasMany(OrderItemLog::class, 'order_id')->orderBy('id', 'desc');
    }

    /**
     * Tên và thông tin phương thức thanh toán
     */
    public function getPaymentMethodInfoAttribute(): array
    {
        $method = (string) $this->payment_method;

        if ($method === '1' || strtolower($method) === 'cod' || strtolower($method) === 'cash' || strtolower($method) === 'tiền mặt') {
            return [
                'code' => 'cash',
                'name' => 'Tiền mặt',
                'icon' => 'fa-money-bill-wave',
                'color' => '#52C41A',
                'bg' => 'bg-emerald-50',
                'text' => 'text-emerald-700',
                'border' => 'border-emerald-200',
                'desc' => 'Thanh toán trực tiếp bằng tiền mặt tại quầy / bàn',
            ];
        }

        if ($method === '2' || strtolower($method) === 'vietqr' || strtolower($method) === 'bank' || str_contains(strtolower($method), 'chuyển khoản') || strtolower($method) === 'momo') {
            return [
                'code' => 'vietqr',
                'name' => 'Chuyển khoản VietQR',
                'icon' => 'fa-qrcode',
                'color' => '#1677FF',
                'bg' => 'bg-blue-50',
                'text' => 'text-blue-700',
                'border' => 'border-blue-200',
                'desc' => 'Quét mã VietQR chuyển khoản ngân hàng (Napas 247)',
            ];
        }

        if ($method === '3' || strtolower($method) === 'card' || strtolower($method) === 'pos' || str_contains(strtolower($method), 'thẻ')) {
            return [
                'code' => 'card',
                'name' => 'Quẹt thẻ POS',
                'icon' => 'fa-credit-card',
                'color' => '#722ED1',
                'bg' => 'bg-purple-50',
                'text' => 'text-purple-700',
                'border' => 'border-purple-200',
                'desc' => 'Thanh toán quẹt thẻ ATM nội địa / Visa / MasterCard qua máy POS',
            ];
        }

        return [
            'code' => 'other',
            'name' => $this->payment_method ?: 'Chưa chọn',
            'icon' => 'fa-circle-question',
            'color' => '#8C8C8C',
            'bg' => 'bg-gray-50',
            'text' => 'text-gray-700',
            'border' => 'border-gray-200',
            'desc' => 'Phương thức thanh toán khác',
        ];
    }

    public function getPaymentMethodNameAttribute(): string
    {
        return $this->payment_method_info['name'];
    }

    /**
     * Tên hình thức phục vụ
     */
    public function getOrderStyleNameAttribute(): string
    {
        if ($this->table_id) {
            return 'Dùng bữa tại bàn';
        }
        if (trim($this->address ?? '') === 'Tại quán' || trim($this->name ?? '') === 'Khách POS') {
            return 'Tại quầy (POS)';
        }
        if ((int)$this->orderStyle === 3) {
            return 'Mang về';
        }
        return 'Giao hàng tận nơi';
    }

    /**
     * Tên khách hàng chuẩn xác của đơn hàng (ưu tiên name trên hóa đơn, sau đó là tài khoản khách hàng, loại trừ tài khoản admin/nhân viên)
     */
    public function getCustomerNameAttribute(): string
    {
        if (!empty(trim($this->name ?? ''))) {
            return trim($this->name);
        }

        if ($this->user && $this->user->roles === 'customer') {
            return $this->user->fullname ?: ($this->user->username ?: 'Khách hàng');
        }

        if ($this->table_id) {
            return 'Khách tại bàn';
        }

        return 'Khách lẻ';
    }

    /**
     * Số điện thoại khách hàng chuẩn xác của đơn hàng
     */
    public function getCustomerPhoneAttribute(): string
    {
        if (!empty(trim($this->phone ?? ''))) {
            return trim($this->phone);
        }

        if ($this->user && $this->user->roles === 'customer' && !empty($this->user->phone)) {
            return $this->user->phone;
        }

        return '---';
    }
}
