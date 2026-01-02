<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    function login()
    {
        return view('frontend.login');
    }
    function register()
    {
        return view('frontend.register');
    }
    // function profile()
    // {
    //     return view('frontend.profile');
    // }
}
