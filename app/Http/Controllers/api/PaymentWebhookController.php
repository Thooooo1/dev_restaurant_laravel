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
        // 1. Kiểm tra xác thực Token khi ở môi trường Production (Tuyệt đối không miễn trừ dựa trên nội dung payload)
        $isProduction = config('payment.mode', 'demo') === 'production' || app()->environment('production');
        $expectedToken = config('payment.webhook_token') 
            ?? config('services.payment.webhook_token') 
            ?? env('PAYMENT_WEBHOOK_TOKEN');

        if ($isProduction) {
            if (empty($expectedToken)) {
                Log::error('Webhook bị từ chối: PAYMENT_WEBHOOK_TOKEN chưa được cấu hình trong môi trường Production.');
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: Token xác thực chưa được cấu hình trên máy chủ.'
                ], 401);
            }

            $authHeader = $request->header('Authorization', '');
            $secureToken = $request->header('secure-token') 
                ?? $request->header('X-Token') 
                ?? $request->header('X-Webhook-Token') 
                ?? $request->input('token');

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
                // Tuyệt đối không log toàn bộ headers để tránh rò rỉ token bảo mật
                Log::warning('Webhook bị từ chối: Token không hợp lệ hoặc thiếu xác thực', [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
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

        Log::info('Nhận Webhook Thanh Toán Hợp Lệ:', [
            'amount' => (int) round($amount),
            'content' => $content,
            'reference_code' => $referenceCode,
            'ip' => $request->ip(),
        ]);

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

        // 2. Chống xử lý trùng lặp Webhook (Idempotency) nhanh ngoài transaction
        // Xác minh xem reference_code đã tồn tại và thuộc đơn hàng nào
        $existingTx = \App\Models\PaymentTransaction::where('reference_code', $referenceCode)->first();
        if ($existingTx) {
            if ((int)$existingTx->order_id === (int)$order->id) {
                Log::info("Idempotency: Giao dịch mã '{$referenceCode}' đã được xử lý trước đó cho đơn #{$order->id}.");
                return response()->json([
                    'success' => true,
                    'message' => "Giao dịch {$referenceCode} đã được xử lý trước đó (Idempotent).",
                    'order_id' => $existingTx->order_id,
                    'already_processed' => true,
                ]);
            } else {
                Log::warning("Xung đột mã giao dịch: '{$referenceCode}' đã thuộc đơn #{$existingTx->order_id}, không thể gán cho đơn #{$order->id}.");
                return response()->json([
                    'success' => false,
                    'message' => "Mã giao dịch {$referenceCode} đã được sử dụng cho đơn hàng #{$existingTx->order_id}.",
                    'order_id' => $order->id,
                    'conflict_order_id' => $existingTx->order_id,
                ], 409);
            }
        }

        // 3. Chuẩn hóa payload an toàn theo danh sách cho phép (Allowlist), tuyệt đối không lưu token/auth
        $sanitizedPayload = [
            'amount' => (int) round($amount),
            'content' => $content,
            'reference_code' => $referenceCode,
            'gateway' => (string) $request->input('gateway', 'vietqr'),
            'account_number' => (string) $request->input('accountNumber', ''),
            'transaction_date' => (string) $request->input('transactionDate', now()->toIso8601String()),
            'ip' => $request->ip(),
        ];

        try {
            // 4. Thực thi giao dịch nguyên tử trong Database Transaction kết hợp Khóa Bi Quan
            $txResult = \Illuminate\Support\Facades\DB::transaction(function () use (
                $order, $amount, $referenceCode, $content, $sanitizedPayload, $isProduction
            ) {
                $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->firstOrFail();

                // 4.1. Chống race-condition cùng reference_code: Kiểm tra lại bên trong transaction
                $sameTx = \App\Models\PaymentTransaction::where('reference_code', $referenceCode)->first();
                if ($sameTx) {
                    if ((int)$sameTx->order_id === (int)$lockedOrder->id) {
                        Log::info("Idempotency intercepted: Giao dịch '{$referenceCode}' đã được commit trước đó.");
                        return [
                            'status' => 'already_processed',
                            'order_id' => $sameTx->order_id,
                            'message' => "Giao dịch {$referenceCode} đã được xử lý trước đó (Idempotent).",
                        ];
                    } else {
                        return [
                            'status' => 'conflict',
                            'order_id' => $lockedOrder->id,
                            'conflict_order_id' => $sameTx->order_id,
                            'message' => "Mã giao dịch {$referenceCode} đã được sử dụng cho đơn hàng #{$sameTx->order_id}.",
                        ];
                    }
                }

                // 4.2. Chống race-condition khác reference_code: Kiểm tra nếu đơn đã thanh toán xong
                if ($lockedOrder->payment_status === Order::PAYMENT_PAID || $lockedOrder->isPaid()) {
                    Log::info("Overpayment intercepted: Đơn hàng #{$lockedOrder->id} đã được thanh toán trước đó. Ghi nhận giao dịch phát sinh '{$referenceCode}'.");
                    \App\Models\PaymentTransaction::create([
                        'order_id' => $lockedOrder->id,
                        'reference_code' => $referenceCode,
                        'amount' => (int) round($amount),
                        'content' => $content,
                        'gateway' => 'vietqr',
                        'status' => 'overpayment',
                        'raw_payload' => json_encode($sanitizedPayload),
                        'created_at' => now(),
                    ]);

                    $lockedOrder->delivery_note = ($lockedOrder->delivery_note ? $lockedOrder->delivery_note . ' | ' : '') 
                        . "Phát sinh thanh toán dư: " . number_format((int)round($amount), 0, ',', '.') . "đ (Mã GD: {$referenceCode}, cần hoàn tiền)";
                    $lockedOrder->save();

                    // Cảnh báo khẩn cấp cho thu ngân qua ServiceCall
                    try {
                        ServiceCall::create([
                            'table_id' => $lockedOrder->table_id,
                            'order_id' => $lockedOrder->id,
                            'customer_name' => $lockedOrder->customer_name ?: 'Khách thanh toán dư',
                            'phone' => $lockedOrder->customer_phone !== '---' ? $lockedOrder->customer_phone : null,
                            'call_type' => 'service',
                            'call_type_label' => 'Cảnh báo thanh toán dư / trùng',
                            'note' => "Phát hiện chuyển khoản phát sinh " . number_format((int)round($amount), 0, ',', '.') . "đ cho đơn #{$lockedOrder->id} đã hoàn tất (Mã GD: {$referenceCode}). Cần kiểm tra đối soát hoàn tiền.",
                            'status' => ServiceCall::STATUS_PENDING,
                            'handled_at' => null,
                        ]);
                    } catch (\Throwable $e) {}

                    return [
                        'status' => 'already_paid',
                        'order_id' => $lockedOrder->id,
                        'message' => "Đơn hàng #{$lockedOrder->id} đã được thanh toán trước đó. Ghi nhận khoản thanh toán phát sinh.",
                    ];
                }

                // 4.3. Kiểm tra số tiền chuyển bên trong khóa bi quan (Sử dụng số nguyên chính xác VND)
                $orderTotal = (int) $lockedOrder->total_price;
                $paidAmount = (int) round($amount);

                if ($isProduction && $orderTotal > 0 && $paidAmount < $orderTotal) {
                    throw new \DomainException("Số tiền chuyển {$paidAmount}đ nhỏ hơn tổng đơn {$orderTotal}đ");
                }

                // Ghi nhận tiền thừa nếu khách chuyển lớn hơn tổng tiền hóa đơn
                $surplus = $paidAmount - $orderTotal;
                $surplusNote = '';
                if ($surplus > 0 && $orderTotal > 0) {
                    Log::notice("Khách chuyển thừa tiền cho đơn #{$lockedOrder->id}: Chuyển {$paidAmount}đ, Tổng đơn {$orderTotal}đ (thừa {$surplus}đ)");
                    $surplusNote = 'Khách chuyển thừa: ' . number_format($surplus, 0, ',', '.') . 'đ (cần đối soát hoàn lại)';
                }

                // 4.4. Phân loại 3 hình thức đơn hàng rõ ràng (Dine-In, POS/Takeaway, Delivery)
                if ($lockedOrder->isDineIn()) {
                    // Nhánh 1: Dùng bữa tại bàn (Dine-in) -> Khách thanh toán xong giải phóng bàn và ghi nhận doanh thu ngay
                    $lockedOrder->status = 2; // 2: Completed
                    $lockedOrder->payment_method = 2; // 2: Chuyển khoản VietQR
                    $lockedOrder->payment_status = Order::PAYMENT_PAID;
                    $lockedOrder->paid_at = now();
                    $lockedOrder->revenue_recognized_at = now();
                    $lockedOrder->total_price = $orderTotal > 0 ? $orderTotal : $paidAmount;
                    $lockedOrder->has_new_items = false;
                    if ($surplusNote) {
                        $lockedOrder->delivery_note = ($lockedOrder->delivery_note ? $lockedOrder->delivery_note . ' | ' : '') . $surplusNote;
                    }
                    $lockedOrder->save();

                    // Giải phóng bàn đã thanh toán
                    Table::where('locked_order_id', $lockedOrder->id)->update(['locked_order_id' => null]);
                    if ($lockedOrder->table_id) {
                        Table::where('id', $lockedOrder->table_id)->update(['status' => 0, 'locked_order_id' => null]);
                    }

                    // Đánh dấu hoàn tất các yêu cầu gọi tính tiền trước đó của bàn này
                    try {
                        ServiceCall::where('table_id', $lockedOrder->table_id)
                            ->where('call_type', 'bill')
                            ->where('status', ServiceCall::STATUS_PENDING)
                            ->update([
                                'status' => ServiceCall::STATUS_HANDLED,
                                'handled_at' => now(),
                            ]);
                    } catch (\Throwable $e) {}
                } elseif ($lockedOrder->isPos() || $lockedOrder->isTakeaway()) {
                    // Nhánh 2: Bán tại quầy POS hoặc Mang về -> Nhận tiền hoàn tất đơn và ghi nhận doanh thu tức thì
                    $lockedOrder->status = 2; // 2: Completed
                    $lockedOrder->payment_method = 2; // 2: Chuyển khoản VietQR
                    $lockedOrder->payment_status = Order::PAYMENT_PAID;
                    $lockedOrder->paid_at = now();
                    $lockedOrder->revenue_recognized_at = now();
                    $lockedOrder->total_price = $orderTotal > 0 ? $orderTotal : $paidAmount;
                    $lockedOrder->has_new_items = false;
                    if ($surplusNote) {
                        $lockedOrder->delivery_note = ($lockedOrder->delivery_note ? $lockedOrder->delivery_note . ' | ' : '') . $surplusNote;
                    }
                    $lockedOrder->save();
                } else {
                    // Nhánh 3: Đơn giao hàng online -> Đã thu tiền (paid_at), nhưng revenue_recognized_at chờ giao thành công (delivered)
                    $lockedOrder->status = 1; // 1: PROCESSING (Đang chế biến & Chờ giao)
                    $lockedOrder->payment_method = 'VietQR';
                    $lockedOrder->payment_status = Order::PAYMENT_PAID;
                    $lockedOrder->paid_at = now();
                    $lockedOrder->revenue_recognized_at = null; // Chưa giao thành công thì chưa ghi nhận doanh thu
                    $lockedOrder->total_price = $orderTotal > 0 ? $orderTotal : $paidAmount;
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
                    'amount' => $paidAmount,
                    'content' => $content,
                    'gateway' => 'vietqr',
                    'status' => \App\Models\PaymentTransaction::STATUS_SUCCESS,
                    'raw_payload' => json_encode($sanitizedPayload),
                    'created_at' => now(),
                ]);

                return ['status' => 'success', 'order_id' => $lockedOrder->id];
            });
        } catch (\DomainException $e) {
            Log::warning("Webhook từ chối do vi phạm quy tắc: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (\Illuminate\Database\QueryException $e) {
            // Bắt lỗi vi phạm Unique Constraint trên reference_code nếu xảy ra race-condition cực điểm
            if ((isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062) || str_contains($e->getMessage(), 'Duplicate entry')) {
                $conflictTx = \App\Models\PaymentTransaction::where('reference_code', $referenceCode)->first();
                if ($conflictTx && (int)$conflictTx->order_id !== (int)$order->id) {
                    Log::warning("1062 Conflict: Mã GD '{$referenceCode}' đã thuộc đơn #{$conflictTx->order_id}.");
                    return response()->json([
                        'success' => false,
                        'message' => "Mã giao dịch {$referenceCode} đã được sử dụng cho đơn hàng #{$conflictTx->order_id}.",
                        'order_id' => $order->id,
                        'conflict_order_id' => $conflictTx->order_id,
                    ], 409);
                }

                Log::info("Idempotency: Bắt ngoại lệ Duplicate entry 1062 cho mã giao dịch '{$referenceCode}'.");
                return response()->json([
                    'success' => true,
                    'message' => "Giao dịch {$referenceCode} đã được xử lý trước đó (Idempotent).",
                    'order_id' => $order->id,
                    'already_processed' => true,
                ]);
            }
            Log::error('Lỗi Database Webhook: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống khi lưu giao dịch thanh toán.'
            ], 500);
        }

        if ($txResult['status'] === 'already_processed') {
            return response()->json([
                'success' => true,
                'message' => $txResult['message'],
                'order_id' => $txResult['order_id'],
                'already_processed' => true,
            ]);
        }

        if ($txResult['status'] === 'conflict') {
            return response()->json([
                'success' => false,
                'message' => $txResult['message'],
                'order_id' => $txResult['order_id'],
                'conflict_order_id' => $txResult['conflict_order_id'] ?? null,
            ], 409);
        }

        if ($txResult['status'] === 'already_paid') {
            return response()->json([
                'success' => true,
                'message' => $txResult['message'],
                'order_id' => $txResult['order_id'],
                'already_paid' => true,
            ]);
        }

        // Tạo thông báo chuông hệ thống thời gian thực (ServiceCall) để thu ngân & nhân viên nhận toast và chuông reo
        $channelLabel = $order->isDineIn() 
            ? ($order->table ? $order->table->name : "Bàn #{$order->table_id}")
            : ($order->isPos() ? "Quầy POS" : "Đơn giao online #{$order->id}");

        $callLabel = $order->isDineIn()
            ? 'Đã nhận chuyển khoản VietQR - Dùng bữa tại bàn'
            : ($order->isPos() ? 'Đã nhận chuyển khoản VietQR - Tại quầy POS' : 'Đã nhận chuyển khoản VietQR - Đơn giao hàng');

        $notificationsSent = [
            'admin' => false,
            'employee' => false,
            'customer' => false,
        ];

        try {
            ServiceCall::create([
                'table_id' => $order->table_id,
                'order_id' => $order->id,
                'customer_name' => $order->customer_name ?: 'Khách VietQR',
                'phone' => $order->customer_phone !== '---' ? $order->customer_phone : null,
                'call_type' => 'bill',
                'call_type_label' => $callLabel,
                'note' => "Hệ thống tự động nhận " . number_format((int)round($amount), 0, ',', '.') . "đ qua VietQR ({$channelLabel}, Mã GD: {$referenceCode})",
                'status' => ServiceCall::STATUS_PENDING,
                'handled_at' => null,
            ]);
        } catch (\Throwable $e) {
            Log::error("Không thể tạo ServiceCall cho webhook thanh toán đơn #{$order->id}: " . $e->getMessage());
        }

        try {
            NotificationService::notifyPaymentSuccess($order, (int)round($amount), $referenceCode);
            $notificationsSent = [
                'admin' => true,
                'employee' => true,
                'customer' => true,
            ];
        } catch (\Throwable $e) {
            Log::error("Không thể gửi thông báo thanh toán thành công cho đơn #{$order->id}: " . $e->getMessage());
        }

        session()->flash('payment_success_toast', [
            'title' => 'Thanh toán thành công!',
            'message' => "Đã nhận " . number_format((int)round($amount), 0, ',', '.') . "đ cho đơn hàng #{$order->id}.",
            'amount' => (int)round($amount),
            'order_id' => $order->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Xử lý thanh toán thành công qua Webhook',
            'order_id' => $order->id,
            'amount' => (int)round($amount),
            'reference_code' => $referenceCode,
            'notifications_sent' => $notificationsSent,
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
        // Tuyệt đối không cho phép chạy API giả lập thanh toán trong môi trường Production
        $isProduction = config('payment.mode', 'demo') === 'production' || app()->environment('production');
        if ($isProduction) {
            return response()->json([
                'success' => false,
                'message' => 'Not Found: API giả lập webhook không được phép hoạt động trên môi trường Production.'
            ], 404);
        }

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
