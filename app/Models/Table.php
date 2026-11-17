<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Table extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tables';

    protected $fillable = [
        'floor_id',
        'name',
        'seats',
        'sort_order',
        'status',
    ];

    protected static function booted(): void
    {
        static::creating(function ($table) {
            if (empty($table->floor_id)) {
                $firstFloor = Floor::first();
                if (!$firstFloor) {
                    $firstFloor = Floor::create(['name' => 'Tầng 1', 'status' => 1]);
                }
                $table->floor_id = $firstFloor->id;
            }
            if (empty($table->seats)) {
                $table->seats = $table->capacity ?? 4;
            }
            if (!isset($table->sort_order)) {
                $table->sort_order = 1;
            }
        });
    }

    public function floor()
    {
        return $this->belongsTo(Floor::class, 'floor_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'table_id');
    }

    public function serviceCalls()
    {
        return $this->hasMany(ServiceCall::class, 'table_id');
    }

    public function activeServiceCall()
    {
        return $this->hasOne(ServiceCall::class, 'table_id')->where('status', ServiceCall::STATUS_PENDING)->latestOfMany();
    }

    /**
     * V4-P1-36: Lấy token bảo mật QR gắn liền với bàn ăn
     */
    public function getQrToken(): string
    {
        if (!empty($this->qr_token)) {
            return (string)$this->qr_token;
        }
        return hash('sha256', 'table_qr_salt_' . $this->id . '_' . config('app.key'));
    }

    /**
     * V4-P1-36: Xác thực token QR của khách quét tại bàn
     */
    public function isValidQrToken(?string $token): bool
    {
        if (empty($token)) {
            return false;
        }
        return hash_equals($this->getQrToken(), (string)$token);
    }
}


