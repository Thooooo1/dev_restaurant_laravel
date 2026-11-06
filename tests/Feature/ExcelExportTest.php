<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RevenueExport;
use App\Exports\OrderExport;
use App\Exports\ProductExport;
use App\Exports\InventoryExport;
use App\Exports\BestSellingExport;
use App\Exports\CustomerExport;
use App\Exports\PaymentReportExport;

class ExcelExportTest extends TestCase
{
    protected function getAdmin()
    {
        return User::where('roles', 'admin')->first();
    }

    public function test_all_seven_exports_render_correctly()
    {
        $admin = $this->getAdmin();

        // 1. Revenue Export
        $res = $this->actingAs($admin)->get(route('admin.order-history.export'));
        $res->assertStatus(200);

        // 2. Order Export
        $res = $this->actingAs($admin)->get(route('admin.order.export'));
        $res->assertStatus(200);

        // 3. Product Export
        $res = $this->actingAs($admin)->get(route('admin.product.export'));
        $res->assertStatus(200);

        // 4. Inventory Export
        $res = $this->actingAs($admin)->get(route('admin.import-goods.export'));
        $res->assertStatus(200);

        // 5. Best Selling Export
        $res = $this->actingAs($admin)->get(route('admin.dashboard.exportBestSelling'));
        $res->assertStatus(200);

        // 6. Customer Export
        $res = $this->actingAs($admin)->get(route('admin.user.customers.export'));
        $res->assertStatus(200);

        // 7. Payment Report Export
        $res = $this->actingAs($admin)->get(route('admin.order-history.exportPayment'));
        $res->assertStatus(200);
    }
}
