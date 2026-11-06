<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItemLog extends Model
{
    protected $table = 'order_item_logs';
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'order_detail_id',
        'product_id',
        'product_name',
        'action',
        'old_qty',
        'new_qty',
        'reason',
        'user_id',
        'kitchen_status',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function orderdetail()
    {
        return $this->belongsTo(Orderdetail::class, 'order_detail_id');
    }
}
