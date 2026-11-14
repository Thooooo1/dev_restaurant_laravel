<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Table;
use App\Models\ServiceCall;
use App\Services\VietQRService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PaymentWebhookController extends Controller
{
    protected VietQRService $vietQRService;

    public function __construct(VietQRService $vietQRService)
    {
        $this->vietQRService = $vietQRService;
    }

    /**
     * Nhận và xử lý Webhook biến động số dư ngân hàng (Sepay, Casso, MBBank, Vietcombank...)
     */
    public function handleWebhook(Request $request)
    {
        Log::info('=== PAYMENT WEBHOOK RECEIVED ===', $request->all());

        // Kiểm tra nếu là yêu cầu giả lập test
        $isSimulated = str_starts_with((string)$request->input('referenceCode', ''), 'SIMULATED_');

        // Kiểm tra xác thực Token khi ở môi trường Production (và không phải test giả lập)
        $isProduction = config('payment.mode', 'demo') === 'production';
        $expectedToken = config('payment.webhook_token', env('PAYMENT_WEBHOOK_TOKEN'));

        if ($isProduction && !$isSimulated) {
            if (empty($expectedToken)) {
                Log::error('Webhook bị từ chối: PAYMENT_WEBHOOK_TOKEN chưa được cấu hình trong môi trường Production.');
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: Token xác thực chưa được cấu hình trên máy chủ.'
                ], 401);
            }

            $authHeader = $request->header('Authorization', '');
            $secureToken = $request->header('secure-token') ?? $request->header('X-Token') ?? $request->input('token');

            $tokenFromAuth = '';
            if (str_starts_with($authHeader, 'Apikey ')) {
                $tokenFromAuth = substr($authHeader, 7);
            } elseif (str_starts_with($authHeader, 'Bearer ')) {
                $tokenFromAuth = substr($authHeader, 7);
            }

            $valid = false;
            if ($tokenFromAuth && hash_equals((string)$expectedToken, (string)$tokenFromAuth)) {
                $valid = true;
            } elseif ($secureToken && hash_equals((string)$expectedToken, (string)$secureToken)) {
                $valid = true;
            }

            if (!$valid) {
                Log::warning('Webhook bị từ chối: Token không hợp lệ hoặc thiếu xác thực', ['headers' => $request->headers->all()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: Token xác thực Webhook không hợp lệ.'
                ], 401);
            }
        }

        // Lấy thông tin giao dịch từ payload của Sepay / Casso hoặc payload tùy chỉnh
        $amount = (float) ($request->input('transferAmount') 
            ?? $request->input('amount') 
            ?? $request->input('creditAmount') 
            ?? 0);

        $content = (string) ($request->input('content') 
            ?? $request->input('description') 
            ?? $request->input('orderInfo') 
            ?? '');

        $referenceCode = (string) ($request->input('referenceCode') 
            ?? $request->input('referenceNumber') 
            ?? $request->input('id') 
            ?? uniqid('PAY_'));

        if ($amount <= 0 || empty($content)) {
            Log::warning('Webhook payload không hợp lệ hoặc số tiền <= 0', ['content' => $content, 'amount' => $amount]);
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu webhook thiếu số tiền hoặc nội dung chuyển khoản'
            ], 400);
        }

        // Phân tích nội dung chuyển khoản để tìm Order ID hoặc Table ID
        $parsed = $this->vietQRService->parseTransferContent($content);
        $order = null;

        if ($parsed['type'] === 'order' && $parsed['id']) {
            $order = Order::with('table')->find($parsed['id']);
        } elseif ($parsed['type'] === 'table' && $parsed['id']) {
            $tableId = $parsed['id'];
            $table = Table::find($tableId);
            if (!$table) {
                // Thử tìm theo tên bàn (ví dụ Bàn 15)
                $table = Table::where('name', 'like', "%{$tableId}%")->first();
            }

            if ($table) {
                $order = Order::where('table_id', $table->id)
                    ->whereNull('deleted_at')
                    ->whereIn('status', [0, 1])
                    ->latest()
                    ->first();
            }
        }

        if (!$order) {
            Log::warning("Không tìm thấy đơn hàng tương ứng với nội dung: '{$content}'");
            return response()->json([
                'success' => false,
                'message' => "Không tìm thấy đơn hàng phù hợp với nội dung: {$content}"
            ], 404);
        }

        // 1. Chống xử lý trùng lặp Webhook (Idempotency) qua bảng payment_transactions
        $existingTx = \App\Models\PaymentTransaction::where('reference_code', $referenceCode)->first();
        if ($existingTx) {
            Log::info("Idempotency: Giao dịch mã '{$referenceCode}' đã được xử lý trước đó cho đơn #{$existingTx->order_id}.");
            return response()->json([
                'success' => true,
                'message' => "Giao dịch {$referenceCode} đã được xử lý trước đó (Idempotent).",
                'order_id' => $existingTx->order_id,
                'already_processed' => true,
            ]);
        }

        // Kiểm tra xem đơn đã được thanh toán chưa
        $isTableOrder = !empty($order->table_id);
        $alreadyPaid = $order->isPaid();

        if ($alreadyPaid) {
            Log::info("Đơn hàng #{$order->id} đã được thanh toán từ trước.");
            // Lưu vết giao dịch trùng
            try {
                \App\Models\PaymentTransaction::create([
                    'order_id' => $order->id,
                    'reference_code' => $referenceCode,
                    'amount' => $amount,
                    'content' => $content,
                    'gateway' => 'vietqr',
                    'status' => 'duplicate',
                    'raw_payload' => json_encode($request->all()),
                    'created_at' => now(),
                ]);
            } catch (\Throwable $e) {}

            return response()->json([
                'success' => true,
                'message' => "Đơn hàng #{$order->id} đã được thanh toán trước đó.",
                'order_id' => $order->id,
                'already_paid' => true
            ]);
        }

        // Kiểm tra đối soát số tiền khi ở môi trường Production
        if ($isProduction && $order->total_price > 0 && $amount < $order->total_price) {
            Log::warning("Số tiền chuyển không đủ cho đơn #{$order->id}: Chuyển {$amount}đ, Tổng đơn {$order->total_price}đ");
            return response()->json([
                'success' => false,
                'message' => "Số tiền chuyển {$amount}đ nhỏ hơn tổng đơn {$order->total_price}đ"
            ], 400);
        }

        // 2. Thực thi giao dịch nguyên tử trong Database Transaction
        $txResult = \Illuminate\Support\Facades\DB::transaction(function () use ($order, $isTableOrder, $amount, $referenceCode, $content, $request, $isProduction) {
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->firstOrFail();

            // Khóa bi quan: kiểm tra lại xem đơn đã được thanh toán bởi 1 webhook khác vừa hoàn tất ngay trước đó
            if ($lockedOrder->payment_status === Order::PAYMENT_PAID || $lockedOrder->isPaid()) {
                Log::info("Race-condition: Đơn hàng #{$lockedOrder->id} đã được thanh toán bởi một giao dịch khác vừa hoàn tất.");
                \App\Models\PaymentTransaction::create([
                    'order_id' => $lockedOrder->id,
                    'reference_code' => $referenceCode,
                    'amount' => $amount,
                    'content' => $content,
                    'gateway' => 'vietqr',
                    'status' => 'duplicate',
                    'raw_payload' => json_encode($request->all()),
                    'created_at' => now(),
                ]);
                return ['already_paid' => true];
            }

            // Ghi nhận tiền thừa nếu khách chuyển lớn hơn tổng tiền hóa đơn
            $surplus = $amount - (float)$lockedOrder->total_price;
            $surplusNote = '';
            if ($surplus > 0 && (float)$lockedOrder->total_price > 0) {
                Log::notice("Khách chuyển thừa tiền cho đơn #{$lockedOrder->id}: Chuyển {$amount}đ, Tổng đơn {$lockedOrder->total_price}đ (thừa {$surplus}đ)");
                $surplusNote = 'Khách chuyển thừa: ' . number_format($surplus, 0, ',', '.') . 'đ (cần đối soát hoàn lại)';
            }

            if ($isTableOrder) {
                $lockedOrder->status = 2; // 2: Completed (Đã thanh toán tại bàn)
                $lockedOrder->payment_method = 2; // 2: Chuyển khoản VietQR
                $lockedOrder->payment_status = Order::PAYMENT_PAID;
                $lockedOrder->paid_at = now();
                $lockedOrder->total_price = $lockedOrder->total_price > 0 ? $lockedOrder->total_price : $amount;
                $lockedOrder->has_new_items = false;
                if ($surplusNote) {
                    $lockedOrder->delivery_note = ($lockedOrder->delivery_note ? $lockedOrder->delivery_note . ' | ' : '') . $surplusNote;
                }
                $lockedOrder->save();

                // Giải phóng bàn đã thanh toán
                Table::where('locked_order_id', $lockedOrder->id)->update(['locked_order_id' => null]);

                // Đánh dấu hoàn tất các yêu cầu gọi tính tiền trước đó của bàn này
                ServiceCall::where('table_id', $lockedOrder->table_id)
                    ->where('call_type', 'bill')
                    ->where('status', ServiceCall::STATUS_PENDING)
                    ->update([
                        'status' => ServiceCall::STATUS_COMPLETED,
                        'handled_at' => Carbon::now(),
                    ]);
            } else {
                // Đơn giao hàng online
                $lockedOrder->status = 1; // 1: PROCESSING (Đã xác nhận & Đã thanh toán chuyển khoản)
                $lockedOrder->payment_method = 'VietQR';
                $lockedOrder->payment_status = Order::PAYMENT_PAID;
                $lockedOrder->paid_at = now();
                $lockedOrder->total_price = $lockedOrder->total_price > 0 ? $lockedOrder->total_price : $amount;
                $lockedOrder->has_new_items = true;
                if ($surplusNote) {
                    $lockedOrder->delivery_note = ($lockedOrder->delivery_note ? $lockedOrder->delivery_note . ' | ' : '') . $surplusNote;
                }
                $lockedOrder->save();
            }

            // Ghi nhật ký giao dịch ngân hàng thành công (Payment Audit Trail)
            \App\Models\PaymentTransaction::create([
                'order_id' => $lockedOrder->id,
                'reference_code' => $referenceCode,
                'amount' => $amount,
                'content' => $content,
                'gateway' => 'vietqr',
                'status' => \App\Models\PaymentTransaction::STATUS_SUCCESS,
                'raw_payload' => json_encode($request->all()),
                'created_at' => now(),
            ]);

            return ['already_paid' => false];
        });

        if (!empty($txResult['already_paid'])) {
            return response()->json([
                'success' => true,
                'message' => "Đơn hàng #{$order->id} đã được thanh toán trước đó (Phát hiện bởi khóa bi quan).",
                'order_id' => $order->id,
                'already_paid' => true,
            ]);
        }

        // Tạo thông báo chuông hệ thống thời gian thực (ServiceCall) để thu ngân & nhân viên nhận toast và chuông reo
        $tableName = $order->table ? $order->table->name : "Đơn online #{$order->id}";
        ServiceCall::create([
            'table_id' => $order->table_id,
            'order_id' => $order->id,
            'customer_name' => $order->name ?: ($order->table ? $order->table->name : 'Khách VietQR'),
            'phone' => $order->phone,
            'call_type' => 'bill',
            'call_type_label' => 'Đã nhận chuyển khoản VietQR',
            'note' => "Hệ thống tự động nhận " . number_format($amount, 0, ',', '.') . "đ qua VietQR (Mã GD: {$referenceCode})",
            'status' => ServiceCall::STATUS_PENDING,
            'handled_at' => null,
        ]);

        // Gửi thông báo hệ thống chính thức cho cả 3 vai trò: Admin, Employee, Customer
        NotificationService::notifyPaymentSuccess($order, $amount, $referenceCode);

        // Lưu thông báo thanh toán vào session flash
        session()->flash('payment_success_notification', [
            'order_id' => $order->id,
            'amount' => $amount,
            'reference_code' => $referenceCode,
            'message' => "Hệ thống đã tự động nhận tiền thành công và gửi thông báo tới Quản trị viên, Nhân viên và Khách hàng."
        ]);

        Log::info("Thanh toán thành công đơn #{$order->id} ({$tableName}) với số tiền {$amount}đ qua VietQR (GD: {$referenceCode}).");

        return response()->json([
            'success' => true,
            'message' => "Thanh toán VietQR thành công cho {$tableName}",
            'order_id' => $order->id,
            'amount' => $amount,
            'reference_code' => $referenceCode,
            'notifications_sent' => [
                'admin' => true,
                'employee' => true,
                'customer' => true,
            ]
        ]);
    }

    /**
     * API kiểm tra trạng thái thanh toán thời gian thực (cho client & quầy POS polling)
     */
    public function checkPaymentStatus($orderId)
    {
        $order = Order::with('table')->find($orderId);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy đơn hàng'], 404);
        }

        $isPaid = $order->isPaid();

        return response()->json([
            'success' => true,
            'order_id' => $order->id,
            'table_id' => $order->table_id,
            'table_name' => $order->table ? $order->table->name : null,
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'payment_status_label' => $order->payment_status_label,
            'payment_method' => $order->payment_method,
            'total_price' => $order->total_price,
            'is_paid' => $isPaid,
        ]);
    }

    /**
     * Trình giả lập Webhook (Simulator) phục vụ kiểm thử thanh toán trực tiếp trên môi trường dev/demo
     */
    public function simulateWebhook(Request $request)
    {
        $orderId = $request->input('order_id');
        $order = Order::find($orderId);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy đơn hàng'], 404);
        }

        $amount = (float) ($request->input('amount') ?: $order->total_price ?: 100000);
        $memo = $order->table_id ? "BAN{$order->table_id}" : "DH{$order->id}";

        // Giả lập payload từ Sepay / Casso
        $simulatedRequest = Request::create('/api/webhook/payment', 'POST', [
            'transferAmount' => $amount,
            'content' => "{$memo} thanh toan tu dong sandbox",
            'referenceCode' => 'SIMULATED_' . time(),
            'transactionDate' => Carbon::now()->format('Y-m-d H:i:s'),
            'accountNumber' => config('vietqr.account_no', '0338990538'),
            'gateway' => config('vietqr.bank_id', 'MB'),
        ]);

        return $this->handleWebhook($simulatedRequest);
    }
}
