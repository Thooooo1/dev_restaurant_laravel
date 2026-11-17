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
    public function scan(Request $request, $id)
    {
        // 1. Kiểm tra bàn tồn tại
        $table = Table::with('floor')->find($id);
        if (!$table) {
            return redirect()->route('site.home')->with('error', 'Bàn ăn không tồn tại hoặc đã bị xóa.');
        }

        $token = $request->query('token');
        $isStaffOrAdmin = Auth::check() && (Auth::user()->isAdmin() || Auth::user()->isEmployee());
        $hasValidToken = $table->isValidQrToken($token);

        // V4-P1-36: Chỉ cấp quyền phiên bàn và tải order nếu quét đúng token QR hoặc là nhân viên/quản trị
        if ($hasValidToken || $isStaffOrAdmin) {
            session([
                'table_id' => $id,
                'table_name' => $table->name,
                'is_table_order' => true,
                'table_verified_' . $id => true,
            ]);

            // 2. Tìm đơn hàng đang hoạt động của bàn này (status = 0: chờ xử lý, 1: đang phục vụ)
            $activeOrder = Order::where('table_id', $id)
                ->whereNull('deleted_at')
                ->whereIn('status', [0, 1])
                ->with(['orderDetails.product', 'table.floor'])
                ->latest('id')
                ->first();

            if ($activeOrder) {
                session(['table_order_id' => $activeOrder->id]);
            }
        } else {
            // Không có token QR hợp lệ -> Không cấp quyền truy cập phiên bàn cho khách vãng lai
            $activeOrder = null;
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

            // P0-02: Lưu số điện thoại vào phiên để tích điểm / gắn đơn hàng.
            // Tuyệt đối KHÔNG tự động đăng nhập Auth::login() để chống chiếm quyền tài khoản qua SĐT.
            $user = User::where('phone', $phone)->first();
            if ($user && empty($customerName)) {
                session(['customer_name' => $user->fullname ?? $user->username]);
            }
        }

        if ($customerName) {
            session(['customer_name' => trim($customerName)]);
        }

        $token = $request->input('token') ?: $request->query('token');
        $isStaffOrAdmin = Auth::check() && (Auth::user()->isAdmin() || Auth::user()->isEmployee());
        $isVerified = session('table_verified_' . $id) || $isStaffOrAdmin || $table->isValidQrToken($token);

        session([
            'table_id' => $id,
            'table_name' => $table->name,
            'is_table_order' => true,
        ]);

        if ($isVerified) {
            session(['table_verified_' . $id => true]);

            // V4-P1-36: Chỉ gắn table_order_id nếu phiên bàn đã được xác thực bằng token QR
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

        // P2-12: Phương thức GET thuần túy hiển thị mã QR, không tạo side effect làm thay đổi DB
        $order = \App\Services\TableOrderService::getActiveOrder($id);
        if (!$order) {
            return redirect()->route('site.product.gridview')->with('error', 'Chưa có món ăn nào để thanh toán. Mời quý khách chọn món trước.');
        }

        // V5 Invariant: Kiểm tra quyền truy cập bàn tập trung qua TableAccessService
        \App\Services\TableAccessService::assertTableAccess($table, null, 'Bạn không có quyền truy cập thanh toán của bàn này.');

        // Tải chi tiết đơn hàng
        $orderDetails = Orderdetail::where('order_id', $order->id)
            ->with('product')
            ->get();

        if ($orderDetails->isEmpty()) {
            return redirect()->route('site.product.gridview')->with('error', 'Hóa đơn của bàn đang trống. Mời quý khách chọn món ăn.');
        }

        $subtotal = \App\Services\PricingService::getOrderSubtotal($order->id);
        $finalAmount = (float)$order->total_price > 0 ? (float)$order->total_price : \App\Services\PricingService::recalculateOrderTotal($order->id, true);
        $discountAmount = max(0, $subtotal - $finalAmount);

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
        if (!$table) {
            return redirect()->route('site.home')->with('error', 'Bàn không tồn tại.');
        }

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

        // P0-04: Ràng buộc chặt chẽ Order ID với Table ID và Session
        if ((int)$order->table_id !== (int)$id) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Hóa đơn không thuộc bàn này.'], 403);
            }
            return redirect()->back()->with('error', 'Hóa đơn không thuộc bàn này.');
        }

        // V5 Invariant: TableAccessService kiểm tra chặt chẽ bàn và visit session
        if (!\App\Services\TableAccessService::authorizeTableAccess($table, $request)) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Bạn không có quyền thực hiện thao tác trên bàn này.'], 403);
            }
            abort(403, 'Bạn không có quyền thực hiện thao tác trên bàn này.');
        }

        // P0-04 Idempotency: Nếu đơn hàng ĐÃ THANH TOÁN, giữ nguyên paid_at và không tạo transaction thừa
        if ($order->payment_status === Order::PAYMENT_PAID || $order->isPaid()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'already_paid' => true,
                    'message' => 'Hóa đơn này đã được thanh toán thành công trước đó.',
                    'redirect' => route('site.table.bill', $id)
                ]);
            }
            return redirect()->route('site.table.bill', $id)->with('info', 'Hóa đơn này đã được thanh toán thành công trước đó.');
        }

        // Chặn khách tự xác nhận đã thanh toán trên môi trường production nếu ngân hàng chưa gửi webhook
        if (config('payment.mode') !== 'demo' && !$isStaffOrAdmin) {
            $hasSuccessTx = \App\Models\PaymentTransaction::where('order_id', $order->id)
                ->where('status', \App\Models\PaymentTransaction::STATUS_SUCCESS)
                ->exists();

            if (!$hasSuccessTx) {
                \App\Models\ServiceCall::firstOrCreate([
                    'table_id' => $id,
                    'call_type' => 'bill',
                    'status' => \App\Models\ServiceCall::STATUS_PENDING,
                ], [
                    'note' => 'Khách bàn ' . $id . ' báo đã chuyển khoản VietQR đơn #' . $order->id . ' - Vui lòng nhân viên kiểm tra tài khoản.',
                ]);

                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Hệ thống đang chờ ngân hàng xác nhận giao dịch hoặc nhân viên sẽ đối soát tại bàn. Vui lòng đợi trong giây lát!',
                        'pending_verification' => true,
                    ]);
                }
                return redirect()->back()->with('warning', 'Hệ thống đang chờ ngân hàng xác nhận giao dịch hoặc nhân viên sẽ đối soát tại bàn. Vui lòng đợi trong giây lát!');
            }
        }

        $paymentMethod = $request->input('payment_method', 'VietQR');
        $refCode = 'VIETQR_TABLE_' . $order->id . '_' . time();

        // Cập nhật trạng thái và ghi transaction an toàn trong DB Transaction với lockForUpdate
        \Illuminate\Support\Facades\DB::transaction(function () use ($order, $id, $paymentMethod, $refCode) {
            /** @var Order $lockedOrder */
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->first();
            if (!$lockedOrder || $lockedOrder->payment_status === Order::PAYMENT_PAID) {
                return;
            }

            $actualTotal = (float) \App\Services\PricingService::recalculateOrderTotal($lockedOrder->id, true);

            $lockedOrder->payment_status = Order::PAYMENT_PAID;
            $lockedOrder->paid_at = now();
            $lockedOrder->revenue_recognized_at = now();
            $lockedOrder->payment_method = ($paymentMethod === 'VietQR' || $paymentMethod == 2) ? 2 : $paymentMethod;
            $lockedOrder->total_price = $actualTotal > 0 ? $actualTotal : $lockedOrder->total_price;
            $lockedOrder->has_new_items = false;
            $lockedOrder->save();

            // Ghi nhận nhật ký PaymentTransaction
            try {
                \App\Models\PaymentTransaction::create([
                    'order_id' => $lockedOrder->id,
                    'reference_code' => $refCode,
                    'amount' => (int) $lockedOrder->total_price,
                    'content' => 'Khách hàng xác nhận thanh toán VietQR tại bàn',
                    'gateway' => 'vietqr',
                    'status' => \App\Models\PaymentTransaction::STATUS_SUCCESS,
                    'raw_payload' => json_encode([
                        'table_id' => $id,
                        'confirmed_at' => (string)now(),
                    ]),
                ]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('PaymentTransaction creation error: ' . $e->getMessage());
            }

            // Đánh dấu hoàn tất yêu cầu tính tiền của bàn này nếu có
            try {
                \App\Models\ServiceCall::where('table_id', $lockedOrder->table_id)
                    ->where('call_type', 'bill')
                    ->where('status', \App\Models\ServiceCall::STATUS_PENDING)
                    ->update([
                        'status' => \App\Models\ServiceCall::STATUS_HANDLED,
                        'handled_at' => now(),
                    ]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('ServiceCall update error: ' . $e->getMessage());
            }
        });

        // Báo dòng tiền thời gian thực cho Admin, Nhân viên và Khách hàng
        try {
            \App\Services\NotificationService::notifyPaymentSuccess($order, (float)$order->total_price, $refCode, 'VietQR');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('NotificationService error: ' . $e->getMessage());
        }

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

        // V5 Invariant: TableAccessService kiểm tra chặt chẽ bàn và visit session
        \App\Services\TableAccessService::assertTableAccess($table, null, 'Bạn không có quyền xem hóa đơn của bàn này.');

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
     * Kết thúc phiên gọi món tại bàn của khách (Chỉ xóa session phía browser, KHÔNG thay đổi trạng thái đơn/bàn trong DB)
     */
    public function endTableOrder()
    {
        $tableId = session('table_id');
        if ($tableId) {
            \App\Services\TableAccessService::clearTableSession($tableId);
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
