<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Booking;
use App\Models\Order;
use App\Models\ServiceCall;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class NotificationService
{
    /**
     * Tạo thông báo mới trong database
     */
    public static function send(array $data)
    {
        return Notification::create([
            'user_id' => $data['user_id'] ?? null,
            'role'    => $data['role'] ?? null,
            'type'    => $data['type'] ?? 'system',
            'title'   => $data['title'] ?? 'Thông báo mới',
            'message' => $data['message'] ?? '',
            'url'     => $data['url'] ?? null,
            'icon'    => $data['icon'] ?? 'fa-bell',
            'color'   => $data['color'] ?? '#F47716',
            'data'    => $data['data'] ?? null,
            'is_read' => false,
        ]);
    }

    /**
     * Bắn thông báo khi có Đặt bàn mới
     */
    public static function notifyNewBooking(Booking $booking)
    {
        $dateFormatted = Carbon::parse($booking->booking_date)->format('d/m/Y');
        $time = $booking->booking_time;

        // 1. Thông báo cho Admin & Nhân viên
        self::send([
            'role'    => 'employee', // Nhân viên & Admin đều xem được
            'type'    => 'booking',
            'title'   => "Đặt bàn mới: {$booking->name} ({$booking->number_of_people} khách)",
            'message' => "Khách {$booking->name} đặt bàn {$booking->number_of_people} người vào lúc {$time} ngày {$dateFormatted}." . ($booking->note ? " Ghi chú: {$booking->note}" : ""),
            'url'     => route('admin.booking.detail', $booking->id),
            'icon'    => 'fa-calendar-check',
            'color'   => '#F47716',
            'data'    => ['booking_id' => $booking->id, 'phone' => $booking->phone],
        ]);

        // 2. Tìm tài khoản khách hàng tương ứng (theo email hoặc phone hoặc Auth)
        $customer = null;
        if (Auth::check()) {
            $customer = Auth::user();
        } else {
            $customer = User::where('email', $booking->email)
                ->orWhere('phone', $booking->phone)
                ->first();
        }

        if ($customer) {
            self::send([
                'user_id' => $customer->id,
                'role'    => 'customer',
                'type'    => 'booking',
                'title'   => "Yêu cầu đặt bàn của bạn đã gửi thành công!",
                'message' => "Nhà hàng CIVILIZE đã tiếp nhận yêu cầu đặt bàn {$booking->number_of_people} khách vào lúc {$time} ngày {$dateFormatted}. Nhân viên sẽ sớm liên hệ xác nhận giữ bàn cho bạn.",
                'url'     => route('site.booking'),
                'icon'    => 'fa-calendar-days',
                'color'   => '#C25E00',
                'data'    => ['booking_id' => $booking->id],
            ]);
        }
    }

    /**
     * Bắn thông báo khi Admin / Nhân viên cập nhật trạng thái Đặt bàn (Xác nhận / Hủy)
     */
    public static function notifyBookingStatusUpdated(Booking $booking)
    {
        $dateFormatted = Carbon::parse($booking->booking_date)->format('d/m/Y');
        $time = $booking->booking_time;

        // Tìm khách hàng
        $customer = User::where('email', $booking->email)
            ->orWhere('phone', $booking->phone)
            ->first();

        if ($booking->status == 2) { // Đã xác nhận
            // Thông báo cho khách hàng
            if ($customer) {
                self::send([
                    'user_id' => $customer->id,
                    'role'    => 'customer',
                    'type'    => 'booking',
                    'title'   => "Đặt bàn của bạn đã được xác nhận!",
                    'message' => "Nhà hàng CIVILIZE đã giữ bàn cho bạn ({$booking->number_of_people} khách) vào lúc {$time} ngày {$dateFormatted}. Chúc quý khách có bữa ăn ngon miệng!",
                    'url'     => route('site.booking'),
                    'icon'    => 'fa-circle-check',
                    'color'   => '#10B981',
                    'data'    => ['booking_id' => $booking->id, 'status' => 'confirmed'],
                ]);
            }
        } elseif ($booking->status == 4 || $booking->status == 3) { // Đã hủy
            if ($customer) {
                self::send([
                    'user_id' => $customer->id,
                    'role'    => 'customer',
                    'type'    => 'booking',
                    'title'   => "Đặt bàn #BK-{$booking->id} đã bị hủy",
                    'message' => "Yêu cầu đặt bàn lúc {$time} ngày {$dateFormatted} của bạn đã được hủy. Mọi thắc mắc xin vui lòng liên hệ hotline nhà hàng.",
                    'url'     => route('site.booking'),
                    'icon'    => 'fa-circle-xmark',
                    'color'   => '#EF4444',
                    'data'    => ['booking_id' => $booking->id, 'status' => 'cancelled'],
                ]);
            }
        }
    }

    /**
     * Bắn thông báo khi khách Rung chuông gọi phục vụ tại bàn
     */
    public static function notifyServiceCall(ServiceCall $call)
    {
        $tableName = $call->table ? $call->table->name : 'Bàn ăn';
        $floorName = ($call->table && $call->table->floor) ? $call->table->floor->name : null;
        $location = $floorName ? "{$tableName} ({$floorName})" : $tableName;

        self::send([
            'role'    => 'employee',
            'type'    => 'service_call',
            'title'   => "{$location}: {$call->call_type_label}",
            'message' => $call->note ? "Yêu cầu: {$call->note}" : "Khách tại {$location} đang gọi nhân viên hỗ trợ.",
            'url'     => $call->table_id ? route('admin.table-order.order', $call->table_id) : route('admin.table-order.index'),
            'icon'    => 'fa-bell-concierge',
            'color'   => '#F59E0B',
            'data'    => ['call_id' => $call->id, 'table_id' => $call->table_id],
        ]);
    }

    /**
     * Bắn thông báo khi có Đơn hàng mới hoặc bàn vừa gọi thêm món
     */
    public static function notifyNewOrder(Order $order)
    {
        $isTable = !empty($order->table_id);
        $tableName = $order->table ? $order->table->name : "Bàn #{$order->table_id}";

        if ($isTable) {
            // Thông báo gọi món tại bàn cho Nhân viên & Bếp
            self::send([
                'role'    => 'employee',
                'type'    => 'order',
                'title'   => "{$tableName} vừa gọi món mới",
                'message' => "Khách tại {$tableName} vừa gửi lượt gọi món vào bếp chế biến.",
                'url'     => route('admin.table-order.order', $order->table_id),
                'icon'    => 'fa-utensils',
                'color'   => '#F97316',
                'data'    => ['order_id' => $order->id, 'table_id' => $order->table_id],
            ]);

            // Thông báo cho khách hàng nếu có tài khoản
            if ($order->user_id) {
                self::send([
                    'user_id' => $order->user_id,
                    'role'    => 'customer',
                    'type'    => 'order',
                    'title'   => "Bếp đã nhận món tại {$tableName}",
                    'message' => "Các món ăn của quý khách đã được chuyển thẳng tới bếp nhà hàng để chế biến nóng hổi.",
                    'url'     => route('site.table.bill', $order->table_id),
                    'icon'    => 'fa-fire-burner',
                    'color'   => '#C25E00',
                    'data'    => ['order_id' => $order->id, 'table_id' => $order->table_id],
                ]);
            }
        } else {
            // Đơn giao hàng
            self::send([
                'role'    => 'employee',
                'type'    => 'order',
                'title'   => "Đơn giao hàng mới #{$order->id}",
                'message' => "Khách {$order->name} vừa đặt đơn giao trị giá " . number_format($order->total_price, 0, ',', '.') . "₫.",
                'url'     => route('admin.order.orderdetail', $order->id),
                'icon'    => 'fa-box',
                'color'   => '#3B82F6',
                'data'    => ['order_id' => $order->id],
            ]);

            if ($order->user_id) {
                self::send([
                    'user_id' => $order->user_id,
                    'role'    => 'customer',
                    'type'    => 'order',
                    'title'   => "Đơn hàng #{$order->id} đặt thành công!",
                    'message' => "Nhà hàng CIVILIZE đang chuẩn bị món ăn cho bạn. Tổng tiền: " . number_format($order->total_price, 0, ',', '.') . "₫.",
                    'url'     => route('site.orders'),
                    'icon'    => 'fa-bag-shopping',
                    'color'   => '#10B981',
                    'data'    => ['order_id' => $order->id],
                ]);
            }
        }
    }

    /**
     * Bắn thông báo khi nhận được thanh toán VietQR thành công cho cả 3 vai trò:
     * 1. Quản trị viên (Admin)
     * 2. Nhân viên phục vụ / Thu ngân (Employee)
     * 3. Khách hàng đặt đơn / thanh toán bàn (Customer)
     */
    public static function notifyPaymentSuccess(Order $order, float $amount, string $referenceCode)
    {
        $amountFormatted = number_format($amount, 0, ',', '.') . '₫';
        $isTable = !empty($order->table_id);
        $orderContext = $isTable 
            ? ($order->table ? $order->table->name : "Bàn #{$order->table_id}") 
            : "Đơn hàng #{$order->id}";

        // 1. Vai trò Quản trị viên (Admin)
        self::send([
            'role'    => 'admin',
            'type'    => 'payment',
            'title'   => "{$orderContext} đã thanh toán VietQR ({$amountFormatted})",
            'message' => !empty($referenceCode) ? "Mã giao dịch: {$referenceCode}" : "Thanh toán VietQR thành công.",
            'url'     => $isTable ? route('admin.table-order.order', $order->table_id) : route('admin.order.orderdetail', $order->id),
            'icon'    => 'fa-circle-check',
            'color'   => '#10B981',
            'data'    => [
                'order_id'       => $order->id,
                'table_id'       => $order->table_id,
                'amount'         => $amount,
                'reference_code' => $referenceCode,
                'target_role'    => 'admin'
            ],
        ]);

        // 2. Vai trò Nhân viên (Employee / Phục vụ / Thu ngân)
        self::send([
            'role'    => 'employee',
            'type'    => 'payment',
            'title'   => "{$orderContext} đã thanh toán VietQR ({$amountFormatted})",
            'message' => !empty($referenceCode) ? "Mã giao dịch: {$referenceCode}" : "Thanh toán VietQR thành công.",
            'url'     => $isTable ? route('admin.table-order.order', $order->table_id) : route('admin.order.orderdetail', $order->id),
            'icon'    => 'fa-receipt',
            'color'   => '#3B82F6',
            'data'    => [
                'order_id'       => $order->id,
                'table_id'       => $order->table_id,
                'amount'         => $amount,
                'reference_code' => $referenceCode,
                'target_role'    => 'employee'
            ],
        ]);

        // 3. Vai trò Khách hàng (Customer)
        $customer = null;
        if (!empty($order->user_id)) {
            $customer = User::find($order->user_id);
        }
        if (!$customer && Auth::check()) {
            $customer = Auth::user();
        }
        if (!$customer && !empty($order->phone)) {
            $customer = User::where('phone', $order->phone)->first();
        }
        if (!$customer && !empty($order->email)) {
            $customer = User::where('email', $order->email)->first();
        }

        // Chỉ gửi thông báo cho khách hàng nếu đơn hàng này gắn liền với tài khoản khách hàng cụ thể
        if ($customer) {
            self::send([
                'user_id' => $customer->id,
                'role'    => 'customer',
                'type'    => 'payment',
                'title'   => "Thanh toán thành công: {$amountFormatted}!",
                'message' => "Nhà hàng CIVILIZE đã xác nhận thanh toán cho {$orderContext} qua VietQR. Cảm ơn bạn đã lựa chọn dùng bữa tại CIVILIZE!",
                'url'     => $isTable ? route('site.table.bill', $order->table_id) : route('site.orders'),
                'icon'    => 'fa-circle-check',
                'color'   => '#10B981',
                'data'    => [
                    'order_id'       => $order->id,
                    'table_id'       => $order->table_id,
                    'amount'         => $amount,
                    'reference_code' => $referenceCode,
                    'target_role'    => 'customer'
                ],
            ]);
        }
    }
}
