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
}


