<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

abstract class TestCase extends BaseTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        if (app()->environment('production')) {
            throw new \RuntimeException('Tuyệt đối không được chạy test suite trên môi trường Production để bảo vệ dữ liệu vận hành!');
        }

        // V3-P1-32 & V4: Bảo vệ cơ sở dữ liệu vận hành - Chặn test suite nếu cấu hình trỏ vào database hoặc host production
        $defaultConn = config('database.default', 'mysql');
        $dbName = (string)config("database.connections.{$defaultConn}.database", '');
        $dbHost = (string)config("database.connections.{$defaultConn}.host", '');
        $dbUsername = (string)config("database.connections.{$defaultConn}.username", '');

        if (
            str_contains(strtolower($dbName), 'production') || 
            str_contains(strtolower($dbName), 'prod_live') ||
            str_contains(strtolower($dbName), 'live') ||
            str_contains(strtolower($dbHost), 'prod') ||
            str_contains(strtolower($dbHost), 'rds.amazonaws.com') ||
            str_contains(strtolower($dbHost), 'cloudsql') ||
            $dbUsername === 'production'
        ) {
            throw new \RuntimeException('Bảo vệ an toàn dữ liệu: Tuyệt đối không được chạy automated test suite trên cơ sở dữ liệu Production!');
        }

        // Đảm bảo luôn có 3 role cơ bản sẵn sàng cho mọi test case
        \App\Models\User::firstOrCreate(['roles' => 'admin', 'username' => 'system_test_admin'], [
            'fullname' => 'System Test Admin',
            'password' => bcrypt('123456'),
            'email' => 'admin_test@civilize.vn',
            'phone' => '0988888888',
            'status' => 1,
            'roles' => 'admin',
        ]);

        \App\Models\User::firstOrCreate(['roles' => 'employee', 'username' => 'system_test_employee'], [
            'fullname' => 'System Test Employee',
            'password' => bcrypt('123456'),
            'email' => 'employee_test@civilize.vn',
            'phone' => '0977777777',
            'status' => 1,
            'roles' => 'employee',
        ]);

        \App\Models\User::firstOrCreate(['roles' => 'customer', 'username' => 'system_test_customer'], [
            'fullname' => 'System Test Customer',
            'password' => bcrypt('123456'),
            'email' => 'customer_test@civilize.vn',
            'phone' => '0966666666',
            'status' => 1,
            'roles' => 'customer',
        ]);
        \App\Models\Floor::firstOrCreate(['id' => 1], [
            'name' => 'Tầng 1',
        ]);

        \App\Models\Table::firstOrCreate(['id' => 1], [
            'floor_id' => 1,
            'name' => 'Bàn 1.01',
            'seats' => 4,
            'sort_order' => 1,
            'status' => 1,
        ]);

        if (!\App\Models\Category::where('id', 1)->exists()) {
            $cat = new \App\Models\Category();
            $cat->id = 1;
            $cat->name = 'Danh Mục Test';
            $cat->slug = 'danh-muc-test';
            $cat->parent_id = 0;
            $cat->sort_order = 1;
            $cat->image = 'default.png';
            $cat->description = 'Test';
            $cat->status = 1;
            $cat->created_by = 1;
            $cat->save();
        }

        if (!\App\Models\Brand::where('id', 1)->exists()) {
            $brand = new \App\Models\Brand();
            $brand->id = 1;
            $brand->name = 'Thương Hiệu Test';
            $brand->slug = 'thuong-hieu-test';
            $brand->image = 'default.png';
            $brand->description = 'Test';
            $brand->sort_order = 1;
            $brand->status = 1;
            $brand->created_by = 1;
            $brand->save();
        }

        if (!\App\Models\Product::where('id', 1)->exists()) {
            $prod = new \App\Models\Product();
            $prod->id = 1;
            $prod->category_id = 1;
            $prod->brand_id = 1;
            $prod->name = 'Món Hệ Thống Test';
            $prod->slug = 'mon-he-thong-test';
            $prod->price_buy = 45000;
            $prod->price_sale = 50000;
            $prod->qty = 100;
            $prod->thumbnail = 'default.png';
            $prod->content = 'Content';
            $prod->description = 'Description';
            $prod->new_p = 1;
            $prod->status = 1;
            $prod->created_by = 1;
            $prod->save();
        }
    }
}

