<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'user_id',
        'role',
        'type',
        'title',
        'message',
        'url',
        'icon',
        'color',
        'data',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope lọc thông báo theo đối tượng người dùng & vai trò
     */
    public function scopeForUser($query, $user = null)
    {
        if (!$user) {
            // Khách chưa đăng nhập: Chỉ thấy thông báo chung toàn hệ thống
            return $query->where('role', 'all')->whereNull('user_id');
        }

        $userId = $user->id;
        $userRole = $user->roles;

        return $query->where(function ($q) use ($userId, $userRole) {
            // 1. Khách hàng:
            // Chỉ thấy thông báo đích danh của chính mình (user_id = $userId),
            // hoặc thông báo chung toàn hệ thống gửi cho tất cả khách hàng (user_id IS NULL AND role IN ('customer', 'all')).
            // Tuyệt đối không thấy thông báo của tài khoản khách khác hoặc thông báo nghiệp vụ của nhân viên / admin.
            if ($userRole === 'customer') {
                $q->where('user_id', $userId)
                  ->orWhere(function ($sub) {
                      $sub->whereNull('user_id')
                          ->whereIn('role', ['customer', 'all']);
                  });
            }
            // 2. Nhân viên:
            // Thấy thông báo đích danh của mình (user_id = $userId),
            // hoặc thông báo nghiệp vụ dành cho nhân viên (user_id IS NULL AND role IN ('employee', 'all')).
            // Không nhận các tin cảm ơn cá nhân của khách hàng khác.
            elseif ($userRole === 'employee') {
                $q->where('user_id', $userId)
                  ->orWhere(function ($sub) {
                      $sub->whereNull('user_id')
                          ->whereIn('role', ['employee', 'all']);
                  });
            }
            // 3. Quản trị viên (Admin):
            // Thấy thông báo đích danh của mình (user_id = $userId),
            // hoặc thông báo quản trị / vận hành hệ thống (user_id IS NULL AND role IN ('admin', 'employee', 'all')).
            // Không nhận các tin cảm ơn cá nhân của khách hàng khác.
            else {
                $q->where('user_id', $userId)
                  ->orWhere(function ($sub) {
                      $sub->whereNull('user_id')
                          ->whereIn('role', ['admin', 'employee', 'all']);
                  });
            }
        });
    }

    public function getTimeAgoAttribute()
    {
        return $this->created_at ? $this->created_at->locale('vi')->diffForHumans() : 'Vừa xong';
    }

    /**
     * Đánh dấu đã đọc
     */
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => Carbon::now(),
        ]);
    }
}
