<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MomoService
{
    // Thông tin test chính thức từ Momo GitHub (momo-wallet/payment)
    const PARTNER_CODE = 'MOMO';
    const ACCESS_KEY = 'F8BBA842ECF85';
    const SECRET_KEY = 'K951B6PE1waDMi640xX08PD3vg6EkVlz';
    const REDIRECT_URL = 'http://127.0.0.1:8000/checkout/return';
    const IPN_URL = 'http://127.0.0.1:8000/checkout/ipn';
    const API_ENDPOINT = 'https://test-payment.momo.vn/v2/gateway/api/create';

    public function createPayment($orderId, $amount, $orderInfo, $extraData = '')
    {
        $requestId = time() . '_' . uniqid();
        $orderId = (string) $orderId;
        $amount = (string) round($amount); // Đảm bảo là số nguyên VND
        $extraData = ''; // Để trống cho đơn giản như ví dụ Momo
        $orderInfo = 'Thanh toan don hang'; // Thông tin đơn giản

        // Raw Hash chính xác theo thứ tự từ Momo GitHub
        $rawHash = "accessKey=" . self::ACCESS_KEY . "&" .
                    "amount=" . $amount . "&" .
                    "extraData=" . $extraData . "&" .
                    "ipnUrl=" . self::IPN_URL . "&" .
                    "orderId=" . $orderId . "&" .
                    "orderInfo=" . $orderInfo . "&" .
                    "partnerCode=" . self::PARTNER_CODE . "&" .
                    "redirectUrl=" . self::REDIRECT_URL . "&" .
                    "requestId=" . $requestId . "&" .
                    "requestType=captureWallet";

        Log::info('=== MOMO RAW HASH ===');
        Log::info($rawHash);

        // Tạo chữ ký
        $signature = hash_hmac('sha256', $rawHash, self::SECRET_KEY);

        $data = [
            'partnerCode' => self::PARTNER_CODE,
            'partnerName' => 'MoMo Payment',
            'storeId' => 'Test Store',
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => self::REDIRECT_URL,
            'ipnUrl' => self::IPN_URL,
            'lang' => 'vi',
            'extraData' => $extraData,
            'requestType' => 'captureWallet',
            'signature' => $signature,
            'autoCapture' => true
        ];

        Log::info('=== MOMO REQUEST ===');
        Log::info(json_encode($data));

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post(self::API_ENDPOINT, $data);

            Log::info('=== MOMO RESPONSE ===');
            Log::info($response->body());

            return $response->json();
        } catch (\Exception $e) {
            Log::error('=== MOMO EXCEPTION ===');
            Log::error($e->getMessage());

            return ['error' => true, 'message' => $e->getMessage()];
        }
    }

    public function verifyPayment($data)
    {
        if (!isset($data['signature'])) {
            return false;
        }

        $rawHash = "accessKey=" . self::ACCESS_KEY . "&" .
                    "amount=" . $data['amount'] . "&" .
                    "extraData=" . $data['extraData'] . "&" .
                    "message=" . $data['message'] . "&" .
                    "orderId=" . $data['orderId'] . "&" .
                    "orderInfo=" . $data['orderInfo'] . "&" .
                    "orderType=" . $data['orderType'] . "&" .
                    "partnerCode=" . $data['partnerCode'] . "&" .
                    "payType=" . $data['payType'] . "&" .
                    "requestId=" . $data['requestId'] . "&" .
                    "responseTime=" . $data['responseTime'] . "&" .
                    "resultCode=" . $data['resultCode'] . "&" .
                    "transId=" . $data['transId'];

        $signature = hash_hmac('sha256', $rawHash, self::SECRET_KEY);

        return $signature === $data['signature'];
    }
}
