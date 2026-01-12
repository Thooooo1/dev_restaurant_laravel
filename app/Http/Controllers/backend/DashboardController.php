<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Orderdetail;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
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

        return view('backend.dashboard.index', [
            'todayRevenue' => $todayRevenue,
            'todayOrderCount' => $todayOrderCount,
        ]);
    }
}
