<?php

return [
    /*
    |--------------------------------------------------------------------------
    | VietQR Configuration
    |--------------------------------------------------------------------------
    | Cấu hình tài khoản ngân hàng nhận tiền chuyển khoản tự động qua mã VietQR
    | và hệ thống Webhook biến động số dư (Sepay, Casso, Open API ngân hàng).
    */
    'bank_id' => env('VIETQR_BANK_ID', 'MB'), // MB (MBBank), VCB (Vietcombank), TCB (Techcombank), ACB, ICB (VietinBank)...
    'bank_name' => env('VIETQR_BANK_NAME', 'MBBank (Ngân Hàng Quân Đội)'),
    'account_no' => env('VIETQR_ACCOUNT_NO', '0338990538'),
    'account_name' => env('VIETQR_ACCOUNT_NAME', 'NHA HANG CIVILIZE'),
    'template' => env('VIETQR_TEMPLATE', 'compact2'), // compact2, compact, qr_only, print
    
    // Khóa bí mật để xác thực Webhook từ bên thứ ba (Sepay / Casso)
    'webhook_token' => env('PAYMENT_WEBHOOK_TOKEN'),
];
