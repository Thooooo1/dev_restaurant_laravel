<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inquiries extends Model
{
    use HasFactory;
    protected $table ='inquiries';
    protected $fillable = ['name', 'phone', 'message', 'status'];

}
