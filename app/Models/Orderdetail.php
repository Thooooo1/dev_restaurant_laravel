<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Orderdetail extends Model
{
    protected $table = 'orderdetail';
    public $timestamps = false;

    protected $fillable = ['order_id', 'product_id', 'qty', 'price', 'discount', 'amount', 'order_turn', 'status'];

    protected static function booted(): void
    {
        static::creating(function ($detail) {
            if ($detail->discount === null) {
                $detail->discount = 0;
            }
            if ($detail->order_turn === null) {
                $detail->order_turn = 1;
            }
        });
    }

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
