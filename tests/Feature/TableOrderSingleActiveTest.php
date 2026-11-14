<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Order;
use App\Models\Orderdetail;
use App\Models\Product;
use App\Models\Table;
use App\Models\Floor;
use App\Enums\OrderStatus;
use App\Models\User;
use App\Models\PaymentTransaction;
use App\Models\OrderDeliveryLog;
use App\Services\TableOrderService;

class TableOrderSingleActiveTest extends TestCase
{
    protected Table $table;
    protected Product $productA;
    protected Product $productB;

    protected function setUp(): void
    {
        parent::setUp();

        $floor = Floor::firstOrCreate(['id' => 99], [
            'name' => 'Tầng Kiểm Thử',
            'status' => 1,
        ]);

        $this->table = Table::firstOrCreate(['id' => 999], [
            'floor_id' => $floor->id,
            'name' => 'Bàn Test 999',
            'status' => 0,
            'seats' => 4,
        ]);

        $this->productA = Product::first();
        if (!$this->productA) {
            $this->productA = new Product();
            $this->productA->category_id = 1;
            $this->productA->brand_id = 1;
            $this->productA->name = 'Món Test A';
            $this->productA->slug = 'mon-test-a';
            $this->productA->content = 'Content';
            $this->productA->description = 'Desc';
            $this->productA->price_buy = 40000;
            $this->productA->price_sale = 50000;
            $this->productA->qty = 100;
            $this->productA->thumbnail = 'thumb.png';
            $this->productA->created_by = 1;
            $this->productA->new_p = 1;
            $this->productA->status = 1;
            $this->productA->save();
        }

        $this->productB = Product::where('id', '!=', $this->productA->id)->first();
        if (!$this->productB) {
            $this->productB = new Product();
            $this->productB->category_id = 1;
            $this->productB->brand_id = 1;
            $this->productB->name = 'Món Test B';
            $this->productB->slug = 'mon-test-b';
            $this->productB->content = 'Content';
            $this->productB->description = 'Desc';
            $this->productB->price_buy = 60000;
            $this->productB->price_sale = 75000;
            $this->productB->qty = 100;
            $this->productB->thumbnail = 'thumb.png';
            $this->productB->created_by = 1;
            $this->productB->new_p = 1;
            $this->productB->status = 1;
            $this->productB->save();
        }

        // Xóa sạch các order thử nghiệm cũ của bàn này
        Orderdetail::whereIn('order_id', Order::where('table_id', $this->table->id)->pluck('id'))->delete();
        Order::where('table_id', $this->table->id)->delete();
    }

    protected function createTableOrder(int $status, float $totalPrice = 100000): Order
    {
        return Order::create([
            'user_id' => 1,
            'table_id' => $this->table->id,
            'name' => $this->table->name,
            'phone' => '0912345678',
            'email' => 'testtable@example.com',
            'address' => $this->table->name,
            'channel' => Order::CHANNEL_DINE_IN,
            'orderStyle' => 0,
            'status' => $status,
            'payment_status' => Order::PAYMENT_PENDING,
            'total_price' => $totalPrice,
            'created_by' => 1,
        ]);
    }

    /**
     * Test 1: Order::activeForTable chỉ trả về các đơn PENDING hoặc PROCESSING
     */
    public function test_order_scope_active_for_table_filters_properly(): void
    {
        $orderPending = $this->createTableOrder(OrderStatus::PENDING, 100000);
        $orderProcessing = $this->createTableOrder(OrderStatus::PROCESSING, 150000);
        $orderCompleted = $this->createTableOrder(OrderStatus::COMPLETED, 200000);
        $orderCancelled = $this->createTableOrder(OrderStatus::CANCELLED, 50000);

        $activeOrders = Order::activeForTable($this->table->id)->pluck('id')->toArray();

        $this->assertContains($orderPending->id, $activeOrders);
        $this->assertContains($orderProcessing->id, $activeOrders);
        $this->assertNotContains($orderCompleted->id, $activeOrders);
        $this->assertNotContains($orderCancelled->id, $activeOrders);

        // findActiveForTable phải trả về đơn active mới nhất
        $latestActive = Order::findActiveForTable($this->table->id);
        $this->assertNotNull($latestActive);
        $this->assertEquals($orderProcessing->id, $latestActive->id);
    }

    /**
     * Test 2: TableOrderService::getOrCreateActiveOrder trả về duy nhất 1 đơn
     */
    public function test_table_order_service_get_or_create_ensures_single_order(): void
    {
        $order1 = TableOrderService::getOrCreateActiveOrder($this->table->id);
        $this->assertNotNull($order1);
        $this->assertEquals(OrderStatus::PENDING, (int)$order1->status);
        $this->assertEquals(Order::CHANNEL_DINE_IN, $order1->channel);

        $order2 = TableOrderService::getOrCreateActiveOrder($this->table->id);
        $this->assertEquals($order1->id, $order2->id);

        $activeCount = Order::activeForTable($this->table->id)->count();
        $this->assertEquals(1, $activeCount);
    }

    /**
     * Test 3: Thêm món vào bàn tự động gộp số lượng và cập nhật tổng tiền
     */
    public function test_table_order_service_add_items_merges_quantities(): void
    {
        // Lần 1: Gọi 2 món A
        $order = TableOrderService::addItemsToTableOrder($this->table->id, [
            ['product_id' => $this->productA->id, 'qty' => 2, 'price' => 50000],
        ]);

        $this->assertEquals(1, $order->orderDetails()->count());
        $this->assertEquals(2, $order->orderDetails()->first()->qty);

        // Lần 2: Gọi thêm 3 món A và 1 món B
        $order = TableOrderService::addItemsToTableOrder($this->table->id, [
            ['product_id' => $this->productA->id, 'qty' => 3, 'price' => 50000],
            ['product_id' => $this->productB->id, 'qty' => 1, 'price' => 75000],
        ]);

        $this->assertEquals(2, $order->orderDetails()->count());

        $detailA = $order->orderDetails()->where('product_id', $this->productA->id)->first();
        $this->assertEquals(5, $detailA->qty); // 2 + 3 = 5

        $detailB = $order->orderDetails()->where('product_id', $this->productB->id)->first();
        $this->assertEquals(1, $detailB->qty);

        $this->assertTrue((bool)$order->has_new_items);
    }

    /**
     * Test 4: Lệnh orders:merge-duplicate-table-orders ở chế độ Dry-run (không apply)
     */
    public function test_artisan_merge_command_dry_run(): void
    {
        // Tạo 2 đơn active cùng bàn (mô phỏng dữ liệu cũ bị tách đôi)
        $order1 = $this->createTableOrder(OrderStatus::PENDING, 50000);
        Orderdetail::create([
            'order_id' => $order1->id,
            'product_id' => $this->productA->id,
            'qty' => 1,
            'price' => 50000,
            'discount' => 0,
            'amount' => 50000,
        ]);

        $order2 = $this->createTableOrder(OrderStatus::PENDING, 75000);
        Orderdetail::create([
            'order_id' => $order2->id,
            'product_id' => $this->productB->id,
            'qty' => 1,
            'price' => 75000,
            'discount' => 0,
            'amount' => 75000,
        ]);

        $this->assertEquals(2, Order::activeForTable($this->table->id)->count());

        // Chạy lệnh dry-run
        $this->artisan('orders:merge-duplicate-table-orders')
            ->expectsOutputToContain('Chế độ xem trước (Dry-run)')
            ->expectsOutputToContain('Cần gộp')
            ->assertExitCode(0);

        // Kiểm tra dữ liệu chưa bị thay đổi
        $this->assertEquals(2, Order::activeForTable($this->table->id)->count());
        $this->assertEquals(OrderStatus::PENDING, (int)$order2->fresh()->status);
    }

    /**
     * Test 5: Lệnh orders:merge-duplicate-table-orders với cờ --apply thực hiện gộp
     */
    public function test_artisan_merge_command_apply(): void
    {
        $order1 = $this->createTableOrder(OrderStatus::PENDING, 50000);
        Orderdetail::create([
            'order_id' => $order1->id,
            'product_id' => $this->productA->id,
            'qty' => 2,
            'price' => 50000,
            'discount' => 0,
            'amount' => 100000,
        ]);

        $order2 = $this->createTableOrder(OrderStatus::PENDING, 75000);
        Orderdetail::create([
            'order_id' => $order2->id,
            'product_id' => $this->productA->id,
            'qty' => 1,
            'price' => 50000,
            'discount' => 0,
            'amount' => 50000,
        ]);

        $this->assertEquals(2, Order::activeForTable($this->table->id)->count());

        // Chạy lệnh có apply
        $this->artisan('orders:merge-duplicate-table-orders', ['--apply' => true])
            ->expectsOutputToContain('Bắt đầu gộp các đơn hàng trùng lặp')
            ->expectsOutputToContain('Đã gộp')
            ->assertExitCode(0);

        // Sau khi gộp: Bàn chỉ còn duy nhất 1 đơn active
        $this->assertEquals(1, Order::activeForTable($this->table->id)->count());

        // Đơn 2 đã bị hủy và có ghi chú gộp
        $freshOrder2 = $order2->fresh();
        $this->assertEquals(OrderStatus::CANCELLED, (int)$freshOrder2->status);
        $this->assertStringContainsString("Đã gộp vào đơn hàng #{$order1->id}", (string)$freshOrder2->delivery_note);

        // Món ăn của đơn 1 đã được gộp số lượng: 2 + 1 = 3
        $freshDetail = Orderdetail::where('order_id', $order1->id)->where('product_id', $this->productA->id)->first();
        $this->assertNotNull($freshDetail);
        $this->assertEquals(3, $freshDetail->qty);
    }

    protected function getAdminUser(): User
    {
        $admin = User::where('roles', 'admin')->where('status', 1)->first();
        if (!$admin) {
            $admin = User::create([
                'username' => 'test_admin_single_' . uniqid(),
                'fullname' => 'Test Admin Single',
                'password' => bcrypt('123456'),
                'phone' => '0988001001',
                'email' => 'adminsingle@example.com',
                'gender' => 'Nam',
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
                'username' => 'test_emp_single_' . uniqid(),
                'fullname' => 'Test Emp Single',
                'password' => bcrypt('123456'),
                'phone' => '0988002002',
                'email' => 'empsingle@example.com',
                'gender' => 'Nam',
                'roles' => 'employee',
                'admin_lever' => 0,
                'status' => 1,
            ]);
        }
        return $employee;
    }

    protected function getCustomerUser(): User
    {
        $customer = User::where('roles', 'customer')->where('status', 1)->first();
        if (!$customer) {
            $customer = User::create([
                'username' => 'test_cust_single_' . uniqid(),
                'fullname' => 'Test Cust Single',
                'password' => bcrypt('123456'),
                'phone' => '0988003003',
                'email' => 'custsingle@example.com',
                'gender' => 'Nam',
                'roles' => 'customer',
                'admin_lever' => 0,
                'status' => 1,
            ]);
        }
        return $customer;
    }

    /**
     * Test 6: sendToKitchenAjax khi session khong co table_order_id tu dong gop vao order duy nhat cua ban
     */
    public function test_send_to_kitchen_ajax_without_table_order_id_session_reuses_single_active_order(): void
    {
        // 1. Tao don active san cho ban
        $existingOrder = $this->createTableOrder(OrderStatus::PENDING, 50000);
        Orderdetail::create([
            'order_id' => $existingOrder->id,
            'product_id' => $this->productA->id,
            'qty' => 1,
            'price' => 50000,
            'discount' => 0,
            'amount' => 50000,
        ]);

        $customer = $this->getCustomerUser();

        // 2. Client gui AJAX ma session KHONG co table_order_id
        $cartKey = 'cart_' . $customer->id;
        $cartData = [
            $this->productA->id => [
                'id' => $this->productA->id,
                'name' => $this->productA->name,
                'price' => 50000,
                'qty' => 2,
            ],
            $this->productB->id => [
                'id' => $this->productB->id,
                'name' => $this->productB->name,
                'price' => 75000,
                'qty' => 1,
            ],
        ];

        $response = $this->actingAs($customer)->withSession([
            'table_id' => $this->table->id,
            $cartKey => $cartData,
        ])->postJson(route('site.cart.sendKitchenAjax'), [
            'table_id' => $this->table->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // 3. Khang dinh: Ban van chi co DUY NHAT 1 don active, khong bi sinh don trung lap
        $this->assertEquals(1, Order::activeForTable($this->table->id)->count());

        // 4. So luong mon A duoc cong don (1 + 2 = 3), mon B duoc them moi (1)
        $detailA = Orderdetail::where('order_id', $existingOrder->id)->where('product_id', $this->productA->id)->first();
        $this->assertNotNull($detailA);
        $this->assertEquals(3, $detailA->qty);

        $detailB = Orderdetail::where('order_id', $existingOrder->id)->where('product_id', $this->productB->id)->first();
        $this->assertNotNull($detailB);
        $this->assertEquals(1, $detailB->qty);
    }

    /**
     * Test 7: payTableOrderNowAjax khi session khong co table_order_id tu dong gop vao order duy nhat cua ban
     */
    public function test_pay_table_now_ajax_without_table_order_id_session_uses_single_active_order(): void
    {
        $existingOrder = $this->createTableOrder(OrderStatus::PENDING, 50000);

        $customer = $this->getCustomerUser();

        $cartKey = 'cart_' . $customer->id;
        $cartData = [
            $this->productA->id => [
                'id' => $this->productA->id,
                'name' => $this->productA->name,
                'price' => 50000,
                'qty' => 2,
            ],
        ];

        $response = $this->actingAs($customer)->withSession([
            'table_id' => $this->table->id,
            $cartKey => $cartData,
        ])->postJson(route('site.cart.payTableNowAjax'), [
            'table_id' => $this->table->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertStringContainsString('thanh-toan-qr', (string)$response->json('redirect_url'));

        // Ban van chi co 1 don duy nhat
        $this->assertEquals(1, Order::activeForTable($this->table->id)->count());
    }

    /**
     * Test 8: Endpoint handover ban giao mon cho don Takeaway hoan tat don va ghi nhan doanh thu
     */
    public function test_handover_endpoint_for_takeaway_order_completes_order_and_sets_revenue(): void
    {
        $employee = $this->getEmployeeUser();

        $takeawayOrder = Order::create([
            'user_id' => $employee->id,
            'name' => 'Khach Mang Ve',
            'phone' => '0912345678',
            'email' => 'takeaway@example.com',
            'address' => 'Mang ve',
            'channel' => Order::CHANNEL_TAKEAWAY,
            'orderStyle' => Order::STYLE_TAKEAWAY,
            'status' => OrderStatus::PROCESSING,
            'payment_status' => Order::PAYMENT_PAID,
            'paid_at' => now(),
            'total_price' => 120000,
            'created_by' => $employee->id,
        ]);

        $this->assertNull($takeawayOrder->revenue_recognized_at);

        $response = $this->actingAs($employee)->postJson(route('admin.order.handover', $takeawayOrder->id), [
            'note' => 'Da gui tui giu nhiet',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $fresh = $takeawayOrder->fresh();
        $this->assertEquals(OrderStatus::COMPLETED, (int)$fresh->status);
        $this->assertNotNull($fresh->revenue_recognized_at);
        $this->assertStringContainsString('Đã bàn giao món cho khách: Da gui tui giu nhiet', (string)$fresh->delivery_note);

        // Kiem tra audit log
        $log = OrderDeliveryLog::where('order_id', $takeawayOrder->id)->latest('id')->first();
        $this->assertNotNull($log);
        $this->assertEquals($employee->id, $log->user_id);
        $this->assertEquals(OrderStatus::COMPLETED, (int)$log->to_order_status);

        // Don nay duoc tinh trong doanh thu ghi nhan
        $this->assertTrue(Order::revenueRecognized()->where('id', $takeawayOrder->id)->exists());
    }

    /**
     * Test 9: Chan updateDelivery doi voi cac don khong phai giao hang (Dine-in / Takeaway / POS)
     */
    public function test_update_delivery_rejected_for_non_delivery_orders(): void
    {
        $employee = $this->getEmployeeUser();

        // Don an tai ban (Dine-in)
        $dineInOrder = $this->createTableOrder(OrderStatus::PROCESSING, 150000);

        $response = $this->actingAs($employee)->postJson(route('admin.order.updateDelivery', $dineInOrder->id), [
            'shipper_name' => 'Shipper Test',
            'shipper_phone' => '0912345678',
            'delivery_status' => 'delivering',
        ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
        $this->assertStringContainsString('chỉ áp dụng cho đơn giao hàng', (string)$response->json('message'));
    }

    /**
     * Test 10: Admin huy don hang da thanh toan kich hoat hoan tien va phuc hoi ton kho san pham
     */
    public function test_admin_cancelling_paid_order_calls_mark_as_refunded_and_restores_stock(): void
    {
        $admin = $this->getAdminUser();

        $initialStock = 50;
        $this->productA->qty = $initialStock;
        $this->productA->save();

        $paidOrder = Order::create([
            'user_id' => $admin->id,
            'name' => 'Khach Hang Da Tra Tien',
            'phone' => '0911223344',
            'email' => 'paidcancel@example.com',
            'address' => '789 Duong Hoa',
            'channel' => Order::CHANNEL_DELIVERY,
            'orderStyle' => Order::STYLE_DELIVERY,
            'status' => OrderStatus::PROCESSING,
            'payment_status' => Order::PAYMENT_PAID,
            'paid_at' => now(),
            'total_price' => 100000,
            'created_by' => $admin->id,
        ]);

        Orderdetail::create([
            'order_id' => $paidOrder->id,
            'product_id' => $this->productA->id,
            'qty' => 3,
            'price' => 50000,
            'discount' => 0,
            'amount' => 150000,
        ]);

        // Admin goi cap nhat status = 3 (CANCELLED) kem ly do
        $response = $this->actingAs($admin)->postJson(route('admin.order.update', $paidOrder->id), [
            'status' => OrderStatus::CANCELLED,
            'reason' => 'Khach ban viec dot xuat xin huy don',
        ]);

        $response->assertStatus(200);

        $fresh = $paidOrder->fresh();
        $this->assertEquals(OrderStatus::CANCELLED, (int)$fresh->status);
        $this->assertEquals(Order::PAYMENT_REFUNDED, $fresh->payment_status);
        $this->assertStringContainsString('Hoàn tiền', (string)$fresh->delivery_note);

        // Ton kho duoc phuc hoi: 50 + 3 = 53
        $this->assertEquals($initialStock + 3, $this->productA->fresh()->qty);

        // Co ban ghi PaymentTransaction cho giao dich hoan tien
        $refundTx = PaymentTransaction::where('order_id', $paidOrder->id)
            ->where('status', 'refunded')
            ->first();
        $this->assertNotNull($refundTx);
        $this->assertEquals((int)$paidOrder->total_price, (int)$refundTx->amount);

        // Don nay KHONG nam trong doanh thu ghi nhan va thuc thu rong
        $this->assertFalse(Order::revenueRecognized()->where('id', $paidOrder->id)->exists());
        $this->assertFalse(Order::netCollected()->where('id', $paidOrder->id)->exists());
    }
}
