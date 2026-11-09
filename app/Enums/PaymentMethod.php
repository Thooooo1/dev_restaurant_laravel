<?php

namespace App\Enums;

class PaymentMethod
{
    public const CASH = 1;
    public const VIETQR = 2;
    public const CARD = 3;

    public static function getLabel(int|string|null $method): string
    {
        $info = self::getInfo($method);
        return $info['name'];
    }

    public static function getInfo(int|string|null $method): array
    {
        $m = (string) $method;

        if ($m === '2' || strtolower($m) === 'vietqr' || strtolower($m) === 'bank' || str_contains(strtolower($m), 'chuyển khoản') || strtolower($m) === 'momo') {
            return [
                'code' => 'vietqr',
                'name' => 'Chuyển khoản VietQR',
                'icon' => 'fa-qrcode',
                'color' => '#1677FF',
                'bg' => 'bg-blue-50',
                'text' => 'text-blue-700',
                'border' => 'border-blue-200',
                'desc' => 'Quét mã VietQR chuyển khoản ngân hàng (Napas 247)',
            ];
        }

        if ($m === '3' || strtolower($m) === 'card' || strtolower($m) === 'pos' || str_contains(strtolower($m), 'thẻ')) {
            return [
                'code' => 'card',
                'name' => 'Quẹt thẻ POS',
                'icon' => 'fa-credit-card',
                'color' => '#722ED1',
                'bg' => 'bg-purple-50',
                'text' => 'text-purple-700',
                'border' => 'border-purple-200',
                'desc' => 'Thanh toán quẹt thẻ ATM nội địa / Visa / MasterCard qua máy POS',
            ];
        }

        return [
            'code' => 'cash',
            'name' => 'Tiền mặt',
            'icon' => 'fa-money-bill-wave',
            'color' => '#52C41A',
            'bg' => 'bg-emerald-50',
            'text' => 'text-emerald-700',
            'border' => 'border-emerald-200',
            'desc' => 'Thanh toán trực tiếp bằng tiền mặt tại quầy / bàn',
        ];
    }
}
