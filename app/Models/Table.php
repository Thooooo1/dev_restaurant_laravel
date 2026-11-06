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


