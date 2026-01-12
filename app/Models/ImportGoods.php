<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImportGoods extends Model
{
    use SoftDeletes;

    protected $table = 'import_goods';

    protected $fillable = [
        'name',
        'type',
        'unit',
        'quantity',
        'price',
        'total_amount',
    ];
}
