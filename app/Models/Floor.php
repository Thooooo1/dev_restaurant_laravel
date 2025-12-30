<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Floor extends Model
{
    use HasFactory;

    protected $table = 'cdw1_floors';

    protected $fillable = [
        'name',
    ];

    public function tables()
    {
        return $this->hasMany(Table::class, 'floor_id');
    }
}


