<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAccess
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->roles == 'admin') {
            return $next($request);
        }

        return redirect('admin/')->with('error', 'Bạn không có quyền truy cập vào trang này!');
    }
}
