<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Banner;

class HomeController extends Controller
{
    function index()
    {
        $args =[
            ['status','=',1],
            ['position','=','Slideshow']
        ];
        $banners = Banner::where($args)
        ->paginate(1);     
        return view('frontend.home',compact('banners'));
    }
}
