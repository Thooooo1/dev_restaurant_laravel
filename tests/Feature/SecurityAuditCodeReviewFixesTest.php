<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Table;
use App\Models\Product;
use App\Models\Orderdetail;
use App\Models\PaymentTransaction;
use App\Services\TableOrderService;
use App\Services\MomoService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class SecurityAuditCodeReviewFixesTest extends TestCase
{
    protected User $customer;
    protected Table $table;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::create([
            'username' => 'cust_' . uniqid(),
            'fullname' => 'Test Customer Audit',
            'password' => Hash::make('password123'),
            'phone' => '097' . random_int(1000000, 9999999),
            'roles' => 'customer',
            'status' => 1,
        ]);

        $this->table = Table::create([
            'name' => 'Bàn Test ' . uniqid(),
            'status' => 0,
        ]);

        $this->product = Product::create([
            'name' => 'Món Test Audit ' . uniqid(),
            'slug' => 'mon-test-audit-' . uniqid(),
            'price' => 50000,
            'price_buy' => 60000,
            'price_sale' => 50000,
            'qty' => 10,
            'status' => 1,
            'category_id' => 1,
            'brand_id' => 1,
        ]);
    }

    /**
     * Test P0-01: sendOtp không tiết lộ mã OTP khi môi trường không phải demo/local
     */
    public function test_p0_01_send_otp_does_not_leak_otp_code_in_production(): void
    {
        Config::set('app.env', 'production');
        Config::set('payment.mode', 'production');

        $response = $this->post(route('site.send_otp'), [
            'phone' => $this->customer->phone,
        ]);

        $response->assertStatus(200);
        $response->assertViewIs('frontend.verify_otp');
        $this->assertFalse(isset($response->original->getData()['otp_display']));
        $this->assertFalse(isset($response->original->getData()['otp_code']));
    }

    /**
     * Test P0-02: startOrder với SĐT không tự động đăng nhập Auth::login()
     */
    public function test_p0_02_table_order_start_does_not_auto_login_customer(): void
    {
        Auth::logout();
        $this->assertFalse(Auth::check());

        $response = $this->post(route('site.table.start', $this->table->id), [
            'phone' => $this->customer->phone,
            'name' => 'Khách Vãng Lai',
        ]);

        $this->assertFalse(Auth::check(), 'Lỗi bảo mật P0-02: startOrder đã tự động login khách hàng mà không qua xác thực!');
        $this->assertEquals($this->customer->phone, session('customer_phone'));
    }

    /**
     * Test P0-03: checkPhone với khách hàng có đơn đang mở không tự động Auth::login()
     */
    public function test_p0_03_check_phone_requires_password_and_never_auto_logins(): void
    {
        Auth::logout();

        // Tạo đơn hàng tại bàn cho khách
        $order = Order::create([
            'user_id' => $this->customer->id,
            'table_id' => $this->table->id,
            'phone' => $this->customer->phone,
            'name' => $this->customer->fullname,
            'address' => 'Tại quán',
            'total_price' => 100000,
            'status' => 0, // Đang mở
            'created_by' => $this->customer->id,
        ]);

        $response = $this->postJson(route('site.check_phone'), [
            'phone' => $this->customer->phone,
        ]);

        $response->assertStatus(200);
        $this->assertFalse(Auth::check(), 'Lỗi bảo mật P0-03: checkPhone đã tự động Auth::login() khách hàng có đơn đang mở!');
        $this->assertEquals('require_password', $response->json('status'));
    }

    /**
     * Test P0-04: confirmPayment từ chối order_id không thuộc table_id và giữ idempotency khi đã thanh toán
     */
    public function test_p0_04_confirm_payment_enforces_table_binding_and_idempotency(): void
    {
        $otherTable = Table::create(['name' => 'Bàn Khác ' . uniqid(), 'status' => 0]);
        $order = Order::create([
            'table_id' => $otherTable->id,
            'user_id' => $this->customer->id,
            'name' => $this->customer->fullname,
            'address' => 'Tại quán',
            'phone' => $this->customer->phone,
            'total_price' => 100000,
            'payment_status' => Order::PAYMENT_PENDING,
            'status' => 0,
            'created_by' => 1,
        ]);

        // Thử xác nhận thanh toán đơn của otherTable thông qua endpoint của this->table
        $response = $this->withSession(['table_id' => $this->table->id])
            ->postJson(route('site.table.payment.confirm', $this->table->id), [
                'order_id' => $order->id,
            ]);

        $response->assertStatus(403);

        // Giờ chuyển sang đúng bàn, đánh dấu đã thanh toán và kiểm tra Idempotency
        $order->payment_status = Order::PAYMENT_PAID;
        $order->paid_at = Carbon::now()->subMinutes(10);
        $initialPaidAt = $order->paid_at;
        $order->save();

        PaymentTransaction::create([
            'order_id' => $order->id,
            'reference_code' => 'TX_EXISTING_' . $order->id,
            'amount' => 100000,
            'status' => PaymentTransaction::STATUS_SUCCESS,
        ]);

        $txCountBefore = PaymentTransaction::where('order_id', $order->id)->count();

        $responseIdempotent = $this->withSession(['table_id' => $otherTable->id, 'table_order_id' => $order->id])
            ->postJson(route('site.table.payment.confirm', $otherTable->id), [
                'order_id' => $order->id,
            ]);

        $responseIdempotent->assertStatus(200);
        $this->assertTrue($responseIdempotent->json('already_paid'));

        // Kiểm tra không bị sửa paid_at và không bị tạo transaction thừa
        $order->refresh();
        $this->assertEquals($initialPaidAt->toDateTimeString(), $order->paid_at->toDateTimeString());
        $this->assertEquals($txCountBefore, PaymentTransaction::where('order_id', $order->id)->count());
    }

    /**
     * Test P1-05: TableOrderService::addItemsToTableOrder kiểm tra tồn kho và trừ tồn kho
     */
    public function test_p1_05_table_order_service_checks_stock_and_decrements_inventory(): void
    {
        $initialStock = $this->product->qty; // 10

        // 1. Đặt 3 phần -> Thành công và kho giảm còn 7
        TableOrderService::addItemsToTableOrder($this->table->id, [
            ['product_id' => $this->product->id, 'qty' => 3, 'price' => 50000]
        ]);

        $this->product->refresh();
        $this->assertEquals($initialStock - 3, $this->product->qty);

        // 2. Thử đặt 10 phần nữa trong khi kho chỉ còn 7 -> Bị chặn DomainException
        $this->expectException(\DomainException::class);
        TableOrderService::addItemsToTableOrder($this->table->id, [
            ['product_id' => $this->product->id, 'qty' => 10, 'price' => 50000]
        ]);
    }

    /**
     * Test P1-07: sendToKitchenAjax chặn giả mạo table_id khi đã có phiên bàn khác
     */
    public function test_p1_07_send_to_kitchen_blocks_mismatched_table_id(): void
    {
        $otherTable = Table::create(['name' => 'Bàn Khác ' . uniqid(), 'status' => 0]);
        $cartKey = 'cart_' . $this->customer->id;

        $response = $this->actingAs($this->customer)->withSession([
            'table_id' => $this->table->id,
            'table_name' => $this->table->name,
            $cartKey => [
                $this->product->id => [
                    'id' => $this->product->id,
                    'name' => $this->product->name,
                    'price' => 50000,
                    'qty' => 1,
                ]
            ]
        ])->postJson(route('site.cart.sendKitchenAjax'), [
            'table_id' => $otherTable->id, // Mismatch
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test P1-08: MoMo Return & IPN cập nhật PAYMENT_PAID và tạo PaymentTransaction
     */
    public function test_p1_08_momo_payment_updates_payment_status_and_records_transaction(): void
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'name' => $this->customer->fullname,
            'address' => 'Tại quán',
            'phone' => $this->customer->phone,
            'total_price' => 200000,
            'payment_status' => Order::PAYMENT_PENDING,
            'status' => 0,
            'created_by' => 1,
        ]);

        // Mock MomoService verifyPayment trả về true
        $momoMock = $this->createMock(MomoService::class);
        $momoMock->method('verifyPayment')->willReturn(true);
        $this->app->instance(MomoService::class, $momoMock);

        $response = $this->get(route('site.momo.return', [
            'partnerCode' => 'MOMO',
            'orderId' => $order->id,
            'requestId' => 'REQ_MOMO_' . uniqid(),
            'amount' => 200000,
            'resultCode' => 0,
            'transId' => 'TRANS_MOMO_' . $order->id,
            'signature' => 'mock_signature',
        ]));

        $order->refresh();
        $this->assertEquals(Order::PAYMENT_PAID, $order->payment_status);
        $this->assertNotNull($order->paid_at);
        $this->assertNotNull($order->revenue_recognized_at);

        $tx = PaymentTransaction::where('order_id', $order->id)->first();
        $this->assertNotNull($tx);
        $this->assertEquals('TRANS_MOMO_' . $order->id, $tx->reference_code);
        $this->assertEquals(PaymentTransaction::STATUS_SUCCESS, $tx->status);
    }

    /**
     * Test P2-10: IDOR protection trên route vietqrPay và momoSandboxPay
     */
    public function test_p2_10_vietqr_pay_blocks_unauthorized_customer_access(): void
    {
        $otherCustomer = User::create([
            'username' => 'other_' . uniqid(),
            'fullname' => 'Other Customer',
            'password' => Hash::make('password123'),
            'phone' => '096' . random_int(1000000, 9999999),
            'roles' => 'customer',
            'status' => 1,
        ]);

        $order = Order::create([
            'user_id' => $this->customer->id,
            'name' => $this->customer->fullname,
            'address' => 'Tại quán',
            'phone' => $this->customer->phone,
            'total_price' => 150000,
            'payment_status' => Order::PAYMENT_PENDING,
            'status' => 0,
            'created_by' => $this->customer->id,
        ]);

        // otherCustomer truy cập mã thanh toán VietQR của this->customer
        $response = $this->actingAs($otherCustomer)->get(route('site.vietqr.pay', $order->id));
        $response->assertStatus(403);

        // this->customer truy cập đơn của chính mình -> Cho phép
        $responseOwner = $this->actingAs($this->customer)->get(route('site.vietqr.pay', $order->id));
        $responseOwner->assertStatus(200);
    }

    /**
     * Test P2-17: Webhook không có reference code sinh fingerprint ổn định chống duplicate
     */
    public function test_p2_17_webhook_without_reference_generates_stable_fingerprint(): void
    {
        Config::set('payment.mode', 'production');
        Config::set('payment.webhook_token', 'test_secret_ci_key');

        $order = Order::create([
            'user_id' => $this->customer->id,
            'name' => $this->customer->fullname,
            'address' => 'Tại quán',
            'phone' => $this->customer->phone,
            'total_price' => 120000,
            'payment_status' => Order::PAYMENT_PENDING,
            'status' => 0,
            'created_by' => 1,
        ]);

        $payload = [
            'amount' => 120000,
            'content' => 'DH' . $order->id,
            'transactionDate' => '2026-11-17 12:00:00',
        ];

        // Lần 1: Thành công
        $res1 = $this->withHeaders(['Authorization' => 'Apikey test_secret_ci_key'])
            ->postJson(route('api.payment.webhook'), $payload);
        $res1->assertStatus(200);

        // Lần 2 (Retry cùng payload không có referenceCode): Phải phát hiện giao dịch đã xử lý (idempotent)
        $res2 = $this->withHeaders(['Authorization' => 'Apikey test_secret_ci_key'])
            ->postJson(route('api.payment.webhook'), $payload);
        $res2->assertStatus(200);
        $this->assertTrue($res2->json('already_processed'));

        // Chỉ có đúng 1 transaction được ghi
        $this->assertEquals(1, PaymentTransaction::where('order_id', $order->id)->count());
    }
}
