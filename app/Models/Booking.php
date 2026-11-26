<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $table ='bookings';
    protected $fillable = [
        'name',
        'email',
        'phone',
        'number_of_people',
        'booking_date',
        'booking_time',
        'note',
        'status',
        'table_id',
    ];

    public function table()
    {
        return $this->belongsTo(Table::class, 'table_id');
    }
}
