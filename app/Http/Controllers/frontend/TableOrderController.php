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

        // Lấy hoặc đồng bộ đơn hàng của bàn
        $cartKey = Auth::check() ? 'cart_' . Auth::id() : 'cart_guest_' . session()->getId();
        $cart = session()->get($cartKey, []);

        $order = null;
        $tableOrderId = session('table_order_id');

        if ($tableOrderId) {
            $order = Order::where('id', $tableOrderId)
                ->where('table_id', $id)
                ->whereNull('deleted_at')
                ->first();
        }

        if (!$order) {
            $order = Order::where('table_id', $id)
                ->whereNull('deleted_at')
                ->whereIn('status', [0, 1])
                ->latest('id')
                ->first();
        }

        // Nếu trong giỏ có món mà chưa gửi vào Order, tự động đồng bộ vào Order
        if (!empty($cart)) {
            if (!$order) {
                $order = new Order();
                $order->user_id = Auth::id() ?? 0;
                $order->name = Auth::user()->fullname ?? ($table->name . ' - Khách thanh toán QR');
                $order->phone = Auth::user()->phone ?? session('customer_phone', '');
                $order->email = Auth::user()->email ?? '';
                $order->address = $table->name;
                $order->table_id = $id;
                $order->orderStyle = 0; // Tại bàn
                $order->status = 0; // Chờ thanh toán
                $order->created_by = Auth::id() ?? 1;
                $order->order_turn = 1;
                $order->save();

                session(['table_order_id' => $order->id]);
            }

            foreach ($cart as $productId => $details) {
                $price = (float)$details['price'];
                $quantity = (int)$details['qty'];
                $amount = $price * $quantity;

                $existingDetail = Orderdetail::where('order_id', $order->id)
                    ->where('product_id', $productId)
                    ->first();

                if ($existingDetail) {
                    $existingDetail->qty += $quantity;
                    $existingDetail->amount = $existingDetail->price * $existingDetail->qty;
                    $existingDetail->save();
                } else {
                    Orderdetail::create([
                        'order_id' => $order->id,
                        'product_id' => $productId,
                        'qty' => $quantity,
                        'price' => $price,
                        'discount' => 0,
                        'amount' => $amount,
                        'order_turn' => $order->order_turn ?? 1,
                        'status' => 'pending',
                    ]);
                }
            }

            // Xóa triệt để session giỏ hàng sau khi đã chuyển vào order
            \App\Http\Controllers\frontend\CartController::clearCartSession();
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

        $subtotal = $orderDetails->sum('amount');
        // Ưu đãi giảm 10% tại bàn
        $discountAmount = round($subtotal * 0.1);
        $finalAmount = max(0, $subtotal - $discountAmount);

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
        if (config('payment.mode') !== 'demo') {
            return back()->with('error', 'Thanh toán sẽ tự động xác nhận khi hệ thống ngân hàng nhận tiền.');
        }

        $table = Table::find($id);
        $orderId = $request->input('order_id', session('table_order_id'));

        $order = Order::where('id', $orderId)
            ->where('table_id', $id)
            ->first();

        if (!$order) {
            return redirect()->route('site.home')->with('error', 'Không tìm thấy hóa đơn cần thanh toán.');
        }

        // Cập nhật đơn hàng thành Đã thanh toán
        $paymentMethod = $request->input('payment_method', 'VietQR');
        $order->status = 1; // 1: Đã thanh toán / Paid
        $order->payment_method = $paymentMethod;
        $order->has_new_items = true; // Báo chuông nhắc nhở thu ngân & bếp
        $order->save();

        try {
            \App\Services\NotificationService::send([
                'role' => 'employee',
                'type' => 'payment',
                'title' => ($table ? $table->name : 'Bàn') . " đã thanh toán {$paymentMethod}!",
                'message' => "Hóa đơn đơn #" . $order->id . " số tiền " . number_format($order->total_price, 0, ',', '.') . "₫ đã được thanh toán.",
                'url' => route('admin.table-order.payment', $id),
                'icon' => 'fa-credit-card',
                'color' => '#10B981',
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

        return redirect()->route('site.table.bill', $id)->with('success', 'Thanh toán thành công! Nhà bếp đang chuẩn bị món ăn cho quý khách.');
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
            ->whereIn('status', [0, 1])
            ->with(['orderDetails.product', 'table.floor'])
            ->latest('id')
            ->first();

        if (!$order) {
            return redirect()->route('site.table.scan', $id)->with('info', 'Bàn hiện chưa có đơn hàng đang hoạt động.');
        }

        $orderDetails = $order->orderDetails;
        $subtotal = $orderDetails->sum('amount');
        $discountAmount = round($subtotal * 0.1);
        $finalAmount = max(0, $subtotal - $discountAmount);

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
