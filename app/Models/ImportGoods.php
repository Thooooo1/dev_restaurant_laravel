<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportGoods extends Model
{
    use SoftDeletes;

    protected $table = 'import_goods';

    protected $fillable = [
        'import_receipt_id',
        'name',
        'type',
        'unit',
        'quantity',
        'price',
        'total_amount',
    ];

    /**
     * Phiếu nhập chứa mặt hàng này
     */
    public function receipt(): BelongsTo
    {
        return $this->belongsTo(ImportReceipt::class, 'import_receipt_id');
    }
}
