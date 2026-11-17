<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Chế độ thanh toán (demo | production)
    |--------------------------------------------------------------------------
    | demo: Cho phép các route giả lập (simulate-webhook, momo-sandbox) phục vụ chấm đồ án.
    | production: Đóng toàn bộ các route giả lập (trả về 404), bắt buộc xác thực token.
    */
    'mode' => env('PAYMENT_MODE', 'production'),

    /*
    | Khóa xác thực Webhook ngân hàng
    | Bắt buộc phải cấu hình trong file .env, không để giá trị mặc định trong repo công khai.
    */
    'webhook_token' => env('PAYMENT_WEBHOOK_TOKEN'),

    /*
    | Cấu hình cổng thanh toán MoMo
    | Trong môi trường production, bắt buộc phải cấu hình MOMO_SECRET_KEY từ merchant thật.
    */
    'momo' => [
        'partner_code' => env('MOMO_PARTNER_CODE'),
        'access_key' => env('MOMO_ACCESS_KEY'),
        'secret_key' => env('MOMO_SECRET_KEY'),
        'endpoint' => env('MOMO_ENDPOINT', 'https://test-payment.momo.vn/v2/gateway/api/create'),
    ],
];
