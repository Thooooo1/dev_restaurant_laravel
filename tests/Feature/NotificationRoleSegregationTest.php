<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Notification;

class NotificationRoleSegregationTest extends TestCase
{
    public function test_customer_only_sees_their_own_notifications(): void
    {
        $customer1 = User::where('roles', 'customer')->first();
        if (!$customer1) {
            $customer1 = User::create([
                'fullname' => 'Test Customer 1',
                'name' => 'testcust1',
                'phone' => '0911111111',
                'roles' => 'customer',
                'status' => 1,
            ]);
        }

        $customer2 = User::where('roles', 'customer')->where('id', '!=', $customer1->id)->first();
        if (!$customer2) {
            $customer2 = User::create([
                'fullname' => 'Test Customer 2',
                'name' => 'testcust2',
                'phone' => '0922222222',
                'roles' => 'customer',
                'status' => 1,
            ]);
        }

        // Tạo 1 thông báo riêng cho customer 2
        $notiCust2 = Notification::create([
            'user_id' => $customer2->id,
            'role' => 'customer',
            'type' => 'payment',
            'title' => 'Thanh toan cust2',
            'message' => 'Cam on cust2',
            'is_read' => false,
        ]);

        // Tạo 1 thông báo riêng cho customer 1
        $notiCust1 = Notification::create([
            'user_id' => $customer1->id,
            'role' => 'customer',
            'type' => 'payment',
            'title' => 'Thanh toan cust1',
            'message' => 'Cam on cust1',
            'is_read' => false,
        ]);

        // Tạo 1 thông báo cho employee
        $notiEmployee = Notification::create([
            'user_id' => null,
            'role' => 'employee',
            'type' => 'service_call',
            'title' => 'Goi nhan vien',
            'message' => 'Ban 1 goi',
            'is_read' => false,
        ]);

        // Query scopeForUser cho customer 1
        $cust1Notis = Notification::forUser($customer1)->pluck('id')->toArray();

        $this->assertContains($notiCust1->id, $cust1Notis);
        $this->assertNotContains($notiCust2->id, $cust1Notis, 'Customer 1 must not see Customer 2 notification');
        $this->assertNotContains($notiEmployee->id, $cust1Notis, 'Customer 1 must not see employee notification');

        // Cleanup
        $notiCust1->delete();
        $notiCust2->delete();
        $notiEmployee->delete();
    }
}
