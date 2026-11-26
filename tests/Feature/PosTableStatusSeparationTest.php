<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Floor;
use App\Models\Order;
use App\Models\Orderdetail;
use App\Models\Product;
use App\Models\ServiceCall;
use App\Models\Table;
use App\Models\User;
use Database\Seeders\TableSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PosTableStatusSeparationTest extends TestCase
{
    use DatabaseTransactions;

    protected function actingAsAdmin(): User
    {
        $admin = User::where('roles', 'admin')->first();
        if (!$admin) {
            $admin = new User();
            $admin->fullname = 'Test Admin';
            $admin->username = 'testadmin_' . uniqid();
            $admin->password = bcrypt('secret123');
            $admin->roles = 'admin';
            $admin->status = 1;
            $admin->created_by = 1;
            $admin->save();
        }
        $this->actingAs($admin);
        return $admin;
    }

    public function test_pos_index_separates_occupancy_and_request_statuses_and_renders_all_filters(): void
    {
        $this->actingAsAdmin();

        $floor = Floor::first() ?? Floor::create(['name' => 'Tầng 1', 'order' => 1]);
        $tServing = Table::create(['floor_id' => $floor->id, 'name' => 'Bàn Test 1', 'seats' => 8, 'sort_order' => 101, 'status' => 1]);
        $tEmpty = Table::create(['floor_id' => $floor->id, 'name' => 'Bàn Test 2', 'seats' => 3, 'sort_order' => 102, 'status' => 1]);
        $tReserved = Table::create(['floor_id' => $floor->id, 'name' => 'Bàn Test 3', 'seats' => 5, 'sort_order' => 103, 'status' => 1]);
        $tWaitPay = Table::create(['floor_id' => $floor->id, 'name' => 'Bàn Test 4', 'seats' => 5, 'sort_order' => 104, 'status' => 1]);

        $product = Product::first();

        // Bàn 1: Đang phục vụ + Có món mới
        $order1 = Order::create([
            'user_id' => 1,
            'name' => 'Bàn Test 1',
            'phone' => '0988000001',
            'email' => '',
            'address' => 'Tại bàn',
            'table_id' => $tServing->id,
            'number_of_guests' => 4,
            'orderStyle' => 0,
            'status' => 1,
            'payment_status' => Order::PAYMENT_PENDING,
            'has_new_items' => 1,
            'total_price' => 369000,
            'created_by' => 1,
        ]);
        if ($product) {
            Orderdetail::create([
                'order_id' => $order1->id,
                'product_id' => $product->id,
                'qty' => 2,
                'price' => 184500,
                'discount' => 0,
                'amount' => 369000,
                'status' => 'pending',
            ]);
        }

        // Bàn 3: Đã đặt trước
        Booking::create([
            'name' => 'Nguyễn Văn A',
            'email' => 'a@test.vn',
            'phone' => '0988000003',
            'number_of_people' => 4,
            'booking_date' => now()->toDateString(),
            'booking_time' => '18:30:00',
            'status' => 2,
            'table_id' => $tReserved->id,
        ]);

        // Bàn 4: Chờ thanh toán (Khách yêu cầu tính tiền)
        $order4 = Order::create([
            'user_id' => 1,
            'name' => 'Bàn Test 4',
            'phone' => '0988000004',
            'email' => '',
            'address' => 'Tại bàn',
            'table_id' => $tWaitPay->id,
            'number_of_guests' => 3,
            'orderStyle' => 0,
            'status' => 1,
            'payment_status' => Order::PAYMENT_PENDING,
            'has_new_items' => 0,
            'total_price' => 650000,
            'created_by' => 1,
        ]);
        ServiceCall::create([
            'table_id' => $tWaitPay->id,
            'order_id' => $order4->id,
            'call_type' => 'bill',
            'call_type_label' => 'Yêu cầu thanh toán hóa đơn',
            'status' => ServiceCall::STATUS_PENDING,
        ]);

        $response = $this->get(route('admin.table-order.index'));
        $response->assertStatus(200);

        // 7 status filter controls
        $response->assertSee('data-status="all"', false);
        $response->assertSee('data-status="empty"', false);
        $response->assertSee('data-status="serving"', false);
        $response->assertSee('data-status="reserved"', false);
        $response->assertSee('data-status="new_items"', false);
        $response->assertSee('data-status="service_call"', false);
        $response->assertSee('data-status="waiting_payment"', false);

        // Separated states rendered
        $response->assertSee('Đang phục vụ');
        $response->assertSee('Có món mới');
        $response->assertSee('Bàn trống');
        $response->assertSee('Đã đặt trước');
        $response->assertSee('18:30');
        $response->assertSee('Chờ thanh toán');
        $response->assertSee('Khách yêu cầu tính tiền');
    }

    public function test_table_seeder_uses_sequential_ban_1_to_20_without_dot_notation(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('tables')->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        (new TableSeeder())->run();

        $names = Table::orderBy('sort_order')->pluck('name')->all();
        $this->assertCount(20, $names);
        $this->assertSame('Bàn 1', $names[0]);
        $this->assertSame('Bàn 2', $names[1]);
        $this->assertSame('Bàn 3', $names[2]);
        $this->assertSame('Bàn 4', $names[3]);
        $this->assertSame('Bàn 20', $names[19]);
        foreach ($names as $n) {
            $this->assertStringNotContainsString('.0', $n);
        }
    }

    public function test_register_table_completes_confirmed_booking_assigned_to_table(): void
    {
        $this->actingAsAdmin();
        $floor = Floor::first() ?? Floor::create(['name' => 'Tầng 1', 'order' => 1]);
        $table = Table::create(['floor_id' => $floor->id, 'name' => 'Bàn 99', 'seats' => 4, 'sort_order' => 99, 'status' => 1]);

        $booking = Booking::create([
            'name' => 'Khách Đặt Trước',
            'email' => 'datban@test.vn',
            'phone' => '0988777666',
            'number_of_people' => 4,
            'booking_date' => now()->toDateString(),
            'booking_time' => '18:30:00',
            'status' => 2,
            'table_id' => $table->id,
        ]);

        $res = $this->post(route('admin.table-order.registerTable'), [
            'table_id' => $table->id,
            'phone' => '0988777666',
            'customer_name' => 'Khách Đặt Trước',
            'number_of_guests' => 4,
        ]);

        $res->assertRedirect(route('admin.table-order.index'));
        $this->assertSame(3, (int) $booking->fresh()->status);
    }
}
