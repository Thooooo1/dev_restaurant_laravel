<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Table;
use App\Models\Order;
use App\Models\Orderdetail;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Services\VietQRService;

class TableOrderController extends Controller
{
    /**
     * Khách quét mã QR tại bàn (URL: /ban/{id}/goi-mon)
     */
    public function scan($id)
    {
        // 1. Kiểm tra bàn tồn tại
        $table = Table::with('floor')->find($id);
        if (!$table) {
            return redirect()->route('site.home')->with('error', 'Bàn ăn không tồn tại hoặc đã bị xóa.');
        }

        // 2. Tìm đơn hàng đang hoạt động của bàn này (status = 0: chờ xử lý, 1: đang phục vụ)
        $activeOrder = Order::where('table_id', $id)
            ->whereNull('deleted_at')
            ->whereIn('status', [0, 1])
            ->with(['orderDetails.product', 'table.floor'])
            ->latest('id')
            ->first();

        // 3. Khởi tạo session bàn ăn
        session([
            'table_id' => $id,
            'table_name' => $table->name,
            'is_table_order' => true,
        ]);

        if ($activeOrder) {
            session(['table_order_id' => $activeOrder->id]);
        }

        return view('frontend.table-scan', compact('table', 'activeOrder'));
    }

    /**
     * Bắt đầu phiên gọi món tại bàn (Nhập SĐT để tích điểm hoặc gọi món ngay không cần SĐT)
     */
    public function startOrder(Request $request, $id)
    {
        $table = Table::find($id);
        if (!$table) {
            return redirect()->route('site.home')->with('error', 'Bàn không tồn tại.');
        }

        $phone = $request->input('phone');
        $customerName = $request->input('name');

        if ($phone) {
            $phone = preg_replace('/[^0-9]/', '', (string)$phone);
            session(['customer_phone' => $phone]);

            // Tự động đăng nhập nếu đã có tài khoản thành viên (CHỈ DÀNH CHO KHÁCH HÀNG, TUYỆT ĐỐI KHÔNG TỰ ĐĂNG NHẬP ADMIN/EMPLOYEE)
            $user = User::where('phone', $phone)->first();
            if ($user && !Auth::check()) {
                if ($user->roles === 'customer') {
                    Auth::login($user);
                }
            }
        }

        if ($customerName) {
            session(['customer_name' => trim($customerName)]);
        }

        session([
            'table_id' => $id,
            'table_name' => $table->name,
            'is_table_order' => true,
        ]);

        // Kiểm tra xem bàn đã có đơn hàng nào chưa
        $activeOrder = Order::where('table_id', $id)
            ->whereNull('deleted_at')
            ->whereIn('status', [0, 1])
            ->first();

        if ($activeOrder) {
            session(['table_order_id' => $activeOrder->id]);
            if ($phone && empty($activeOrder->phone)) {
                $activeOrder->phone = $phone;
                $activeOrder->save();
            }
        }

        return redirect()->route('site.product.gridview')->with('success', 'Chào mừng quý khách đến với ' . $table->name . '! Hãy chọn món và thưởng thức nhé.');
    }

    /**
     * Xác thực số điện thoại tìm đơn (tương thích ngược với hệ thống cũ)
     */
    public function verify(Request $request, $id)
    {
        return $this->startOrder($request, $id);
    }

    /**
     * Màn hình Thanh Toán VietQR Trực Tiếp Tại Bàn (/ban/{id}/thanh-toan-qr)
     */
    public function paymentQr($id, VietQRService $vietQRService)
    {
        $table = Table::with('floor')->find($id);
        if (!$table) {
            return redirect()->route('site.home')->with('error', 'Bàn không tồn tại.');
        }

        // Lấy hoặc đồng bộ đơn hàng duy nhất của bàn
        $cartKey = Auth::check() ? 'cart_' . Auth::id() : 'cart_guest_' . session()->getId();
        $cart = session()->get($cartKey, []);

        if (!empty($cart)) {
            $items = [];
            foreach ($cart as $productId => $details) {
                $items[] = [
                    'product_id' => $productId,
                    'qty' => (int)$details['qty'],
                    'price' => (float)$details['price'],
                ];
            }
            $order = \App\Services\TableOrderService::addItemsToTableOrder($id, $items);
            session(['table_order_id' => $order->id]);
            \App\Http\Controllers\frontend\CartController::clearCartSession();
        } else {
            $order = \App\Services\TableOrderService::getActiveOrder($id);
            if ($order) {
                session(['table_order_id' => $order->id]);
            }
        }

        if (!$order) {
            return redirect()->route('site.product.gridview')->with('error', 'Chưa có món ăn nào để thanh toán. Mời quý khách chọn món trước.');
        }

        // Tải chi tiết đơn hàng
        $orderDetails = Orderdetail::where('order_id', $order->id)
            ->with('product')
            ->get();

        if ($orderDetails->isEmpty()) {
            return redirect()->route('site.product.gridview')->with('error', 'Hóa đơn của bàn đang trống. Mời quý khách chọn món ăn.');
        }

        $subtotal = \App\Services\PricingService::getOrderSubtotal($order->id);
        $finalAmount = \App\Services\PricingService::recalculateOrderTotal($order->id, true);
        $discountAmount = max(0, $subtotal - $finalAmount);

        $order->total_price = $finalAmount;
        $order->save();

        // Cập nhật trạng thái bàn Có khách
        if ($table->status != 1) {
            $table->status = 1;
            $table->save();
        }

        // Sinh mã VietQR chuyển khoản
        $memo = "BAN{$id} DH{$order->id}";
        $qrUrl = $vietQRService->generateQrUrl($finalAmount, $memo);
        $bankInfo = $vietQRService->getBankInfo();

        return view('frontend.table-payment-qr', compact(
            'table',
            'order',
            'orderDetails',
            'subtotal',
            'discountAmount',
            'finalAmount',
            'qrUrl',
            'bankInfo',
            'memo'
        ));
    }

    /**
     * Khách hàng xác nhận đã chuyển khoản thành công tại bàn
     */
    public function confirmPayment(Request $request, $id)
    {
        $table = Table::find($id);
        $orderId = $request->input('order_id', session('table_order_id'));

        $order = null;
        if ($orderId) {
            $order = Order::where('id', $orderId)->first();
        }
        if (!$order) {
            $order = Order::where('table_id', $id)
                ->whereNull('deleted_at')
                ->whereIn('status', [0, 1])
                ->latest('id')
                ->first();
        }

        if (!$order) {
            return redirect()->route('site.home')->with('error', 'Không tìm thấy hóa đơn cần thanh toán.');
        }

        $paymentMethod = $request->input('payment_method', 'VietQR');
        $actualTotal = (float) \App\Services\PricingService::recalculateOrderTotal($order->id, true);

        // Cập nhật đơn hàng thành Đã thanh toán
        $order->payment_status = Order::PAYMENT_PAID;
        $order->paid_at = now();
        $order->revenue_recognized_at = now();
        $order->payment_method = ($paymentMethod === 'VietQR' || $paymentMethod == 2) ? 2 : $paymentMethod;
        $order->total_price = $actualTotal > 0 ? $actualTotal : $order->total_price;
        $order->has_new_items = false;
        $order->save();

        // Ghi nhận nhật ký PaymentTransaction
        try {
            \App\Models\PaymentTransaction::create([
                'order_id' => $order->id,
                'reference_code' => 'VIETQR_TABLE_' . $order->id . '_' . time(),
                'amount' => (int) $order->total_price,
                'content' => 'Khách hàng xác nhận thanh toán VietQR tại bàn',
                'gateway' => 'vietqr',
                'status' => \App\Models\PaymentTransaction::STATUS_SUCCESS,
                'raw_payload' => json_encode([
                    'table_id' => $id,
                    'confirmed_at' => (string)now(),
                ]),
            ]);
        } catch (\Throwable $e) {}

        // Đánh dấu hoàn tất yêu cầu tính tiền của bàn này nếu có
        try {
            \App\Models\ServiceCall::where('table_id', $order->table_id)
                ->where('call_type', 'bill')
                ->where('status', \App\Models\ServiceCall::STATUS_PENDING)
                ->update([
                    'status' => \App\Models\ServiceCall::STATUS_HANDLED,
                    'handled_at' => now(),
                ]);
        } catch (\Throwable $e) {}

        try {
            $tableName = $table ? $table->name : "Bàn #{$id}";
            $totalFmt = number_format($order->total_price, 0, ',', '.') . '₫';
            \App\Services\NotificationService::send([
                'role' => 'employee',
                'type' => 'payment',
                'title' => "{$tableName} đã thanh toán VietQR ({$totalFmt})",
                'message' => "Hóa đơn #{$order->id} đã được khách xác nhận thanh toán qua VietQR.",
                'url' => route('admin.table-order.payment', $id),
                'icon' => 'fa-credit-card',
                'color' => '#10B981',
                'data' => [
                    'order_id' => $order->id,
                    'table_id' => $id,
                    'is_paid' => true,
                    'is_vietqr' => true
                ]
            ]);
        } catch (\Exception $e) {}

        // Xóa triệt để giỏ hàng và reset đơn hàng đã thanh toán
        \App\Http\Controllers\frontend\CartController::clearCartSession();
        session()->forget('table_order_id');
        session()->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Xác nhận thanh toán thành công!',
                'redirect_url' => route('site.table.bill', $id)
            ]);
        }

        return redirect()->route('site.table.bill', $id);
    }

    /**
     * Xem hóa đơn điện tử trực tiếp của bàn (/ban/{id}/hoa-don)
     */
    public function viewBill($id, VietQRService $vietQRService)
    {
        $table = Table::with('floor')->find($id);
        if (!$table) {
            return redirect()->route('site.home')->with('error', 'Bàn không tồn tại.');
        }

        $order = Order::where('table_id', $id)
            ->whereNull('deleted_at')
            ->whereIn('status', [0, 1, 2])
            ->with(['orderDetails.product', 'table.floor'])
            ->latest('id')
            ->first();

        if (!$order) {
            return redirect()->route('site.table.scan', $id)->with('info', 'Bàn hiện chưa có đơn hàng đang hoạt động.');
        }

        $orderDetails = $order->orderDetails;
        $subtotal = \App\Services\PricingService::getOrderSubtotal($order->id);
        $finalAmount = \App\Services\PricingService::recalculateOrderTotal($order->id, true);
        $discountAmount = max(0, $subtotal - $finalAmount);

        $memo = "BAN{$id} DH{$order->id}";
        $qrUrl = $vietQRService->generateQrUrl($finalAmount, $memo);
        $bankInfo = $vietQRService->getBankInfo();

        return view('frontend.table-bill', compact(
            'table',
            'order',
            'orderDetails',
            'subtotal',
            'discountAmount',
            'finalAmount',
            'qrUrl',
            'bankInfo',
            'memo'
        ));
    }

    /**
     * Kết thúc phiên gọi món tại bàn
     */
    public function endTableOrder()
    {
        $tableId = session('table_id');
        if ($tableId) {
            $order = Order::where('table_id', $tableId)
                ->whereNull('deleted_at')
                ->whereIn('status', [0, 1])
                ->latest('id')
                ->first();
            if ($order && ($order->payment_status === Order::PAYMENT_PAID || $order->isPaid())) {
                $order->status = \App\Enums\OrderStatus::COMPLETED;
                $order->save();
                Table::where('id', $tableId)->update(['status' => 0, 'locked_order_id' => null]);
                Table::where('locked_order_id', $order->id)->update(['locked_order_id' => null]);
            }
        }

        \App\Http\Controllers\frontend\CartController::clearCartSession();
        session()->forget([
            'table_id',
            'table_order_id',
            'table_name',
            'table_floor_name',
            'is_table_order',
            'dining_mode',
            'customer_phone',
            'customer_name'
        ]);
        session()->save();

        return redirect()->route('site.home')->with('success', 'Đã kết thúc phiên gọi món tại bàn.');
    }
}
