<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class NotificationController extends Controller
{
    /**
     * Lấy danh sách thông báo theo vai trò và người dùng hiện tại (Facebook / Messenger style)
     */
    public function getFeed(Request $request)
    {
        $user = Auth::user();
        $filter = $request->query('filter', 'all'); // 'all' hoặc 'unread'

        $query = Notification::forUser($user)->orderBy('created_at', 'DESC');

        $unreadCount = (clone $query)->where('is_read', false)->count();

        if ($filter === 'unread') {
            $query->where('is_read', false);
        }

        $notifications = $query->take(25)->get()->map(function ($noti) {
            return [
                'id' => $noti->id,
                'type' => $noti->type,
                'title' => $noti->title,
                'message' => $noti->message,
                'url' => $noti->url ?: 'javascript:void(0)',
                'icon' => $noti->icon ?: 'fa-bell',
                'color' => $noti->color ?: '#F47716',
                'is_read' => (bool)$noti->is_read,
                'time_ago' => $noti->created_at ? $noti->created_at->locale('vi')->diffForHumans() : 'Vừa xong',
                'created_at' => $noti->created_at ? $noti->created_at->toIso8601String() : null,
                'data' => $noti->data,
            ];
        });

        return response()->json([
            'success' => true,
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->fullname ?? $user->name,
                'role' => $user->roles ?? 'customer',
            ] : null,
            'unread_count' => $unreadCount,
            'notifications' => $notifications,
        ]);
    }

    /**
     * Đánh dấu 1 thông báo là đã đọc
     */
    public function markAsRead(Request $request, $id)
    {
        $user = Auth::user();
        $notification = Notification::find($id);

        if (!$notification) {
            return response()->json(['success' => false, 'message' => 'Thông báo không tồn tại.'], 404);
        }

        // Kiểm tra quyền sở hữu thông báo: người dùng chỉ được đánh dấu thông báo của chính mình hoặc thông báo chung của vai trò mình
        if ($notification->user_id !== null && (!$user || (int)$notification->user_id !== (int)$user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền thao tác trên thông báo này.'
            ], 403);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Đã đánh dấu đã đọc.',
            'notification_id' => $notification->id
        ]);
    }

    /**
     * Đánh dấu tất cả thông báo của người dùng/vai trò là đã đọc
     */
    public function markAllAsRead(Request $request)
    {
        $user = Auth::user();

        Notification::forUser($user)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã đánh dấu tất cả thông báo là đã đọc.'
        ]);
    }
}
