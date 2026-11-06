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
            // Khách chưa đăng nhập: Chỉ thấy thông báo chung
            return $query->where('role', 'all')->whereNull('user_id');
        }

        $userId = $user->id;
        $userRole = $user->roles;

        return $query->where(function ($q) use ($userId, $userRole) {
            // Thông báo đích danh cho người này
            $q->where('user_id', $userId);

            // Hoặc thông báo theo vai trò
            if ($userRole === 'admin') {
                // Admin thấy thông báo của Admin, Employee và All
                $q->orWhereIn('role', ['admin', 'employee', 'all']);
            } elseif ($userRole === 'employee') {
                // Nhân viên thấy thông báo của Employee và All
                $q->orWhereIn('role', ['employee', 'all']);
            } else {
                // Khách hàng / thành viên thấy thông báo khách hàng và All
                $q->orWhereIn('role', ['customer', 'all']);
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
