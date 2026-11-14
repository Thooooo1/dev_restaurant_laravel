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

        // =========================================================================
        // 5. BẢO VỆ NGHIỆP VỤ VẬN HÀNH 3 VAI TRÒ (KHÔNG ROLE SHIPPER):
        // Shipper là metadata trên bảng order do Employee/Admin quản lý.
        // =========================================================================
        $customerB = User::create([
            'username' => 'test_role_cust_b_' . uniqid(),
            'fullname' => 'Customer B Role Test',
            'password' => \Illuminate\Support\Facades\Hash::make('123456'),
            'phone' => '096' . rand(1000000, 9999999),
            'gender' => 1,
            'roles' => 'customer',
            'status' => 1,
        ]);

        $orderA = \App\Models\Order::create([
            'user_id' => $customer->id,
            'name' => 'Đơn Hàng Khách A',
            'phone' => $customer->phone,
            'email' => 'khachA@example.com',
            'address' => '123 Đường Số 1, TP.HCM',
            'note' => 'Giao trước 12h',
            'total_amount' => 250000,
            'order_type' => 'delivery',
            'payment_method' => 'cod',
            'delivery_status' => \App\Models\Order::DELIVERY_WAITING,
            'status' => \App\Enums\OrderStatus::PENDING,
            'created_by' => $customer->id,
        ]);

        $orderB = \App\Models\Order::create([
            'user_id' => $customerB->id,
            'name' => 'Đơn Hàng Khách B',
            'phone' => $customerB->phone,
            'email' => 'khachB@example.com',
            'address' => '456 Đường Số 2, TP.HCM',
            'total_amount' => 180000,
            'order_type' => 'delivery',
            'payment_method' => 'cod',
            'delivery_status' => \App\Models\Order::DELIVERY_WAITING,
            'status' => \App\Enums\OrderStatus::PENDING,
            'created_by' => $customerB->id,
        ]);

        // 5.1. Customer không được tự ý sửa thông tin hoặc tài xế shipper
        $custTamperDelivery = $this->actingAs($customer)->postJson(route('admin.order.updateDelivery', $orderA->id), [
            'shipper_name' => 'Tài xế Giả mạo',
            'shipper_phone' => '0999999999',
            'delivery_status' => 'delivering',
        ]);
        $custTamperDelivery->assertRedirect(route('site.home'));
        $this->assertEquals(\App\Models\Order::DELIVERY_WAITING, $orderA->fresh()->delivery_status);

        // 5.2. Employee được quyền cập nhật thông tin tài xế shipper và trạng thái giao hàng
        $empAssignShipper = $this->actingAs($employee)->postJson(route('admin.order.updateDelivery', $orderA->id), [
            'shipper_name' => 'Nguyễn Văn Tài Xế',
            'shipper_phone' => '0912345678',
            'delivery_status' => 'picked_up',
            'delivery_note' => 'Đã giao kiện hàng cho shipper',
        ]);
        $empAssignShipper->assertStatus(200);
        $this->assertEquals('Nguyễn Văn Tài Xế', $orderA->fresh()->shipper_name);
        $this->assertEquals('0912345678', $orderA->fresh()->shipper_phone);
        $this->assertEquals(\App\Models\Order::DELIVERY_PICKED_UP, $orderA->fresh()->delivery_status);

        // 5.3. Employee không được phép sửa giá món ăn hoặc vào bảng quản trị sản phẩm
        $empEditProduct = $this->actingAs($employee)->put('/admin/product/1/update', [
            'price' => 1000,
        ]);
        $empEditProduct->assertRedirect(route('admin.table-order.index'));

        // 5.4. Customer chỉ được xem đơn hàng của chính mình (chặn xem đơn của người khác)
        $custViewOtherOrder = $this->actingAs($customer)->get('/don-hang/' . $orderB->id);
        $custViewOtherOrder->assertRedirect(route('site.orders'));
        $custViewOtherOrder->assertSessionHas('error', 'Đơn hàng không tồn tại.');

        $custViewOwnOrder = $this->actingAs($customer)->get('/don-hang/' . $orderA->id);
        $custViewOwnOrder->assertStatus(200);

        // 5.5. Employee giao thành công đơn hàng (đồng bộ COMPLETED) và bị chặn quay ngược (chống rollback)
        $empDeliverSuccess = $this->actingAs($employee)->postJson(route('admin.order.updateDelivery', $orderA->id), [
            'shipper_name' => 'Nguyễn Văn Tài Xế',
            'shipper_phone' => '0912345678',
            'delivery_status' => 'delivered',
            'delivery_note' => 'Khách đã nhận hàng và thanh toán đầy đủ',
        ]);
        $empDeliverSuccess->assertStatus(200);
        $this->assertEquals('delivered', $orderA->fresh()->delivery_status);
        $this->assertEquals(\App\Enums\OrderStatus::COMPLETED, (int)$orderA->fresh()->status);

        // Thử quay ngược trạng thái từ "đã giao" về "đang giao" -> bị từ chối 422
        $empRollback = $this->actingAs($employee)->postJson(route('admin.order.updateDelivery', $orderA->id), [
            'delivery_status' => 'delivering',
        ]);
        $empRollback->assertStatus(422);
        $this->assertEquals('delivered', $orderA->fresh()->delivery_status);
        $this->assertEquals(\App\Enums\OrderStatus::COMPLETED, (int)$orderA->fresh()->status);

        // Dọn dẹp dữ liệu kiểm thử
        $orderA->forceDelete();
        $orderB->forceDelete();
        $customerB->forceDelete();
    }
}
