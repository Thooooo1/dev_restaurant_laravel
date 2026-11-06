<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table='user';
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    protected $fillable = [
        'username',
        'email',
        'password',
        'fullname',
        'gender',
        'phone',
        'address',
        'roles',
        'google_id',  
        'image', 
        'otp_code' ,
        'otp_expires_at	'    
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Danh sách đơn hàng của khách hàng
     */
    public function orders()
    {
        return $this->hasMany(\App\Models\Order::class, 'user_id');
    }

    /**
     * Kiểm tra xem người dùng có phải là quản lý không
     */
    public function isAdmin(): bool
    {
        return $this->roles == 'admin'; // Hoặc giá trị tương ứng trong database của bạn
    }

    /**
     * Kiểm tra xem người dùng có phải là nhân viên không
     */
    public function isEmployee(): bool
    {
        return $this->roles == 'employee'; // Hoặc giá trị tương ứng trong database của bạn
    }
}
