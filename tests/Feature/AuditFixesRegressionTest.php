<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Floor;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Orderdetail;
use App\Models\Product;
use App\Models\ServiceCall;
use App\Models\Table;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\PricingService;
use App\Services\TableOrderService;
use Tests\TestCase;

class AuditFixesRegressionTest extends TestCase
{
    public function test_blog_page_and_product_detail_and_admin_order_detail_return_200(): void
    {
        $admin = User::where('username', 'system_test_admin')->firstOrFail();

        $product = Product::findOrFail(1);
        $product->update([
            'price_buy' => 100000,
            'price_sale' => 125000,
            'status' => 1,
        ]);

        // 1. /bai-viet renders without SQL error on product.price_buy/image
        $this->get(route('site.blog'))->assertStatus(200);

        // 2. /chi-tiet-san-pham/{slug} renders 200 and displays price_sale (125.000), not price_buy (100.000)
        $detailResponse = $this->get(route('site.product.detail', ['slug' => $product->slug]));
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('125.000');

        // 3. /admin/order/orders/{id} compiles and renders cleanly for delivery order (no Blade @endif error)
        $deliveryOrder = Order::create([
            'user_id' => User::where('username', 'system_test_customer')->firstOrFail()->id,
            'name' => 'Khách Giao Hàng Test',
            'phone' => '0911223344',
            'address' => 'Số 10 Đường Test',
            'total_price' => 150000,
            'status' => OrderStatus::PROCESSING,
            'orderStyle' => Order::STYLE_DELIVERY,
            'channel' => Order::CHANNEL_DELIVERY,
            'delivery_status' => Order::DELIVERY_WAITING,
            'payment_method' => 'COD',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.order.orderdetail', ['id' => $deliveryOrder->id]))
            ->assertStatus(200);
    }

    public function test_change_table_succeeds_with_existing_dishes_and_migrates_service_calls_and_token(): void
    {
        $admin = User::where('username', 'system_test_admin')->firstOrFail();
        $floor = Floor::findOrFail(1);
        $product = Product::findOrFail(1);

        $oldTable = Table::create([
            'floor_id' => $floor->id,
            'name' => 'Bàn Cũ ' . uniqid(),
            'seats' => 4,
            'sort_order' => 10,
            'status' => 1,
            'visit_token' => 'tok_old_' . uniqid(),
            'visit_started_at' => now(),
        ]);

        $newTable = Table::create([
            'floor_id' => $floor->id,
            'name' => 'Bàn Mới ' . uniqid(),
            'seats' => 6,
            'sort_order' => 11,
            'status' => 0,
        ]);

        $order = Order::create([
            'user_id' => User::where('username', 'system_test_customer')->firstOrFail()->id,
            'table_id' => $oldTable->id,
            'name' => 'Khách Đổi Bàn',
            'phone' => '0901234567',
            'address' => 'Tại bàn',
            'total_price' => 90000,
            'status' => OrderStatus::PENDING,
            'payment_status' => Order::PAYMENT_PENDING,
            'created_by' => $admin->id,
        ]);

        $oldTable->update(['locked_order_id' => $order->id]);
        $visitToken = $oldTable->visit_token;

        Orderdetail::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'qty' => 2,
            'price' => 50000,
            'discount' => 0,
            'amount' => 100000,
            'status' => 'confirmed',
        ]);

        $call = ServiceCall::create([
            'table_id' => $oldTable->id,
            'order_id' => $order->id,
            'customer_name' => 'Khách Đổi Bàn',
            'phone' => '0901234567',
            'call_type' => 'water',
            'call_type_label' => 'Xin thêm nước',
            'status' => ServiceCall::STATUS_PENDING,
        ]);

        $res = $this->actingAs($admin)->postJson(route('admin.table-order.changeTable'), [
            'order_id' => $order->id,
            'new_table_id' => $newTable->id,
        ]);

        $res->assertStatus(200)->assertJson(['success' => true]);

        $this->assertEquals($newTable->id, $order->fresh()->table_id);
        $this->assertEquals(0, $oldTable->fresh()->status);
        $this->assertNull($oldTable->fresh()->locked_order_id);
        $this->assertNull($oldTable->fresh()->visit_token);

        $this->assertEquals(1, $newTable->fresh()->status);
        $this->assertEquals($order->id, $newTable->fresh()->locked_order_id);
        $this->assertEquals($visitToken, $newTable->fresh()->visit_token);
        $this->assertEquals($newTable->id, $call->fresh()->table_id);
    }

    public function test_get_table_dishes_excludes_cancelled_and_available_tables_allows_empty_status_zero(): void
    {
        $admin = User::where('username', 'system_test_admin')->firstOrFail();
        $floor = Floor::findOrFail(1);
        $product = Product::findOrFail(1);

        $emptyTable = Table::create([
            'floor_id' => $floor->id,
            'name' => 'Bàn Trống Status0 ' . uniqid(),
            'seats' => 4,
            'sort_order' => 20,
            'status' => 0,
        ]);

        $disabledTable = Table::create([
            'floor_id' => $floor->id,
            'name' => 'Bàn Bảo Trì Status2 ' . uniqid(),
            'seats' => 4,
            'sort_order' => 21,
            'status' => 2,
        ]);

        $availRes = $this->getJson(route('site.cart.availableTables'));
        $availRes->assertStatus(200)->assertJson(['success' => true]);
        $tables = collect($availRes->json('floors'))->flatMap(fn ($f) => $f['tables']);

        $emptyEntry = $tables->firstWhere('id', $emptyTable->id);
        $disabledEntry = $tables->firstWhere('id', $disabledTable->id);
        $this->assertTrue($emptyEntry['is_available']);
        $this->assertFalse($emptyEntry['is_disabled']);
        $this->assertFalse($disabledEntry['is_available']);
        $this->assertTrue($disabledEntry['is_disabled']);

        // Test getTableDishes skips cancelled items & maps 'served'
        $occupiedTable = Table::create([
            'floor_id' => $floor->id,
            'name' => 'Bàn Có Món ' . uniqid(),
            'seats' => 4,
            'sort_order' => 22,
            'status' => 1,
        ]);

        $order = Order::create([
            'user_id' => User::where('username', 'system_test_customer')->firstOrFail()->id,
            'table_id' => $occupiedTable->id,
            'name' => 'Khách Bàn Món',
            'phone' => '0909998887',
            'address' => 'Tại bàn',
            'total_price' => 90000,
            'status' => OrderStatus::PENDING,
            'created_by' => $admin->id,
        ]);

        Orderdetail::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'qty' => 2,
            'price' => 50000,
            'discount' => 0,
            'amount' => 100000,
            'status' => 'served',
        ]);

        Orderdetail::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'qty' => 1,
            'price' => 50000,
            'discount' => 0,
            'amount' => 0,
            'status' => 'cancelled',
        ]);

        $dishesRes = $this->actingAs($admin)->getJson(route('admin.table.dishes', ['id' => $occupiedTable->id]));
        $dishesRes->assertStatus(200)->assertJson(['success' => true]);
        $dishes = $dishesRes->json('dishes');
        $this->assertCount(1, $dishes);
        $this->assertEquals('Đã lên bàn', $dishes[0]['status_text']);
    }

    public function test_pos_process_payment_and_inventory_reconcile_use_consistent_rules(): void
    {
        $admin = User::where('username', 'system_test_admin')->firstOrFail();
        $product = Product::findOrFail(1);
        $product->update(['qty' => 50, 'price_buy' => 60000, 'price_sale' => 100000, 'status' => 1]);

        $table = Table::create([
            'floor_id' => 1,
            'name' => 'Bàn QR Ledger ' . uniqid(),
            'seats' => 4,
            'sort_order' => 30,
            'status' => 0,
        ]);

        $order = TableOrderService::addItemsToTable($table->id, [
            ['product_id' => $product->id, 'qty' => 2],
        ]);

        // TableOrderService logs InventoryMovement and applies 10% dine-in discount (2 * 100k - 10% = 180k)
        $reconcile = InventoryService::reconcile($product->id);
        $this->assertEquals(48, $reconcile['current_quantity']);
        $this->assertGreaterThan(0, InventoryMovement::where('order_id', $order->id)->count());
        $this->assertEquals(180000, (int) $order->fresh()->total_price);

        // Ensure POS payment screen renders HTTP 200 cleanly (VietQRService resolved via container)
        $this->actingAs($admin)
            ->get(route('admin.table-order.payment', ['id' => $table->id]))
            ->assertStatus(200);

        // POS processPayment also enforces PricingService 10% dine-in discount
        $payRes = $this->actingAs($admin)->postJson(route('admin.table-order.processPayment'), [
            'order_id' => $order->id,
            'payment_method' => '1',
            'total_price' => 180000,
        ]);
        $payRes->assertStatus(200)->assertJson(['success' => true]);
        $this->assertEquals(180000, (int) $order->fresh()->total_price);
        $this->assertEquals(PricingService::getShippingSettings()['max_km'], 15);
    }
}
