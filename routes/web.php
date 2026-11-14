<?php

use Illuminate\Support\Facades\Route;

// Frontend Controllers
use App\Http\Controllers\frontend\HomeController as TrangchuController;
use App\Http\Controllers\frontend\ProductController as SanPhamController;
use App\Http\Controllers\frontend\ContactController as LienheController;
use App\Http\Controllers\frontend\AboutController as GioithieuController;
use App\Http\Controllers\frontend\BlogController as BaivietController;
use App\Http\Controllers\frontend\ImageController as HinhAnhController;
use App\Http\Controllers\frontend\UserController as NguoidungController;
use App\Http\Controllers\frontend\CartController as GiohangController;
use App\Http\Controllers\frontend\AuthController as ThanhVienController;
use App\Http\Controllers\frontend\ChinhsachController;
use App\Http\Controllers\frontend\BookingController as DatBanController;
use App\Http\Controllers\frontend\ReviewController as DanhGiaController;
use App\Http\Controllers\frontend\OrderController as DonHangController;
use App\Http\Controllers\frontend\UserFavoriteController as YeuThichController;
use App\Http\Controllers\frontend\InquiryController as YeuCauController;
use App\Http\Controllers\frontend\ChatbotController;

// Backend Controllers
use App\Http\Controllers\backend\DashboardController;
use App\Http\Controllers\backend\ReviewController;
use App\Http\Controllers\backend\ProductController;
use App\Http\Controllers\backend\CategoryController;
use App\Http\Controllers\backend\BrandController;
use App\Http\Controllers\backend\BannerController;
use App\Http\Controllers\backend\BlogController;
use App\Http\Controllers\backend\TopicController;
use App\Http\Controllers\backend\ImageController;
use App\Http\Controllers\backend\UserController;
use App\Http\Controllers\backend\OrderController;
use App\Http\Controllers\backend\OrderHistoryController;
use App\Http\Controllers\backend\ContactController;
use App\Http\Controllers\backend\KeywordController;
use App\Http\Controllers\backend\MenuController;
use App\Http\Controllers\backend\BookingController;
use App\Http\Controllers\backend\InquiryController;
use App\Http\Controllers\backend\TableController;
use App\Http\Controllers\backend\TableOrderController;
use App\Http\Controllers\backend\ImportGoodsController;
use App\Http\Controllers\backend\NotificationController;
use App\Http\Controllers\backend\SettingController;
use App\Http\Controllers\frontend\ServiceCallController;

// Auth Controllers
use App\Http\Controllers\auth\AuthController;

// ============================================
// FRONTEND ROUTES
// ============================================

// Home
Route::get('/', [TrangchuController::class, 'index'])->name('site.home');

// Products
Route::get('/thuc-don', [SanPhamController::class, 'index'])->name('site.product');
Route::get('/danh-sach', [SanPhamController::class, 'product_gridview'])->name('site.product.gridview');
Route::get('/chi-tiet-san-pham/{slug}', [SanPhamController::class, 'product_detail'])->name('site.product.detail');
Route::get('/tim-kiem-nhanh', [SanPhamController::class, 'quickSearch'])->middleware('throttle:60,1')->name('site.product.quick_search');

// Blog
Route::get('/bai-viet', [BaivietController::class, 'index'])->name('site.blog');
Route::get('/chi-tiet-bai-viet/{slug}', [BaivietController::class, 'blog_detail'])->name('site.blog.detail');

// Image Gallery
Route::get('/hinh-anh', [HinhAnhController::class, 'index'])->name('site.image');

// Contact
Route::get('/lien-he', [LienheController::class, 'index'])->name('site.contact');

// About
Route::get('/gioi-thieu', [GioithieuController::class, 'index'])->name('site.about');

// Cart
Route::get('/gio-hang', [GiohangController::class, 'index'])->name('site.cart');
Route::post('/addcart/{id}', [GiohangController::class, 'addcart'])->name('site.addcart');
Route::post('/updatecart', [GiohangController::class, 'updatecart'])->name('site.updatecart');
Route::match(['post', 'delete'], '/delcart/{id}', [GiohangController::class, 'delcart'])->name('site.delcart');
Route::get('/thanh-toan', [GiohangController::class, 'checkoutForm'])->name('site.checkoutForm');
Route::post('/thanh-toan', [GiohangController::class, 'checkout'])->middleware('throttle:15,1')->name('site.checkout');
Route::get('/cam-on', [GiohangController::class, 'thanks'])->name('site.thanks');

// Cart AJAX Endpoints for In-Menu Live Cart
Route::post('/cart/add-ajax/{id}', [GiohangController::class, 'addcartAjax'])->name('site.cart.addAjax');
Route::post('/cart/update-qty-ajax', [GiohangController::class, 'updateQtyAjax'])->name('site.cart.updateQtyAjax');
Route::delete('/cart/remove-ajax/{id}', [GiohangController::class, 'removeItemAjax'])->name('site.cart.removeAjax');
Route::post('/cart/clear-ajax', [GiohangController::class, 'clearCartAjax'])->name('site.cart.clearAjax');
Route::get('/cart/live-data', [GiohangController::class, 'getLiveCart'])->name('site.cart.liveData');
Route::post('/cart/send-kitchen-ajax', [GiohangController::class, 'sendToKitchenAjax'])->name('site.cart.sendKitchenAjax');
Route::post('/cart/pay-table-now-ajax', [GiohangController::class, 'payTableOrderNowAjax'])->name('site.cart.payTableNowAjax');
Route::post('/cart/switch-dining-mode', [GiohangController::class, 'switchDiningMode'])->name('site.cart.switchDiningMode');
Route::get('/cart/available-tables', [GiohangController::class, 'getAvailableTables'])->name('site.cart.availableTables');
// Momo Callback
Route::get('/checkout/return', [GiohangController::class, 'momoReturn'])->name('site.momo.return');
Route::post('/checkout/ipn', [GiohangController::class, 'momoIpn'])->name('site.momo.ipn');

// Momo Sandbox Giả lập (Chỉ khả dụng ở chế độ PAYMENT_MODE=demo)
Route::middleware('payment-demo')->group(function () {
    Route::get('/checkout/momo-sandbox/{order}', [GiohangController::class, 'momoSandboxPay'])->name('site.momo.sandbox');
    Route::post('/checkout/momo-sandbox/{order}/confirm', [GiohangController::class, 'momoSandboxConfirm'])->name('site.momo.sandbox.confirm');
});

// VietQR Online Payment (Quét mã VietQR trực tuyến)
Route::get('/checkout/vietqr/{order}', [GiohangController::class, 'vietqrPay'])->name('site.vietqr.pay');

// Webhook Thanh toán trực tuyến (Sepay / Casso / Ngân hàng tự động & Polling)
Route::prefix('api')->group(function () {
    Route::post('/webhook/payment', [App\Http\Controllers\api\PaymentWebhookController::class, 'handleWebhook'])->name('api.payment.webhook');
    Route::post('/webhook/sepay', [App\Http\Controllers\api\PaymentWebhookController::class, 'handleWebhook'])->name('api.payment.sepay');
    Route::post('/webhook/casso', [App\Http\Controllers\api\PaymentWebhookController::class, 'handleWebhook'])->name('api.payment.casso');
    Route::get('/payment/check-status/{orderId}', [App\Http\Controllers\api\PaymentWebhookController::class, 'checkPaymentStatus'])->name('api.payment.check-status');
    
    // Giả lập webhook (Chỉ hoạt động ở PAYMENT_MODE=demo)
    Route::middleware('payment-demo')->group(function () {
        Route::post('/payment/simulate-webhook', [App\Http\Controllers\api\PaymentWebhookController::class, 'simulateWebhook'])->name('api.payment.simulate-webhook');
    });
});

// Alias routes cho webhook bên thứ 3 (không cần prefix api)
Route::post('/webhook/payment', [App\Http\Controllers\api\PaymentWebhookController::class, 'handleWebhook']);
Route::post('/webhook/sepay', [App\Http\Controllers\api\PaymentWebhookController::class, 'handleWebhook']);
Route::post('/webhook/casso', [App\Http\Controllers\api\PaymentWebhookController::class, 'handleWebhook']);
Route::get('/payment/check-status/{orderId}', [App\Http\Controllers\api\PaymentWebhookController::class, 'checkPaymentStatus']);

// Orders
Route::get('/don-hang', [DonHangController::class, 'index'])->name('site.orders');
Route::get('/don-hang/{id}', [DonHangController::class, 'detail'])->name('site.orders.detail');
Route::get('/don-hang/reorder/{id}', [DonHangController::class, 'reorder'])->name('site.orders.reorder');
Route::post('/don-hang/{id}/huy', [DonHangController::class, 'cancel'])->name('site.orders.cancel');

// Table QR Scan, Order & Pay (Quẹt QR Gọi Món & Thanh Toán Trực Tiếp)
Route::get('/ban/{id}/goi-mon', [App\Http\Controllers\frontend\TableOrderController::class, 'scan'])->name('site.table.scan');
Route::post('/ban/{id}/bat-dau', [App\Http\Controllers\frontend\TableOrderController::class, 'startOrder'])->name('site.table.start');
Route::post('/ban/{id}/xac-thuc', [App\Http\Controllers\frontend\TableOrderController::class, 'verify'])->name('site.table.verify');
Route::get('/ban/{id}/thanh-toan-qr', [App\Http\Controllers\frontend\TableOrderController::class, 'paymentQr'])->name('site.table.payment.qr');
Route::post('/ban/{id}/xac-nhan-thanh-toan', [App\Http\Controllers\frontend\TableOrderController::class, 'confirmPayment'])->name('site.table.payment.confirm');
Route::get('/ban/{id}/hoa-don', [App\Http\Controllers\frontend\TableOrderController::class, 'viewBill'])->name('site.table.bill');
Route::post('/clear-kitchen-session', [App\Http\Controllers\frontend\CartController::class, 'clearKitchenSession'])->name('site.clear.kitchen.session');
Route::get('/ket-thuc-goi-mon', [App\Http\Controllers\frontend\TableOrderController::class, 'endTableOrder'])->name('site.table.end');

// Service Call (Gọi nhân viên phục vụ)
Route::post('/api/call-waiter', [ServiceCallController::class, 'store'])->name('site.service-call.store');
Route::get('/api/service-call/tables', [ServiceCallController::class, 'getTables'])->name('site.service-call.tables');

// Notifications (Thông báo cho thành viên & khách hàng)
Route::prefix('notifications')->group(function () {
    Route::get('/feed', [App\Http\Controllers\frontend\NotificationController::class, 'getFeed'])->name('site.notifications.feed');
    Route::post('/{id}/read', [App\Http\Controllers\frontend\NotificationController::class, 'markAsRead'])->name('site.notifications.read');
    Route::post('/mark-all-read', [App\Http\Controllers\frontend\NotificationController::class, 'markAllAsRead'])->name('site.notifications.markAllRead');
});

// Booking (Throttled: 10 requests / minute)
Route::get('/dat-ban', [DatBanController::class, 'index'])->name('site.booking');
Route::post('/dat-ban', [DatBanController::class, 'store'])->middleware('throttle:10,1')->name('site.booking.store');

// Reviews (Throttled & Authenticated)
Route::post('/reviews', [DanhGiaController::class, 'store'])->middleware(['auth', 'throttle:15,1'])->name('reviews.store');
Route::delete('/reviews/{review_id}', [DanhGiaController::class, 'delete'])->middleware('auth')->name('reviews.delete');

// Favorites
Route::get('/yeu-thich', [YeuThichController::class, 'index'])->name('site.favorites');
Route::post('/yeu-thich/{productId}', [YeuThichController::class, 'store'])->name('site.favorites.add');
Route::delete('/yeu-thich/{productId}', [YeuThichController::class, 'destroy'])->name('site.favorites.remove');

// Inquiry
Route::post('/yeu-cau', [YeuCauController::class, 'store'])->middleware('throttle:10,1')->name('inquiries.store');

// Policy Pages
Route::get('/dieu-khoan-su-dung', [ChinhsachController::class, 'dksd'])->name('site.chinhsach.dksd');
Route::get('/chinh-sach-bao-mat', [ChinhsachController::class, 'csbm'])->name('site.chinhsach.csbm');
Route::get('/chinh-sach-van-chuyen', [ChinhsachController::class, 'csvc'])->name('site.chinhsach.csvc');
Route::get('/chinh-sach-an-toan-thuc-pham', [ChinhsachController::class, 'attp'])->name('site.chinhsach.attp');
Route::get('/chinh-sach-lien-he', [ChinhsachController::class, 'cslh'])->name('site.chinhsach.cslh');

// Authentication
Route::get('/dang-ky', [ThanhVienController::class, 'register'])->name('site.register');
Route::post('/dang-ky', [ThanhVienController::class, 'doregister'])->middleware('throttle:10,1')->name('site.doregister');
Route::get('/dang-nhap', [ThanhVienController::class, 'login'])->name('site.login');
Route::post('/check-phone', [ThanhVienController::class, 'checkPhone'])->middleware('throttle:20,1')->name('site.check_phone');
Route::post('/dang-nhap', [ThanhVienController::class, 'dologin'])->middleware('throttle:15,1')->name('site.dologin');
Route::get('/dang-xuat', function () {
    return redirect()->route('site.login');
});
Route::post('/dang-xuat', [ThanhVienController::class, 'logout'])->name('site.logout');
Route::get('/thong-tin', [ThanhVienController::class, 'profile'])->name('site.profile');
Route::put('/thong-tin', [ThanhVienController::class, 'updateProfile'])->name('site.updateProfile');

// Password Reset (Strict Throttling to prevent SMS/Email Flooding)
Route::get('/quen-mat-khau', [ThanhVienController::class, 'forgotPassword'])->name('site.forgot_password');
Route::post('/quen-mat-khau', [ThanhVienController::class, 'sendOtp'])->middleware('throttle:5,1')->name('site.send_otp');
Route::post('/xac-nhan-otp', [ThanhVienController::class, 'verifyOtp'])->middleware('throttle:10,1')->name('site.verify_otp');
Route::get('/dat-lai-mat-khau', [ThanhVienController::class, 'showResetPasswordForm'])->name('site.show_reset_password');
Route::post('/dat-lai-mat-khau', [ThanhVienController::class, 'resetPassword'])->middleware('throttle:10,1')->name('site.reset_password');

// Chatbot / Virtual Assistant Routes (Throttled: 120 requests / minute)
// Note: InfinityFree WAF blocks /chatbot and /chat URLs with 403 redirect. Using /tu-van avoids this block completely.
Route::middleware('throttle:120,1')->group(function () {
    Route::post('/tu-van/tin-nhan', [ChatbotController::class, 'sendMessage'])->name('chatbot.message');
    Route::get('/tu-van/goi-y', [ChatbotController::class, 'getSuggestions'])->name('chatbot.suggestions');
    Route::post('/tu-van/lam-moi', [ChatbotController::class, 'clearSession'])->name('chatbot.clear');
});



// ============================================
// ADMIN ROUTES
// ============================================

// CSRF Token Refresh Endpoint (Keep-alive & auto-refresh)
Route::get('/refresh-csrf', function () {
    return response()->json([
        'token' => csrf_token(),
        'status' => 'ok'
    ]);
})->name('refresh-csrf');

// Admin Auth
Route::get('/admin/login', [AuthController::class, 'login'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'dologin'])->name('admin.dologin');
Route::get('/admin/logout', function () {
    return redirect()->route('admin.login');
});
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

// Admin Dashboard (Protected)
Route::prefix('admin')->middleware('login-admin')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');

    // ==============================
    // ROUTE CHO CẢ ADMIN VÀ EMPLOYEE
    // ==============================

    // Orders
    Route::prefix('order')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('admin.order.index');
        Route::get('/orders/{id}', [OrderController::class, 'detail'])->name('admin.order.orderdetail');
        Route::get('/print/{id}', [OrderController::class, 'print'])->name('admin.order.printorder');
        Route::get('/{id}/edit', [OrderController::class, 'edit'])->middleware('admin-access')->name('admin.order.edit');
        Route::post('/{id}/update', [OrderController::class, 'update'])->middleware('admin-access')->name('admin.order.update');
        Route::post('/{id}/delivery', [OrderController::class, 'updateDelivery'])->name('admin.order.updateDelivery');
        Route::post('/{id}/confirm-cod', [OrderController::class, 'confirmCodCollected'])->name('admin.order.confirmCod');
    });

    // Table Orders (POS)
    Route::prefix('table-order')->group(function () {
        Route::get('/', [TableOrderController::class, 'index'])->name('admin.table-order.index');
        Route::post('/check-user-by-phone', [TableOrderController::class, 'checkUserByPhone'])->name('admin.table-order.checkUserByPhone');
        Route::post('/register-table', [TableOrderController::class, 'registerTable'])->name('admin.table-order.registerTable');
        Route::post('/get-products', [TableOrderController::class, 'getProducts'])->name('admin.table-order.getProducts');
        Route::post('/get-table-order', [TableOrderController::class, 'getTableOrder'])->name('admin.table-order.getTableOrder');
        Route::get('/get-order-details/{id}', [TableOrderController::class, 'getOrderDetails'])->name('admin.table-order.getOrderDetails');
        Route::post('/add-product-to-order', [TableOrderController::class, 'addProductToOrder'])->name('admin.table-order.addProductToOrder');
        Route::post('/remove-product-from-order', [TableOrderController::class, 'removeProductFromOrder'])->name('admin.table-order.removeProductFromOrder');
        Route::post('/update-product-qty', [TableOrderController::class, 'updateProductQty'])->name('admin.table-order.updateProductQty');
        Route::post('/update-table-order-quantities', [TableOrderController::class, 'updateTableOrderQuantities'])->name('admin.table-order.updateTableOrderQuantities');
        Route::post('/save-order', [TableOrderController::class, 'saveTableOrder'])->name('admin.table-order.saveOrder');
        Route::post('/increment-order-turn', [TableOrderController::class, 'incrementOrderTurn'])->name('admin.table-order.incrementOrderTurn');
        Route::post('/clear-new-items', [TableOrderController::class, 'clearNewItems'])->name('admin.table-order.clearNewItems');
        Route::get('/check-new-orders', [TableOrderController::class, 'checkNewOrders'])->name('admin.table-order.checkNewOrders');
        Route::get('/{id}/order', [TableOrderController::class, 'order'])->name('admin.table-order.order');
        Route::get('/{id}/payment', [TableOrderController::class, 'payment'])->name('admin.table-order.payment');
        Route::post('/merge-table', [TableOrderController::class, 'mergeTable'])->name('admin.table-order.mergeTable');
        Route::post('/unmerge-table', [TableOrderController::class, 'unmergeTable'])->name('admin.table-order.unmergeTable');
        Route::post('/process-payment', [TableOrderController::class, 'processPayment'])->name('admin.table-order.processPayment');
        Route::post('/cancel-table', [TableOrderController::class, 'cancelTable'])->name('admin.table-order.cancelTable');
        Route::post('/change-table', [TableOrderController::class, 'changeTable'])->name('admin.table-order.changeTable');
        Route::post('/mark-item-as-served', [TableOrderController::class, 'markItemAsServed'])->name('admin.table-order.markItemAsServed');
    });

    // Bookings
    Route::prefix('booking')->group(function () {
        Route::get('/', [BookingController::class, 'index'])->name('admin.booking.index');
        Route::get('/{id}/edit', [BookingController::class, 'edit'])->name('admin.booking.edit');
        Route::post('/{id}/update', [BookingController::class, 'update'])->name('admin.booking.update');
        Route::get('/{id}/detail', [BookingController::class, 'detail'])->name('admin.booking.detail');
    });

    // System Live Notifications & Service Calls (Thông báo hệ thống & Gọi nhân viên)
    Route::prefix('notifications')->group(function () {
        Route::get('/latest', [NotificationController::class, 'getLatestNotifications'])->name('admin.notifications.latest');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('admin.notifications.read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('admin.notifications.markAllRead');
        Route::post('/booking/{id}/viewed', [NotificationController::class, 'markBookingViewed'])->name('admin.notifications.booking.viewed');
        Route::post('/service-call/{id}/complete', [NotificationController::class, 'completeServiceCall'])->name('admin.notifications.service-call.complete');
        Route::post('/service-call/{id}/cancel', [NotificationController::class, 'cancelServiceCall'])->name('admin.notifications.service-call.cancel');
        Route::post('/table/{tableId}/complete-calls', [NotificationController::class, 'completeTableCalls'])->name('admin.notifications.table.complete-calls');
    });

    // Dishes & Bill Breakdown for Table (Dành cho POS cả Admin & Employee)
    Route::get('/table/{id}/dishes', [TableController::class, 'getTableDishes'])->name('admin.table.dishes');

    // ==============================
    // ROUTE CHỈ CHO ADMIN
    // ==============================
    Route::middleware('admin-access')->group(function () {
        // Báo cáo & Doanh thu xuất Excel (Dành riêng cho Admin)
        Route::get('/export-best-selling', [DashboardController::class, 'exportBestSelling'])->name('admin.dashboard.exportBestSelling');
        Route::get('/order/export', [OrderController::class, 'export'])->name('admin.order.export');

        // System Migration Runner: Chi kich hoat khi ALLOW_SYSTEM_MIGRATE=true trong .env (doc qua config app)
        if (config('app.allow_system_migrate', false)) {
        // System Migration Runner (Yêu cầu xác nhận POST với CSRF token, chỉ tài khoản Admin thực hiện)
        Route::get('/system-migrate', function () {
            $csrfField = csrf_field();
            $actionUrl = route('admin.system-migrate.post');
            $html = <<<HTML
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>CIVILIZE - Xác nhận thực thi Migration</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f5f5f5; padding: 40px; margin: 0; }
        .card { max-width: 600px; margin: 0 auto; background: #fff; padding: 32px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        h2 { margin-top: 0; color: #1a1a1a; font-size: 20px; }
        p { color: #555; line-height: 1.6; font-size: 14px; }
        .btn { display: inline-block; padding: 10px 24px; background: #ea580c; color: #fff; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; text-decoration: none; font-size: 14px; }
        .btn:hover { background: #c2410c; }
        .cancel { display: inline-block; margin-left: 12px; color: #666; text-decoration: none; font-size: 14px; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Xác nhận cập nhật Cơ sở dữ liệu (System Migration)</h2>
        <p>Hệ thống sẽ chạy lệnh <code>php artisan migrate --force</code> để đồng bộ các bảng và cột mới vào cơ sở dữ liệu. Thao tác này an toàn và không làm mất dữ liệu hiện tại.</p>
        <p>Để đảm bảo an toàn bảo mật và chống tấn công CSRF, thao tác bắt buộc thực hiện qua phương thức <strong>POST</strong> có mã bảo vệ CSRF hợp lệ.</p>
        <form method="POST" action="{$actionUrl}" style="margin-top: 24px;">
            {$csrfField}
            <button type="submit" class="btn">Xác nhận chạy Migration (POST CSRF)</button>
            <a href="/admin" class="cancel">Quay lại Dashboard</a>
        </form>
    </div>
</body>
</html>
HTML;
            return response($html);
        })->name('admin.system-migrate');

        Route::post('/system-migrate', function () {
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            $output = \Illuminate\Support\Facades\Artisan::output();
            return response("<pre style='padding:24px;background:#1e1e1e;color:#52c41a;font-family:monospace;font-size:14px;border-radius:8px;line-height:1.6;'>[CIVILIZE RESTAURANT] System Migration Executed Successfully via POST CSRF:\n\n" . htmlspecialchars($output ?: "Nothing to migrate (Database is up to date).\n") . "\n<a href='/admin' style='color:#38bdf8;text-decoration:underline;'>Quay lại trang quản trị</a></pre>");
        })->name('admin.system-migrate.post');
        }

        // Order History (Báo cáo & Doanh thu)
            Route::prefix('order-history')->group(function () {
                Route::get('/', [OrderHistoryController::class, 'index'])->name('admin.order-history.index');
                Route::get('/export', [OrderHistoryController::class, 'export'])->name('admin.order-history.export');
                Route::get('/export-payment', [OrderHistoryController::class, 'exportPayment'])->name('admin.order-history.exportPayment');
                Route::get('/{id}', [OrderHistoryController::class, 'show'])->name('admin.order-history.show');
            });

            // Inquiries
            Route::prefix('inquiries')->group(function () {
                Route::get('/', [InquiryController::class, 'index'])->name('admin.inquiry.index');
                Route::get('/{id}', [InquiryController::class, 'show'])->name('admin.inquiry.show');
                Route::post('/{id}/update-status', [InquiryController::class, 'updateStatus'])->name('admin.inquiry.updateStatus');
            });
        // Products
        Route::prefix('product')->group(function () {
            Route::get('/', [ProductController::class, 'index'])->name('admin.product.index');
            Route::get('/export', [ProductController::class, 'export'])->name('admin.product.export');
            Route::get('/create', [ProductController::class, 'create'])->name('admin.product.create');
            Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('admin.product.edit');
            Route::get('/{product}/show', [ProductController::class, 'show'])->name('admin.product.show');
            Route::put('/{product}/update', [ProductController::class, 'update'])->name('admin.product.update');
            Route::post('/{product}/delete', [ProductController::class, 'delete'])->name('admin.product.delete');
            Route::delete('/{product}/destroy', [ProductController::class, 'destroy'])->name('admin.product.destroy');
            Route::get('/trash', [ProductController::class, 'trash'])->name('admin.product.trash');
            Route::post('/store', [ProductController::class, 'store'])->name('admin.product.store');
            Route::post('/{product}/status', [ProductController::class, 'status'])->name('admin.product.status');
            Route::post('/{product}/restore', [ProductController::class, 'restore'])->name('admin.product.restore');
            Route::get('/{product}/reviews', [ProductController::class, 'showReviews'])->name('admin.product.reviews');
            Route::post('/review/respond/{product}/{review}', [ReviewController::class, 'respond'])->name('admin.review.respond');
        });

        // Categories
        Route::prefix('category')->group(function () {
            Route::get('/', [CategoryController::class, 'index'])->name('admin.category.index');
            Route::get('/create', [CategoryController::class, 'create'])->name('admin.category.create');
            Route::get('/{category}/show', [CategoryController::class, 'show'])->name('admin.category.show');
            Route::put('/{category}/update', [CategoryController::class, 'update'])->name('admin.category.update');
            Route::delete('/{category}/destroy', [CategoryController::class, 'destroy'])->name('admin.category.destroy');
            Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('admin.category.edit');
            Route::post('/store', [CategoryController::class, 'store'])->name('admin.category.store');
            Route::post('/{category}/delete', [CategoryController::class, 'delete'])->name('admin.category.delete');
            Route::get('/trash', [CategoryController::class, 'trash'])->name('admin.category.trash');
            Route::post('/{category}/status', [CategoryController::class, 'status'])->name('admin.category.status');
            Route::post('/{category}/restore', [CategoryController::class, 'restore'])->name('admin.category.restore');
        });

        // Import Goods (Quản lý nhập kho - Phiếu nhập hàng)
        Route::prefix('import-goods')->group(function () {
            Route::get('/', [ImportGoodsController::class, 'index'])->name('admin.import-goods.index');
            Route::get('/export', [ImportGoodsController::class, 'export'])->name('admin.import-goods.export');
            Route::get('/create', [ImportGoodsController::class, 'create'])->name('admin.import-goods.create');
            Route::post('/', [ImportGoodsController::class, 'store'])->name('admin.import-goods.store');
            Route::get('/{id}', [ImportGoodsController::class, 'show'])->name('admin.import-goods.show');
            Route::get('/{id}/edit', [ImportGoodsController::class, 'edit'])->name('admin.import-goods.edit');
            Route::put('/{id}', [ImportGoodsController::class, 'update'])->name('admin.import-goods.update');
            Route::delete('/{id}', [ImportGoodsController::class, 'destroy'])->name('admin.import-goods.destroy');
            Route::get('/{id}/print', [ImportGoodsController::class, 'printSlip'])->name('admin.import-goods.print');
        });

        // Brands
        Route::prefix('brand')->group(function () {
            Route::get('/', [BrandController::class, 'index'])->name('admin.brand.index');
            Route::get('/{brand}/show', [BrandController::class, 'show'])->name('admin.brand.show');
            Route::delete('/{brand}/destroy', [BrandController::class, 'destroy'])->name('admin.brand.destroy');
            Route::post('/store', [BrandController::class, 'store'])->name('admin.brand.store');
            Route::get('/create', [BrandController::class, 'create'])->name('admin.brand.create');
            Route::put('/{brand}/update', [BrandController::class, 'update'])->name('admin.brand.update');
            Route::get('/{brand}/edit', [BrandController::class, 'edit'])->name('admin.brand.edit');
            Route::post('/{brand}/delete', [BrandController::class, 'delete'])->name('admin.brand.delete');
            Route::get('/trash', [BrandController::class, 'trash'])->name('admin.brand.trash');
            Route::post('/{brand}/status', [BrandController::class, 'status'])->name('admin.brand.status');
            Route::post('/{brand}/restore', [BrandController::class, 'restore'])->name('admin.brand.restore');
        });

        // Banners
        Route::prefix('banner')->group(function () {
            Route::get('/', [BannerController::class, 'index'])->name('admin.banner.index');
            Route::get('/{banner}/show', [BannerController::class, 'show'])->name('admin.banner.show');
            Route::delete('/{banner}/destroy', [BannerController::class, 'destroy'])->name('admin.banner.destroy');
            Route::put('/{banner}/update', [BannerController::class, 'update'])->name('admin.banner.update');
            Route::post('/store', [BannerController::class, 'store'])->name('admin.banner.store');
            Route::get('/create', [BannerController::class, 'create'])->name('admin.banner.create');
            Route::get('/{banner}/edit', [BannerController::class, 'edit'])->name('admin.banner.edit');
            Route::post('/{banner}/delete', [BannerController::class, 'delete'])->name('admin.banner.delete');
            Route::get('/trash', [BannerController::class, 'trash'])->name('admin.banner.trash');
            Route::post('/{banner}/status', [BannerController::class, 'status'])->name('admin.banner.status');
            Route::post('/{banner}/restore', [BannerController::class, 'restore'])->name('admin.banner.restore');
        });

        // Blogs
        Route::prefix('blog')->group(function () {
            Route::get('/', [BlogController::class, 'index'])->name('admin.blog.index');
            Route::get('/create', [BlogController::class, 'create'])->name('admin.blog.create');
            Route::get('/{blog}/show', [BlogController::class, 'show'])->name('admin.blog.show');
            Route::put('/{blog}/update', [BlogController::class, 'update'])->name('admin.blog.update');
            Route::post('/store', [BlogController::class, 'store'])->name('admin.blog.store');
            Route::delete('/{blog}/destroy', [BlogController::class, 'destroy'])->name('admin.blog.destroy');
            Route::get('/{blog}/edit', [BlogController::class, 'edit'])->name('admin.blog.edit');
            Route::post('/{blog}/delete', [BlogController::class, 'delete'])->name('admin.blog.delete');
            Route::get('/trash', [BlogController::class, 'trash'])->name('admin.blog.trash');
            Route::post('/{blog}/status', [BlogController::class, 'status'])->name('admin.blog.status');
            Route::post('/{blog}/restore', [BlogController::class, 'restore'])->name('admin.blog.restore');
        });

        // Topics
        Route::prefix('topic')->group(function () {
            Route::get('/', [TopicController::class, 'index'])->name('admin.topic.index');
            Route::get('/{topic}/show', [TopicController::class, 'show'])->name('admin.topic.show');
            Route::put('/{topic}/update', [TopicController::class, 'update'])->name('admin.topic.update');
            Route::post('/store', [TopicController::class, 'store'])->name('admin.topic.store');
            Route::delete('/{topic}/destroy', [TopicController::class, 'destroy'])->name('admin.topic.destroy');
            Route::get('/create', [TopicController::class, 'create'])->name('admin.topic.create');
            Route::get('/{topic}/edit', [TopicController::class, 'edit'])->name('admin.topic.edit');
            Route::post('/{topic}/delete', [TopicController::class, 'delete'])->name('admin.topic.delete');
            Route::get('/trash', [TopicController::class, 'trash'])->name('admin.topic.trash');
            Route::post('/{topic}/status', [TopicController::class, 'status'])->name('admin.topic.status');
            Route::post('/{topic}/restore', [TopicController::class, 'restore'])->name('admin.topic.restore');
        });

        // Images
        Route::prefix('image')->group(function () {
            Route::get('/', [ImageController::class, 'index'])->name('admin.image.index');
            Route::get('/create', [ImageController::class, 'create'])->name('admin.image.create');
            Route::get('/{image}/edit', [ImageController::class, 'edit'])->name('admin.image.edit');
            Route::get('/{image}/show', [ImageController::class, 'show'])->name('admin.image.show');
            Route::put('/{image}/update', [ImageController::class, 'update'])->name('admin.image.update');
            Route::post('/store', [ImageController::class, 'store'])->name('admin.image.store');
            Route::delete('/{image}/destroy', [ImageController::class, 'destroy'])->name('admin.image.destroy');
            Route::get('/trash', [ImageController::class, 'trash'])->name('admin.image.trash');
            Route::post('/{image}/status', [ImageController::class, 'status'])->name('admin.image.status');
            Route::post('/{image}/delete', [ImageController::class, 'delete'])->name('admin.image.delete');
            Route::post('/{image}/restore', [ImageController::class, 'restore'])->name('admin.image.restore');
        });

        // Contacts
        Route::prefix('contact')->group(function () {
            Route::get('/', [ContactController::class, 'index'])->name('admin.contact.index');
            Route::get('/create', [ContactController::class, 'create'])->name('admin.contact.create');
            Route::get('/{contact}/show', [ContactController::class, 'show'])->name('admin.contact.show');
            Route::put('/{contact}/update', [ContactController::class, 'update'])->name('admin.contact.update');
            Route::post('/store', [ContactController::class, 'store'])->name('admin.contact.store');
            Route::delete('/{contact}/destroy', [ContactController::class, 'destroy'])->name('admin.contact.destroy');
            Route::get('/{contact}/edit', [ContactController::class, 'edit'])->name('admin.contact.edit');
            Route::post('/{contact}/delete', [ContactController::class, 'delete'])->name('admin.contact.delete');
            Route::get('/trash', [ContactController::class, 'trash'])->name('admin.contact.trash');
            Route::post('/{contact}/status', [ContactController::class, 'status'])->name('admin.contact.status');
            Route::post('/{contact}/restore', [ContactController::class, 'restore'])->name('admin.contact.restore');
        });

        // Users
        Route::prefix('user')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('admin.user.index');
            Route::get('/customers', [UserController::class, 'customers'])->name('admin.user.customers');
            Route::get('/customers/export', [UserController::class, 'exportCustomers'])->name('admin.user.customers.export');
            Route::get('/employees', [UserController::class, 'listEmployees'])->name('admin.user.employees');
            Route::get('/create', [UserController::class, 'create'])->name('admin.user.create');
            Route::get('/{user}/show', [UserController::class, 'show'])->name('admin.user.show');
            Route::put('/{user}/update', [UserController::class, 'update'])->name('admin.user.update');
            Route::post('/store', [UserController::class, 'store'])->name('admin.user.store');
            Route::delete('/{user}/destroy', [UserController::class, 'destroy'])->name('admin.user.destroy');
            Route::get('/{user}/edit', [UserController::class, 'edit'])->name('admin.user.edit');
            Route::post('/{user}/delete', [UserController::class, 'delete'])->name('admin.user.delete');
            Route::get('/trash', [UserController::class, 'trash'])->name('admin.user.trash');
            Route::post('/{user}/status', [UserController::class, 'status'])->name('admin.user.status');
            Route::post('/{user}/restore', [UserController::class, 'restore'])->name('admin.user.restore');
            Route::post('/{user}/remove-admin', [UserController::class, 'removeAdmin'])->name('admin.user.removeAdmin');
            Route::post('/{user}/add-admin', [UserController::class, 'addAdmin'])->name('admin.user.addAdmin');
        });
        Route::post('/user/{user}/toggle-line', [UserController::class, 'toggleLine'])->name('admin.user.toggleLine');

        // Keywords
        Route::prefix('keyword')->group(function () {
            Route::get('/', [KeywordController::class, 'index'])->name('admin.keyword.index');
            Route::get('/create', [KeywordController::class, 'create'])->name('admin.keyword.create');
            Route::get('/{keyword}/show', [KeywordController::class, 'show'])->name('admin.keyword.show');
            Route::put('/{keyword}/update', [KeywordController::class, 'update'])->name('admin.keyword.update');
            Route::post('/store', [KeywordController::class, 'store'])->name('admin.keyword.store');
            Route::delete('/{keyword}/destroy', [KeywordController::class, 'destroy'])->name('admin.keyword.destroy');
            Route::get('/{keyword}/edit', [KeywordController::class, 'edit'])->name('admin.keyword.edit');
            Route::post('/{keyword}/delete', [KeywordController::class, 'delete'])->name('admin.keyword.delete');
            Route::get('/trash', [KeywordController::class, 'trash'])->name('admin.keyword.trash');
            Route::post('/{keyword}/status', [KeywordController::class, 'status'])->name('admin.keyword.status');
            Route::post('/{keyword}/restore', [KeywordController::class, 'restore'])->name('admin.keyword.restore');
        });

        // Menus
        Route::prefix('menu')->group(function () {
            Route::get('/', [MenuController::class, 'index'])->name('admin.menu.index');
            Route::get('/create', [MenuController::class, 'create'])->name('admin.menu.create');
            Route::get('/{menu}/edit', [MenuController::class, 'edit'])->name('admin.menu.edit');
            Route::get('/{menu}/show', [MenuController::class, 'show'])->name('admin.menu.show');
            Route::put('/{menu}/update', [MenuController::class, 'update'])->name('admin.menu.update');
            Route::post('/store', [MenuController::class, 'store'])->name('admin.menu.store');
            Route::delete('/{menu}/destroy', [MenuController::class, 'destroy'])->name('admin.menu.destroy');
            Route::post('/{menu}/delete', [MenuController::class, 'delete'])->name('admin.menu.delete');
            Route::get('/trash', [MenuController::class, 'trash'])->name('admin.menu.trash');
            Route::post('/{menu}/status', [MenuController::class, 'status'])->name('admin.menu.status');
            Route::post('/{menu}/restore', [MenuController::class, 'restore'])->name('admin.menu.restore');
        });

        // Tables
        Route::prefix('table')->group(function () {
            Route::get('/', [TableController::class, 'index'])->name('admin.table.index');
            Route::get('/create', [TableController::class, 'create'])->name('admin.table.create');
            Route::post('/store', [TableController::class, 'store'])->name('admin.table.store');
            Route::get('/{id}/edit', [TableController::class, 'edit'])->name('admin.table.edit');
            Route::post('/{id}/update', [TableController::class, 'update'])->name('admin.table.update');
            Route::post('/{id}/status', [TableController::class, 'status'])->name('admin.table.status');
            Route::post('/{id}/delete', [TableController::class, 'delete'])->name('admin.table.delete');
            Route::get('/trash', [TableController::class, 'trash'])->name('admin.table.trash');
            Route::post('/{id}/restore', [TableController::class, 'restore'])->name('admin.table.restore');
            Route::delete('/{id}/destroy', [TableController::class, 'destroy'])->name('admin.table.destroy');
            // Download QR
            Route::get('/{id}/qr', [TableController::class, 'downloadQr'])->name('admin.table.qr');
        });

        // Settings (Cài đặt hệ thống)
        Route::prefix('setting')->group(function () {
            Route::get('/', [SettingController::class, 'index'])->name('admin.setting.index');
            Route::post('/update', [SettingController::class, 'update'])->name('admin.setting.update');
        });
    });
});
