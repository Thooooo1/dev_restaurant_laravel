<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
        
        $args =[
            ['status','=',1],
            ['position','=','Slideshow']
        ];
        $banners = Banner::where($args)
            ->paginate(1);     
        return view('frontend.home', compact('banners'));
    }
}
