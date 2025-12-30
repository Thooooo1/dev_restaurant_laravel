<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Order extends Model
{
    use SoftDeletes;
    protected $table ='order';
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'order_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
