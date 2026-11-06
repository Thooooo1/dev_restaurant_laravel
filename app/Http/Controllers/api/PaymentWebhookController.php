<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Table;
use App\Models\ServiceCall;
use App\Services\VietQRService;
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

        // Kiểm tra xem đơn đã được thanh toán chưa
        $isTableOrder = !empty($order->table_id);
        $alreadyPaid = $isTableOrder ? ($order->status == 2) : ($order->status >= 1 && $order->payment_method === 'VietQR');

        if ($alreadyPaid) {
            Log::info("Đơn hàng #{$order->id} đã được thanh toán từ trước.");
            return response()->json([
                'success' => true,
                'message' => "Đơn hàng #{$order->id} đã được thanh toán trước đó.",
                'order_id' => $order->id,
                'already_paid' => true
            ]);
        }

        // Cập nhật trạng thái đơn hàng sang ĐÃ THANH TOÁN
        if ($isTableOrder) {
            $order->status = 2; // 2: Completed (Đã thanh toán tại bàn)
            $order->payment_method = 2; // 2: Chuyển khoản VietQR
            $order->total_price = $order->total_price > 0 ? $order->total_price : $amount;
            $order->save();

            // Giải phóng bàn đã thanh toán
            Table::where('locked_order_id', $order->id)->update(['locked_order_id' => null]);

            // Đánh dấu hoàn tất các yêu cầu gọi tính tiền của bàn này
            ServiceCall::where('table_id', $order->table_id)
                ->where('status', ServiceCall::STATUS_PENDING)
                ->update([
                    'status' => ServiceCall::STATUS_COMPLETED,
                    'handled_at' => Carbon::now(),
                ]);
        } else {
            // Đơn giao hàng online
            $order->status = 1; // 1: Confirmed (Đã xác nhận & Đã thanh toán chuyển khoản)
            $order->payment_method = 'VietQR';
            $order->total_price = $order->total_price > 0 ? $order->total_price : $amount;
            $order->save();
        }

        // Tạo thông báo chuông hệ thống để thu ngân & nhân viên biết tiền đã vào
        $tableName = $order->table ? $order->table->name : "Đơn online #{$order->id}";
        ServiceCall::create([
            'table_id' => $order->table_id,
            'order_id' => $order->id,
            'customer_name' => $order->name ?: 'Khách VietQR',
            'phone' => $order->phone,
            'call_type' => 'bill',
            'call_type_label' => 'Đã nhận chuyển khoản VietQR',
            'note' => "Hệ thống tự động nhận " . number_format($amount, 0, ',', '.') . "đ qua VietQR (Mã GD: {$referenceCode})",
            'status' => ServiceCall::STATUS_COMPLETED,
            'handled_at' => Carbon::now(),
        ]);

        Log::info("Thanh toán thành công đơn #{$order->id} ({$tableName}) với số tiền {$amount}đ qua VietQR.");

        return response()->json([
            'success' => true,
            'message' => "Thanh toán VietQR thành công cho {$tableName}",
            'order_id' => $order->id,
            'amount' => $amount,
            'reference_code' => $referenceCode
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

        $isTableOrder = !empty($order->table_id);
        $isPaid = $isTableOrder 
            ? ($order->status == 2 || ($order->status == 1 && in_array($order->payment_method, ['VietQR', '2']))) 
            : ($order->status >= 1 && in_array($order->payment_method, ['VietQR', 'Momo']));

        return response()->json([
            'success' => true,
            'order_id' => $order->id,
            'table_id' => $order->table_id,
            'table_name' => $order->table ? $order->table->name : null,
            'status' => $order->status,
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
