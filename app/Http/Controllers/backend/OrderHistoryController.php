<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Orderdetail;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RevenueExport;

class OrderHistoryController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);
        $quarter = $request->input('quarter');
        $month = $request->input('month');

        $query = Order::query()
            ->whereNull('deleted_at')
            ->paid()
            ->with(['user', 'orderDetails']);

        // P2-13: Thống nhất mốc tính doanh thu theo chuẩn dồn tích (revenue_recognized_at / paid_at)
        $dateExpr = 'COALESCE(revenue_recognized_at, paid_at, created_at)';

        // Filter by Year
        if ($year) {
            $query->whereRaw("YEAR({$dateExpr}) = ?", [$year]);
        }

        // Filter by Quarter
        if ($quarter) {
            $startMonth = ($quarter - 1) * 3 + 1;
            $endMonth = $startMonth + 2;
            $query->whereRaw("MONTH({$dateExpr}) >= ? AND MONTH({$dateExpr}) <= ?", [$startMonth, $endMonth]);
        }

        // Filter by Month
        if ($month) {
            $query->whereRaw("MONTH({$dateExpr}) = ?", [$month]);
        }

        // Calculate total revenue for the filtered set using final recognized total_price
        $totalQuery = clone $query;
        $totalRevenue = (float)$totalQuery->sum('total_price');

        // Get paginated results
        $orders = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->appends($request->all());

        return view('backend.order-history.index', compact('orders', 'year', 'quarter', 'month', 'totalRevenue'));
    }

    public function show($id)
    {
        $order = Order::with(['user', 'orderDetails.product'])->findOrFail($id);
        
        $html = view('backend.order-history.detail-modal', compact('order'))->render();
        
        return response()->json(['html' => $html]);
    }

    public function export(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);
        $quarter = $request->input('quarter');
        $month = $request->input('month');

        return \App\Services\ExcelExporter::download(new RevenueExport($year, $quarter, $month), 'bao-cao-doanh-thu.xls');
    }

    public function exportPayment(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);
        $quarter = $request->input('quarter');
        $month = $request->input('month');

        return \App\Services\ExcelExporter::download(new \App\Exports\PaymentReportExport($year, $quarter, $month), 'bao-cao-thanh-toan.xls');
    }
}
