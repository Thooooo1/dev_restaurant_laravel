<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Table;
use App\Models\Orderdetail;
use App\Models\Product;
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
}
