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

        $responseIdempotent = $this->withSession([
            'table_id' => $otherTable->id,
            'table_order_id' => $order->id,
            'table_verified_' . $otherTable->id => true,
        ])
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
            'status' => 2, // Đã hoàn tất chế biến/phục vụ để ghi nhận doanh thu khi thanh toán
            'created_by' => 1,
        ]);

        // Mock MomoService verifyPayment trả về true
        config(['payment.mode' => 'demo']);
        $momoMock = $this->createMock(MomoService::class);
        $momoMock->method('verifyPayment')->willReturn(true);
        $this->app->instance(MomoService::class, $momoMock);

        $response = $this->postJson(route('site.momo.ipn'), [
            'partnerCode' => 'MOMO',
            'orderId' => $order->id,
            'requestId' => 'REQ_MOMO_' . uniqid(),
            'amount' => 200000,
            'resultCode' => 0,
            'transId' => 'TRANS_MOMO_' . $order->id,
            'signature' => 'mock_signature',
        ]);

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

    /**
     * Test V2-P1-19: ServiceCall Controller loại bỏ thẻ HTML và xác thực chặt chẽ call_type
     */
    public function test_v2_p1_19_service_call_sanitizes_html_and_validates_type(): void
    {
        // 1. Gửi HTML độc hại trong note và customer_name (đã quét mã QR bàn)
        $response = $this->withSession(['table_verified_' . $this->table->id => true])
            ->postJson(route('site.service-call.store'), [
            'table_id' => $this->table->id,
            'call_type' => 'call_waiter',
            'note' => 'Cần hỗ trợ <script>alert("xss")</script> ngay',
            'customer_name' => '<b>Hacker</b>',
            'phone' => '0912345678',
        ]);

        $response->assertStatus(200);
        $callId = $response->json('call.id') ?? $response->json('call_id');
        $this->assertNotNull($callId);

        $savedCall = \App\Models\ServiceCall::find($callId);
        $this->assertStringNotContainsString('<script>', $savedCall->note);
        $this->assertStringNotContainsString('<b>', $savedCall->customer_name);

        // 2. Gửi call_type không hợp lệ phải bị từ chối 422
        $invalidResponse = $this->postJson(route('site.service-call.store'), [
            'table_id' => $this->table->id,
            'call_type' => 'invalid_malicious_type',
        ]);
        $invalidResponse->assertStatus(422);
    }

    /**
     * Test V2-P1-20: Khách hàng / khách vãng lai không thể đánh dấu đã đọc thông báo của nhân viên
     */
    public function test_v2_p1_20_customer_cannot_mark_employee_notifications_as_read(): void
    {
        // Tạo thông báo nội bộ của nhân viên
        $staffNoti = \App\Models\Notification::create([
            'user_id' => null,
            'role' => 'employee',
            'type' => 'service_call',
            'title' => 'Bàn 5 gọi nhân viên',
            'message' => 'Yêu cầu hỗ trợ',
            'is_read' => false,
        ]);

        // Khách vãng lai (chưa đăng nhập) thử markAsRead
        Auth::logout();
        $guestResponse = $this->postJson("/notifications/{$staffNoti->id}/read");
        $guestResponse->assertStatus(403);

        // Khách hàng thông thường thử markAsRead
        $custResponse = $this->actingAs($this->customer)->postJson("/notifications/{$staffNoti->id}/read");
        $custResponse->assertStatus(403);
        $this->assertFalse($staffNoti->fresh()->is_read);

        // Nhân viên đăng nhập được phép markAsRead
        $employee = User::create([
            'username' => 'emp_' . uniqid(),
            'fullname' => 'Staff Member',
            'phone' => '098' . random_int(1000000, 9999999),
            'password' => Hash::make('password123'),
            'roles' => 'employee',
            'status' => 1,
        ]);

        $empResponse = $this->actingAs($employee)->postJson("/notifications/{$staffNoti->id}/read");
        $empResponse->assertStatus(200);
        $this->assertTrue($staffNoti->fresh()->isReadBy($employee->id));
    }

    /**
     * Test V2-P1-22: Không thể checkout gộp thêm món vào đơn bàn đã thanh toán
     */
    public function test_v2_p1_22_checkout_rejects_merging_into_paid_table_order(): void
    {
        // Tạo đơn hàng tại bàn đã thanh toán (payment_status = paid)
        $paidOrder = Order::create([
            'table_id' => $this->table->id,
            'user_id' => $this->customer->id,
            'name' => $this->customer->fullname,
            'phone' => $this->customer->phone,
            'address' => 'Tại quán',
            'total_price' => 50000,
            'status' => 1, // Processing
            'payment_status' => Order::PAYMENT_PAID,
            'paid_at' => Carbon::now(),
            'created_by' => 1,
        ]);

        $cartKey = "cart_" . $this->customer->id;
        session([
            'table_id' => $this->table->id,
            $cartKey => [
                $this->product->id => [
                    'id' => $this->product->id,
                    'name' => $this->product->name,
                    'slug' => $this->product->slug,
                    'price' => (float)$this->product->price,
                    'qty' => 1,
                    'discount' => 0,
                    'image' => $this->product->image,
                ]
            ]
        ]);

        // Thử thực hiện checkout khi bàn đã thanh toán
        $response = $this->actingAs($this->customer)->post(route('site.checkout'), [
            'payment_method' => 'TaiBan',
            'name' => $this->customer->fullname,
            'phone' => $this->customer->phone,
        ]);

        // Phải bị từ chối với thông báo lỗi
        $response->assertSessionHas('error');
        $this->assertEquals(0, Orderdetail::where('order_id', $paidOrder->id)->where('product_id', $this->product->id)->count());
    }

    /**
     * Test V2-P2-23: Đăng ký thành viên ngăn chặn lỗ hổng Open Redirect
     */
    public function test_v2_p2_23_registration_rejects_open_redirect(): void
    {
        $uniquePhone = '098' . random_int(1000000, 9999999);
        $response = $this->post(route('site.doregister'), [
            'fullname' => 'New User Test',
            'username' => 'newuser_' . uniqid(),
            'phone' => $uniquePhone,
            'email' => 'newuser_' . uniqid() . '@civilize.vn',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'redirect' => 'https://evil.com/phishing',
        ]);

        // Không được redirect sang external domain evil.com
        $response->assertRedirect(route('site.home'));
    }

    /**
     * Test V2-P2-24: Đặt lại đơn (Reorder) lấy giá niêm yết hiện tại và bỏ qua món ngừng kinh doanh
     */
    public function test_v2_p2_24_reorder_uses_live_pricing_and_ignores_inactive_products(): void
    {
        // 1. Món A còn kinh doanh nhưng giá đã tăng từ 50k lên 80k
        $this->product->update(['price_sale' => 80000, 'price_buy' => 80000, 'status' => 1]);

        // 2. Món B đã tạm dừng kinh doanh (status = 0)
        $inactiveProduct = Product::create([
            'name' => 'Món Đã Ngừng ' . uniqid(),
            'slug' => 'mon-ngung-' . uniqid(),
            'price' => 30000,
            'price_buy' => 20000,
            'price_sale' => 30000,
            'qty' => 0,
            'status' => 0,
            'category_id' => 1,
            'brand_id' => 1,
        ]);

        // Tạo đơn hàng cũ đã hoàn thành
        $oldOrder = Order::create([
            'user_id' => $this->customer->id,
            'name' => $this->customer->fullname,
            'phone' => $this->customer->phone,
            'address' => 'Tại quán',
            'total_price' => 80000,
            'status' => 2, // Đã hoàn thành (cho phép reorder)
            'payment_status' => Order::PAYMENT_PAID,
            'created_by' => 1,
        ]);

        Orderdetail::create([
            'order_id' => $oldOrder->id,
            'product_id' => $this->product->id,
            'price' => 50000, // Giá cũ trong lịch sử
            'qty' => 2,
            'amount' => 100000,
        ]);

        Orderdetail::create([
            'order_id' => $oldOrder->id,
            'product_id' => $inactiveProduct->id,
            'price' => 30000,
            'qty' => 1,
            'amount' => 30000,
        ]);

        // V4-P2-41: Yêu cầu GET phải bị chặn 405 Method Not Allowed
        $getResp = $this->actingAs($this->customer)->get(route('site.orders.reorder', $oldOrder->id));
        $getResp->assertStatus(405);

        // POST reorder có bảo vệ CSRF thành công
        $response = $this->actingAs($this->customer)->post(route('site.orders.reorder', $oldOrder->id));
        $response->assertRedirect(route('site.cart'));

        $cartKey = "cart_" . $this->customer->id;
        $cart = session($cartKey);

        // Món A phải có trong giỏ hàng với giá mới (80.000đ) thay vì giá cũ (50.000đ)
        $this->assertArrayHasKey($this->product->id, $cart);
        $this->assertEquals(80000, $cart[$this->product->id]['price']);
        $this->assertEquals(2, $cart[$this->product->id]['qty']);

        // Món B ngừng phục vụ phải bị loại trừ khỏi giỏ hàng
        $this->assertArrayNotHasKey($inactiveProduct->id, $cart);
    }

    /**
     * Test V3-P1-26 & TC-V3-01: Nhập kho -> Sửa phiếu -> Xóa phiếu đồng bộ tồn kho chính xác
     */
    public function test_v3_p1_26_import_goods_update_and_destroy_sync_inventory(): void
    {
        $admin = User::firstOrCreate(['roles' => 'admin', 'username' => 'test_admin_import'], [
            'fullname' => 'Admin Import Goods',
            'phone' => '0981112233',
            'password' => bcrypt('password123'),
            'status' => 1,
            'roles' => 'admin',
        ]);

        $this->product->update(['qty' => 10]);

        // 1. Nhập kho 20 phần -> Tồn kho tăng từ 10 lên 30
        $responseCreate = $this->actingAs($admin)->post(route('admin.import-goods.store'), [
            'code' => 'PNK_' . uniqid(),
            'supplier_name' => 'Nhà Cung Cấp Test',
            'import_date' => now()->toDateString(),
            'items' => [
                [
                    'name' => $this->product->name,
                    'unit' => 'phần',
                    'quantity' => 20,
                    'price' => 30000,
                ]
            ]
        ]);
        $responseCreate->assertRedirect();
        $this->assertEquals(30, $this->product->fresh()->qty);

        $receipt = \App\Models\ImportReceipt::latest('id')->first();

        // 2. Chỉnh sửa phiếu nhập xuống còn 5 phần -> Tồn kho cập nhật về 15 (10 ban đầu + 5 mới)
        $responseUpdate = $this->actingAs($admin)->put(route('admin.import-goods.update', $receipt->id), [
            'code' => $receipt->code,
            'supplier_name' => 'Nhà Cung Cấp Test',
            'import_date' => now()->toDateString(),
            'items' => [
                [
                    'name' => $this->product->name,
                    'unit' => 'phần',
                    'quantity' => 5,
                    'price' => 30000,
                ]
            ]
        ]);
        $responseUpdate->assertRedirect();
        $this->assertEquals(15, $this->product->fresh()->qty);

        // 3. Xóa phiếu nhập -> Tồn kho hoàn trả về mức ban đầu (10 phần)
        $responseDestroy = $this->actingAs($admin)->delete(route('admin.import-goods.destroy', $receipt->id));
        $responseDestroy->assertRedirect();
        $this->assertEquals(10, $this->product->fresh()->qty);
    }

    /**
     * Test V3-P1-27 & TC-V3-02: Không thể mở lại đơn hàng đã hủy để nhân bản hoàn tồn kho
     */
    public function test_v3_p1_27_order_cannot_be_reopened_from_cancelled_status(): void
    {
        $admin = User::firstOrCreate(['roles' => 'admin', 'username' => 'test_admin_import'], [
            'fullname' => 'Admin Import Goods',
            'phone' => '0981112233',
            'password' => bcrypt('password123'),
            'status' => 1,
            'roles' => 'admin',
        ]);

        $this->product->update(['qty' => 10]);

        // Tạo đơn hàng PROCESSING với 2 phần món
        $order = Order::create([
            'user_id' => $this->customer->id,
            'name' => $this->customer->fullname,
            'phone' => $this->customer->phone,
            'address' => 'Tại quán',
            'total_price' => 100000,
            'status' => \App\Enums\OrderStatus::PROCESSING,
            'payment_status' => Order::PAYMENT_PENDING,
            'created_by' => 1,
        ]);

        Orderdetail::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'price' => 50000,
            'qty' => 2,
            'amount' => 100000,
        ]);

        // 1. Hủy đơn hàng lần 1 -> Hoàn tồn kho từ 10 lên 12
        $resCancel = $this->actingAs($admin)->post(route('admin.order.update', $order->id), [
            'status' => \App\Enums\OrderStatus::CANCELLED,
            'reason' => 'Khách hủy món',
        ]);
        $resCancel->assertSessionHas('success');
        $this->assertEquals(12, $this->product->fresh()->qty);
        $this->assertEquals(\App\Enums\OrderStatus::CANCELLED, (int)$order->fresh()->status);

        // 2. Cố tình mở lại đơn đã hủy (chuyển CANCELLED -> PROCESSING) -> Phải bị từ chối
        $resReopen = $this->actingAs($admin)->post(route('admin.order.update', $order->id), [
            'status' => \App\Enums\OrderStatus::PROCESSING,
        ]);
        $resReopen->assertSessionHas('error');
        $this->assertEquals(12, $this->product->fresh()->qty);
        $this->assertEquals(\App\Enums\OrderStatus::CANCELLED, (int)$order->fresh()->status);
    }

    /**
     * Test V3-P1-28 & TC-V3-03: POS từ chối sửa/xóa/thêm món vào đơn hàng đã thanh toán (paid)
     */
    public function test_v3_p1_28_pos_rejects_modifying_paid_order_details(): void
    {
        $employee = User::firstOrCreate(['roles' => 'employee', 'username' => 'test_emp_pos'], [
            'fullname' => 'Staff POS',
            'phone' => '0971122334',
            'password' => bcrypt('password123'),
            'status' => 1,
            'roles' => 'employee',
        ]);

        $paidOrder = Order::create([
            'table_id' => $this->table->id,
            'user_id' => $this->customer->id,
            'name' => $this->customer->fullname,
            'phone' => $this->customer->phone,
            'address' => 'Tại quán',
            'total_price' => 50000,
            'status' => 1, // PROCESSING
            'payment_status' => Order::PAYMENT_PAID,
            'paid_at' => now(),
            'created_by' => 1,
        ]);

        $detail = Orderdetail::create([
            'order_id' => $paidOrder->id,
            'product_id' => $this->product->id,
            'price' => 50000,
            'qty' => 1,
            'amount' => 50000,
            'status' => 'served',
        ]);

        // 1. Thử thêm món vào đơn đã paid -> Bị từ chối 422
        $resAdd = $this->actingAs($employee)->postJson(route('admin.table-order.addProductToOrder'), [
            'order_id' => $paidOrder->id,
            'product_id' => $this->product->id,
            'qty' => 1,
        ]);
        $resAdd->assertStatus(422);

        // 2. Thử xóa món khỏi đơn đã paid -> Bị từ chối 422
        $resRemove = $this->actingAs($employee)->postJson(route('admin.table-order.removeProductFromOrder'), [
            'order_detail_id' => $detail->id,
        ]);
        $resRemove->assertStatus(422);

        // 3. Thử cập nhật số lượng món -> Bị từ chối 422
        $resQty = $this->actingAs($employee)->postJson(route('admin.table-order.updateProductQty'), [
            'order_detail_id' => $detail->id,
            'qty' => 3,
        ]);
        $resQty->assertStatus(422);

        // 4. Thử lưu toàn bộ đơn hàng POS -> Bị từ chối 422
        $resSave = $this->actingAs($employee)->postJson(route('admin.table-order.saveOrder'), [
            'order_id' => $paidOrder->id,
            'items' => [
                ['product_id' => $this->product->id, 'qty' => 2]
            ]
        ]);
        $resSave->assertStatus(422);
    }

    /**
     * Test V3-P1-29 & TC-V3-04: Đổi bàn có Pessimistic Locking và chặn đổi sang bàn đã có người
     */
    public function test_v3_p1_29_change_table_rejects_occupied_target_table_or_paid_order(): void
    {
        $employee = User::firstOrCreate(['roles' => 'employee', 'username' => 'test_emp_pos'], [
            'fullname' => 'Staff POS',
            'phone' => '0971122334',
            'password' => bcrypt('password123'),
            'status' => 1,
            'roles' => 'employee',
        ]);

        $tableA = $this->table;
        $tableB = Table::create(['name' => 'Bàn Test B ' . uniqid(), 'status' => 1]);

        // Đơn hàng tại bàn A
        $orderA = Order::create([
            'user_id' => $this->customer->id,
            'table_id' => $tableA->id,
            'name' => 'Khách Bàn A',
            'phone' => '0912345678',
            'address' => 'Tại quán',
            'total_price' => 0,
            'status' => 0,
            'payment_status' => Order::PAYMENT_PENDING,
            'created_by' => 1,
        ]);

        // Bàn B đã có một đơn hàng active khác
        $orderB = Order::create([
            'user_id' => $this->customer->id,
            'table_id' => $tableB->id,
            'name' => 'Khách Bàn B',
            'phone' => '0987654321',
            'address' => 'Tại quán',
            'total_price' => 0,
            'status' => 1,
            'payment_status' => Order::PAYMENT_PENDING,
            'created_by' => 1,
        ]);
        $tableB->update(['locked_order_id' => $orderB->id]);

        // Thử đổi orderA sang tableB (đang bận) -> Phải bị từ chối 422
        $res = $this->actingAs($employee)->postJson(route('admin.table-order.changeTable'), [
            'order_id' => $orderA->id,
            'new_table_id' => $tableB->id,
        ]);
        $res->assertStatus(422);
        $this->assertEquals($tableA->id, $orderA->fresh()->table_id);
    }

    /**
     * Test V3-P2-30 & TC-V3-05: Form đặt bàn từ chối giờ ngoài khung giờ mở cửa hoặc đặt trễ hôm nay
     */
    public function test_v3_p2_30_booking_form_rejects_invalid_hours(): void
    {
        // 1. Đặt bàn lúc 06:00 sáng (ngoài giờ phục vụ 09:00 - 21:30)
        $resEarly = $this->post(route('site.booking.store'), [
            'name' => 'Khách Đặt Sớm',
            'email' => 'early@gmail.com',
            'phone' => '0912345678',
            'number_of_people' => 4,
            'booking_date' => now()->addDay()->toDateString(),
            'booking_time' => '06:00',
        ]);
        $resEarly->assertSessionHasErrors('booking_time');

        // 2. Đặt bàn hôm nay với giờ đã qua hoặc chưa đủ 15 phút
        $resPast = $this->post(route('site.booking.store'), [
            'name' => 'Khách Đặt Trễ',
            'email' => 'late@gmail.com',
            'phone' => '0912345678',
            'number_of_people' => 4,
            'booking_date' => now()->toDateString(),
            'booking_time' => now()->subMinutes(10)->format('H:i'),
        ]);
        $resPast->assertSessionHasErrors('booking_time');
    }

    /**
     * Test V3-P2-31 & TC-V3-06: Phiếu nhập từ chối số lượng không phải số nguyên dương
     */
    public function test_v3_p2_31_import_goods_rejects_decimal_quantity(): void
    {
        $admin = User::firstOrCreate(['roles' => 'admin', 'username' => 'test_admin_import'], [
            'fullname' => 'Admin Import Goods',
            'phone' => '0981112233',
            'password' => bcrypt('password123'),
            'status' => 1,
            'roles' => 'admin',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.import-goods.store'), [
            'code' => 'PNK_DEC_' . uniqid(),
            'supplier_name' => 'Nhà Cung Cấp Test',
            'import_date' => now()->toDateString(),
            'items' => [
                [
                    'name' => $this->product->name,
                    'unit' => 'phần',
                    'quantity' => 2.5,
                    'price' => 30000,
                ]
            ]
        ]);
        $response->assertSessionHasErrors('items.0.quantity');
    }

    /**
     * Test V3-P2-33 & TC-V3-08: Chatbot không xác nhận bừa còn bàn cho đoàn lớn vượt sức chứa
     */
    public function test_v3_p2_33_chatbot_safely_handles_large_groups(): void
    {
        $chatbotService = app(\App\Services\ChatbotService::class);
        $reflection = new \ReflectionClass($chatbotService);
        $method = $reflection->getMethod('toolCheckTableAvailability');
        $method->setAccessible(true);

        // Đoàn 500 khách vượt quá tổng số ghế nhà hàng
        $result = $method->invoke($chatbotService, [
            'number_of_people' => 500,
            'booking_date' => now()->addDays(2)->toDateString(),
            'booking_time' => '19:00',
        ]);

        $this->assertFalse($result['available']);
        $this->assertTrue($result['is_large_group']);
        $this->assertTrue($result['requires_staff_confirmation']);
    }

    /**
     * Test V1-P2-11 & TC-V3-09: Chatbot clearSession không thể xóa lịch sử chat của session_id người khác
     */
    public function test_v1_p2_11_chatbot_clear_session_is_isolated(): void
    {
        $victimSessionId = 'victim_session_' . uniqid();
        \App\Models\ChatbotMessage::create([
            'session_id' => $victimSessionId,
            'user_id' => null,
            'sender' => 'user',
            'message' => 'Thông tin riêng tư của khách hàng A',
        ]);

        // Người dùng B (session khác) gửi request clearSession với session_id của khách hàng A
        $response = $this->postJson(route('chatbot.clear'), [
            'session_id' => $victimSessionId,
        ]);
        $response->assertStatus(200);

        // Tin nhắn của khách hàng A phải còn nguyên vẹn, không bị xóa
        $this->assertEquals(1, \App\Models\ChatbotMessage::where('session_id', $victimSessionId)->count());
    }

    /**
     * Test V4-P1-35 & Scenario 1: Guest mới không cookie GET /ban/{id}/thanh-toan-qr của bàn có order -> 403
     */
    public function test_v4_p1_35_fresh_guest_without_token_gets_403_on_payment_qr(): void
    {
        $table = Table::create([
            'floor_id' => 1,
            'name' => 'Bàn Test V4 QR ' . uniqid(),
            'seats' => 4,
            'status' => 1,
        ]);

        $order = Order::create([
            'table_id' => $table->id,
            'user_id' => $this->customer->id,
            'name' => 'Khách Bàn',
            'phone' => '0912345678',
            'address' => 'Tại quán',
            'total_price' => 50000,
            'status' => 0,
            'payment_status' => Order::PAYMENT_PENDING,
            'created_by' => 1,
        ]);

        Orderdetail::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'price' => 50000,
            'qty' => 1,
            'amount' => 50000,
            'status' => 'pending',
        ]);

        // 1. Guest mới không cookie/session gọi thanh toán QR -> Phải nhận HTTP 403
        $response = $this->get(route('site.table.payment.qr', $table->id));
        $response->assertStatus(403);

        // 2. Guest có token QR hợp lệ -> Truy cập thành công HTTP 200
        $validToken = $table->getQrToken();
        $responseWithToken = $this->withSession([
            'table_id' => $table->id,
            'table_verified_' . $table->id => true,
        ])->get(route('site.table.payment.qr', $table->id));
        $responseWithToken->assertStatus(200);
    }

    /**
     * Test V4-P1-36 & Scenario 2: Quét scan không token hoặc POST service-call không cấp quyền xem bill hay QR
     */
    public function test_v4_p1_36_guest_table_scan_or_service_call_without_token_does_not_grant_bill_access(): void
    {
        $table = Table::create([
            'floor_id' => 1,
            'name' => 'Bàn Test V4 Scan ' . uniqid(),
            'seats' => 4,
            'status' => 1,
        ]);

        $order = Order::create([
            'table_id' => $table->id,
            'user_id' => $this->customer->id,
            'name' => 'Khách Bàn',
            'phone' => '0912345678',
            'address' => 'Tại quán',
            'total_price' => 50000,
            'status' => 0,
            'payment_status' => Order::PAYMENT_PENDING,
            'created_by' => 1,
        ]);

        Orderdetail::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'price' => 50000,
            'qty' => 1,
            'amount' => 50000,
            'status' => 'pending',
        ]);

        // 1. Guest gọi GET /ban/{id}/goi-mon không có token -> Không được cấp session xem bill
        $scanResponse = $this->get(route('site.table.scan', $table->id));
        $scanResponse->assertStatus(200);

        // Thử truy cập hóa đơn của bàn -> Bị 403 Forbidden
        $billResponse = $this->get(route('site.table.bill', $table->id));
        $billResponse->assertStatus(403);

        // 2. Gửi POST service-call với table_id không kèm token -> Bị chặn 403 Forbidden
        $callResponse = $this->postJson(route('site.service-call.store'), [
            'table_id' => $table->id,
            'call_type' => 'call_waiter',
        ]);
        $callResponse->assertStatus(403);

        $billResponse2 = $this->get(route('site.table.bill', $table->id));
        $billResponse2->assertStatus(403);

        // 3. Quét QR kèm token hợp lệ -> Cấp quyền xem bill thành công
        $scanWithToken = $this->get(route('site.table.scan', ['id' => $table->id, 'token' => $table->getQrToken()]));
        $scanWithToken->assertStatus(200);

        $billResponse3 = $this->get(route('site.table.bill', $table->id));
        $billResponse3->assertStatus(200);
    }

    /**
     * Test V4-P0-34 & V4-P1-40 & Scenario 3: MoMo Production key isolation, IPN amount verification & GET return state isolation
     */
    public function test_v4_p0_34_and_p1_40_momo_security_and_amount_verification(): void
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'name' => 'Khách MoMo',
            'phone' => '0988776655',
            'address' => '123 Đường Test',
            'total_price' => 100000,
            'status' => 0,
            'payment_status' => Order::PAYMENT_PENDING,
            'created_by' => 1,
        ]);

        // 1. GET return tuyệt đối KHÔNG mutate payment_status thành paid
        config(['payment.mode' => 'demo']);
        $momoService = app(\App\Services\MomoService::class);
        $returnParams = [
            'orderId' => $order->id,
            'resultCode' => 0,
            'amount' => 100000,
            'message' => 'Success',
            'extraData' => '',
            'orderInfo' => 'Test',
            'orderType' => 'momo_wallet',
            'partnerCode' => 'MOMOBKUN20180529',
            'payType' => 'qr',
            'requestId' => 'REQ_' . uniqid(),
            'responseTime' => (string)time(),
            'transId' => 'TRANS_' . uniqid(),
        ];
        $secretKey = $momoService->getSecretKey() ?: 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa';
        $rawHash = "accessKey=" . $momoService->getAccessKey() . "&amount={$returnParams['amount']}&extraData={$returnParams['extraData']}&message={$returnParams['message']}&orderId={$returnParams['orderId']}&orderInfo={$returnParams['orderInfo']}&orderType={$returnParams['orderType']}&partnerCode={$returnParams['partnerCode']}&payType={$returnParams['payType']}&requestId={$returnParams['requestId']}&responseTime={$returnParams['responseTime']}&resultCode={$returnParams['resultCode']}&transId={$returnParams['transId']}";
        $returnParams['signature'] = hash_hmac('sha256', $rawHash, $secretKey);

        $this->get(route('site.momo.return', $returnParams));
        $this->assertFalse($order->fresh()->isPaid(), 'GET return không được phép chuyển trạng thái đơn sang paid.');

        // 2. IPN với amount sai lệch (ít hơn total_price) -> Không chuyển paid
        $badIpnParams = $returnParams;
        $badIpnParams['amount'] = 50000; // Đơn 100k nhưng gateway báo 50k
        $badRawHash = "accessKey=" . $momoService->getAccessKey() . "&amount={$badIpnParams['amount']}&extraData={$badIpnParams['extraData']}&message={$badIpnParams['message']}&orderId={$badIpnParams['orderId']}&orderInfo={$badIpnParams['orderInfo']}&orderType={$badIpnParams['orderType']}&partnerCode={$badIpnParams['partnerCode']}&payType={$badIpnParams['payType']}&requestId={$badIpnParams['requestId']}&responseTime={$badIpnParams['responseTime']}&resultCode={$badIpnParams['resultCode']}&transId={$badIpnParams['transId']}";
        $badIpnParams['signature'] = hash_hmac('sha256', $badRawHash, $secretKey);

        $ipnResponse = $this->postJson(route('site.momo.ipn'), $badIpnParams);
        $this->assertTrue(in_array($ipnResponse->status(), [400, 422]));
        $this->assertFalse($order->fresh()->isPaid());

        // 3. IPN đúng amount và chữ ký hợp lệ -> Chuyển paid
        $validIpnResponse = $this->postJson(route('site.momo.ipn'), $returnParams);
        $validIpnResponse->assertStatus(200);
        $this->assertTrue($order->fresh()->isPaid());

        // 4. Nếu ở môi trường production mà không có merchant secret key cấu hình -> Bị từ chối HTTP 403 (V4-P0-34)
        config(['payment.mode' => 'production']);
        $prodIpnResponse = $this->postJson(route('site.momo.ipn'), $returnParams);
        $this->assertEquals(403, $prodIpnResponse->status());
        config(['payment.mode' => 'demo']);
    }

    /**
     * Test V4-P1-37 & Scenario 4: Đồng bộ tồn kho giữa POS và QR (QR trừ 3, POS thêm 4, hủy 2 -> Tồn cuối = Tồn đầu - 5)
     */
    public function test_v4_p1_37_pos_and_qr_inventory_policy_synchronized(): void
    {
        $admin = User::firstOrCreate(['roles' => 'admin', 'username' => 'test_admin_pos_sync'], [
            'fullname' => 'Admin POS Sync',
            'phone' => '0981199887',
            'password' => bcrypt('password123'),
            'status' => 1,
            'roles' => 'admin',
        ]);

        $this->product->update(['qty' => 100, 'price' => 50000, 'price_buy' => 50000, 'price_sale' => 50000]);

        $table = Table::create([
            'floor_id' => 1,
            'name' => 'Bàn Test V4 Stock ' . uniqid(),
            'seats' => 4,
            'status' => 1,
        ]);

        // 1. Khách gọi món qua QR 3 phần -> Tồn kho giảm từ 100 xuống 97
        \App\Services\TableOrderService::addItemsToTable($table->id, [
            ['product_id' => $this->product->id, 'qty' => 3]
        ]);
        $this->assertEquals(97, $this->product->fresh()->qty);

        $activeOrder = \App\Services\TableOrderService::getActiveOrder($table->id);

        // 2. Nhân viên thêm 4 phần tại POS -> Tồn kho giảm từ 97 xuống 93
        $addPosResponse = $this->actingAs($admin)->postJson(route('admin.table-order.addProductToOrder'), [
            'order_id' => $activeOrder->id,
            'product_id' => $this->product->id,
            'qty' => 4,
        ]);
        $addPosResponse->assertStatus(200);
        $this->assertEquals(93, $this->product->fresh()->qty);

        $posDetail = Orderdetail::where('order_id', $activeOrder->id)->where('product_id', $this->product->id)->latest('id')->first();

        // 3. Nhân viên điều chỉnh giảm 2 phần (hủy 2 phần từ 7 xuống 5) -> Hoàn lại 2 tồn kho -> Tồn kho là 95
        $updateQtyResponse = $this->actingAs($admin)->postJson(route('admin.table-order.updateProductQty'), [
            'order_detail_id' => $posDetail->id,
            'qty' => $posDetail->qty - 2,
            'reason' => 'Khách hủy 2 phần',
        ]);
        $updateQtyResponse->assertStatus(200);

        // Tồn cuối = Tồn đầu (100) - 5 = 95
        $this->assertEquals(95, $this->product->fresh()->qty);
    }

    /**
     * Test V4-P1-38 & Scenario 5: Nhập 20, bán 18 (tồn 12), sửa phiếu từ 20 xuống 5 -> Bị từ chối, không clamp mất dấu vết
     */
    public function test_v4_p1_38_import_goods_rejects_reduction_when_stock_already_sold(): void
    {
        $admin = User::firstOrCreate(['roles' => 'admin', 'username' => 'test_admin_clamp_guard'], [
            'fullname' => 'Admin Clamp Guard',
            'phone' => '0981199112',
            'password' => bcrypt('password123'),
            'status' => 1,
            'roles' => 'admin',
        ]);

        // Tồn đầu = 10
        $this->product->update(['qty' => 10]);

        // Nhập 20 phần -> Tồn kho tăng lên 30
        $this->actingAs($admin)->post(route('admin.import-goods.store'), [
            'code' => 'PNK_CLAMP_' . uniqid(),
            'supplier_name' => 'Nhà Cung Cấp Test',
            'import_date' => now()->toDateString(),
            'items' => [
                [
                    'name' => $this->product->name,
                    'unit' => 'phần',
                    'quantity' => 20,
                    'price' => 30000,
                ]
            ]
        ]);
        $this->assertEquals(30, $this->product->fresh()->qty);

        $receipt = \App\Models\ImportReceipt::latest('id')->first();

        // Đã xuất bán 18 phần -> Tồn kho còn 12
        $this->product->update(['qty' => 12]);

        // Thử sửa phiếu từ 20 xuống 5 (yêu cầu giảm 15 phần, trong khi tồn chỉ còn 12 phần):
        // Hệ thống phải từ chối thao tác và giữ nguyên tồn kho 12 phần (tuyệt đối không cắt về 0 hay che giấu chênh lệch)
        $updateResponse = $this->actingAs($admin)->put(route('admin.import-goods.update', $receipt->id), [
            'code' => $receipt->code,
            'supplier_name' => 'Nhà Cung Cấp Test',
            'import_date' => now()->toDateString(),
            'items' => [
                [
                    'name' => $this->product->name,
                    'unit' => 'phần',
                    'quantity' => 5,
                    'price' => 30000,
                ]
            ]
        ]);

        $updateResponse->assertSessionHas('error');
        $this->assertEquals(12, $this->product->fresh()->qty, 'Tồn kho không được phép bị clamp ngầm làm mất số liệu.');
    }

    /**
     * Test V4-P1-39 & Scenario 6: Rate limit OTP theo số điện thoại qua Cache (chống spam từ nhiều sessions) và Single-use OTP
     */
    public function test_v4_p1_39_otp_throttling_across_sessions_and_single_use(): void
    {
        $testPhone = '0987654321';
        $user = User::firstOrCreate(['phone' => $testPhone], [
            'fullname' => 'Test User OTP',
            'username' => 'test_user_otp_' . uniqid(),
            'password' => bcrypt('password123'),
            'status' => 1,
            'roles' => 'customer',
        ]);

        \Illuminate\Support\Facades\Cache::forget('otp_request_throttle_' . $testPhone);

        // Gửi 5 yêu cầu từ các session khác nhau
        for ($i = 1; $i <= 5; $i++) {
            $this->flushSession();
            $resp = $this->post(route('site.send_otp'), ['phone' => $testPhone]);
            $resp->assertStatus(200);
        }

        // Yêu cầu thứ 6 từ session hoàn toàn mới phải bị rate limit (HTTP 429 hoặc redirect with error)
        $this->flushSession();
        $sixthResp = $this->from(route('site.forgot_password'))->post(route('site.send_otp'), ['phone' => $testPhone]);
        $this->assertTrue(in_array($sixthResp->status(), [429, 302]));
        if ($sixthResp->status() === 302) {
            $sixthResp->assertRedirect(route('site.forgot_password'));
            $sixthResp->assertSessionHas('error');
        }

        // Kiểm tra Single-use: Mã OTP sau khi xác thực thành công phải bị xóa ngay lập tức
        $cached = \Illuminate\Support\Facades\Cache::get('otp_' . $testPhone);
        $code = $cached ? $cached['code'] : $user->fresh()->otp_code;
        $this->assertNotNull($code);

        $verifyResp = $this->post(route('site.verify_otp'), [
            'phone' => $testPhone,
            'otp_code' => $code,
        ]);
        $verifyResp->assertRedirect(route('site.show_reset_password'));
        $this->assertNull(\Illuminate\Support\Facades\Cache::get('otp_' . $testPhone));
        $this->assertNull($user->fresh()->otp_code, 'Mã OTP phải bị hủy ngay sau khi xác thực thành công.');
    }

    /**
     * Test V2-20 & Scenario 7: Thông báo chung cho 2 nhân viên -> A đọc không làm mất unread của B
     */
    public function test_v2_20_and_scenario_7_shared_notification_read_status_is_per_user(): void
    {
        $employeeA = User::firstOrCreate(['username' => 'emp_a_test'], [
            'fullname' => 'Employee A',
            'phone' => '0911000001',
            'password' => bcrypt('123456'),
            'roles' => 'employee',
            'status' => 1,
        ]);

        $employeeB = User::firstOrCreate(['username' => 'emp_b_test'], [
            'fullname' => 'Employee B',
            'phone' => '0911000002',
            'password' => bcrypt('123456'),
            'roles' => 'employee',
            'status' => 1,
        ]);

        // Tạo thông báo chung cho bộ phận nhân viên (user_id = null, role = 'employee')
        $noti = \App\Models\Notification::create([
            'user_id' => null,
            'role' => 'employee',
            'type' => 'system',
            'title' => 'Thông báo họp giao ca chiều',
            'message' => 'Toàn bộ nhân viên chú ý họp lúc 14h',
            'is_read' => false,
        ]);

        // Ban đầu cả A và B đều thấy chưa đọc
        $this->assertFalse($noti->isReadBy($employeeA->id));
        $this->assertFalse($noti->isReadBy($employeeB->id));

        // Nhân viên A đánh dấu đã đọc qua API
        $this->actingAs($employeeA)->postJson("/notifications/{$noti->id}/read");

        // Nhân viên A thấy đã đọc
        $this->assertTrue($noti->fresh()->isReadBy($employeeA->id));

        // Nhân viên B VẪN THẤY CHƯA ĐỌC
        $this->assertFalse($noti->fresh()->isReadBy($employeeB->id));

        // Unread count trên feed của B vẫn còn ít nhất 1
        $feedB = $this->actingAs($employeeB)->getJson('/notifications/feed');
        $this->assertGreaterThanOrEqual(1, $feedB->json('unread_count'));
    }

    /**
     * Test V4-P2-45 & Scenario 12: Khi admin cập nhật giá trong lúc khách đang gọi thêm món -> Cập nhật đúng giá mới nhất
     */
    public function test_v4_p2_45_table_order_service_pending_item_syncs_latest_price(): void
    {
        $table = Table::create([
            'floor_id' => 1,
            'name' => 'Bàn Test V4 Price Sync ' . uniqid(),
            'seats' => 4,
            'status' => 1,
        ]);

        $this->product->update(['qty' => 50, 'price' => 50000, 'price_buy' => 50000, 'price_sale' => 50000]);

        // Khách gọi 1 phần giá 50.000đ (đang pending)
        \App\Services\TableOrderService::addItemsToTable($table->id, [
            ['product_id' => $this->product->id, 'qty' => 1]
        ]);

        $activeOrder = \App\Services\TableOrderService::getActiveOrder($table->id);
        $detail = Orderdetail::where('order_id', $activeOrder->id)->first();
        $this->assertEquals(50000, $detail->price);
        $this->assertEquals(1, $detail->qty);

        // Admin cập nhật giá món lên 75.000đ
        $this->product->update(['price' => 75000, 'price_sale' => 75000]);

        // Khách gọi thêm 1 phần cùng món đó khi dòng trước vẫn đang 'pending'
        \App\Services\TableOrderService::addItemsToTable($table->id, [
            ['product_id' => $this->product->id, 'qty' => 1]
        ]);

        $detailFresh = $detail->fresh();
        $this->assertEquals(2, $detailFresh->qty);
        // Giá được cập nhật đồng bộ lên 75.000đ và tổng tiền là 150.000đ
        $this->assertEquals(75000, $detailFresh->price);
        $this->assertEquals(150000, $detailFresh->amount);
    }
}
