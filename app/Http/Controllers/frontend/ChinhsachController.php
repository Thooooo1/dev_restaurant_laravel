<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ChinhsachController extends Controller
{
    function dksd()
    {
        return view('frontend.chinhsach.dksd');
    }
    function csbm()
    {
        return view('frontend.chinhsach.csbm');
    }
    
    function csvc()
    {
        return view('frontend.chinhsach.csvc');
    }
    function attp()
    {
        return view('frontend.chinhsach.attp');
    }
    function cslh()
    {
        return view('frontend.chinhsach.cslh');
    }
}
