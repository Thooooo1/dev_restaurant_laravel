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

        if ($method === '2' || strtolower($method) === 'vietqr' || strtolower($method) === 'bank' || str_contains(strtolower($method), 'chuyển khoản')) {
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

        if (strtolower($method) === 'momo') {
            return [
                'code' => 'momo',
                'name' => 'Ví Điện Tử MoMo',
                'icon' => 'fa-wallet',
                'color' => '#D4380D',
                'bg' => 'bg-pink-50',
                'text' => 'text-pink-700',
                'border' => 'border-pink-200',
                'desc' => 'Thanh toán qua ví điện tử MoMo',
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
        return (int)$this->orderStyle === 2 ? 'Giao hàng tận nơi' : 'Mang về';
    }
}
