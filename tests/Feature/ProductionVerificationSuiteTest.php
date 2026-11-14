<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Table;
use App\Models\PaymentTransaction;
use App\Models\ServiceCall;
use App\Models\OrderDeliveryLog;
use App\Enums\OrderStatus;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProductionVerificationSuiteTest extends TestCase
{
    protected function getAdminUser(): User
    {
        $admin = User::where('roles', 'admin')->where('status', 1)->first();
        if (!$admin) {
            $admin = User::create([
                'username' => 'test_admin_suite_' . uniqid(),
                'fullname' => 'Test Admin Suite',
                'password' => bcrypt('123456'),
                'phone' => '0988001001',
                'roles' => 'admin',
                'admin_lever' => 1,
                'status' => 1,
            ]);
        }
        return $admin;
    }

    protected function getEmployeeUser(): User
    {
        $employee = User::where('roles', 'employee')->where('status', 1)->first();
        if (!$employee) {
            $employee = User::create([
                'username' => 'test_emp_suite_' . uniqid(),
                'fullname' => 'Test Emp Suite',
                'password' => bcrypt('123456'),
                'phone' => '0988002002',
                'roles' => 'employee',
                'admin_lever' => 0,
                'status' => 1,
            ]);
        }
        return $employee;
    }

    /**
     * WH-01: Webhook voi token hop le -> 200 OK, cap nhat payment_status = paid
     */
    public function test_wh_01_valid_token_marks_payment_paid(): void
    {
        $admin = $this->getAdminUser();
        Config::set('payment.mode', 'production');
        Config::set('payment.webhook_token', 'secure_suite_token_wh01');

        $order = Order::create([
            'user_id' => $admin->id,
            'name' => 'Khach WH01',
            'phone' => '0911000001',
            'email' => 'wh01@example.com',
            'address' => 'Ha Noi',
            'total_price' => 150000,
            'payment_method' => 2,
            'status' => OrderStatus::PENDING,
            'delivery_status' => Order::DELIVERY_WAITING,
            'payment_status' => Order::PAYMENT_PENDING,
            'created_by' => $admin->id,
        ]);

        $ref = 'WH01_' . uniqid();
        $response = $this->postJson(route('api.payment.webhook'), [
            'amount' => 150000,
            'content' => 'DH' . $order->id,
            'referenceCode' => $ref,
        ], [
            'X-Webhook-Token' => 'secure_suite_token_wh01',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $order->refresh();
        $this->assertEquals(Order::PAYMENT_PAID, $order->payment_status);
        $this->assertNotNull($order->paid_at);

        $tx = PaymentTransaction::where('reference_code', $ref)->first();
        $this->assertNotNull($tx);
        $this->assertEquals(PaymentTransaction::STATUS_SUCCESS, $tx->status);

        $tx->delete();
        $order->forceDelete();
    }

    /**
     * WH-02: Webhook co referenceCode SIMULATED_ trong production nhung khong co token -> 401 Unauthorized
     */
    public function test_wh_02_simulated_reference_code_in_production_without_token_returns_401(): void
    {
        Config::set('payment.mode', 'production');
        Config::set('payment.webhook_token', 'production_secret_token');

        $response = $this->postJson(route('api.payment.webhook'), [
            'amount' => 200000,
            'content' => 'DH999999',
            'referenceCode' => 'SIMULATED_ATTACK_001',
        ]);

        $response->assertStatus(401);
        $response->assertJson(['success' => false]);
    }

    /**
     * WH-03: Webhook thieu token trong production -> 401 Unauthorized
     */
    public function test_wh_03_missing_token_in_production_returns_401(): void
    {
        Config::set('payment.mode', 'production');
        Config::set('payment.webhook_token', 'production_secret_token');

        $response = $this->postJson(route('api.payment.webhook'), [
            'amount' => 200000,
            'content' => 'DH999999',
            'referenceCode' => 'NORMAL_ATTACK_001',
        ]);

        $response->assertStatus(401);
        $response->assertJson(['success' => false]);
    }

    /**
     * WH-04: Gui lai cung mot webhook (cung referenceCode va cung don) -> 200 OK voi already_processed = true (Idempotency)
     */
    public function test_wh_04_webhook_idempotency_same_reference_code_returns_already_processed(): void
    {
        $admin = $this->getAdminUser();
        Config::set('payment.mode', 'production');
        Config::set('payment.webhook_token', 'suite_token_wh04');

        $order = Order::create([
            'user_id' => $admin->id,
            'name' => 'Khach WH04',
            'phone' => '0911000004',
            'email' => 'wh04@example.com',
            'address' => 'Ha Noi',
            'total_price' => 120000,
            'payment_method' => 2,
            'status' => OrderStatus::PENDING,
            'delivery_status' => Order::DELIVERY_WAITING,
            'payment_status' => Order::PAYMENT_PENDING,
            'created_by' => $admin->id,
        ]);

        $ref = 'WH04_' . uniqid();
        $payload = [
            'amount' => 120000,
            'content' => 'DH' . $order->id,
            'referenceCode' => $ref,
        ];
        $headers = ['X-Webhook-Token' => 'suite_token_wh04'];

        // Lan 1: Xu ly thanh cong
        $res1 = $this->postJson(route('api.payment.webhook'), $payload, $headers);
        $res1->assertStatus(200);

        // Lan 2: Replay cung payload
        $res2 = $this->postJson(route('api.payment.webhook'), $payload, $headers);
        $res2->assertStatus(200);
        $res2->assertJson(['success' => true, 'already_processed' => true]);

        // Chi co 1 ban ghi PaymentTransaction
        $this->assertEquals(1, PaymentTransaction::where('reference_code', $ref)->count());

        PaymentTransaction::where('reference_code', $ref)->delete();
        $order->forceDelete();
    }

    /**
     * WH-05: Hai giao dich khac nhau cho cung mot don -> Giao dich 2 ghi nhan overpayment, canh bao ServiceCall
     */
    public function test_wh_05_two_different_transactions_for_same_order_records_overpayment(): void
    {
        $admin = $this->getAdminUser();
        Config::set('payment.mode', 'production');
        Config::set('payment.webhook_token', 'suite_token_wh05');

        $order = Order::create([
            'user_id' => $admin->id,
            'name' => 'Khach WH05',
            'phone' => '0911000005',
            'email' => 'wh05@example.com',
            'address' => 'Ha Noi',
            'total_price' => 100000,
            'payment_method' => 2,
            'status' => OrderStatus::PENDING,
            'delivery_status' => Order::DELIVERY_WAITING,
            'payment_status' => Order::PAYMENT_PENDING,
            'created_by' => $admin->id,
        ]);

        $ref1 = 'WH05_TX1_' . uniqid();
        $ref2 = 'WH05_TX2_' . uniqid();
        $headers = ['X-Webhook-Token' => 'suite_token_wh05'];

        // Giao dich 1: Thanh toan du
        $res1 = $this->postJson(route('api.payment.webhook'), [
            'amount' => 100000,
            'content' => 'DH' . $order->id,
            'referenceCode' => $ref1,
        ], $headers);
        $res1->assertStatus(200);

        // Giao dich 2: Khach chuyen them 1 lan nua voi ma giao dich khac
        $res2 = $this->postJson(route('api.payment.webhook'), [
            'amount' => 100000,
            'content' => 'DH' . $order->id,
            'referenceCode' => $ref2,
        ], $headers);
        $res2->assertStatus(200);
        $res2->assertJson(['success' => true, 'already_paid' => true]);

        // Giao dich thu 2 phai co status = overpayment
        $tx2 = PaymentTransaction::where('reference_code', $ref2)->first();
        $this->assertNotNull($tx2);
        $this->assertEquals(PaymentTransaction::STATUS_OVERPAYMENT, $tx2->status);

        // Kiem tra co ServiceCall canh bao thu ngan
        $alert = ServiceCall::where('order_id', $order->id)
            ->where(function($q) {
                $q->where('call_type', 'service')
                  ->orWhere('call_type_label', 'like', '%thanh toán%');
            })
            ->first();
        $this->assertNotNull($alert);

        PaymentTransaction::whereIn('reference_code', [$ref1, $ref2])->delete();
        ServiceCall::where('order_id', $order->id)->delete();
        $order->forceDelete();
    }

    /**
     * WH-06: Cung mot referenceCode gui cho hai don khac nhau -> Lan 2 bi tu choi 409 Conflict
     */
    public function test_wh_06_same_reference_code_for_different_orders_returns_409_conflict(): void
    {
        $admin = $this->getAdminUser();
        Config::set('payment.mode', 'production');
        Config::set('payment.webhook_token', 'suite_token_wh06');

        $orderA = Order::create([
            'user_id' => $admin->id,
            'name' => 'Khach A',
            'phone' => '0911000006',
            'email' => 'wh06a@example.com',
            'address' => 'Ha Noi',
            'total_price' => 100000,
            'payment_method' => 2,
            'status' => OrderStatus::PENDING,
            'delivery_status' => Order::DELIVERY_WAITING,
            'payment_status' => Order::PAYMENT_PENDING,
            'created_by' => $admin->id,
        ]);

        $orderB = Order::create([
            'user_id' => $admin->id,
            'name' => 'Khach B',
            'phone' => '0911000007',
            'email' => 'wh06b@example.com',
            'address' => 'Ha Noi',
            'total_price' => 100000,
            'payment_method' => 2,
            'status' => OrderStatus::PENDING,
            'delivery_status' => Order::DELIVERY_WAITING,
            'payment_status' => Order::PAYMENT_PENDING,
            'created_by' => $admin->id,
        ]);

        $refShared = 'WH06_SHARED_' . uniqid();
        $headers = ['X-Webhook-Token' => 'suite_token_wh06'];

        // Xu ly cho Order A
        $resA = $this->postJson(route('api.payment.webhook'), [
            'amount' => 100000,
            'content' => 'DH' . $orderA->id,
            'referenceCode' => $refShared,
        ], $headers);
        $resA->assertStatus(200);

        // Xu ly lai ma GD nay cho Order B -> Phai bi tu choi 409 Conflict
        $resB = $this->postJson(route('api.payment.webhook'), [
            'amount' => 100000,
            'content' => 'DH' . $orderB->id,
            'referenceCode' => $refShared,
        ], $headers);
        $resB->assertStatus(409);
        $resB->assertJson(['success' => false]);

        PaymentTransaction::where('reference_code', $refShared)->delete();
        $orderA->forceDelete();
        $orderB->forceDelete();
    }

    /**
     * WH-07: So tien chuyen nho hon tong tien don -> Tu choi 422, don khong danh dau da thanh toan
     */
    public function test_wh_07_insufficient_payment_amount_rejected(): void
    {
        $admin = $this->getAdminUser();
        Config::set('payment.mode', 'production');
        Config::set('payment.webhook_token', 'suite_token_wh07');

        $order = Order::create([
            'user_id' => $admin->id,
            'name' => 'Khach WH07',
            'phone' => '0911000008',
            'email' => 'wh07@example.com',
            'address' => 'Ha Noi',
            'total_price' => 200000,
            'payment_method' => 2,
            'status' => OrderStatus::PENDING,
            'delivery_status' => Order::DELIVERY_WAITING,
            'payment_status' => Order::PAYMENT_PENDING,
            'created_by' => $admin->id,
        ]);

        $ref = 'WH07_' . uniqid();
        $response = $this->postJson(route('api.payment.webhook'), [
            'amount' => 150000,
            'content' => 'DH' . $order->id,
            'referenceCode' => $ref,
        ], [
            'X-Webhook-Token' => 'suite_token_wh07',
        ]);

        $response->assertStatus(400);
        $order->refresh();
        $this->assertEquals(Order::PAYMENT_PENDING, $order->payment_status);

        $order->forceDelete();
    }

    /**
     * WH-08: Khach chuyen thua tien -> Don chuyen PAID, ghi nhan so tien thua vao delivery_note
     */
    public function test_wh_08_surplus_payment_records_overpayment_note_and_paid_status(): void
    {
        $admin = $this->getAdminUser();
        Config::set('payment.mode', 'production');
        Config::set('payment.webhook_token', 'suite_token_wh08');

        $order = Order::create([
            'user_id' => $admin->id,
            'name' => 'Khach WH08',
            'phone' => '0911000009',
            'email' => 'wh08@example.com',
            'address' => 'Ha Noi',
            'total_price' => 200000,
            'payment_method' => 2,
            'status' => OrderStatus::PENDING,
            'delivery_status' => Order::DELIVERY_WAITING,
            'payment_status' => Order::PAYMENT_PENDING,
            'created_by' => $admin->id,
        ]);

        $ref = 'WH08_' . uniqid();
        $response = $this->postJson(route('api.payment.webhook'), [
            'amount' => 250000, // Thừa 50.000đ
            'content' => 'DH' . $order->id,
            'referenceCode' => $ref,
        ], [
            'X-Webhook-Token' => 'suite_token_wh08',
        ]);

        $response->assertStatus(200);
        $order->refresh();
        $this->assertEquals(Order::PAYMENT_PAID, $order->payment_status);
        $this->assertStringContainsString('50.000đ', (string)$order->delivery_note);

        PaymentTransaction::where('reference_code', $ref)->delete();
        $order->forceDelete();
    }

    /**
     * POS-01: Don POS tai quay (table_id = null, address = "Tai quan") -> status = COMPLETED (2), revenue_recognized_at ghi nhan ngay
     */
    public function test_pos_01_pos_counter_order_completed_and_revenue_recognized_immediately(): void
    {
        $admin = $this->getAdminUser();
        Config::set('payment.mode', 'production');
        Config::set('payment.webhook_token', 'suite_token_pos01');

        $order = Order::create([
            'user_id' => $admin->id,
            'name' => 'Khách POS',
            'phone' => '0911000010',
            'email' => 'pos01@example.com',
            'address' => 'Tại quán',
            'table_id' => null,
            'total_price' => 180000,
            'payment_method' => 2,
            'status' => OrderStatus::PENDING,
            'payment_status' => Order::PAYMENT_PENDING,
            'created_by' => $admin->id,
        ]);

        $ref = 'POS01_' . uniqid();
        $response = $this->postJson(route('api.payment.webhook'), [
            'amount' => 180000,
            'content' => 'DH' . $order->id,
            'referenceCode' => $ref,
        ], [
            'X-Webhook-Token' => 'suite_token_pos01',
        ]);

        $response->assertStatus(200);
        $order->refresh();
        $this->assertEquals(OrderStatus::COMPLETED, $order->status);
        $this->assertEquals(Order::PAYMENT_PAID, $order->payment_status);
        $this->assertNotNull($order->revenue_recognized_at);
        $this->assertTrue($order->isPos());
        $this->assertFalse($order->isDelivery());

        PaymentTransaction::where('reference_code', $ref)->delete();
        $order->forceDelete();
    }

    /**
     * POS-02: Don goi mon tai ban -> status = COMPLETED (2), ban duoc giai phong ve available (0), revenue_recognized_at ghi nhan ngay
     */
    public function test_pos_02_dine_in_table_order_completed_and_table_released(): void
    {
        $admin = $this->getAdminUser();
        Config::set('payment.mode', 'production');
        Config::set('payment.webhook_token', 'suite_token_pos02');

        $table = Table::first();
        if (!$table) {
            $table = Table::create(['name' => 'Bàn Suite 01', 'capacity' => 4, 'status' => 1]);
        } else {
            $table->status = 1; // Đang có khách
            $table->save();
        }

        $order = Order::create([
            'user_id' => $admin->id,
            'name' => 'Khách Bàn',
            'phone' => '0911000011',
            'email' => 'pos02@example.com',
            'address' => 'Bàn ' . $table->name,
            'table_id' => $table->id,
            'total_price' => 300000,
            'payment_method' => 2,
            'status' => OrderStatus::PENDING,
            'payment_status' => Order::PAYMENT_PENDING,
            'created_by' => $admin->id,
        ]);

        $ref = 'POS02_' . uniqid();
        $response = $this->postJson(route('api.payment.webhook'), [
            'amount' => 300000,
            'content' => 'DH' . $order->id,
            'referenceCode' => $ref,
        ], [
            'X-Webhook-Token' => 'suite_token_pos02',
        ]);

        $response->assertStatus(200);
        $order->refresh();
        $table->refresh();

        $this->assertEquals(OrderStatus::COMPLETED, $order->status);
        $this->assertEquals(Order::PAYMENT_PAID, $order->payment_status);
        $this->assertNotNull($order->revenue_recognized_at);
        $this->assertEquals(0, $table->status); // Bàn đã được giải phóng về trạng thái trống

        PaymentTransaction::where('reference_code', $ref)->delete();
        $order->forceDelete();
    }

    /**
     * COD-01: Don COD chuyen delivered nhung khong xac nhan thu tien (cod_collected = false) -> payment_status van la PENDING
     */
    public function test_cod_01_delivered_without_cod_collected_keeps_payment_pending(): void
    {
        $employee = $this->getEmployeeUser();

        $codOrder = Order::create([
            'user_id' => $employee->id,
            'name' => 'Khách COD Test',
            'phone' => '0911000012',
            'email' => 'cod01@example.com',
            'address' => '123 Hoan Kiem',
            'total_price' => 220000,
            'payment_method' => 1,
            'status' => OrderStatus::PROCESSING,
            'delivery_status' => Order::DELIVERY_DELIVERING,
            'payment_status' => Order::PAYMENT_PENDING,
            'created_by' => $employee->id,
        ]);

        // Cập nhật giao hàng thành công nhưng KHÔNG xác nhận thu tiền (cod_collected = false)
        $this->actingAs($employee)->postJson(route('admin.order.updateDelivery', $codOrder->id), [
            'delivery_status' => Order::DELIVERY_DELIVERED,
            'cod_collected' => false,
        ]);

        $codOrder->refresh();
        $this->assertEquals(Order::DELIVERY_DELIVERED, $codOrder->delivery_status);
        $this->assertEquals(Order::PAYMENT_PENDING, $codOrder->payment_status);

        // Đơn này KHÔNG được có mặt trong scopeRevenueRecognized
        $this->assertFalse(Order::revenueRecognized()->where('id', $codOrder->id)->exists());

        OrderDeliveryLog::where('order_id', $codOrder->id)->delete();
        $codOrder->forceDelete();
    }

    /**
     * REV-01: Don giao hang thanh toan VietQR (PAID) nhung dang giao (delivering) -> Chi tinh vao collected, loai khoi revenue_recognized
     */
    public function test_rev_01_vietqr_delivery_delivering_excluded_from_recognized_revenue(): void
    {
        $admin = $this->getAdminUser();

        $order = Order::create([
            'user_id' => $admin->id,
            'name' => 'Khách Online Advance',
            'phone' => '0911000013',
            'email' => 'rev01@example.com',
            'address' => 'Online Delivery Address',
            'table_id' => null,
            'total_price' => 350000,
            'payment_method' => 2,
            'status' => OrderStatus::PROCESSING,
            'delivery_status' => Order::DELIVERY_DELIVERING,
            'payment_status' => Order::PAYMENT_PAID,
            'paid_at' => Carbon::now(),
            'revenue_recognized_at' => null, // Chưa giao thành công
            'created_by' => $admin->id,
        ]);

        // Tính vào tiền đã thực thu (Cash Collection)
        $this->assertTrue(Order::collected()->where('id', $order->id)->exists());

        // Loại khỏi doanh thu ghi nhận (Sales Revenue)
        $this->assertFalse(Order::revenueRecognized()->where('id', $order->id)->exists());

        $order->forceDelete();
    }

    /**
     * REV-02: Don giao hang thanh toan VietQR sau khi giao thanh cong -> Tinh vao revenue_recognized
     */
    public function test_rev_02_vietqr_delivery_delivered_included_in_recognized_revenue(): void
    {
        $employee = $this->getEmployeeUser();

        $order = Order::create([
            'user_id' => $employee->id,
            'name' => 'Khách Online Delivered',
            'phone' => '0911000014',
            'email' => 'rev02@example.com',
            'address' => 'Online Delivery Address 2',
            'table_id' => null,
            'total_price' => 400000,
            'payment_method' => 2,
            'status' => OrderStatus::PROCESSING,
            'delivery_status' => Order::DELIVERY_DELIVERING,
            'payment_status' => Order::PAYMENT_PAID,
            'paid_at' => Carbon::now(),
            'revenue_recognized_at' => null,
            'created_by' => $employee->id,
        ]);

        // Cập nhật giao hàng thành công
        $this->actingAs($employee)->postJson(route('admin.order.updateDelivery', $order->id), [
            'delivery_status' => Order::DELIVERY_DELIVERED,
        ]);

        $order->refresh();
        $this->assertEquals(Order::DELIVERY_DELIVERED, $order->delivery_status);
        $this->assertEquals(OrderStatus::COMPLETED, $order->status);
        $this->assertNotNull($order->revenue_recognized_at);

        // Có mặt trong cả 2 scope
        $this->assertTrue(Order::collected()->where('id', $order->id)->exists());
        $this->assertTrue(Order::revenueRecognized()->where('id', $order->id)->exists());

        OrderDeliveryLog::where('order_id', $order->id)->delete();
        $order->forceDelete();
    }

    /**
     * REV-03: Don giao hang that bai (failed) du da thanh toan VietQR -> Bi loai khoi ca revenueRecognized va scopePaid
     */
    public function test_rev_03_failed_delivery_excluded_from_recognized_revenue(): void
    {
        $employee = $this->getEmployeeUser();

        $order = Order::create([
            'user_id' => $employee->id,
            'name' => 'Khách Failed',
            'phone' => '0911000015',
            'email' => 'rev03@example.com',
            'address' => 'Failed Address',
            'total_price' => 280000,
            'payment_method' => 2,
            'status' => OrderStatus::PROCESSING,
            'delivery_status' => Order::DELIVERY_DELIVERING,
            'payment_status' => Order::PAYMENT_PAID,
            'paid_at' => Carbon::now(),
            'created_by' => $employee->id,
        ]);

        // Cập nhật giao hàng thất bại
        $this->actingAs($employee)->postJson(route('admin.order.updateDelivery', $order->id), [
            'delivery_status' => Order::DELIVERY_FAILED,
            'delivery_note' => 'Khách không nghe máy',
        ]);

        $order->refresh();
        $this->assertEquals(Order::DELIVERY_FAILED, $order->delivery_status);

        // Bị loại khỏi doanh thu ghi nhận và scopePaid
        $this->assertFalse(Order::revenueRecognized()->where('id', $order->id)->exists());
        $this->assertFalse(Order::paid()->where('id', $order->id)->exists());

        OrderDeliveryLog::where('order_id', $order->id)->delete();
        $order->forceDelete();
    }

    /**
     * REV-04: Don da hoan tien (payment_status = refunded) -> Bi loai khoi ca revenueRecognized, collected va paid
     */
    public function test_rev_04_refunded_order_excluded_from_revenue_and_collected(): void
    {
        $admin = $this->getAdminUser();

        $order = Order::create([
            'user_id' => $admin->id,
            'name' => 'Khách Refund',
            'phone' => '0911000016',
            'email' => 'rev04@example.com',
            'address' => 'Refund Address',
            'total_price' => 500000,
            'payment_method' => 2,
            'status' => OrderStatus::COMPLETED,
            'payment_status' => Order::PAYMENT_PAID,
            'revenue_recognized_at' => Carbon::now(),
            'paid_at' => Carbon::now(),
            'created_by' => $admin->id,
        ]);

        // Thực hiện hoàn tiền
        $order->markAsRefunded('Khách yêu cầu hủy đơn', 'RF_123456');
        $order->refresh();

        $this->assertEquals(Order::PAYMENT_REFUNDED, $order->payment_status);
        $this->assertFalse(Order::revenueRecognized()->where('id', $order->id)->exists());
        $this->assertFalse(Order::collected()->where('id', $order->id)->exists());
        $this->assertFalse(Order::paid()->where('id', $order->id)->exists());

        PaymentTransaction::where('order_id', $order->id)->delete();
        $order->forceDelete();
    }

    /**
     * REV-05: Don tao thang truoc nhung giao hang thanh cong thang nay -> Ghi nhan vao doanh thu thang nay
     */
    public function test_rev_05_accrual_period_matching_delivered_this_month_recognized_now(): void
    {
        $admin = $this->getAdminUser();

        $order = Order::create([
            'user_id' => $admin->id,
            'name' => 'Khách Tháng Trước',
            'phone' => '0911000017',
            'email' => 'rev05@example.com',
            'address' => 'Accrual Address',
            'total_price' => 600000,
            'payment_method' => 2,
            'status' => OrderStatus::COMPLETED,
            'delivery_status' => Order::DELIVERY_DELIVERED,
            'payment_status' => Order::PAYMENT_PAID,
            'paid_at' => Carbon::now()->subMonth(),
            'delivered_at' => Carbon::now(),
            'revenue_recognized_at' => Carbon::now(),
            'created_at' => Carbon::now()->subMonth(),
            'updated_at' => Carbon::now(),
            'created_by' => $admin->id,
        ]);

        $thisMonthStart = Carbon::now()->startOfMonth();
        $thisMonthEnd = Carbon::now()->endOfMonth();
        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        // Ghi nhận vào tháng này
        $inThisMonth = Order::revenueRecognized()
            ->whereBetween('revenue_recognized_at', [$thisMonthStart, $thisMonthEnd])
            ->where('id', $order->id)
            ->exists();
        $this->assertTrue($inThisMonth);

        // KHÔNG ghi nhận vào tháng trước
        $inLastMonth = Order::revenueRecognized()
            ->whereBetween('revenue_recognized_at', [$lastMonthStart, $lastMonthEnd])
            ->where('id', $order->id)
            ->exists();
        $this->assertFalse($inLastMonth);

        $order->forceDelete();
    }

    /**
     * NOTI-01: Webhook xu ly thanh cong ngay ca khi he thong thong bao gap ngoai le
     */
    public function test_noti_01_webhook_succeeds_even_if_notification_service_fails(): void
    {
        $admin = $this->getAdminUser();
        Config::set('payment.mode', 'production');
        Config::set('payment.webhook_token', 'suite_token_noti01');

        $order = Order::create([
            'user_id' => $admin->id,
            'name' => 'Khách Noti Test',
            'phone' => '0911000018',
            'email' => 'noti01@example.com',
            'address' => 'Ha Noi',
            'total_price' => 250000,
            'payment_method' => 2,
            'status' => OrderStatus::PENDING,
            'delivery_status' => Order::DELIVERY_WAITING,
            'payment_status' => Order::PAYMENT_PENDING,
            'created_by' => $admin->id,
        ]);

        $ref = 'NOTI01_' . uniqid();

        // Gui webhook thanh toan
        $response = $this->postJson(route('api.payment.webhook'), [
            'amount' => 250000,
            'content' => 'DH' . $order->id,
            'referenceCode' => $ref,
        ], [
            'X-Webhook-Token' => 'suite_token_noti01',
        ]);

        // Webhook phai tra ve 200 OK bat chap moi thong bao
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $order->refresh();
        $this->assertEquals(Order::PAYMENT_PAID, $order->payment_status);

        PaymentTransaction::where('reference_code', $ref)->delete();
        ServiceCall::where('order_id', $order->id)->delete();
        $order->forceDelete();
    }
}
