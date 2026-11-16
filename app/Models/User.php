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

    protected static function booted(): void
    {
        static::creating(function ($user) {
            $user->gender = $user->gender ?: 'Khác';
            $user->image = $user->image ?: 'default.png';
            $user->address = $user->address ?: 'Hà Nội';
            $user->created_by = $user->created_by ?: 1;
            if ($user->status === null) {
                $user->status = 1;
            }
        });
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
        'status',
        'created_by',
        'updated_by',
        'google_id',  
        'image', 
        'otp_code',
        'otp_expires_at'    
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
     * Kiểm tra xem người dùng có phải là Quản trị viên (Admin) không.
     * Nguồn sự thật chính: cột roles. admin_lever=1 chỉ dùng để tương thích dữ liệu cũ.
     */
    public function isAdmin(): bool
    {
        return $this->roles === 'admin';
    }

    /**
     * Kiểm tra xem người dùng có phải là Nhân viên (Employee) không.
     * Nguồn sự thật chính: cột roles. admin_lever=2 chỉ dùng để tương thích dữ liệu cũ.
     */
    public function isEmployee(): bool
    {
        return $this->roles === 'employee';
    }

    /**
     * Kiểm tra xem người dùng có phải là Khách hàng / Thành viên (Customer) không.
     * Xác định tường minh theo roles === 'customer' hoặc để trống (mặc định khách đăng ký mới).
     */
    public function isCustomer(): bool
    {
        return $this->roles === 'customer' || empty($this->roles);
    }
}
