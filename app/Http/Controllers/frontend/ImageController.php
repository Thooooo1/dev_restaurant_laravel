<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Image;

class ImageController extends Controller
{
    function index()
    {
        $args =[
            ['status','=',1]
        ];
        $images = Image::where($args)
        ->paginate(8);     
        return view('frontend.image',compact('images'));
    }
}
