<?php

return [
    'partner_code' => env('MOMO_PARTNER_CODE', 'MOMO'),
    'access_key' => env('MOMO_ACCESS_KEY', 'F8BBA363-7B3E-4F2B-9B0E-0E5F5F5F5F5F'),
    'secret_key' => env('MOMO_SECRET_KEY', '0F0D4B9E-0A1D-4D0E-8C1F-1F2F3F4F5F6F'),
    'redirect_url' => env('MOMO_REDIRECT_URL', 'http://127.0.0.1:8000/checkout/return'),
    'ipn_url' => env('MOMO_IPN_URL', 'http://127.0.0.1:8000/checkout/ipn'),
    'api_url' => env('MOMO_API_URL', 'https://test-payment.momo.vn/v2/gateway/api/create'),
];
