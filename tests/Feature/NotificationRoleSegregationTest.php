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

    /**
     * Kiểm tra phân quyền truy cập tuyệt đối giữa 3 vai trò: Admin, Employee, Customer
     */
    public function test_three_roles_access_boundaries_and_protections(): void
    {
        $admin = User::where('roles', 'admin')->where('status', 1)->first();
        if (!$admin) {
            $admin = User::create([
                'username' => 'test_role_admin_' . uniqid(),
                'fullname' => 'Admin Role Test',
                'password' => \Illuminate\Support\Facades\Hash::make('123456'),
                'phone' => '093' . rand(1000000, 9999999),
                'gender' => 1,
                'roles' => 'admin',
                'admin_lever' => 1,
                'status' => 1,
            ]);
        }

        $employee = User::where('roles', 'employee')->where('status', 1)->first();
        if (!$employee) {
            $employee = User::create([
                'username' => 'test_role_emp_' . uniqid(),
                'fullname' => 'Employee Role Test',
                'password' => \Illuminate\Support\Facades\Hash::make('123456'),
                'phone' => '094' . rand(1000000, 9999999),
                'gender' => 1,
                'roles' => 'employee',
                'admin_lever' => 2,
                'status' => 1,
            ]);
        }

        $customer = User::create([
            'username' => 'test_role_cust_' . uniqid(),
            'fullname' => 'Customer Role Test',
            'password' => \Illuminate\Support\Facades\Hash::make('123456'),
            'phone' => '095' . rand(1000000, 9999999),
            'gender' => 1,
            'roles' => 'customer',
            'status' => 1,
        ]);

        // 1. Phân định Model helper methods
        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isEmployee());
        $this->assertFalse($admin->isCustomer());

        $this->assertTrue($employee->isEmployee());
        $this->assertFalse($employee->isAdmin());
        $this->assertFalse($employee->isCustomer());

        $this->assertTrue($customer->isCustomer());
        $this->assertFalse($customer->isAdmin());
        $this->assertFalse($customer->isEmployee());

        // 2. Customer: BỊ CHẶN TUYỆT ĐỐI KHỎI KHU VỰC ADMIN
        $custAdminRes = $this->actingAs($customer)->get('/admin');
        $custAdminRes->assertRedirect(route('site.home'));

        $custTableOrderRes = $this->actingAs($customer)->get('/admin/table-order');
        $custTableOrderRes->assertRedirect(route('site.home'));

        $custProductRes = $this->actingAs($customer)->get('/admin/product');
        $custProductRes->assertRedirect(route('site.home'));

        // Customer thử đăng nhập ở /admin/login bị từ chối
        $custLoginRes = $this->post(route('admin.dologin'), [
            'phone' => $customer->phone,
            'password' => '123456',
        ]);
        $custLoginRes->assertRedirect(route('admin.login'));
        $custLoginRes->assertSessionHas('error');

        // 3. Employee: CÓ QUYỀN VẬN HÀNH POS & ĐƠN HÀNG, BỊ CHẶN KHỎI QUẢN TRỊ HỆ THỐNG
        // Được vào POS
        $empTableOrderRes = $this->actingAs($employee)->get('/admin/table-order');
        $empTableOrderRes->assertStatus(200);

        // Được vào danh sách đơn giao
        $empOrderRes = $this->actingAs($employee)->get('/admin/order');
        $empOrderRes->assertStatus(200);

        // Được vào danh sách đặt bàn
        $empBookingRes = $this->actingAs($employee)->get('/admin/booking');
        $empBookingRes->assertStatus(200);

        // Bị chặn khỏi Dashboard tổng quan (chuyển hướng sang POS)
        $empDashRes = $this->actingAs($employee)->get('/admin');
        $empDashRes->assertRedirect(route('admin.table-order.index'));

        // Bị chặn khỏi Quản lý thực đơn / sản phẩm
        $empProductRes = $this->actingAs($employee)->get('/admin/product');
        $empProductRes->assertRedirect(route('admin.table-order.index'));

        // Bị chặn khỏi Quản lý danh mục
        $empCategoryRes = $this->actingAs($employee)->get('/admin/category');
        $empCategoryRes->assertRedirect(route('admin.table-order.index'));

        // Bị chặn khỏi Quản lý nhập kho
        $empImportRes = $this->actingAs($employee)->get('/admin/import-goods');
        $empImportRes->assertRedirect(route('admin.table-order.index'));

        // Bị chặn khỏi Báo cáo doanh thu & Lịch sử đơn hàng
        $empHistoryRes = $this->actingAs($employee)->get('/admin/order-history');
        $empHistoryRes->assertRedirect(route('admin.table-order.index'));

        // Bị chặn khỏi Quản lý nhân viên
        $empUserRes = $this->actingAs($employee)->get('/admin/user/employees');
        $empUserRes->assertRedirect(route('admin.table-order.index'));

        // Bị chặn khỏi Cài đặt hệ thống
        $empSettingRes = $this->actingAs($employee)->get('/admin/setting');
        $empSettingRes->assertRedirect(route('admin.table-order.index'));

        // Bị chặn khỏi Xuất Excel báo cáo món bán chạy
        $empExportBestSelling = $this->actingAs($employee)->get('/admin/export-best-selling');
        $empExportBestSelling->assertRedirect(route('admin.table-order.index'));

        // Bị chặn khỏi Xuất Excel đơn hàng
        $empExportOrder = $this->actingAs($employee)->get('/admin/order/export');
        $empExportOrder->assertRedirect(route('admin.table-order.index'));

        // 4. Admin: TOÀN QUYỀN TRUY CẬP TẤT CẢ CÁC KHU VỰC
        $adminDashRes = $this->actingAs($admin)->get('/admin');
        $adminDashRes->assertStatus(200);

        $adminTableOrderRes = $this->actingAs($admin)->get('/admin/table-order');
        $adminTableOrderRes->assertStatus(200);

        $adminProductRes = $this->actingAs($admin)->get('/admin/product');
        $adminProductRes->assertStatus(200);

        $adminCategoryRes = $this->actingAs($admin)->get('/admin/category');
        $adminCategoryRes->assertStatus(200);

        $adminImportRes = $this->actingAs($admin)->get('/admin/import-goods');
        $adminImportRes->assertStatus(200);

        $adminHistoryRes = $this->actingAs($admin)->get('/admin/order-history');
        $adminHistoryRes->assertStatus(200);

        $adminUserRes = $this->actingAs($admin)->get('/admin/user/employees');
        $adminUserRes->assertStatus(200);

        $adminSettingRes = $this->actingAs($admin)->get('/admin/setting');
        $adminSettingRes->assertStatus(200);
    }
}
