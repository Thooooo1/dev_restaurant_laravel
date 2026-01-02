<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Order;
use App\Models\OrderDetail;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $productCount = Product::count();
        $categoryCount = Category::count();
        $brandCount = Brand::count();

        $todayRevenue = OrderDetail::whereIn('order_id', function ($query) {
            $query->select('id')
                ->from('order')
                ->whereDate('created_at', Carbon::today())
                ->where('status', 2);
        })->sum('amount');

        $monthlyRevenue = OrderDetail::whereIn('order_id', function ($query) {
            $query->select('id')
                ->from('order')
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->where('status', 2);
        })->sum('amount');

        
        $topRatedProducts = Product::withCount('reviews')
            ->orderByDesc('reviews_count')
            ->limit(4)
            ->get();
        // dd(Order::where('status', 2)->get());

        return view('backend.dashboard.index', [
            'productCount' => $productCount,
            'categoryCount' => $categoryCount,
            'brandCount' => $brandCount,
            'todayRevenue' => $todayRevenue,
            'monthlyRevenue' => $monthlyRevenue,
            'topRatedProducts' => $topRatedProducts,
           
        ]);
    }
}
