<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAccess
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('admin.login')->with('error', 'Vui lòng đăng nhập!');
        }

        // Nhân viên (admin_lever = 2 hoặc roles = employee) không được vào các menu quản trị viên
        if (Auth::user()->roles === 'employee' || Auth::user()->admin_lever == 2) {
            return redirect()->route('admin.table-order.index')->with('error', 'Nhân viên không có quyền truy cập vào chức năng quản trị này!');
        }

        // Quản trị viên tối cao
        if (Auth::user()->roles === 'admin') {
            return $next($request);
        }

        return redirect()->route('site.home')->with('error', 'Bạn không có quyền truy cập!');
    }
}
