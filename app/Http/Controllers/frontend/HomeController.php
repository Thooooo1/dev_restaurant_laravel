<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Banner;

class HomeController extends Controller
{
    function index()
    {
        // Xóa hết các session liên quan đến bàn nếu không có is_table_order
        if (!session()->has('is_table_order')) {
            session()->forget('table_id');
            session()->forget('table_order_id');
            session()->forget('table_name');
        }
        
        $banners = Cache::remember('frontend_home_banners_v1', 300, function () {
            $args = [
                ['status', '=', 1],
                ['position', '=', 'Slideshow']
            ];
            $items = Banner::where($args)->orderBy('sort_order', 'asc')->get();
            if ($items->isEmpty()) {
                $items = Banner::where('status', 1)->get();
            }
            return $items;
        });

        return view('frontend.home', compact('banners'));
    }
}
