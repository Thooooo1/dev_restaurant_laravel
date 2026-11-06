<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Orderdetail extends Model
{
    protected $table = 'orderdetail';
    public $timestamps = false;

    protected $fillable = ['order_id', 'product_id', 'qty', 'price', 'discount', 'amount', 'order_turn', 'status'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function logs()
    {
        return $this->hasMany(OrderItemLog::class, 'order_detail_id');
    }
}
