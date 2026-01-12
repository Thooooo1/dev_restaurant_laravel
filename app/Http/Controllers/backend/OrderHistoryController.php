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
            ->where('status', 2) // Filter for completed orders
            ->with(['user', 'orderDetails']);

        // Filter by Year
        if ($year) {
            $query->whereYear('created_at', $year);
        }

        // Filter by Quarter
        if ($quarter) {
            $startMonth = ($quarter - 1) * 3 + 1;
            $endMonth = $startMonth + 2;
            $query->whereMonth('created_at', '>=', $startMonth)
                  ->whereMonth('created_at', '<=', $endMonth);
        }

        // Filter by Month
        if ($month) {
            $query->whereMonth('created_at', $month);
        }

        // Calculate total revenue for the filtered set
        // Use clone to avoid modifying the base query for pagination
        $totalQuery = clone $query;
        $orderIds = $totalQuery->pluck('id');
        $totalRevenue = Orderdetail::whereIn('order_id', $orderIds)->sum('amount');

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

        return Excel::download(new RevenueExport($year, $quarter, $month), 'bao-cao-doanh-thu.xlsx');
    }
}
