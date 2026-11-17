<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MomoService
{
    // Giá trị fallback chỉ dùng cho môi trường local demo / testing kín
    const SAMPLE_PARTNER_CODE = 'MOMO';
    const SAMPLE_ACCESS_KEY = 'F8BBA842ECF85';
    const SAMPLE_SECRET_KEY = 'K951B6PE1waDMi640xX08PD3vg6EkVlz';
    const API_ENDPOINT = 'https://test-payment.momo.vn/v2/gateway/api/create';

    public function getPartnerCode(): string
    {
        return config('payment.momo.partner_code') ?: env('MOMO_PARTNER_CODE', self::SAMPLE_PARTNER_CODE);
    }

    public function getAccessKey(): string
    {
        return config('payment.momo.access_key') ?: env('MOMO_ACCESS_KEY', self::SAMPLE_ACCESS_KEY);
    }

    /**
     * V4-P0-34: Lấy Secret Key an toàn.
     * Ở môi trường Production, cấm tuyệt đối sử dụng secret key mẫu từ public repo.
     */
    public function getSecretKey(): ?string
    {
        $key = config('payment.momo.secret_key') ?: env('MOMO_SECRET_KEY');
        if (config('payment.mode') === 'production') {
            if (!$key || $key === self::SAMPLE_SECRET_KEY) {
                return null;
            }
        }
        return $key ?: self::SAMPLE_SECRET_KEY;
    }

    public function createPayment($orderId, $amount, $orderInfo, $extraData = '')
    {
        $secretKey = $this->getSecretKey();
        if (!$secretKey) {
            Log::warning('MoMo createPayment: Secret key is not configured for production.');
            return [
                'payUrl' => route('site.momo.sandbox', ['order' => $orderId]),
                'is_sandbox_simulation' => true,
                'message' => 'MoMo chưa cấu hình key production, chuyển sang mô phỏng sandbox'
            ];
        }

        $partnerCode = $this->getPartnerCode();
        $accessKey = $this->getAccessKey();
        $redirectUrl = route('site.momo.return');
        $ipnUrl = route('site.momo.ipn');

        $requestId = time() . '_' . uniqid();
        $orderId = (string) $orderId;
        $amount = (string) round($amount); // Đảm bảo là số nguyên VND
        $extraData = '';
        $orderInfo = 'Thanh toan don hang #' . $orderId;

        // Raw Hash chính xác theo chuẩn MoMo
        $rawHash = "accessKey=" . $accessKey . "&" .
                    "amount=" . $amount . "&" .
                    "extraData=" . $extraData . "&" .
                    "ipnUrl=" . $ipnUrl . "&" .
                    "orderId=" . $orderId . "&" .
                    "orderInfo=" . $orderInfo . "&" .
                    "partnerCode=" . $partnerCode . "&" .
                    "redirectUrl=" . $redirectUrl . "&" .
                    "requestId=" . $requestId . "&" .
                    "requestType=captureWallet";

        $signature = hash_hmac('sha256', $rawHash, $secretKey);

        $data = [
            'partnerCode' => $partnerCode,
            'partnerName' => 'MoMo Payment',
            'storeId' => 'CIVILIZE Restaurant',
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl' => $ipnUrl,
            'lang' => 'vi',
            'extraData' => $extraData,
            'requestType' => 'captureWallet',
            'signature' => $signature,
            'autoCapture' => true
        ];

        try {
            $httpClient = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]);

            // P1-08: Bật xác thực chứng chỉ TLS/SSL trên môi trường production
            if (config('payment.mode') === 'demo') {
                $httpClient = $httpClient->withoutVerifying();
            }

            $endpoint = config('payment.momo.endpoint') ?: self::API_ENDPOINT;
            $response = $httpClient->timeout(5)->post($endpoint, $data);

            if ($response->successful() && !empty($response->json()) && !empty($response->json()['payUrl'])) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::warning('=== MOMO GATEWAY FAILED OR TIMEOUT ===: ' . $e->getMessage());
        }

        // Tự động chuyển hướng sang giao diện MoMo Sandbox Simulator khi cổng test MoMo không phản hồi
        return [
            'payUrl' => route('site.momo.sandbox', ['order' => $orderId]),
            'is_sandbox_simulation' => true,
            'message' => 'Chuyển hướng cổng thanh toán MoMo Sandbox'
        ];
    }

    public function verifyPayment($data)
    {
        if (!isset($data['signature'])) {
            return false;
        }

        $secretKey = $this->getSecretKey();
        if (!$secretKey) {
            Log::error('MoMo verifyPayment rejected: Production secret key missing or sample key used.');
            return false;
        }

        $accessKey = $this->getAccessKey();

        $rawHash = "accessKey=" . $accessKey . "&" .
                    "amount=" . ($data['amount'] ?? '') . "&" .
                    "extraData=" . ($data['extraData'] ?? '') . "&" .
                    "message=" . ($data['message'] ?? '') . "&" .
                    "orderId=" . ($data['orderId'] ?? '') . "&" .
                    "orderInfo=" . ($data['orderInfo'] ?? '') . "&" .
                    "orderType=" . ($data['orderType'] ?? '') . "&" .
                    "partnerCode=" . ($data['partnerCode'] ?? '') . "&" .
                    "payType=" . ($data['payType'] ?? '') . "&" .
                    "requestId=" . ($data['requestId'] ?? '') . "&" .
                    "responseTime=" . ($data['responseTime'] ?? '') . "&" .
                    "resultCode=" . ($data['resultCode'] ?? '') . "&" .
                    "transId=" . ($data['transId'] ?? '');

        $signature = hash_hmac('sha256', $rawHash, $secretKey);

        return hash_equals($signature, (string)$data['signature']);
    }
}
