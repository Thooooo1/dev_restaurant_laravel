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
use App\Http\Controllers\frontend\ChinhSachController;
use App\Http\Controllers\frontend\BookingController as DatBanController;
use App\Http\Controllers\frontend\ReviewController as DanhGiaController;
use App\Http\Controllers\frontend\OrderController as DonHangController;
use App\Http\Controllers\frontend\UserFavoriteController as YeuThichController;
use App\Http\Controllers\frontend\InquiryController as YeuCauController;

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
use App\Http\Controllers\backend\ContactController;
use App\Http\Controllers\backend\KeywordController;
use App\Http\Controllers\backend\MenuController;
use App\Http\Controllers\backend\BookingController;
use App\Http\Controllers\backend\InquiryController;
use App\Http\Controllers\backend\TableController;
use App\Http\Controllers\backend\TableOrderController;

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
Route::get('/delcart/{id}', [GiohangController::class, 'delcart'])->name('site.delcart');
Route::get('/thanh-toan', [GiohangController::class, 'checkoutForm'])->name('site.checkoutForm');
Route::post('/thanh-toan', [GiohangController::class, 'checkout'])->name('site.checkout');
Route::get('/cam-on', [GiohangController::class, 'thanks'])->name('site.thanks');

// Orders
Route::get('/don-hang', [DonHangController::class, 'index'])->name('site.orders');
Route::get('/don-hang/{id}', [DonHangController::class, 'detail'])->name('site.orders.detail');
Route::get('/don-hang/reorder/{id}', [DonHangController::class, 'reorder'])->name('site.orders.reorder');
Route::post('/don-hang/{id}/huy', [DonHangController::class, 'cancel'])->name('site.orders.cancel');

// Booking
Route::get('/dat-ban', [DatBanController::class, 'index'])->name('site.booking');
Route::post('/dat-ban', [DatBanController::class, 'store'])->name('site.booking.store');

// Reviews
Route::post('/reviews', [DanhGiaController::class, 'store'])->name('reviews.store');
Route::delete('/reviews/{review_id}', [DanhGiaController::class, 'delete'])->name('reviews.delete');

// Favorites
Route::get('/yeu-thich', [YeuThichController::class, 'index'])->name('site.favorites');
Route::post('/yeu-thich/{productId}', [YeuThichController::class, 'store'])->name('site.favorites.add');
Route::delete('/yeu-thich/{productId}', [YeuThichController::class, 'destroy'])->name('site.favorites.remove');

// Inquiry
Route::post('/yeu-cau', [YeuCauController::class, 'store'])->name('inquiries.store');

// Policy Pages
Route::get('/dieu-khoan-su-dung', [ChinhSachController::class, 'dksd'])->name('site.chinhsach.dksd');
Route::get('/chinh-sach-bao-mat', [ChinhSachController::class, 'csbm'])->name('site.chinhsach.csbm');
Route::get('/chinh-sach-van-chuyen', [ChinhSachController::class, 'csvc'])->name('site.chinhsach.csvc');
Route::get('/chinh-sach-an-toan-thuc-pham', [ChinhSachController::class, 'attp'])->name('site.chinhsach.attp');
Route::get('/chinh-sach-lien-he', [ChinhSachController::class, 'cslh'])->name('site.chinhsach.cslh');

// Authentication
Route::get('/dang-ky', [ThanhVienController::class, 'register'])->name('site.register');
Route::post('/dang-ky', [ThanhVienController::class, 'doregister'])->name('site.doregister');
Route::get('/dang-nhap', [ThanhVienController::class, 'login'])->name('site.login');
Route::post('/dang-nhap', [ThanhVienController::class, 'dologin'])->name('site.dologin');
Route::get('/dang-xuat', [ThanhVienController::class, 'logout'])->name('site.logout');
Route::get('/thong-tin', [ThanhVienController::class, 'profile'])->name('site.profile');
Route::put('/thong-tin', [ThanhVienController::class, 'updateProfile'])->name('site.updateProfile');

// Password Reset
Route::get('/quen-mat-khau', [ThanhVienController::class, 'forgotPassword'])->name('site.forgot_password');
Route::post('/quen-mat-khau', [ThanhVienController::class, 'sendOtp'])->name('site.send_otp');
Route::post('/xac-nhan-otp', [ThanhVienController::class, 'verifyOtp'])->name('site.verify_otp');
Route::get('/dat-lai-mat-khau', [ThanhVienController::class, 'showResetPasswordForm'])->name('site.show_reset_password');
Route::post('/dat-lai-mat-khau', [ThanhVienController::class, 'resetPassword'])->name('site.reset_password');

// Google OAuth
Route::get('/google/redirect', [ThanhVienController::class, 'redirectToGoogle'])->name('google.redirect');
Route::get('/google/callback', [ThanhVienController::class, 'handleGoogleCallback'])->name('google.callback');

// ============================================
// ADMIN ROUTES
// ============================================

// Admin Auth
Route::get('/admin/login', [AuthController::class, 'login'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'dologin'])->name('admin.dologin');
Route::get('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

// Admin Dashboard (Protected)
Route::prefix('admin')->middleware('login-admin')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Products
    Route::prefix('product')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('admin.product.index');
        Route::get('/create', [ProductController::class, 'create'])->name('admin.product.create');
        Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('admin.product.edit');
        Route::get('/{product}/show', [ProductController::class, 'show'])->name('admin.product.show');
        Route::put('/{product}/update', [ProductController::class, 'update'])->name('admin.product.update');
        Route::get('/{product}/delete', [ProductController::class, 'delete'])->name('admin.product.delete');
        Route::delete('/{product}/destroy', [ProductController::class, 'destroy'])->name('admin.product.destroy');
        Route::get('/trash', [ProductController::class, 'trash'])->name('admin.product.trash');
        Route::post('/store', [ProductController::class, 'store'])->name('admin.product.store');
        Route::get('/{product}/status', [ProductController::class, 'status'])->name('admin.product.status');
        Route::get('/{product}/restore', [ProductController::class, 'restore'])->name('admin.product.restore');
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
        Route::get('/{category}/delete', [CategoryController::class, 'delete'])->name('admin.category.delete');
        Route::get('/trash', [CategoryController::class, 'trash'])->name('admin.category.trash');
        Route::get('/{category}/status', [CategoryController::class, 'status'])->name('admin.category.status');
        Route::get('/{category}/restore', [CategoryController::class, 'restore'])->name('admin.category.restore');
    });

    // Orders
    Route::prefix('order')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('admin.order.index');
        Route::get('/orders/{id}', [OrderController::class, 'detail'])->name('admin.order.orderdetail');
        Route::get('/print/{id}', [OrderController::class, 'print'])->name('admin.order.printorder');
        Route::get('/{id}/edit', [OrderController::class, 'edit'])->name('admin.order.edit');
        Route::post('/{id}/update', [OrderController::class, 'update'])->name('admin.order.update');
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
        Route::post('/save-order', [TableOrderController::class, 'saveTableOrder'])->name('admin.table-order.saveOrder');
        Route::post('/merge-table', [TableOrderController::class, 'mergeTable'])->name('admin.table-order.mergeTable');
    });

    // Bookings
    Route::prefix('booking')->group(function () {
        Route::get('/', [BookingController::class, 'index'])->name('admin.booking.index');
        Route::get('/{id}/edit', [BookingController::class, 'edit'])->name('admin.booking.edit');
        Route::post('/{id}/update', [BookingController::class, 'update'])->name('admin.booking.update');
        Route::get('/{id}/detail', [BookingController::class, 'detail'])->name('admin.booking.detail');
    });

    // Inquiries
    Route::prefix('inquiries')->group(function () {
        Route::get('/', [InquiryController::class, 'index'])->name('admin.inquiry.index');
        Route::get('/{id}', [InquiryController::class, 'show'])->name('admin.inquiry.show');
        Route::post('/{id}/update-status', [InquiryController::class, 'updateStatus'])->name('admin.inquiry.updateStatus');
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
        Route::get('/{brand}/delete', [BrandController::class, 'delete'])->name('admin.brand.delete');
        Route::get('/trash', [BrandController::class, 'trash'])->name('admin.brand.trash');
        Route::get('/{brand}/status', [BrandController::class, 'status'])->name('admin.brand.status');
        Route::get('/{brand}/restore', [BrandController::class, 'restore'])->name('admin.brand.restore');
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
        Route::get('/{banner}/delete', [BannerController::class, 'delete'])->name('admin.banner.delete');
        Route::get('/trash', [BannerController::class, 'trash'])->name('admin.banner.trash');
        Route::get('/{banner}/status', [BannerController::class, 'status'])->name('admin.banner.status');
        Route::get('/{banner}/restore', [BannerController::class, 'restore'])->name('admin.banner.restore');
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
        Route::get('/{blog}/delete', [BlogController::class, 'delete'])->name('admin.blog.delete');
        Route::get('/trash', [BlogController::class, 'trash'])->name('admin.blog.trash');
        Route::get('/{blog}/status', [BlogController::class, 'status'])->name('admin.blog.status');
        Route::get('/{blog}/restore', [BlogController::class, 'restore'])->name('admin.blog.restore');
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
        Route::get('/{topic}/delete', [TopicController::class, 'delete'])->name('admin.topic.delete');
        Route::get('/trash', [TopicController::class, 'trash'])->name('admin.topic.trash');
        Route::get('/{topic}/status', [TopicController::class, 'status'])->name('admin.topic.status');
        Route::get('/{topic}/restore', [TopicController::class, 'restore'])->name('admin.topic.restore');
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
        Route::get('/{image}/status', [ImageController::class, 'status'])->name('admin.image.status');
        Route::get('/{image}/delete', [ImageController::class, 'delete'])->name('admin.image.delete');
        Route::get('/{image}/restore', [ImageController::class, 'restore'])->name('admin.image.restore');
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
        Route::get('/{contact}/delete', [ContactController::class, 'delete'])->name('admin.contact.delete');
        Route::get('/trash', [ContactController::class, 'trash'])->name('admin.contact.trash');
        Route::get('/{contact}/status', [ContactController::class, 'status'])->name('admin.contact.status');
        Route::get('/{contact}/restore', [ContactController::class, 'restore'])->name('admin.contact.restore');
    });

    // Users
    Route::prefix('user')->middleware('admin-access')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('admin.user.index');
        Route::get('/customers', [UserController::class, 'customers'])->name('admin.user.customers');
        Route::get('/employees', [UserController::class, 'listEmployees'])->name('admin.user.employees');
        Route::get('/create', [UserController::class, 'create'])->name('admin.user.create');
        Route::get('/{user}/show', [UserController::class, 'show'])->name('admin.user.show');
        Route::put('/{user}/update', [UserController::class, 'update'])->name('admin.user.update');
        Route::post('/store', [UserController::class, 'store'])->name('admin.user.store');
        Route::delete('/{user}/destroy', [UserController::class, 'destroy'])->name('admin.user.destroy');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('admin.user.edit');
        Route::get('/{user}/delete', [UserController::class, 'delete'])->name('admin.user.delete');
        Route::get('/trash', [UserController::class, 'trash'])->name('admin.user.trash');
        Route::get('/{user}/status', [UserController::class, 'status'])->name('admin.user.status');
        Route::get('/{user}/restore', [UserController::class, 'restore'])->name('admin.user.restore');
        Route::get('/{user}/remove-admin', [UserController::class, 'removeAdmin'])->name('admin.user.removeAdmin');
        Route::get('/{user}/add-admin', [UserController::class, 'addAdmin'])->name('admin.user.addAdmin');
    });
    Route::get('/user/{user}/toggle-line', [UserController::class, 'toggleLine'])->name('admin.user.toggleLine');

    // Keywords
    Route::prefix('keyword')->group(function () {
        Route::get('/', [KeywordController::class, 'index'])->name('admin.keyword.index');
        Route::get('/create', [KeywordController::class, 'create'])->name('admin.keyword.create');
        Route::get('/{keyword}/show', [KeywordController::class, 'show'])->name('admin.keyword.show');
        Route::put('/{keyword}/update', [KeywordController::class, 'update'])->name('admin.keyword.update');
        Route::post('/store', [KeywordController::class, 'store'])->name('admin.keyword.store');
        Route::delete('/{keyword}/destroy', [KeywordController::class, 'destroy'])->name('admin.keyword.destroy');
        Route::get('/{keyword}/edit', [KeywordController::class, 'edit'])->name('admin.keyword.edit');
        Route::get('/{keyword}/delete', [KeywordController::class, 'delete'])->name('admin.keyword.delete');
        Route::get('/trash', [KeywordController::class, 'trash'])->name('admin.keyword.trash');
        Route::get('/{keyword}/status', [KeywordController::class, 'status'])->name('admin.keyword.status');
        Route::get('/{keyword}/restore', [KeywordController::class, 'restore'])->name('admin.keyword.restore');
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
        Route::get('/{menu}/delete', [MenuController::class, 'delete'])->name('admin.menu.delete');
        Route::get('/trash', [MenuController::class, 'trash'])->name('admin.menu.trash');
        Route::get('/{menu}/status', [MenuController::class, 'status'])->name('admin.menu.status');
        Route::get('/{menu}/restore', [MenuController::class, 'restore'])->name('admin.menu.restore');
    });

    // Tables
    Route::prefix('table')->group(function () {
        Route::get('/', [TableController::class, 'index'])->name('admin.table.index');
        Route::get('/create', [TableController::class, 'create'])->name('admin.table.create');
        Route::post('/store', [TableController::class, 'store'])->name('admin.table.store');
        Route::get('/{id}/edit', [TableController::class, 'edit'])->name('admin.table.edit');
        Route::post('/{id}/update', [TableController::class, 'update'])->name('admin.table.update');
        Route::get('/{id}/status', [TableController::class, 'status'])->name('admin.table.status');
        Route::get('/{id}/delete', [TableController::class, 'delete'])->name('admin.table.delete');
        Route::get('/trash', [TableController::class, 'trash'])->name('admin.table.trash');
        Route::get('/{id}/restore', [TableController::class, 'restore'])->name('admin.table.restore');
        Route::delete('/{id}/destroy', [TableController::class, 'destroy'])->name('admin.table.destroy');
    });
});
