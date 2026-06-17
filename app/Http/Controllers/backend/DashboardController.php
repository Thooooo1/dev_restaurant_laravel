<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Orderdetail;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Lấy loại thống kê
        $type = $request->get('type', 'day');
        
        // Tạo khoảng thời gian mặc định (không dùng request start_date/end_date để tránh lỗi)
        if ($type === 'day') {
            $endDate = Carbon::today();
            $startDate = $endDate->copy()->subDays(29);
        } elseif ($type === 'month') {
            $endDate = Carbon::today()->endOfMonth();
            $startDate = $endDate->copy()->subMonths(11)->startOfMonth();
        } elseif ($type === 'year') {
            $endDate = Carbon::today()->endOfYear();
            $startDate = $endDate->copy()->subYears(4)->startOfYear();
        }

        // === Thống kê hôm nay ===
        $todayOrderCount = Order::whereNull('deleted_at')
            ->whereDate('created_at', Carbon::today())
            ->where('status', 2)
            ->count();

        $todayRevenue = Orderdetail::whereIn('order_id', function ($query) {
            $query->select('id')
                ->from('order')
                ->whereNull('deleted_at')
                ->whereDate('created_at', Carbon::today())
                ->where('status', 2);
        })->sum('amount');

        $todayCustomerCount = Order::whereNull('deleted_at')
            ->whereDate('created_at', Carbon::today())
            ->where('status', 2)
            ->distinct('phone')
            ->count('phone');

        $todayAvgOrderValue = $todayOrderCount > 0 ? $todayRevenue / $todayOrderCount : 0;

        $chartLabels = [];
        $chartRevenueData = [];

        if ($type === 'day') {
            $current = $startDate->copy();
            while ($current->lte($endDate)) {
                $chartLabels[] = $current->format('d/m');
                $dateStr = $current->toDateString();
                
                $rev = Orderdetail::whereIn('order_id', function ($q) use ($dateStr) {
                    $q->select('id')->from('order')->whereNull('deleted_at')->whereDate('created_at', $dateStr)->where('status', 2);
                })->sum('amount');
                
                $chartRevenueData[] = $rev;
                $current->addDay();
            }
        } elseif ($type === 'month') {
            $chartLabels = [];
            $chartRevenueData = [];
            
            // Tạo 12 tháng thủ công, chắc chắn 100% không thiếu
            $startYear = $startDate->year;
            $startMonth = $startDate->month;
            $endYear = $endDate->year;
            $endMonth = $endDate->month;
            
            $currentYear = $startYear;
            $currentMonth = $startMonth;
            
            while (true) {
                // Tạo ngày đầu và cuối tháng hiện tại
                $tempDate = Carbon::create($currentYear, $currentMonth, 1);
                $monthStart = $tempDate->startOfMonth()->toDateString();
                $monthEnd = $tempDate->endOfMonth()->toDateString();
                $chartLabels[] = $tempDate->format('m/Y');
                
                // Tính doanh thu
                $rev = Orderdetail::whereIn('order_id', function ($q) use ($monthStart, $monthEnd) {
                    $q->select('id')->from('order')->whereNull('deleted_at')->whereBetween('created_at', [$monthStart.' 00:00:00', $monthEnd.' 23:59:59'])->where('status', 2);
                })->sum('amount');
                
                $chartRevenueData[] = $rev;
                
                // Kiểm tra nếu đã đến tháng cuối thì break
                if ($currentYear == $endYear && $currentMonth == $endMonth) {
                    break;
                }
                
                // Tăng tháng
                $currentMonth++;
                if ($currentMonth > 12) {
                    $currentMonth = 1;
                    $currentYear++;
                }
            }
        } elseif ($type === 'year') {
            $current = $startDate->copy()->startOfYear();
            $endLoop = $endDate->copy()->endOfYear();
            
            while ($current->lte($endLoop)) {
                $chartLabels[] = $current->format('Y');
                $yearStart = $current->startOfYear()->toDateString();
                $yearEnd = $current->endOfYear()->toDateString();
                
                $rev = Orderdetail::whereIn('order_id', function ($q) use ($yearStart, $yearEnd) {
                    $q->select('id')->from('order')->whereNull('deleted_at')->whereBetween('created_at', [$yearStart.' 00:00:00', $yearEnd.' 23:59:59'])->where('status', 2);
                })->sum('amount');
                
                $chartRevenueData[] = $rev;
                $current->addYear();
            }
        }

      
        return view('backend.dashboard.index', [
            'todayRevenue' => $todayRevenue,
            'todayOrderCount' => $todayOrderCount,
            'todayCustomerCount' => $todayCustomerCount,
            'todayAvgOrderValue' => $todayAvgOrderValue,
            'type' => $type,
            'chartLabels' => $chartLabels,
            'chartRevenueData' => $chartRevenueData
        ]);
    }
}
