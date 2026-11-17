<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentIntent extends Model
{
    use HasFactory;

    protected $table = 'payment_intents';

    const STATUS_PENDING = 'pending';
    const STATUS_SUCCESSFUL = 'successful';
    const STATUS_FAILED = 'failed';
    const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'order_id',
        'gateway',
        'merchant_id',
        'request_id',
        'expected_amount',
        'currency',
        'status',
        'gateway_transaction_id',
        'expires_at',
        'payload',
    ];

    protected $casts = [
        'expected_amount' => 'decimal:2',
        'expires_at' => 'datetime',
        'payload' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_SUCCESSFUL;
    }

    public function isExpired(): bool
    {
        if ($this->expires_at && now()->greaterThan($this->expires_at)) {
            return true;
        }
        return $this->status === self::STATUS_EXPIRED;
    }
}
