<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerAiProfile extends Model
{
    protected $table = 'customer_ai_profiles';

    protected $fillable = [
        'phone',
        'user_id',
        'customer_name',
        'taste_preferences',
        'dietary_restrictions',
        'favorite_seating',
        'total_visits',
        'internal_notes',
    ];

    protected $casts = [
        'taste_preferences' => 'array',
        'dietary_restrictions' => 'array',
        'total_visits' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
