<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Order;
use App\Models\Orderdetail;
use App\Models\Product;
use App\Models\Table;
use App\Models\Floor;
use App\Enums\OrderStatus;
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
}
