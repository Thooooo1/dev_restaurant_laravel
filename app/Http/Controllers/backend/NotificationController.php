<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServiceCall;
use App\Models\Order;
use App\Models\Booking;
use App\Models\Table;
use App\Models\Notification as SystemNotification;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class NotificationController extends Controller
{
    /**
     * Lấy dữ liệu thông báo thời gian thực cho chuông thông báo Header
     */
    public function getLatestNotifications(Request $request)
    {
        $user = Auth::user();

        // 1. Tự động đồng bộ các đơn hàng đã thanh toán thành công gần đây vào bảng notifications nếu chưa có
        try {
            $recentPaidOrders = Order::whereIn('status', [1, 2])
                ->whereNotNull('payment_method')
                ->where('updated_at', '>=', Carbon::now()->subDays(14))
                ->get();

            foreach ($recentPaidOrders as $order) {
                $hasNoti = SystemNotification::where('type', 'payment')
                    ->where(function ($q) use ($order) {
                        $q->where('data->order_id', $order->id)
                          ->orWhere('title', 'like', "%#{$order->id}%")
                          ->orWhere('message', 'like', "%#{$order->id}%");
                    })->exists();

                if (!$hasNoti) {
                    $tableName = $order->table ? $order->table->name : ($order->table_id ? "Bàn #{$order->table_id}" : "Đơn online #{$order->id}");
                    $amountFormatted = number_format($order->total_price, 0, ',', '.') . '₫';
                    $pm = (string) $order->payment_method;
                    $isVietQr = in_array(strtolower($pm), ['vietqr', '2', 'chuyển khoản', 'bank_transfer']);
                    $pmText = $isVietQr ? 'Chuyển khoản VietQR' : ($pm === '3' ? 'Quẹt thẻ POS' : 'Tiền mặt');

                    SystemNotification::create([
                        'role'       => 'employee',
                        'type'       => 'payment',
                        'title'      => "{$tableName} đã thanh toán {$pmText} ({$amountFormatted})",
                        'message'    => "Hóa đơn {$tableName} (#{$order->id}) số tiền {$amountFormatted} đã được thanh toán thành công qua {$pmText}.",
                        'url'        => $order->table_id ? route('admin.table-order.payment', $order->table_id) : route('admin.order.orderdetail', $order->id),
                        'icon'       => 'fa-receipt',
                        'color'      => '#10B981',
                        'data'       => ['order_id' => $order->id, 'table_id' => $order->table_id, 'is_vietqr' => $isVietQr],
                        'is_read'    => true,
                        'created_at' => $order->updated_at,
                        'updated_at' => $order->updated_at,
                    ]);
                }
            }
        } catch (\Exception $e) {}

        // 2. Lấy danh sách thông báo thanh toán & chuyển khoản thành công
        $viewedPaymentIds = session('admin_viewed_payment_ids', []);
        if (!is_array($viewedPaymentIds)) $viewedPaymentIds = [];

        $paymentNotifications = SystemNotification::forUser($user)
            ->where('type', 'payment')
            ->orderBy('created_at', 'DESC')
            ->take(20)
            ->get();

        $paymentCount = SystemNotification::forUser($user)
            ->where('type', 'payment')
            ->where('is_read', false)
            ->whereNotIn('id', $viewedPaymentIds)
            ->count();

        // 3. Lấy danh sách gọi phục vụ đang chờ (Pending)
        $viewedCallIds = session('admin_viewed_call_ids', []);
        if (!is_array($viewedCallIds)) $viewedCallIds = [];

        $serviceCalls = ServiceCall::with(['table.floor', 'order'])
            ->where('status', ServiceCall::STATUS_PENDING)
            ->orderBy('created_at', 'DESC')
            ->take(20)
            ->get();

        // 4. Lấy đơn hàng tại bàn có món mới (has_new_items) hoặc đơn giao hàng mới trong 24h
        $newOrders = Order::whereNull('deleted_at')
            ->whereIn('status', [0, 1])
            ->where('has_new_items', true)
            ->with(['table.floor', 'orderDetails.product'])
            ->orderBy('updated_at', 'DESC')
            ->take(10)
            ->get();

        // 5. Lấy đặt bàn mới đang chờ xác nhận (status 1: Chờ xử lý)
        $viewedBookingIds = session('admin_viewed_booking_ids', []);
        if (!is_array($viewedBookingIds)) $viewedBookingIds = [];

        $newBookings = Booking::whereIn('status', [0, 1])
            ->orderBy('created_at', 'DESC')
            ->take(10)
            ->get();

        // Đếm số lượng chưa xem (thông báo xem rồi sẽ giảm bớt hoặc hết luôn)
        $serviceCallCount = ServiceCall::where('status', ServiceCall::STATUS_PENDING)
            ->whereNotIn('id', $viewedCallIds)
            ->count();
        $newOrderCount = $newOrders->count();
        $newBookingCount = Booking::whereIn('status', [0, 1])
            ->whereNotIn('id', $viewedBookingIds)
            ->count();
        $totalCount = $paymentCount + $serviceCallCount + $newOrderCount + $newBookingCount;

        // Biến đổi paymentNotifications sang format hiển thị
        $formattedPayments = $paymentNotifications->map(function ($p) {
            $isVietQr = (stripos($p->title, 'VietQR') !== false || stripos($p->message, 'VietQR') !== false || stripos($p->title, 'chuyển khoản') !== false);
            return [
                'id' => $p->id,
                'title' => $p->title,
                'message' => $p->message,
                'url' => $p->url ?: 'javascript:void(0)',
                'is_read' => (bool)$p->is_read,
                'time_ago' => $p->created_at ? $p->created_at->locale('vi')->diffForHumans() : 'Vừa xong',
                'created_at' => $p->created_at ? $p->created_at->format('d/m/Y H:i') : '',
                'is_vietqr' => $isVietQr,
                'badge_label' => $isVietQr ? 'Chuyển khoản VietQR' : 'Thanh toán thành công',
            ];
        });

        // Biến đổi serviceCalls sang format hiển thị
        $formattedCalls = $serviceCalls->map(function ($call) {
            $tableName = $call->table ? $call->table->name : ($call->order_id ? "Đơn online #{$call->order_id}" : ($call->customer_name ?: 'Khách vãng lai'));
            $floorName = ($call->table && $call->table->floor) ? $call->table->floor->name : null;
            $typeInfo = $call->type_info;

            $posUrl = $call->table_id 
                ? route('admin.table-order.order', $call->table_id) 
                : ($call->order_id ? route('admin.order.orderdetail', $call->order_id) : route('admin.table-order.index'));

            return [
                'id' => $call->id,
                'table_id' => $call->table_id,
                'table_name' => $tableName,
                'floor_name' => $floorName,
                'full_table_name' => $floorName ? "{$tableName} ({$floorName})" : $tableName,
                'type' => $call->call_type,
                'type_label' => $call->call_type_label ?: $typeInfo['label'],
                'icon' => $typeInfo['icon'],
                'color' => $typeInfo['color'],
                'note' => $call->note,
                'customer_name' => $call->customer_name,
                'created_at' => $call->created_at->toIso8601String(),
                'time_ago' => $call->created_at->locale('vi')->diffForHumans(),
                'pos_url' => $posUrl,
            ];
        });

        // Biến đổi newOrders sang format hiển thị
        $formattedOrders = $newOrders->map(function ($order) {
            $tableName = $order->table ? $order->table->name : ($order->orderStyle == 2 ? 'Đơn giao hàng' : 'Đơn mang về');
            $floorName = ($order->table && $order->table->floor) ? $order->table->floor->name : null;

            return [
                'id' => $order->id,
                'table_id' => $order->table_id,
                'order_style' => $order->orderStyle,
                'title' => $order->table ? "Bàn {$tableName} vừa gọi thêm món" : "Đơn hàng mới #{$order->id}",
                'subtitle' => $floorName ? "Vị trí: {$floorName}" : ($order->name ? "Khách: {$order->name}" : ''),
                'total_price' => number_format($order->total_price, 0, ',', '.') . 'đ',
                'time_ago' => $order->updated_at->locale('vi')->diffForHumans(),
                'url' => $order->table_id ? route('admin.table-order.order', $order->table_id) : route('admin.order.orderdetail', $order->id),
            ];
        });

        // Biến đổi newBookings sang format hiển thị
        $formattedBookings = $newBookings->map(function ($b) {
            return [
                'id' => $b->id,
                'customer_name' => $b->name,
                'phone' => $b->phone,
                'people' => $b->number_of_people,
                'booking_date' => Carbon::parse($b->booking_date)->format('d/m/Y'),
                'booking_time' => $b->booking_time,
                'time_ago' => $b->created_at->locale('vi')->diffForHumans(),
                'url' => route('admin.booking.detail', $b->id),
            ];
        });

        return response()->json([
            'success' => true,
            'total_count' => $totalCount,
            'payment_count' => $paymentCount,
            'service_call_count' => $serviceCallCount,
            'new_order_count' => $newOrderCount,
            'new_booking_count' => $newBookingCount,
            'payments' => $formattedPayments,
            'service_calls' => $formattedCalls,
            'new_orders' => $formattedOrders,
            'new_bookings' => $formattedBookings,
        ]);
    }

    /**
     * Xác nhận đã phục vụ bàn (Hoàn thành yêu cầu gọi nhân viên)
     */
    public function completeServiceCall(Request $request, $id)
    {
        $call = ServiceCall::find($id);
        if (!$call) {
            return response()->json(['success' => false, 'message' => 'Yêu cầu không tồn tại.'], 404);
        }

        $call->status = ServiceCall::STATUS_COMPLETED;
        $call->handled_by = Auth::id() ?? 1;
        $call->handled_at = Carbon::now();
        $call->save();

        return response()->json([
            'success' => true,
            'message' => 'Đã xác nhận hoàn thành phục vụ!',
            'call_id' => $call->id
        ]);
    }

    /**
     * Hủy yêu cầu gọi phục vụ
     */
    public function cancelServiceCall(Request $request, $id)
    {
        $call = ServiceCall::find($id);
        if (!$call) {
            return response()->json(['success' => false, 'message' => 'Yêu cầu không tồn tại.'], 404);
        }

        $call->status = ServiceCall::STATUS_CANCELLED;
        $call->handled_by = Auth::id() ?? 1;
        $call->handled_at = Carbon::now();
        $call->save();

        return response()->json([
            'success' => true,
            'message' => 'Đã hủy yêu cầu phục vụ.',
            'call_id' => $call->id
        ]);
    }

    /**
     * Hoàn thành tất cả yêu cầu phục vụ của một bàn
     */
    public function completeTableCalls(Request $request, $tableId)
    {
        ServiceCall::where('table_id', $tableId)
            ->where('status', ServiceCall::STATUS_PENDING)
            ->update([
                'status' => ServiceCall::STATUS_COMPLETED,
                'handled_by' => Auth::id() ?? 1,
                'handled_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã xử lý tất cả yêu cầu của bàn.'
        ]);
    }

    /**
     * Đánh dấu thông báo là đã đọc
     */
    public function markAsRead(Request $request, $id)
    {
        $noti = SystemNotification::find($id);
        if ($noti) {
            $noti->markAsRead();
        }
        return response()->json(['success' => true]);
    }

    /**
     * Đánh dấu tất cả thông báo hệ thống là đã đọc / đã xem
     */
    public function markAllAsRead(Request $request)
    {
        $user = Auth::user();
        SystemNotification::forUser($user)->where('is_read', false)->update([
            'is_read' => true,
            'read_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Đánh dấu tất cả thông báo thanh toán hiện tại là đã xem
        $pendingPaymentIds = SystemNotification::forUser($user)->where('type', 'payment')->pluck('id')->toArray();
        $viewedPayments = session('admin_viewed_payment_ids', []);
        if (!is_array($viewedPayments)) $viewedPayments = [];
        session(['admin_viewed_payment_ids' => array_values(array_unique(array_merge($viewedPayments, $pendingPaymentIds)))]);

        // Đánh dấu tất cả đặt bàn hiện tại là đã xem
        $pendingBookingIds = Booking::whereIn('status', [0, 1])->pluck('id')->toArray();
        $viewed = session('admin_viewed_booking_ids', []);
        if (!is_array($viewed)) $viewed = [];
        session(['admin_viewed_booking_ids' => array_values(array_unique(array_merge($viewed, $pendingBookingIds)))]);

        // Đánh dấu tất cả yêu cầu phục vụ hiện tại là đã xem
        $pendingCallIds = ServiceCall::where('status', ServiceCall::STATUS_PENDING)->pluck('id')->toArray();
        $viewedCalls = session('admin_viewed_call_ids', []);
        if (!is_array($viewedCalls)) $viewedCalls = [];
        session(['admin_viewed_call_ids' => array_values(array_unique(array_merge($viewedCalls, $pendingCallIds)))]);

        // Đánh dấu tất cả đơn món mới là đã xem
        Order::where('has_new_items', true)->update(['has_new_items' => false]);

        return response()->json([
            'success' => true, 
            'message' => 'Đã đánh dấu tất cả thông báo là đã xem.'
        ]);
    }

    /**
     * Đánh dấu 1 đặt bàn cụ thể là đã xem
     */
    public function markBookingViewed(Request $request, $id)
    {
        $viewed = session('admin_viewed_booking_ids', []);
        if (!is_array($viewed)) $viewed = [];
        $viewed[] = (int)$id;
        session(['admin_viewed_booking_ids' => array_values(array_unique($viewed))]);

        return response()->json(['success' => true]);
    }
}
