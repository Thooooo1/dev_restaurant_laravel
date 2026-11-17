<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentIntent;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PaymentIntentService
{
    /**
     * Tạo hoặc cập nhật Payment Intent trước khi yêu cầu cổng thanh toán
     */
    public static function createIntent(
        Order $order,
        string $gateway,
        string $requestId,
        float $amount,
        ?string $merchantId = null,
        ?array $payload = null,
        int $ttlMinutes = 30
    ): PaymentIntent {
        return PaymentIntent::updateOrCreate(
            [
                'order_id' => $order->id,
                'request_id' => $requestId,
            ],
            [
                'gateway' => $gateway,
                'merchant_id' => $merchantId,
                'expected_amount' => $amount,
                'currency' => 'VND',
                'status' => PaymentIntent::STATUS_PENDING,
                'expires_at' => now()->addMinutes($ttlMinutes),
                'payload' => $payload,
            ]
        );
    }

    /**
     * Đối soát và xử lý Webhook / IPN thanh toán nguyên tử (Atomic IPN Reconciliation)
     * Đảm bảo:
     * 1. Số tiền phải khớp chính xác từng đồng với expected_amount của Intent
     * 2. Transaction ID phải duy nhất, chống Replay Attack
     * 3. Thực thi an toàn trong Database Transaction với pessimistic lock
     */
    public static function reconcileIpn(
        string $gateway,
        string $requestId,
        string $transactionId,
        float $paidAmount,
        array $rawPayload = []
    ): array {
        return DB::transaction(function () use ($gateway, $requestId, $transactionId, $paidAmount, $rawPayload) {
            // 1. Tìm Intent có khóa bi quan để đồng bộ xử lý
            $intent = PaymentIntent::where('request_id', $requestId)->lockForUpdate()->first();
            if (!$intent) {
                return [
                    'success' => false,
                    'code' => 404,
                    'message' => 'Không tìm thấy Payment Intent khớp với request_id: ' . $requestId,
                ];
            }

            // 2. Kiểm tra Replay Attack: Nếu transaction_id này đã được ghi nhận bởi một intent khác
            $duplicateTx = PaymentIntent::where('gateway_transaction_id', $transactionId)
                ->where('id', '!=', $intent->id)
                ->first();
            if ($duplicateTx) {
                return [
                    'success' => false,
                    'code' => 409,
                    'message' => 'Mã giao dịch ' . $transactionId . ' đã được ghi nhận cho đơn hàng khác.',
                ];
            }

            // 3. Nếu Intent này đã xử lý thành công với cùng transactionId -> Idempotent response
            if ($intent->status === PaymentIntent::STATUS_SUCCESSFUL && $intent->gateway_transaction_id === $transactionId) {
                return [
                    'success' => true,
                    'code' => 200,
                    'status' => 'already_processed',
                    'message' => 'Giao dịch đã được xử lý trước đó.',
                    'order' => $intent->order,
                ];
            }

            // 4. Kiểm tra số tiền nhận được có khớp chính xác từng đồng không
            $expected = (float)$intent->expected_amount;
            if (abs($expected - $paidAmount) > 0.001) {
                return [
                    'success' => false,
                    'code' => 422,
                    'message' => "Số tiền thanh toán ({$paidAmount}) không khớp với số tiền yêu cầu ({$expected}).",
                ];
            }

            // 5. Cập nhật trạng thái Intent
            $intent->status = PaymentIntent::STATUS_SUCCESSFUL;
            $intent->gateway_transaction_id = $transactionId;
            $intent->payload = $rawPayload;
            $intent->save();

            // 6. Cập nhật đơn hàng Order sang PAID một cách an toàn
            $order = Order::where('id', $intent->order_id)->lockForUpdate()->first();
            if ($order) {
                $order->payment_status = Order::PAYMENT_PAID;
                $order->paid_at = now();

                // Nếu là đơn tại bàn hoặc mang về bàn giao ngay thì ghi nhận doanh thu
                if (in_array($order->order_style, ['table', 'pos']) || $order->channel === 'pos') {
                    $order->revenue_recognized_at = now();
                }
                $order->save();

                // Ghi nhận bản ghi thanh toán
                PaymentTransaction::create([
                    'order_id' => $order->id,
                    'reference_code' => $transactionId,
                    'amount' => $paidAmount,
                    'content' => "Thanh toán {$gateway} IPN",
                    'gateway' => $gateway,
                    'status' => PaymentTransaction::STATUS_SUCCESS,
                    'raw_payload' => $rawPayload,
                ]);
            }

            return [
                'success' => true,
                'code' => 200,
                'message' => 'Xác nhận thanh toán thành công.',
                'order' => $order,
                'intent' => $intent,
            ];
        });
    }
}
