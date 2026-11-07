<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Booking;
use App\Models\Table;
use App\Models\Product;
use App\Enums\OrderStatus;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Nhân viên vận hành không xem bảng doanh thu tổng quan, điều hướng trực tiếp sang POS bán hàng
        if (\Illuminate\Support\Facades\Auth::user() && \Illuminate\Support\Facades\Auth::user()->roles === 'employee') {
            return redirect()->route('admin.table-order.index');
        }

        // Bộ lọc thời gian: 'today', '7day', '30day', '12month' (mặc định 'today')
        $type = $request->get('type', 'today');
        if (!in_array($type, ['today', '7day', '30day', '12month'])) {
            $type = 'today';
        }

        $today = Carbon::today();

        // 1. Xác định phạm vi thời gian hiện tại và kỳ đối chiếu
        if ($type === 'today') {
            $periodLabel = 'Hôm nay';
            $periodMetricSuffix = 'HÔM NAY';
            $prevPeriodLabel = 'hôm qua';
            
            $currentStart = $today->copy()->startOfDay();
            $currentEnd = $today->copy()->endOfDay();
            
            $prevStart = $today->copy()->subDay()->startOfDay();
            $prevEnd = $today->copy()->subDay()->endOfDay();
        } elseif ($type === '7day') {
            $periodLabel = '7 ngày qua';
            $periodMetricSuffix = '7 NGÀY QUA';
            $prevPeriodLabel = '7 ngày trước';
            
            $currentStart = $today->copy()->subDays(6)->startOfDay();
            $currentEnd = $today->copy()->endOfDay();
            
            $prevStart = $today->copy()->subDays(13)->startOfDay();
            $prevEnd = $today->copy()->subDays(7)->endOfDay();
        } elseif ($type === '30day') {
            $periodLabel = '30 ngày qua';
            $periodMetricSuffix = '30 NGÀY QUA';
            $prevPeriodLabel = '30 ngày trước';
            
            $currentStart = $today->copy()->subDays(29)->startOfDay();
            $currentEnd = $today->copy()->endOfDay();
            
            $prevStart = $today->copy()->subDays(59)->startOfDay();
            $prevEnd = $today->copy()->subDays(30)->endOfDay();
        } else { // 12month
            $periodLabel = '12 tháng qua';
            $periodMetricSuffix = '12 THÁNG QUA';
            $prevPeriodLabel = 'năm trước';
            
            $currentStart = $today->copy()->subMonths(11)->startOfMonth();
            $currentEnd = $today->copy()->endOfMonth();
            
            $prevStart = $today->copy()->subMonths(23)->startOfMonth();
            $prevEnd = $today->copy()->subMonths(12)->endOfMonth();
        }

        // === 2. TÍNH TOÁN 4 CHỈ SỐ KPI CHUẨN DOANH NGHIỆP ===
        // Hàm lọc đơn hàng phát sinh hoặc hoàn tất thanh toán trong kỳ
        $buildPeriodOrders = function($start, $end) {
            return Order::whereNull('deleted_at')
                ->whereIn('status', [1, 2])
                ->where(function($q) use ($start, $end) {
                    $q->whereBetween('created_at', [$start, $end])
                      ->orWhere(function($sub) use ($start, $end) {
                          $sub->where('status', 2)
                              ->whereBetween('updated_at', [$start, $end]);
                      });
                });
        };

        // KPI 1: DOANH THU
        $revenue = (int) $buildPeriodOrders($currentStart, $currentEnd)->sum('total_price');
        $prevRevenue = (int) $buildPeriodOrders($prevStart, $prevEnd)->sum('total_price');

        $revenueGrowth = $prevRevenue > 0 
            ? round((($revenue - $prevRevenue) / $prevRevenue) * 100, 1) 
            : ($revenue > 0 ? 100.0 : 0.0);

        $cashRevenue = (int) $buildPeriodOrders($currentStart, $currentEnd)
            ->where(function($q) {
                $q->whereIn('payment_method', [1, '1', 'COD', 'cod', 'cash', 'tiền mặt']);
            })
            ->sum('total_price');

        $transferRevenue = (int) $buildPeriodOrders($currentStart, $currentEnd)
            ->where(function($q) {
                $q->whereIn('payment_method', [2, '2', 'VietQR', 'vietqr', 'bank', 'chuyển khoản']);
            })
            ->sum('total_price');

        // KPI 2: ĐƠN HÀNG
        $orderCount = $buildPeriodOrders($currentStart, $currentEnd)->count();
        $prevOrderCount = $buildPeriodOrders($prevStart, $prevEnd)->count();

        $orderGrowth = $prevOrderCount > 0 
            ? round((($orderCount - $prevOrderCount) / $prevOrderCount) * 100, 1) 
            : ($orderCount > 0 ? 100.0 : 0.0);

        $dineInCount = $buildPeriodOrders($currentStart, $currentEnd)
            ->whereNotNull('table_id')
            ->count();

        $takeawayCount = $buildPeriodOrders($currentStart, $currentEnd)
            ->whereNull('table_id')
            ->count();

        // KPI 3: GIÁ TRỊ ĐƠN TRUNG BÌNH (AOV)
        $avgOrderValue = $orderCount > 0 ? round($revenue / $orderCount) : 0;
        $prevAvgOrderValue = $prevOrderCount > 0 ? round($prevRevenue / $prevOrderCount) : 0;
        $aovGrowth = $prevAvgOrderValue > 0 
            ? round((($avgOrderValue - $prevAvgOrderValue) / $prevAvgOrderValue) * 100, 1) 
            : 0.0;

        $customersCount = $buildPeriodOrders($currentStart, $currentEnd)
            ->distinct('phone')
            ->count('phone');

        // KPI 4: LƯỢT ĐẶT BÀN
        $bookingsCount = Booking::whereBetween('booking_date', [$currentStart->toDateString(), $currentEnd->toDateString()])
            ->where('status', '!=', 0)
            ->count();

        $confirmedBookings = Booking::whereBetween('booking_date', [$currentStart->toDateString(), $currentEnd->toDateString()])
            ->where('status', 2)
            ->count();

        $pendingBookings = Booking::whereBetween('booking_date', [$currentStart->toDateString(), $currentEnd->toDateString()])
            ->where('status', 1)
            ->count();

        $prevBookingsCount = Booking::whereBetween('booking_date', [$prevStart->toDateString(), $prevEnd->toDateString()])
            ->where('status', '!=', 0)
            ->count();

        $bookingGrowth = $prevBookingsCount > 0 
            ? round((($bookingsCount - $prevBookingsCount) / $prevBookingsCount) * 100, 1) 
            : ($bookingsCount > 0 ? 100.0 : 0.0);

        // === 3. THÔNG SỐ VẬN HÀNH THỰC TẾ NHÀ HÀNG ===
        $totalTables = Table::count();
        $activeTableIds = Order::whereNull('deleted_at')
            ->whereIn('status', [0, 1])
            ->whereNotNull('table_id')
            ->pluck('table_id')
            ->toArray();
        $lockedTableIds = Table::whereNotNull('locked_order_id')->pluck('id')->toArray();
        $occupiedTables = count(array_unique(array_merge($activeTableIds, $lockedTableIds)));
        $totalActiveDishes = Product::where('status', 1)->count();
        $totalAllTimeRevenue = Order::whereNull('deleted_at')->whereIn('status', [OrderStatus::PROCESSING, OrderStatus::COMPLETED])->sum('total_price');

        // === 4. THỐNG KÊ TRẠNG THÁI ĐƠN HÀNG (BYTEBITE POS STYLE PILLS) ===
        $totalOrdersCount = Order::whereNull('deleted_at')->count();
        $pendingOrdersCount = Order::whereNull('deleted_at')->where('status', OrderStatus::PENDING)->count();
        $paidOrdersCount = Order::whereNull('deleted_at')->where('status', OrderStatus::PROCESSING)->count();
        $completedOrdersCount = Order::whereNull('deleted_at')->where('status', OrderStatus::COMPLETED)->count();
        $cancelledOrdersCount = Order::whereNull('deleted_at')->where('status', OrderStatus::CANCELLED)->count();

        // Thống kê trạng thái Đặt bàn
        $totalBookingsCount = Booking::count();
        $totalPendingBookings = Booking::where('status', 1)->count();
        $totalConfirmedBookings = Booking::where('status', 2)->count();
        $totalCancelledBookings = Booking::where('status', 0)->count();

        // === 5. DANH SÁCH HOẠT ĐỘNG GẦN ĐÂY ===
        $recentOrders = Order::whereNull('deleted_at')
            ->orderBy('id', 'desc')
            ->limit(8)
            ->get();

        $recentBookings = Booking::orderBy('id', 'desc')
            ->limit(8)
            ->get();

        // === 6. TÍNH TOÁN DỮ LIỆU BIỂU ĐỒ ===
        $chartLabels = [];
        $chartRevenueData = [];

        if ($type === 'today') {
            $hours = ['08:00', '10:00', '12:00', '14:00', '16:00', '18:00', '20:00', '22:00'];
            foreach ($hours as $h) {
                $chartLabels[] = $h;
                $hourInt = (int)explode(':', $h)[0];
                $hStart = $today->copy()->setHour($hourInt)->setMinute(0)->setSecond(0);
                $hEnd = $hStart->copy()->addHours(2)->subSecond();
                $rev = (int) Order::whereNull('deleted_at')
                    ->whereIn('status', [1, 2])
                    ->where(function($q) use ($hStart, $hEnd) {
                        $q->whereBetween('created_at', [$hStart, $hEnd])
                          ->orWhere(function($sub) use ($hStart, $hEnd) {
                              $sub->where('status', 2)
                                  ->whereBetween('updated_at', [$hStart, $hEnd]);
                          });
                    })
                    ->sum('total_price');
                $chartRevenueData[] = $rev;
            }
        } elseif ($type === '7day') {
            $current = $today->copy()->subDays(6);
            while ($current->lte($today)) {
                $chartLabels[] = $current->format('d/m');
                $dateStr = $current->toDateString();
                $dayStart = $current->copy()->startOfDay();
                $dayEnd = $current->copy()->endOfDay();
                $rev = (int) Order::whereNull('deleted_at')
                    ->whereIn('status', [1, 2])
                    ->where(function($q) use ($dayStart, $dayEnd) {
                        $q->whereBetween('created_at', [$dayStart, $dayEnd])
                          ->orWhere(function($sub) use ($dayStart, $dayEnd) {
                              $sub->where('status', 2)
                                  ->whereBetween('updated_at', [$dayStart, $dayEnd]);
                          });
                    })
                    ->sum('total_price');
                $chartRevenueData[] = $rev;
                $current->addDay();
            }
        } elseif ($type === '30day') {
            $current = $today->copy()->subDays(29);
            while ($current->lte($today)) {
                $chartLabels[] = $current->format('d/m');
                $dateStr = $current->toDateString();
                $dayStart = $current->copy()->startOfDay();
                $dayEnd = $current->copy()->endOfDay();
                $rev = (int) Order::whereNull('deleted_at')
                    ->whereIn('status', [1, 2])
                    ->where(function($q) use ($dayStart, $dayEnd) {
                        $q->whereBetween('created_at', [$dayStart, $dayEnd])
                          ->orWhere(function($sub) use ($dayStart, $dayEnd) {
                              $sub->where('status', 2)
                                  ->whereBetween('updated_at', [$dayStart, $dayEnd]);
                          });
                    })
                    ->sum('total_price');
                $chartRevenueData[] = $rev;
                $current->addDay();
            }
        } else { // 12month
            $currentYear = $currentStart->year;
            $currentMonth = $currentStart->month;
            $endYear = $currentEnd->year;
            $endMonth = $currentEnd->month;

            while (true) {
                $tempDate = Carbon::create($currentYear, $currentMonth, 1);
                $monthStart = $tempDate->copy()->startOfMonth();
                $monthEnd = $tempDate->copy()->endOfMonth();
                $chartLabels[] = $tempDate->format('m/Y');

                $rev = (int) Order::whereNull('deleted_at')
                    ->whereIn('status', [1, 2])
                    ->where(function($q) use ($monthStart, $monthEnd) {
                        $q->whereBetween('created_at', [$monthStart, $monthEnd])
                          ->orWhere(function($sub) use ($monthStart, $monthEnd) {
                              $sub->where('status', 2)
                                  ->whereBetween('updated_at', [$monthStart, $monthEnd]);
                          });
                    })
                    ->sum('total_price');

                $chartRevenueData[] = $rev;

                if ($currentYear == $endYear && $currentMonth == $endMonth) {
                    break;
                }
                $currentMonth++;
                if ($currentMonth > 12) {
                    $currentMonth = 1;
                    $currentYear++;
                }
            }
        }

        $hasChartData = array_sum($chartRevenueData) > 0;

        return view('backend.dashboard.index', [
            'type' => $type,
            'periodLabel' => $periodLabel,
            'periodMetricSuffix' => $periodMetricSuffix,
            'prevPeriodLabel' => $prevPeriodLabel,

            // KPI 1: Doanh thu
            'revenue' => $revenue,
            'prevRevenue' => $prevRevenue,
            'revenueGrowth' => $revenueGrowth,
            'cashRevenue' => $cashRevenue,
            'transferRevenue' => $transferRevenue,

            // KPI 2: Đơn hàng
            'orderCount' => $orderCount,
            'prevOrderCount' => $prevOrderCount,
            'orderGrowth' => $orderGrowth,
            'dineInCount' => $dineInCount,
            'takeawayCount' => $takeawayCount,

            // KPI 3: AOV
            'avgOrderValue' => $avgOrderValue,
            'prevAvgOrderValue' => $prevAvgOrderValue,
            'aovGrowth' => $aovGrowth,
            'customersCount' => $customersCount,

            // KPI 4: Đặt bàn
            'bookingsCount' => $bookingsCount,
            'confirmedBookings' => $confirmedBookings,
            'pendingBookings' => $pendingBookings,
            'bookingGrowth' => $bookingGrowth,

            // Vận hành chung
            'totalTables' => $totalTables,
            'occupiedTables' => $occupiedTables,
            'totalActiveDishes' => $totalActiveDishes,
            'totalAllTimeRevenue' => $totalAllTimeRevenue,

            // Thống kê phân loại đơn
            'totalOrdersCount' => $totalOrdersCount,
            'pendingOrdersCount' => $pendingOrdersCount,
            'paidOrdersCount' => $paidOrdersCount,
            'completedOrdersCount' => $completedOrdersCount,
            'cancelledOrdersCount' => $cancelledOrdersCount,

            // Thống kê đặt bàn
            'totalBookingsCount' => $totalBookingsCount,
            'totalPendingBookings' => $totalPendingBookings,
            'totalConfirmedBookings' => $totalConfirmedBookings,
            'totalCancelledBookings' => $totalCancelledBookings,

            // Danh sách
            'recentOrders' => $recentOrders,
            'recentBookings' => $recentBookings,

            // Biểu đồ
            'chartLabels' => $chartLabels,
            'chartRevenueData' => $chartRevenueData,
            'hasChartData' => $hasChartData,
        ]);
    }

    public function exportBestSelling(Request $request)
    {
        $type = $request->get('type', 'all');
        return \App\Services\ExcelExporter::download(
            new \App\Exports\BestSellingExport($type),
            'bao-cao-mon-ban-chay.xls'
        );
    }
}
