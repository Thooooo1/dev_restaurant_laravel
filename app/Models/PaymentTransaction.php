<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    protected $table = 'payment_transactions';
    public $timestamps = false;

    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';
    public const STATUS_REFUNDED = 'refunded';
    public const STATUS_OVERPAYMENT = 'overpayment';

    protected $fillable = [
        'order_id',
        'reference_code',
        'amount',
        'content',
        'gateway',
        'status',
        'raw_payload',
        'created_at',
    ];

    protected $casts = [
        'amount' => 'float',
        'created_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
