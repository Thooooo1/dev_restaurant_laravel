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
    */
    'webhook_token' => env('PAYMENT_WEBHOOK_TOKEN', 'civilize_webhook_key_2026'),
];
