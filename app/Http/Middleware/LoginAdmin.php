<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Auth;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LoginAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(!Auth::check()){
            return redirect()->route('admin.login')->with('error','Vui lòng đăng nhập để truy cập trang quản trị!');
        }
        $user = Auth::user();

        // Khách hàng / thành viên tuyệt đối không được vào backend
        if($user->roles !== 'admin' && $user->roles !== 'employee')
        {
            return redirect()->route('site.home')->with('error','Tài khoản của bạn là Khách hàng / Thành viên, không có quyền truy cập trang quản trị!');
        }

        // Kiểm tra tài khoản có bị khóa không
        if($user->status != 1)
        {
            Auth::logout();
            return redirect()->route('admin.login')->with('error','Tài khoản của bạn đang bị khóa hoặc ngừng hoạt động!');
        }

        return $next($request);
    }
}
