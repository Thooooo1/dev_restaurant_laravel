<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Chế độ thanh toán (demo | production)
    |--------------------------------------------------------------------------
    | demo: Cho phép các route giả lập (simulate-webhook, momo-sandbox) phục vụ chấm đồ án.
    | production: Đóng toàn bộ các route giả lập (trả về 404), bắt buộc xác thực token.
    */
    'mode' => env('PAYMENT_MODE', 'demo'),

    /*
    | Khóa xác thực Webhook ngân hàng
    | Bắt buộc phải cấu hình trong file .env, không để giá trị mặc định trong repo công khai.
    */
    'webhook_token' => env('PAYMENT_WEBHOOK_TOKEN'),
];
