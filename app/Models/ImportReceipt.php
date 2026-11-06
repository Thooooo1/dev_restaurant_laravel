<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportReceipt extends Model
{
    use SoftDeletes;

    protected $table = 'import_receipts';

    protected $fillable = [
        'code',
        'supplier_name',
        'supplier_phone',
        'supplier_address',
        'import_date',
        'total_amount',
        'note',
        'creator_name',
        'created_by',
        'status',
    ];

    protected $casts = [
        'import_date' => 'datetime',
        'total_amount' => 'decimal:0',
        'status' => 'integer',
    ];

    /**
     * Danh sách mặt hàng thuộc phiếu nhập
     */
    public function items(): HasMany
    {
        return $this->hasMany(ImportGoods::class, 'import_receipt_id');
    }

    /**
     * Người tạo phiếu (nếu liên kết với bảng users)
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Helper sinh mã phiếu nhập tự động: PNK-YYYYMMDD-001
     */
    public static function generateCode(): string
    {
        $datePrefix = 'PNK-' . date('Ymd') . '-';
        $latest = self::where('code', 'like', $datePrefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($latest) {
            $num = (int) substr($latest->code, -3);
            $nextNum = str_pad($num + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $nextNum = '001';
        }

        return $datePrefix . $nextNum;
    }
}
