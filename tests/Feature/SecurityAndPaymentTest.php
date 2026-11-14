<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Table;
use App\Models\Orderdetail;
use App\Models\Product;
use App\Models\Review;
use App\Models\Booking;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SecurityAndPaymentTest extends TestCase
{
    /**
     * 1. Test Phân quyền: Nhân viên vào trang quản lý sản phẩm bị chặn, Quản trị viên vào được
     */
    public function test_employee_cannot_access_product_admin_but_admin_can(): void
    {
        $admin = User::where('roles', 'admin')->where('status', 1)->first();
        if (!$admin) {
            $admin = User::create([
                'username' => 'test_admin_' . uniqid(),
                'fullname' => 'Test Admin',
                'password' => Hash::make('123456'),
                'phone' => '0999999991',
                'roles' => 'admin',
                'admin_lever' => 1,
                'status' => 1,
            ]);
        }

        $employee = User::where('roles', 'employee')->where('status', 1)->first();
        if (!$employee) {
            $employee = User::create([
                'username' => 'test_emp_' . uniqid(),
                'fullname' => 'Test Employee',
                'password' => Hash::make('123456'),
                'phone' => '0999999992',
                'roles' => 'employee',
                'admin_lever' => 2,
                'status' => 1,
            ]);
        }

        // Nhân viên truy cập bị chặn (403 hoặc redirect có thông báo lỗi)
        $responseEmp = $this->actingAs($employee)->get(route('admin.product.index'));
        $this->assertTrue(in_array($responseEmp->getStatusCode(), [403, 302]));

        // Quản trị viên truy cập hợp lệ (200)
        $responseAdmin = $this->actingAs($admin)->get(route('admin.product.index'));
        $this->assertEquals(200, $responseAdmin->getStatusCode());
    }

    /**
     * 2. Test A1: Nhập SĐT Admin tại bàn gọi món tuyệt đối không tự động đăng nhập Admin
     */
    public function test_table_order_with_admin_phone_does_not_login_admin(): void
    {
        Auth::logout();

        $admin = User::where('roles', 'admin')->where('status', 1)->first();
        $table = Table::first();

        if ($admin && $table) {
            $response = $this->post(route('site.table.start', $table->id), [
                'phone' => $admin->phone,
                'name' => 'Khách Giả Mạo Admin',
            ]);

            // Trạng thái đăng nhập phải không chứa quyền Admin
            if (Auth::check()) {
                $this->assertNotEquals('admin', Auth::user()->roles);
            } else {
                $this->assertFalse(Auth::check());
            }
        } else {
            $this->assertTrue(true);
        }
    }

    /**
     * 3. Test A2: Quên mật khẩu nhập SĐT Admin bị từ chối
     */
    public function test_forgot_password_with_admin_phone_is_rejected(): void
    {
        $admin = User::where('roles', 'admin')->where('status', 1)->first();

        if ($admin) {
            $response = $this->post(route('site.send_otp'), [
                'phone' => $admin->phone,
            ]);

            // Phải bị redirect về với thông báo lỗi
            $response->assertSessionHas('error');
        } else {
            $this->assertTrue(true);
        }
    }

    /**
     * 4. Test Webhook: Ở chế độ Production, không có Token hoặc Token sai trả về 401
     */
    public function test_payment_webhook_in_production_requires_valid_token(): void
    {
        Config::set('payment.mode', 'production');
        Config::set('payment.webhook_token', 'test_secure_token_xyz');

        // Gửi webhook không kèm Token
        $responseNoToken = $this->postJson(route('api.payment.webhook'), [
            'amount' => 100000,
            'content' => 'DH9999',
        ]);
        $responseNoToken->assertStatus(401);

        // Gửi webhook với Token sai
        $responseWrongToken = $this->withHeaders([
            'Authorization' => 'Apikey wrong_token_abc'
        ])->postJson(route('api.payment.webhook'), [
            'amount' => 100000,
            'content' => 'DH9999',
        ]);
        $responseWrongToken->assertStatus(401);
    }

    /**
     * 5. Test Simulate Webhook: Ở chế độ Production, route giả lập bị khóa trả về 404
     */
    public function test_simulate_webhook_in_production_returns_404(): void
    {
        Config::set('payment.mode', 'production');

        $response = $this->postJson(route('api.payment.simulate-webhook'), [
            'order_id' => 1,
        ]);

        $response->assertStatus(404);
    }

    /**
     * 6. Test Webhook: Ở chế độ Production, số tiền chuyển nhỏ hơn tổng đơn bị từ chối
     */
    public function test_payment_webhook_with_insufficient_amount_is_rejected(): void
    {
        Config::set('payment.mode', 'production');
        Config::set('payment.webhook_token', 'valid_token_123');

        $order = Order::create([
            'user_id' => 1,
            'name' => 'Khách Test',
            'email' => 'test@civilize.vn',
            'phone' => '0912345678',
            'address' => 'Hà Nội',
            'total_price' => 200000,
            'payment_method' => 'VietQR',
            'status' => 0,
            'created_by' => 1,
        ]);

        // Gửi số tiền chỉ có 10.000đ (nhỏ hơn 200.000đ)
        $response = $this->withHeaders([
            'Authorization' => 'Apikey valid_token_123'
        ])->postJson(route('api.payment.webhook'), [
            'amount' => 10000,
            'content' => 'DH' . $order->id,
            'referenceCode' => 'TEST_REF_' . uniqid(),
        ]);

        $response->assertStatus(400);

        // Dọn dẹp
        $order->delete();
    }

    /**
     * 7. Test Chống thanh toán 2 lần: processPayment gọi lần 2 bị từ chối
     */
    public function test_process_payment_rejects_second_payment(): void
    {
        $admin = User::where('roles', 'admin')->where('status', 1)->first();
        $this->actingAs($admin);

        $order = Order::create([
            'user_id' => $admin->id,
            'name' => 'Khách POS',
            'email' => 'pos@civilize.vn',
            'phone' => '0912345678',
            'address' => 'Tại quán',
            'total_price' => 150000,
            'payment_method' => 1,
            'status' => 2, // Đã thanh toán hoàn tất
            'created_by' => 1,
        ]);

        $response = $this->postJson(route('admin.table-order.processPayment'), [
            'order_id' => $order->id,
            'payment_method' => '1',
            'total_price' => 150000,
        ]);

        $response->assertStatus(400);

        // Dọn dẹp
        $order->delete();
    }

    /**
     * 8. Test Phương thức HTTP: Gọi GET vào add-admin hoặc delete trả về 405 (Method Not Allowed)
     */
    public function test_get_method_on_add_admin_returns_405(): void
    {
        $admin = User::where('roles', 'admin')->where('status', 1)->first();
        $this->actingAs($admin);

        // Gọi GET vào route chỉ chấp nhận POST
        $response = $this->get('/admin/user/1/add-admin');
        $this->assertEquals(405, $response->getStatusCode());
    }

    /**
     * 9. Test Thanh toán VietQR tự động bắn thông báo cho cả 3 vai trò: Admin, Employee, Customer
     */
    public function test_vietqr_payment_creates_notifications_for_all_three_roles(): void
    {
        Config::set('payment.mode', 'demo');

        $customer = User::where('roles', 'customer')->first();
        $customerId = $customer ? $customer->id : 9;

        $order = Order::create([
            'user_id' => $customerId,
            'name' => 'Khách Test VietQR 3 Roles',
            'email' => 'customer3roles@civilize.vn',
            'phone' => '0988776655',
            'address' => 'Hà Nội',
            'total_price' => 500000,
            'payment_method' => 'VietQR',
            'status' => 0,
            'created_by' => 1,
        ]);

        $response = $this->postJson(route('api.payment.simulate-webhook'), [
            'order_id' => $order->id,
            'amount' => 500000,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'order_id' => $order->id,
            'notifications_sent' => [
                'admin' => true,
                'employee' => true,
                'customer' => true,
            ]
        ]);

        // Kiểm tra đơn hàng cập nhật thành công
        $order->refresh();
        $this->assertEquals(1, $order->status);

        // Kiểm tra thông báo được lưu cho cả 3 vai trò
        $adminNoti = \App\Models\Notification::where('role', 'admin')
            ->where('type', 'payment')
            ->whereJsonContains('data->order_id', $order->id)
            ->first();
        $this->assertNotNull($adminNoti, 'Chưa có thông báo cho Quản trị viên (Admin)');

        $empNoti = \App\Models\Notification::where('role', 'employee')
            ->where('type', 'payment')
            ->whereJsonContains('data->order_id', $order->id)
            ->first();
        $this->assertNotNull($empNoti, 'Chưa có thông báo cho Nhân viên (Employee)');

        $custNoti = \App\Models\Notification::where('role', 'customer')
            ->where('type', 'payment')
            ->whereJsonContains('data->order_id', $order->id)
            ->first();
        $this->assertNotNull($custNoti, 'Chưa có thông báo cho Khách hàng (Customer)');

        // Kiểm tra ServiceCall được tạo để chuông thu ngân / POS reo
        $serviceCall = \App\Models\ServiceCall::where('order_id', $order->id)
            ->where('status', \App\Models\ServiceCall::STATUS_PENDING)
            ->first();
        $this->assertNotNull($serviceCall, 'Chưa có chuông gọi ServiceCall cho thu ngân/phục vụ');

        // Dọn dẹp
        if ($adminNoti) $adminNoti->delete();
        if ($empNoti) $empNoti->delete();
        if ($custNoti) $custNoti->delete();
        if ($serviceCall) $serviceCall->delete();
        $order->delete();
    }

    /**
     * 10. Test Chuông thông báo Backend hiển thị đầy đủ thông báo thanh toán & chuyển khoản thành công
     */
    public function test_backend_notification_dropdown_returns_payment_and_transfer_notifications(): void
    {
        $admin = User::where('roles', 'admin')->where('status', 1)->first();
        if (!$admin) {
            $admin = User::create([
                'username' => 'test_admin_noti_' . uniqid(),
                'fullname' => 'Test Admin Noti',
                'password' => Hash::make('123456'),
                'phone' => '0999999995',
                'roles' => 'admin',
                'admin_lever' => 1,
                'status' => 1,
            ]);
        }

        // Tạo 1 thông báo thanh toán test
        $testNoti = \App\Models\Notification::create([
            'role'    => 'admin',
            'type'    => 'payment',
            'title'   => 'Đã nhận tiền VietQR: 500.000₫ (Bàn Test)',
            'message' => 'Hệ thống đã nhận 500.000₫ qua VietQR cho Bàn Test.',
            'url'     => 'http://localhost:8000/admin',
            'icon'    => 'fa-circle-check',
            'color'   => '#10B981',
            'data'    => ['is_vietqr' => true],
            'is_read' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.notifications.latest'));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'total_count',
            'payment_count',
            'service_call_count',
            'new_order_count',
            'new_booking_count',
            'payments',
            'service_calls',
            'new_orders',
            'new_bookings'
        ]);

        $data = $response->json();
        $this->assertTrue(count($data['payments']) > 0, 'Danh sách thông báo thanh toán không được rỗng');

        // Tìm thông báo vừa tạo
        $found = collect($data['payments'])->firstWhere('id', $testNoti->id);
        $this->assertNotNull($found, 'Không tìm thấy thông báo chuyển khoản test trong danh sách chuông');
        $this->assertTrue($found['is_vietqr']);
        $this->assertEquals('Chuyển khoản VietQR', $found['badge_label']);

        // Dọn dẹp
        $testNoti->delete();
    }

    /**
     * 11. Test Trang Đơn hàng Giao tận nơi & Mang đi chỉ hiển thị đơn giao, loại trừ đơn ăn tại bàn
     */
    public function test_delivery_order_list_excludes_dine_in_table_orders(): void
    {
        $admin = User::where('roles', 'admin')->where('status', 1)->first();

        // Tạo 1 đơn ăn tại bàn
        $tableOrder = Order::create([
            'user_id' => $admin->id ?? 1,
            'table_id' => 14,
            'name' => 'Bàn 18 (Ăn tại quán)',
            'email' => 'ban18@gmail.com',
            'phone' => '0912345678',
            'address' => 'Bàn 18',
            'orderStyle' => 0,
            'total_price' => 500000,
            'status' => 2,
            'created_by' => 1,
        ]);

        // Tạo 1 đơn giao tận nơi
        $deliveryOrder = Order::create([
            'user_id' => $admin->id ?? 1,
            'table_id' => null,
            'name' => 'Khách Giao Tận Nơi Test',
            'email' => 'giaotannoi@gmail.com',
            'phone' => '0987654321',
            'address' => '123 Đường Test, Quận 1',
            'orderStyle' => 2,
            'total_price' => 350000,
            'status' => 0,
            'created_by' => 1,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.order.index'));
        $response->assertStatus(200);

        // Đơn giao hàng phải có trong danh sách
        $response->assertSee('Khách Giao Tận Nơi Test');

        // Đơn ăn tại bàn tuyệt đối không được xuất hiện trong danh sách Đơn hàng Giao tận nơi & Mang đi
        $response->assertDontSee('Bàn 18 (Ăn tại quán)');

        // Dọn dẹp
        $tableOrder->delete();
        $deliveryOrder->delete();
    }

    /**
     * 12. Test Xoa danh gia yeu cau quyen so huu, ke khac xoa se bi 403 Forbidden
     */
    public function test_unauthorized_review_delete_returns_403(): void
    {
        $userA = User::create([
            'username' => 'user_rev_a_' . uniqid(),
            'fullname' => 'Khach A',
            'password' => Hash::make('123456'),
            'phone' => '093' . rand(1000000, 9999999),
            'gender' => 1,
            'roles' => 'customer',
            'status' => 1,
        ]);

        $userB = User::create([
            'username' => 'user_rev_b_' . uniqid(),
            'fullname' => 'Khach B',
            'password' => Hash::make('123456'),
            'phone' => '093' . rand(1000000, 9999999),
            'gender' => 1,
            'roles' => 'customer',
            'status' => 1,
        ]);

        $product = Product::first();
        if (!$product) {
            $this->markTestSkipped('No product available');
        }

        $review = Review::create([
            'product_id' => $product->id,
            'user_id' => $userA->id,
            'rating' => 5,
            'comment' => 'Mon ngon tuyet voi test',
        ]);

        // User B khong phai chu so huu cung khong phai admin -> 403
        $response = $this->actingAs($userB)->delete(route('reviews.delete', $review->review_id));
        $response->assertStatus(403);

        // User A la chu so huu -> Xoa thanh cong (302)
        $responseOwner = $this->actingAs($userA)->delete(route('reviews.delete', $review->review_id));
        $responseOwner->assertStatus(302);

        $this->assertSoftDeleted('reviews', ['review_id' => $review->review_id]);

        $userA->forceDelete();
        $userB->forceDelete();
    }

    /**
     * 13. Test Danh gia mon an bat buoc rating tu 1 den 5 sao
     */
    public function test_review_validation_rejects_invalid_rating(): void
    {
        $user = User::create([
            'username' => 'rev_val_' . uniqid(),
            'fullname' => 'Khach Test Review',
            'password' => Hash::make('123456'),
            'phone' => '093' . rand(1000000, 9999999),
            'gender' => 1,
            'roles' => 'customer',
            'status' => 1,
        ]);

        $product = Product::first();

        // Rating = 6 vuot qua 5 sao
        $response = $this->actingAs($user)->post(route('reviews.store'), [
            'product_id' => $product->id,
            'rating' => 6,
            'comment' => 'Rating sai',
        ]);

        $response->assertSessionHasErrors(['rating']);

        // Rating = 0 duoi 1 sao
        $responseZero = $this->actingAs($user)->post(route('reviews.store'), [
            'product_id' => $product->id,
            'rating' => 0,
            'comment' => 'Rating khong',
        ]);

        $responseZero->assertSessionHasErrors(['rating']);

        $user->forceDelete();
    }

    /**
     * 14. Test Dat ban chan ngay trong qua khu va dinh dang SDT khong hop le
     */
    public function test_booking_rejects_past_date(): void
    {
        $response = $this->post(route('site.booking.store'), [
            'name' => 'Nguyen Van Test',
            'email' => 'testbooking@gmail.com',
            'phone' => '0912345678',
            'number_of_people' => 4,
            'booking_date' => now()->subDay()->format('Y-m-d'),
            'booking_time' => '18:00',
        ]);

        $response->assertSessionHasErrors(['booking_date']);
    }

    /**
     * 15. Test Checkout giao hang chan ban vuot ton kho (Stock Insufficient)
     */
    public function test_checkout_rejects_insufficient_stock(): void
    {
        $user = User::create([
            'username' => 'stock_user_' . uniqid(),
            'fullname' => 'Khach Mua Hang',
            'password' => Hash::make('123456'),
            'phone' => '098' . rand(1000000, 9999999),
            'gender' => 1,
            'roles' => 'customer',
            'status' => 1,
        ]);

        // Tao san pham thu nghiem voi ton kho = 1
        $product = new Product();
        $product->category_id = 1;
        $product->brand_id = 1;
        $product->name = 'Mon Test Ton Kho ' . uniqid();
        $product->slug = 'mon-test-ton-kho-' . uniqid();
        $product->price_buy = 100000;
        $product->price_sale = 0;
        $product->qty = 1;
        $product->image = 'default.jpg';
        $product->description = 'Chi tiet';
        $product->content = 'Noi dung chi tiet';
        $product->status = 1;
        $product->created_by = 1;
        $product->save();

        // Dat so luong 5 trong gio hang (vuot qua ton kho 1)
        $cartKey = 'cart_' . $user->id;
        $cartData = [
            $product->id => [
                'name' => $product->name,
                'slug' => $product->slug,
                'id' => $product->id,
                'price' => 100000,
                'qty' => 5,
                'discount' => 0,
            ],
        ];

        $response = $this->actingAs($user)
            ->withSession([$cartKey => $cartData])
            ->post(route('site.checkout'), [
                'name' => 'Nguyen Van A',
                'phone' => '0981234567',
                'address' => '123 Pho Hue, Hai Ba Trung, Ha Noi',
                'payment_method' => 'COD',
            ]);

        $response->assertSessionHas('error');

        // Don dep
        $product->forceDelete();
        $user->forceDelete();
    }

    /**
     * 16. Test Checkout giao hang tu dong tinh phi ship 25.000d khi tong don duoi 500.000d
     */
    public function test_checkout_applies_shipping_fee(): void
    {
        $user = User::create([
            'username' => 'ship_user_' . uniqid(),
            'fullname' => 'Khach Ship Hang',
            'password' => Hash::make('123456'),
            'phone' => '098' . rand(1000000, 9999999),
            'gender' => 1,
            'roles' => 'customer',
            'status' => 1,
        ]);

        $product = new Product();
        $product->category_id = 1;
        $product->brand_id = 1;
        $product->name = 'Mon Test Ship ' . uniqid();
        $product->slug = 'mon-test-ship-' . uniqid();
        $product->price_buy = 100000;
        $product->price_sale = 0;
        $product->qty = 10;
        $product->image = 'default.jpg';
        $product->description = 'Chi tiet';
        $product->content = 'Noi dung chi tiet';
        $product->status = 1;
        $product->created_by = 1;
        $product->save();

        $cartKey = 'cart_' . $user->id;
        $cartData = [
            $product->id => [
                'name' => $product->name,
                'slug' => $product->slug,
                'id' => $product->id,
                'price' => 100000,
                'qty' => 1,
                'discount' => 0,
            ],
        ];

        $response = $this->actingAs($user)
            ->withSession([$cartKey => $cartData])
            ->post(route('site.checkout'), [
                'name' => 'Nguyen Van Ship',
                'phone' => '0981234567',
                'address' => '123 Pho Hue, Hai Ba Trung, Ha Noi',
                'payment_method' => 'COD',
            ]);

        $response->assertStatus(302);

        // Don hang tao ra phai co tong tien = 100.000 (mon) + 25.000 (phi ship) = 125.000d
        $order = Order::where('user_id', $user->id)->latest('id')->first();
        $this->assertNotNull($order);
        $this->assertEquals(125000, (int)$order->total_price);

        // Don dep
        Orderdetail::where('order_id', $order->id)->delete();
        $order->delete();
        $product->forceDelete();
        $user->forceDelete();
    }

    /**
     * 17. Test Chan hoan toan phuong thuc GET doi voi cac route thay doi du lieu admin (CSRF Protection)
     */
    public function test_get_method_on_admin_actions_returns_405(): void
    {
        $admin = User::where('roles', 'admin')->where('status', 1)->first();
        if (!$admin) {
            $admin = User::create([
                'username' => 'test_admin_csrf_' . uniqid(),
                'fullname' => 'Admin CSRF Test',
                'password' => Hash::make('123456'),
                'phone' => '093' . rand(1000000, 9999999),
                'gender' => 1,
                'roles' => 'admin',
                'admin_lever' => 1,
                'status' => 1,
            ]);
        }

        $product = Product::first();
        if (!$product) {
            $this->markTestSkipped('No product');
        }

        // GET request on admin product delete/status/restore phai bi chan bang 405 Method Not Allowed
        $responseDelete = $this->actingAs($admin)->get(route('admin.product.delete', $product->id));
        $responseDelete->assertStatus(405);

        $responseStatus = $this->actingAs($admin)->get(route('admin.product.status', $product->id));
        $responseStatus->assertStatus(405);

        $responseRestore = $this->actingAs($admin)->get(route('admin.product.restore', $product->id));
        $responseRestore->assertStatus(405);
    }

    /**
     * 18. Test Chan hoan toan phuong thuc GET doi voi route xoa mon gio hang delcart
     */
    public function test_get_method_on_delcart_returns_405(): void
    {
        $response = $this->get('/delcart/1');
        $response->assertStatus(405);
    }

    /**
     * 19. Test Xu ly dang xuat site va admin an toan qua ca GET va POST khong gay loi 405
     */
    public function test_logout_handles_both_get_and_post_gracefully(): void
    {
        // 1. GET /dang-xuat chuyen huong ve trang dang nhap (302), khong bi loi 405 Method Not Allowed
        $resSiteLogoutGet = $this->get('/dang-xuat');
        $resSiteLogoutGet->assertStatus(302);
        $resSiteLogoutGet->assertRedirect(route('site.login'));

        // 2. POST /dang-xuat chuyen huong ve trang dang nhap (302)
        $resSiteLogoutPost = $this->post('/dang-xuat');
        $resSiteLogoutPost->assertStatus(302);
        $resSiteLogoutPost->assertRedirect(route('site.login'));

        // 3. GET /admin/logout chuyen huong ve trang dang nhap admin (302), khong bi loi 405
        $resAdminLogoutGet = $this->get('/admin/logout');
        $resAdminLogoutGet->assertStatus(302);
        $resAdminLogoutGet->assertRedirect(route('admin.login'));

        // 4. POST /admin/logout chuyen huong ve trang dang nhap admin (302)
        $resAdminLogoutPost = $this->post('/admin/logout');
        $resAdminLogoutPost->assertStatus(302);
        $resAdminLogoutPost->assertRedirect(route('admin.login'));
    }

    /**
     * 20. Test Doanh thu Dashboard dong bo don hoan tat trong ngay va phan loai kenh ban
     */
    public function test_dashboard_revenue_counts_today_completed_orders_and_differentiates_channels(): void
    {
        $admin = User::where('roles', 'admin')->where('status', 1)->first();
        if (!$admin) {
            $admin = User::create([
                'username' => 'test_admin_dash_' . uniqid(),
                'fullname' => 'Admin Dashboard Test',
                'password' => Hash::make('123456'),
                'phone' => '093' . rand(1000000, 9999999),
                'gender' => 1,
                'roles' => 'admin',
                'admin_lever' => 1,
                'status' => 1,
            ]);
        }

        $employee = User::where('roles', 'employee')->where('status', 1)->first();
        if (!$employee) {
            $employee = User::create([
                'username' => 'test_emp_dash_' . uniqid(),
                'fullname' => 'Employee Dashboard Test',
                'password' => Hash::make('123456'),
                'phone' => '094' . rand(1000000, 9999999),
                'gender' => 1,
                'roles' => 'employee',
                'admin_lever' => 2,
                'status' => 1,
            ]);
        }

        // Nhan vien truy cap /admin phai bi chuyen huong sang POS ban hang tai ban
        $resEmp = $this->actingAs($employee)->get(route('admin.dashboard'));
        $resEmp->assertRedirect(route('admin.table-order.index'));

        // Tao 1 ban test neu chua co
        $table = Table::first();
        $tableId = $table ? $table->id : 1;

        // Tao don 1: Tao tu hom qua nhung hoan tat/thanh toan hom nay (status = 2, updated_at = today)
        $orderYesterdayPaidToday = Order::create([
            'user_id' => $admin->id,
            'name' => 'Khach POS Tai Ban',
            'phone' => '0912999888',
            'email' => 'postest@example.com',
            'address' => 'Ban 1',
            'table_id' => $tableId,
            'total_price' => 500000,
            'created_by' => $admin->id,
            'payment_method' => 'VietQR',
            'payment_status' => Order::PAYMENT_PAID,
            'paid_at' => Carbon::now(),
            'revenue_recognized_at' => Carbon::now(),
            'status' => 2,
            'created_at' => Carbon::yesterday(),
            'updated_at' => Carbon::now(),
        ]);

        // Tao don 2: Don giao hang mang ve phat sinh hom nay da giao thanh cong va thu tien COD (status = 2, delivery_status = delivered, table_id = null)
        $orderTodayDelivery = Order::create([
            'user_id' => $admin->id,
            'name' => 'Khach Giao Hang',
            'phone' => '0912999777',
            'email' => 'deliverytest@example.com',
            'address' => '123 Pho Hue',
            'table_id' => null,
            'total_price' => 200000,
            'created_by' => $admin->id,
            'payment_method' => 1,
            'delivery_status' => Order::DELIVERY_DELIVERED,
            'payment_status' => Order::PAYMENT_PAID,
            'delivered_at' => Carbon::now(),
            'revenue_recognized_at' => Carbon::now(),
            'status' => 2,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $resAdmin = $this->actingAs($admin)->get(route('admin.dashboard'));
        $resAdmin->assertStatus(200);
        $resAdmin->assertViewHas('revenue');
        $resAdmin->assertViewHas('dineInCount');
        $resAdmin->assertViewHas('takeawayCount');
        $resAdmin->assertViewHas('transferRevenue');
        $resAdmin->assertViewHas('cashRevenue');

        $viewData = $resAdmin->original->getData();
        $this->assertGreaterThanOrEqual(700000, (int)$viewData['revenue']);
        $this->assertGreaterThanOrEqual(500000, (int)$viewData['transferRevenue']);
        $this->assertGreaterThanOrEqual(200000, (int)$viewData['cashRevenue']);
        $this->assertGreaterThanOrEqual(1, (int)$viewData['dineInCount']);
        $this->assertGreaterThanOrEqual(1, (int)$viewData['takeawayCount']);

        // Don dep du lieu test
        $orderYesterdayPaidToday->forceDelete();
        $orderTodayDelivery->forceDelete();
    }

    /**
     * 21. Test HTTP: Admin cap nhat tien trinh giao hang qua POST /admin/order/{id}/delivery
     * - Khi giao thanh cong -> status chuyen sang OrderStatus::COMPLETED (2), khong phai CANCELLED (3)
     * - Don hang duoc tinh vao tong doanh thu
     */
    public function test_admin_update_delivery_to_delivered_sets_completed_and_counts_revenue(): void
    {
        $admin = User::where('roles', 'admin')->where('status', 1)->first();
        if (!$admin) {
            $admin = User::create([
                'username' => 'test_admin_deliv_' . uniqid(),
                'fullname' => 'Test Admin Delivery',
                'password' => Hash::make('123456'),
                'phone' => '0999888771',
                'roles' => 'admin',
                'admin_lever' => 1,
                'status' => 1,
            ]);
        }

        $order = Order::create([
            'user_id' => $admin->id,
            'name' => 'Khach Test HTTP Delivery',
            'phone' => '0988776655',
            'email' => 'httpdelivery@test.com',
            'address' => 'So 10 Pho Hue, Ha Noi',
            'total_price' => 350000,
            'payment_method' => 1,
            'status' => \App\Enums\OrderStatus::PENDING,
            'delivery_status' => Order::DELIVERY_WAITING,
            'created_by' => $admin->id,
        ]);

        // 1. Gui request HTTP picked_up
        $resPickedUp = $this->actingAs($admin)->postJson(route('admin.order.updateDelivery', $order->id), [
            'shipper_name' => 'Shipper Test 1',
            'shipper_phone' => '0987654321',
            'delivery_status' => 'picked_up',
            'delivery_note' => 'Da giao kien hang cho shipper',
        ]);
        $resPickedUp->assertStatus(200);
        $resPickedUp->assertJson(['success' => true]);

        $order->refresh();
        $this->assertEquals(\App\Enums\OrderStatus::PROCESSING, $order->status);
        $this->assertEquals(1, $order->status);

        // 2. Gui request HTTP delivered (Da giao thanh cong)
        $resDelivered = $this->actingAs($admin)->postJson(route('admin.order.updateDelivery', $order->id), [
            'shipper_name' => 'Shipper Test 1',
            'shipper_phone' => '0987654321',
            'delivery_status' => 'delivered',
            'delivery_note' => 'Khach da nhan hang va thanh toan',
        ]);
        $resDelivered->assertStatus(200);
        $resDelivered->assertJson(['success' => true]);

        $order->refresh();
        // Kiem tra toi quan trong: status PHAI la COMPLETED (2), KHONG DUOC la CANCELLED (3)
        $this->assertEquals(\App\Enums\OrderStatus::COMPLETED, $order->status);
        $this->assertEquals(2, $order->status);
        $this->assertNotEquals(\App\Enums\OrderStatus::CANCELLED, $order->status);

        // 3. Kiem tra tinh vao doanh thu
        $revenueTotal = Order::whereNull('deleted_at')
            ->whereIn('status', [\App\Enums\OrderStatus::PROCESSING, \App\Enums\OrderStatus::COMPLETED])
            ->where('id', $order->id)
            ->sum('total_price');
        $this->assertEquals(350000, (int)$revenueTotal);

        // Don dep
        $order->forceDelete();
    }

    /**
     * 22. Test HTTP: updateDelivery chan quay nguoc trang thai tu "da giao" va chan don da huy
     */
    public function test_update_delivery_prevents_rollback_and_blocks_cancelled(): void
    {
        $admin = User::where('roles', 'admin')->where('status', 1)->first();
        if (!$admin) {
            $admin = User::create([
                'username' => 'test_admin_rb_' . uniqid(),
                'fullname' => 'Test Admin Rollback',
                'password' => Hash::make('123456'),
                'phone' => '0999888772',
                'roles' => 'admin',
                'admin_lever' => 1,
                'status' => 1,
            ]);
        }

        // Ca 1: Don da giao thanh cong -> khong cho quay ve waiting
        $deliveredOrder = Order::create([
            'user_id' => $admin->id,
            'name' => 'Khach Da Nhan',
            'phone' => '0988776655',
            'email' => 'delivered@test.com',
            'address' => 'Ha Noi',
            'total_price' => 200000,
            'payment_method' => 1,
            'status' => \App\Enums\OrderStatus::COMPLETED,
            'delivery_status' => 'delivered',
            'created_by' => $admin->id,
        ]);

        $resRollback = $this->actingAs($admin)->postJson(route('admin.order.updateDelivery', $deliveredOrder->id), [
            'delivery_status' => 'waiting',
        ]);
        $resRollback->assertStatus(422);
        $resRollback->assertJson(['success' => false]);
        $this->assertEquals('delivered', $deliveredOrder->fresh()->delivery_status);

        // Ca 2: Don da bi huy -> khong cho cap nhat giao hang
        $cancelledOrder = Order::create([
            'user_id' => $admin->id,
            'name' => 'Khach Huy Don',
            'phone' => '0988776655',
            'email' => 'cancelled@test.com',
            'address' => 'Ha Noi',
            'total_price' => 200000,
            'payment_method' => 1,
            'status' => \App\Enums\OrderStatus::CANCELLED,
            'delivery_status' => 'waiting',
            'created_by' => $admin->id,
        ]);

        $resCancelled = $this->actingAs($admin)->postJson(route('admin.order.updateDelivery', $cancelledOrder->id), [
            'delivery_status' => 'delivered',
        ]);
        $resCancelled->assertStatus(422);
        $resCancelled->assertJson(['success' => false]);
        $this->assertEquals(\App\Enums\OrderStatus::CANCELLED, $cancelledOrder->fresh()->status);

        // Don dep
        $deliveredOrder->forceDelete();
        $cancelledOrder->forceDelete();
    }

    /**
     * 23. Test HTTP: Audit Trail cho tiến trình giao hàng và quy trình giao thất bại (failed)
     */
    public function test_delivery_audit_trail_and_failed_workflow_via_http(): void
    {
        $employee = User::where('roles', 'employee')->where('status', 1)->first();
        if (!$employee) {
            $employee = User::create([
                'username' => 'test_emp_audit_' . uniqid(),
                'fullname' => 'Test Employee Audit',
                'password' => Hash::make('123456'),
                'phone' => '0999888775',
                'roles' => 'employee',
                'status' => 1,
            ]);
        }

        $order = Order::create([
            'user_id' => $employee->id,
            'name' => 'Khach Giao Hang Audit',
            'phone' => '0977112233',
            'email' => 'audit_deliv@test.com',
            'address' => '456 Tran Phu, Ha Noi',
            'total_price' => 180000,
            'payment_method' => 1,
            'status' => \App\Enums\OrderStatus::PROCESSING,
            'delivery_status' => Order::DELIVERY_DELIVERING,
            'shipper_name' => 'Nguyen Van B',
            'shipper_phone' => '0912000111',
            'created_by' => $employee->id,
        ]);

        // 1. Employee cap nhat sang 'failed' nhung khong nhap note -> bi loi 422
        $resFailedNoNote = $this->actingAs($employee)->postJson(route('admin.order.updateDelivery', $order->id), [
            'delivery_status' => 'failed',
            'delivery_note' => '',
        ]);
        $resFailedNoNote->assertStatus(422);

        // 2. Employee cap nhat sang 'failed' co note hop le -> thanh cong, status giu PROCESSING
        $resFailedWithNote = $this->actingAs($employee)->postJson(route('admin.order.updateDelivery', $order->id), [
            'delivery_status' => 'failed',
            'delivery_note' => 'Khach di vang, khong nhan duoc hang',
        ]);
        $resFailedWithNote->assertStatus(200);

        $order->refresh();
        $this->assertEquals(Order::DELIVERY_FAILED, $order->delivery_status);
        $this->assertEquals(\App\Enums\OrderStatus::PROCESSING, $order->status);
        $this->assertEquals($employee->id, $order->updated_by);

        // 3. Kiem tra audit log da ghi lai thay doi
        $log = \App\Models\OrderDeliveryLog::where('order_id', $order->id)->latest('id')->first();
        $this->assertNotNull($log);
        $this->assertEquals($employee->id, $log->user_id);
        $this->assertEquals('failed', $log->to_delivery_status);
        $this->assertEquals('Khach di vang, khong nhan duoc hang', $log->note);

        // 4. Dieu phoi lai: Employee chuyen tu failed ve waiting -> thanh cong
        $resRedispatch = $this->actingAs($employee)->postJson(route('admin.order.updateDelivery', $order->id), [
            'delivery_status' => 'waiting',
            'delivery_note' => 'Sap xep lai tai xe giao ca tiep theo',
        ]);
        $resRedispatch->assertStatus(200);

        $order->refresh();
        $this->assertEquals(Order::DELIVERY_WAITING, $order->delivery_status);
        $this->assertEquals(\App\Enums\OrderStatus::PROCESSING, $order->status);

        // Don dep
        \App\Models\OrderDeliveryLog::where('order_id', $order->id)->delete();
        $order->forceDelete();
    }

    /**
     * 24. Test Vòng đời payment_status (pending -> paid -> refunded) và loại khỏi doanh thu khi hoàn tiền
     */
    public function test_payment_status_lifecycle_and_revenue_exclusion_on_refund(): void
    {
        $admin = User::where('roles', 'admin')->where('status', 1)->first();

        // 1. Tao don hang ban dau: payment_status = pending
        $order = Order::create([
            'user_id' => $admin->id,
            'name' => 'Khach Test Doanh Thu',
            'phone' => '0933445566',
            'email' => 'refund_test@test.com',
            'address' => 'Ha Noi',
            'total_price' => 350000,
            'payment_method' => 2, // VietQR
            'status' => \App\Enums\OrderStatus::PROCESSING,
            'payment_status' => Order::PAYMENT_PENDING,
            'created_by' => $admin->id,
        ]);

        $this->assertFalse($order->isPaid());
        $this->assertFalse(Order::paid()->where('id', $order->id)->exists());

        // 2. Chuyen sang da thanh toan (PAID)
        $order->payment_status = Order::PAYMENT_PAID;
        $order->status = \App\Enums\OrderStatus::COMPLETED;
        $order->save();

        $this->assertTrue($order->fresh()->isPaid());
        $this->assertTrue(Order::paid()->where('id', $order->id)->exists());

        // 3. Hoan tien (REFUNDED)
        $order->markAsRefunded('Giao hang that bai - Hoan tien VietQR');
        $order->refresh();

        $this->assertEquals(Order::PAYMENT_REFUNDED, $order->payment_status);
        $this->assertEquals('Đã hoàn tiền', $order->payment_status_label);
        $this->assertFalse($order->isPaid());
        // Tuyet doi khong con nam trong doanh thu ghi nhan (scopePaid)
        $this->assertFalse(Order::paid()->where('id', $order->id)->exists());

        // Don dep
        $order->forceDelete();
    }

    /**
     * 25. Test Webhook VietQR: Chống xử lý trùng lặp (Idempotency) và chống Replay Attack
     */
    public function test_vietqr_webhook_idempotency_and_replay_protection(): void
    {
        Config::set('services.payment.webhook_token', 'test_secret_token_123');

        $order = Order::create([
            'user_id' => 1,
            'name' => 'Khach Test Webhook',
            'phone' => '0911223344',
            'email' => 'webhook_test@test.com',
            'address' => 'Ha Noi',
            'total_price' => 250000,
            'payment_method' => 2,
            'status' => \App\Enums\OrderStatus::PENDING,
            'payment_status' => Order::PAYMENT_PENDING,
            'created_by' => 1,
        ]);

        $referenceCode = 'TEST_REF_' . uniqid();
        $payload = [
            'order_id' => $order->id,
            'amount' => 250000,
            'content' => 'Thanh toan don #' . $order->id,
            'referenceCode' => $referenceCode,
        ];

        // 1. Gui Webhook lan 1 -> Thanh cong ghi nhan thanh toan va tao PaymentTransaction
        $res1 = $this->postJson(route('api.payment.webhook'), $payload, [
            'X-Webhook-Token' => 'test_secret_token_123',
        ]);
        $res1->assertStatus(200);
        $res1->assertJson(['success' => true]);

        $order->refresh();
        $this->assertEquals(Order::PAYMENT_PAID, $order->payment_status);
        $this->assertEquals(\App\Enums\OrderStatus::PROCESSING, $order->status);

        $txCount = \App\Models\PaymentTransaction::where('reference_code', $referenceCode)->count();
        $this->assertEquals(1, $txCount);

        // 2. Gui lai cung webhook lan 2 (Replay / Network retry) -> He thong phat hien da xu ly (Idempotent)
        $res2 = $this->postJson(route('api.payment.webhook'), $payload, [
            'X-Webhook-Token' => 'test_secret_token_123',
        ]);
        $res2->assertStatus(200);
        $res2->assertJson(['success' => true, 'already_processed' => true]);

        // So luong giao dich van la 1, khong bi duplicate hoac ghi de
        $this->assertEquals(1, \App\Models\PaymentTransaction::where('reference_code', $referenceCode)->count());

        // Don dep
        \App\Models\PaymentTransaction::where('reference_code', $referenceCode)->delete();
        $order->forceDelete();
    }

    /**
     * 26. Test COD: Chi ghi nhan paid khi trang thai giao hang la delivered
     */
    public function test_cod_payment_marked_paid_only_upon_successful_delivery(): void
    {
        $employee = User::where('roles', 'employee')->where('status', 1)->first();

        $codOrder = Order::create([
            'user_id' => $employee->id,
            'name' => 'Khach COD',
            'phone' => '0966554433',
            'email' => 'cod_test@test.com',
            'address' => 'Ha Noi',
            'total_price' => 150000,
            'payment_method' => 1, // COD
            'status' => \App\Enums\OrderStatus::PROCESSING,
            'delivery_status' => Order::DELIVERY_WAITING,
            'payment_status' => Order::PAYMENT_PENDING,
            'created_by' => $employee->id,
        ]);

        // Khi dang giao -> payment_status van la pending
        $this->actingAs($employee)->postJson(route('admin.order.updateDelivery', $codOrder->id), [
            'delivery_status' => Order::DELIVERY_DELIVERING,
            'shipper_name' => 'Shipper A',
            'shipper_phone' => '0901234567',
        ]);
        $codOrder->refresh();
        $this->assertEquals(Order::PAYMENT_PENDING, $codOrder->payment_status);

        // Khi da giao thanh cong (delivered) nhung chua xac nhan thu COD -> payment_status van la PENDING (COD-01)
        $this->actingAs($employee)->postJson(route('admin.order.updateDelivery', $codOrder->id), [
            'delivery_status' => Order::DELIVERY_DELIVERED,
            'cod_collected' => false,
        ]);
        $codOrder->refresh();
        $this->assertEquals(Order::DELIVERY_DELIVERED, $codOrder->delivery_status);
        $this->assertEquals(Order::PAYMENT_PENDING, $codOrder->payment_status);

        // Khi xac nhan da thu du tien COD (cod_collected = true) -> payment_status chuyen sang paid va order status la COMPLETED
        $this->actingAs($employee)->postJson(route('admin.order.updateDelivery', $codOrder->id), [
            'delivery_status' => Order::DELIVERY_DELIVERED,
            'cod_collected' => true,
        ]);
        $codOrder->refresh();
        $this->assertEquals(Order::DELIVERY_DELIVERED, $codOrder->delivery_status);
        $this->assertEquals(Order::PAYMENT_PAID, $codOrder->payment_status);
        $this->assertEquals(\App\Enums\OrderStatus::COMPLETED, $codOrder->status);

        // Don dep
        \App\Models\OrderDeliveryLog::where('order_id', $codOrder->id)->delete();
        $codOrder->forceDelete();
    }
}
