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
}
