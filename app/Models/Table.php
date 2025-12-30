<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Table extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cdw1_tables';

    protected $fillable = [
        'floor_id',
        'name',
        'seats',
        'sort_order',
        'status',
    ];

    public function floor()
    {
        return $this->belongsTo(Floor::class, 'floor_id');
    }
}


