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

        // Quản trị viên tối cao: toàn quyền truy cập
        if (Auth::user()->isAdmin()) {
            return $next($request);
        }

        // Nhân viên: chỉ được thao tác trên màn hình POS và vận hành bán hàng, không được vào trang quản trị
        if (Auth::user()->isEmployee()) {
            return redirect()->route('admin.table-order.index')->with('error', 'Nhân viên không có quyền truy cập vào chức năng quản trị này!');
        }

        return redirect()->route('site.home')->with('error', 'Bạn không có quyền truy cập!');
    }
}
