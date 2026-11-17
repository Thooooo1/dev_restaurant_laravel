<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Order;
use App\Models\Orderdetail;
use App\Models\Product;
use App\Models\Table;
use App\Models\Floor;
use App\Models\User;
use App\Models\PaymentIntent;
use App\Models\InventoryMovement;
use App\Services\TableAccessService;
use App\Services\InventoryService;
use App\Services\PaymentIntentService;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CivilizeStabilizationTest extends TestCase
{
    use DatabaseTransactions;

    protected Table $table;
    protected Product $productA;
    protected Product $productB;
    protected User $staffUser;
    protected User $customerUser;

    protected function setUp(): void
    {
        parent::setUp();

        $floor = Floor::firstOrCreate(['id' => 88], [
            'name' => 'Tầng Test Stabilization',
            'status' => 1,
        ]);

        $this->table = Table::create([
            'floor_id' => $floor->id,
            'name' => 'Bàn Test 888',
            'status' => 1,
            'seats' => 4,
            'visit_token' => 'test_visit_token_' . uniqid(),
            'visit_started_at' => now(),
        ]);

        $this->productA = Product::create([
            'category_id' => 1,
            'brand_id' => 1,
            'name' => 'Món Test Stab A',
            'slug' => 'mon-test-stab-a-' . uniqid(),
            'content' => 'Content',
            'description' => 'Desc',
            'price_buy' => 40000,
            'price_sale' => 50000,
            'qty' => 10,
            'status' => 1,
        ]);

        $this->productB = Product::create([
            'category_id' => 1,
            'brand_id' => 1,
            'name' => 'Món Test Stab B',
            'slug' => 'mon-test-stab-b-' . uniqid(),
            'content' => 'Content',
            'description' => 'Desc',
            'price_buy' => 30000,
            'price_sale' => 35000,
            'qty' => 2,
            'status' => 1,
        ]);

        $this->staffUser = User::create([
            'fullname' => 'Staff Test',
            'username' => 'staff_stab_' . uniqid(),
            'email' => 'staff_stab_' . uniqid() . '@civilize.vn',
            'password' => bcrypt('password123'),
            'phone' => '0988776655',
            'roles' => 'admin',
            'admin_lever' => 1,
            'status' => 1,
        ]);

        $this->customerUser = User::create([
            'fullname' => 'Khách Test',
            'username' => 'cust_stab_' . uniqid(),
            'email' => 'cust_stab_' . uniqid() . '@gmail.com',
            'password' => bcrypt('password123'),
            'phone' => '0912345678',
            'roles' => 'customer',
            'admin_lever' => null,
            'status' => 1,
        ]);
    }

    /**
     * Test 1: Quét QR không có token hợp lệ không cấp session table_verified
     */
    public function test_negative_qr_scan_without_token_does_not_grant_verified_session()
    {
        $response = $this->get(route('site.table.scan', $this->table->id));
        $response->assertStatus(200);
        $response->assertSessionMissing('table_verified_' . $this->table->id);
    }

    /**
     * Test 2: Quét QR với đúng token được cấp session table_verified và visit_token
     */
    public function test_positive_qr_scan_with_valid_token_grants_verified_session()
    {
        $token = $this->table->getOrCreateQrToken();

        $response = $this->get(route('site.table.scan', ['id' => $this->table->id, 'token' => $token]));
        $response->assertStatus(200);
        $response->assertSessionHas('table_verified_' . $this->table->id, true);
    }

    /**
     * Test 3: Khách cũ cầm visit_token cũ không được phép truy cập lượt khách mới sau khi bàn đã đóng lượt
     */
    public function test_old_visit_token_cannot_access_new_visit_after_table_closed()
    {
        $oldVisitToken = $this->table->visit_token;

        // Giả lập khách cũ đã có session gắn với visit cũ
        $this->withSession([
            'table_id' => $this->table->id,
            'table_verified_' . $this->table->id => true,
            'table_visit_token_' . $this->table->id => $oldVisitToken,
        ]);

        // Đóng bàn và mở lượt khách mới
        $this->table->closeVisit();
        $this->table->startNewVisit();
        $this->assertNotEquals($oldVisitToken, $this->table->visit_token);

        // Khách cũ gọi TableAccessService::authorizeTableAccess -> phải trả về false
        $authorized = TableAccessService::authorizeTableAccess($this->table);
        $this->assertFalse($authorized);
    }

    /**
     * Test 4: Gọi nhân viên phục vụ bắt buộc phải có QR token hoặc verified session
     */
    public function test_service_call_requires_qr_or_verified_session()
    {
        // Thử gửi yêu cầu khi chưa quét QR -> 403
        $res = $this->postJson(route('site.service-call.store'), [
            'table_id' => $this->table->id,
            'call_type' => 'call_waiter',
        ]);
        $res->assertStatus(403);

        // Gửi kèm QR token hợp lệ -> 200
        $token = $this->table->getOrCreateQrToken();
        $resSuccess = $this->postJson(route('site.service-call.store'), [
            'table_id' => $this->table->id,
            'token' => $token,
            'call_type' => 'call_waiter',
        ]);
        $resSuccess->assertStatus(200);
    }

    /**
     * Test 5: Gửi món vào bếp (sendToKitchenAjax) bắt buộc phải có verified session
     */
    public function test_send_to_kitchen_requires_qr_or_verified_session()
    {
        $this->actingAs($this->customerUser);
        $cartKey = 'cart_' . $this->customerUser->id;
        $cartData = [
            $this->productA->id => [
                'id' => $this->productA->id,
                'name' => $this->productA->name,
                'price' => 50000,
                'qty' => 1,
                'image' => 'sample.jpg',
            ]
        ];

        $this->withSession([
            $cartKey => $cartData,
            'table_id' => $this->table->id,
            // Không có 'table_verified_' . $this->table->id
        ]);

        $res = $this->postJson(route('site.cart.sendKitchenAjax'), [
            'table_id' => $this->table->id,
        ]);
        $res->assertStatus(403);

        // Có session verified
        $this->withSession([
            $cartKey => $cartData,
            'table_id' => $this->table->id,
            'table_verified_' . $this->table->id => true,
            'table_visit_token_' . $this->table->id => $this->table->visit_token,
        ]);
        $resSuccess = $this->postJson(route('site.cart.sendKitchenAjax'), [
            'table_id' => $this->table->id,
        ]);
        $resSuccess->assertStatus(200);
    }

    /**
     * Test 6: Xác nhận thanh toán bắt buộc phải có verified session
     */
    public function test_payment_confirmation_requires_qr_or_verified_session()
    {
        $order = Order::create([
            'user_id' => 1,
            'name' => 'Khách Test',
            'phone' => '0912345678',
            'address' => 'Tại quán',
            'table_id' => $this->table->id,
            'status' => 0,
            'payment_status' => Order::PAYMENT_PENDING,
            'total_price' => 50000,
            'orderStyle' => 0,
            'created_by' => 1,
        ]);

        // Request không có session verified -> 403
        $res = $this->postJson(route('site.table.payment.confirm', $this->table->id), [
            'order_id' => $order->id,
            'payment_method' => 'TaiBan',
        ]);
        $res->assertStatus(403);

        // Có verified session -> được phép xử lý
        $this->withSession([
            'table_id' => $this->table->id,
            'table_verified_' . $this->table->id => true,
            'table_visit_token_' . $this->table->id => $this->table->visit_token,
        ]);
        $resSuccess = $this->postJson(route('site.table.payment.confirm', $this->table->id), [
            'order_id' => $order->id,
            'payment_method' => 'TaiBan',
        ]);
        $resSuccess->assertStatus(200);
    }

    /**
     * Test 7: Hủy món 'cancelled' lần hai trả về 422 Unprocessable Entity
     */
    public function test_double_cancellation_of_order_detail_returns_422()
    {
        $order = Order::create([
            'user_id' => 1,
            'name' => 'Khách Test',
            'phone' => '0912345678',
            'address' => 'Tại quán',
            'table_id' => $this->table->id,
            'status' => 1,
            'payment_status' => Order::PAYMENT_PENDING,
            'total_price' => 50000,
            'created_by' => 1,
        ]);

        $detail = Orderdetail::create([
            'order_id' => $order->id,
            'product_id' => $this->productA->id,
            'qty' => 1,
            'price' => 50000,
            'discount' => 0,
            'amount' => 50000,
            'status' => 'confirmed',
        ]);

        $this->actingAs($this->staffUser);

        // Hủy lần 1 -> Thành công
        $res1 = $this->postJson(route('admin.table-order.removeProductFromOrder'), [
            'order_detail_id' => $detail->id,
            'reason' => 'Khách đổi ý',
        ]);
        $res1->assertStatus(200);
        $this->assertEquals('cancelled', $detail->fresh()->status);

        // Hủy lần 2 -> 422
        $res2 = $this->postJson(route('admin.table-order.removeProductFromOrder'), [
            'order_detail_id' => $detail->id,
            'reason' => 'Hủy lần hai',
        ]);
        $res2->assertStatus(422);
    }

    /**
     * Test 8: Cập nhật số lượng của món đã hủy (cancelled) trả về 422
     */
    public function test_updating_quantity_of_cancelled_item_returns_422()
    {
        $order = Order::create([
            'user_id' => 1,
            'name' => 'Khách Test',
            'phone' => '0912345678',
            'address' => 'Tại quán',
            'table_id' => $this->table->id,
            'status' => 1,
            'payment_status' => Order::PAYMENT_PENDING,
            'total_price' => 50000,
            'created_by' => 1,
        ]);

        $detail = Orderdetail::create([
            'order_id' => $order->id,
            'product_id' => $this->productA->id,
            'qty' => 1,
            'price' => 50000,
            'discount' => 0,
            'amount' => 50000,
            'status' => 'cancelled',
        ]);

        $this->actingAs($this->staffUser);

        $res = $this->postJson(route('admin.table-order.updateProductQty'), [
            'order_detail_id' => $detail->id,
            'qty' => 2,
        ]);
        $res->assertStatus(422);
    }

    /**
     * Test 9: POS Batch update quantities rollback toàn bộ khi có 1 món thiếu hàng
     */
    public function test_pos_batch_update_rolls_back_atomically_if_any_product_out_of_stock()
    {
        $order = Order::create([
            'user_id' => 1,
            'name' => 'Khách Test',
            'phone' => '0912345678',
            'address' => 'Tại quán',
            'table_id' => $this->table->id,
            'status' => 1,
            'payment_status' => Order::PAYMENT_PENDING,
            'total_price' => 85000,
            'created_by' => 1,
        ]);

        // Detail 1: Món A (còn 10 trong kho)
        $detail1 = Orderdetail::create([
            'order_id' => $order->id,
            'product_id' => $this->productA->id,
            'qty' => 1,
            'price' => 50000,
            'amount' => 50000,
            'status' => 'confirmed',
        ]);

        // Detail 2: Món B (chỉ còn 2 trong kho)
        $detail2 = Orderdetail::create([
            'order_id' => $order->id,
            'product_id' => $this->productB->id,
            'qty' => 1,
            'price' => 35000,
            'amount' => 35000,
            'status' => 'confirmed',
        ]);

        $initialStockA = $this->productA->fresh()->qty;
        $initialStockB = $this->productB->fresh()->qty;

        $this->actingAs($this->staffUser);

        // Gửi batch: Detail 1 tăng lên 2 (hợp lệ), Detail 2 tăng lên 10 (vượt quá tồn kho 2)
        $res = $this->postJson(route('admin.table-order.updateTableOrderQuantities'), [
            'order_id' => $order->id,
            'items' => [
                ['order_detail_id' => $detail1->id, 'qty' => 2],
                ['order_detail_id' => $detail2->id, 'qty' => 10],
            ]
        ]);

        $res->assertStatus(422);

        // Kiểm tra tính nguyên tử (Atomic Rollback): Tồn kho món A và món B đều không bị trừ
        $this->assertEquals($initialStockA, $this->productA->fresh()->qty);
        $this->assertEquals($initialStockB, $this->productB->fresh()->qty);
        $this->assertEquals(1, $detail1->fresh()->qty);
    }

    /**
     * Test 10: Sổ giao dịch tồn kho InventoryMovement ngăn chặn trừ trùng lặp (Idempotency)
     */
    public function test_inventory_movement_ledger_is_idempotent()
    {
        $initialStock = $this->productA->fresh()->qty;
        $idempotencyKey = 'test_movement_' . uniqid();

        // Áp dụng lần 1: Trừ 2 sản phẩm
        $m1 = InventoryService::applyMovement(
            $this->productA,
            -2,
            'sale_order',
            $idempotencyKey,
            ['order_id' => 1, 'order_detail_id' => 1, 'actor_id' => $this->staffUser->id]
        );
        $this->assertTrue($m1);
        $this->assertEquals($initialStock - 2, $this->productA->fresh()->qty);

        // Áp dụng lần 2 với cùng idempotencyKey -> Không được trừ thêm lần nữa
        $m2 = InventoryService::applyMovement(
            $this->productA,
            -2,
            'sale_order',
            $idempotencyKey,
            ['order_id' => 1, 'order_detail_id' => 1, 'actor_id' => $this->staffUser->id]
        );
        $this->assertTrue($m2);
        $this->assertEquals($initialStock - 2, $this->productA->fresh()->qty);
    }

    /**
     * Test 11: Payment Intent đối soát kiểm tra chính xác số tiền và chống Replay Attack
     */
    public function test_payment_intent_reconciliation_verifies_exact_amount_and_prevents_replay()
    {
        $order = Order::create([
            'user_id' => 1,
            'name' => 'Khách Test',
            'phone' => '0912345678',
            'address' => 'Tại quán',
            'table_id' => $this->table->id,
            'status' => 0,
            'payment_status' => Order::PAYMENT_PENDING,
            'total_price' => 100000,
            'created_by' => 1,
        ]);

        $requestId = 'MOMO_REQ_' . uniqid();
        $intent = PaymentIntentService::createIntent($order, 'momo', $requestId, 100000);

        // Thử reconcile với số tiền sai lệch (100001 VND) -> Trả về lỗi 422
        $resMismatch = PaymentIntentService::reconcileIpn('momo', $requestId, 'TXN_1', 100001);
        $this->assertFalse($resMismatch['success']);
        $this->assertEquals(422, $resMismatch['code']);

        // Reconcile với đúng số tiền 100000 VND -> Thành công
        $resSuccess = PaymentIntentService::reconcileIpn('momo', $requestId, 'TXN_1', 100000);
        $this->assertTrue($resSuccess['success']);

        // Thử dùng lại transaction ID 'TXN_1' cho một request_id khác -> Bị từ chối chống Replay Attack (409)
        $intent2 = PaymentIntentService::createIntent($order, 'momo', 'MOMO_REQ_OTHER', 100000);
        $resReplay = PaymentIntentService::reconcileIpn('momo', 'MOMO_REQ_OTHER', 'TXN_1', 100000);
        $this->assertFalse($resReplay['success']);
        $this->assertEquals(409, $resReplay['code']);
    }

    /**
     * Test 12: Khách kết thúc phiên gọi món không được phép tự động sửa status đơn/bàn trong DB
     */
    public function test_end_table_order_does_not_mutate_db_order_or_table_status()
    {
        $order = Order::create([
            'user_id' => 1,
            'name' => 'Khách Test',
            'phone' => '0912345678',
            'address' => 'Tại quán',
            'table_id' => $this->table->id,
            'status' => 1, // Đang phục vụ
            'payment_status' => Order::PAYMENT_PAID,
            'total_price' => 50000,
            'created_by' => 1,
        ]);

        $this->table->status = 1;
        $this->table->save();

        $this->withSession([
            'table_id' => $this->table->id,
            'table_order_id' => $order->id,
            'table_verified_' . $this->table->id => true,
        ]);

        $res = $this->post(route('site.table.end'));
        $res->assertRedirect(route('site.home'));

        // Kiểm tra DB: Đơn hàng vẫn giữ nguyên status = 1, bàn vẫn status = 1 (Chỉ nhân viên POS mới được đóng bàn)
        $this->assertEquals(1, $order->fresh()->status);
        $this->assertEquals(1, $this->table->fresh()->status);

        // Session của khách đã được xóa sạch
        $res->assertSessionMissing('table_id');
        $res->assertSessionMissing('table_order_id');
        $res->assertSessionMissing('table_verified_' . $this->table->id);
    }
}
