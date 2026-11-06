<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function login()
    {
        return view("backend.login");
    }
    function dologin(Request $request){
        $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        $loginInput = trim($request->phone);
        $password = $request->password;

        // Thử tìm tài khoản theo Số điện thoại hoặc Tên đăng nhập (username)
        $user = \App\Models\User::where('phone', $loginInput)
            ->orWhere('username', $loginInput)
            ->first();

        if (!$user) {
            return redirect()->route('admin.login')->with('error', 'Tài khoản không tồn tại trong hệ thống!')->withInput();
        }

        if (!Hash::check($password, $user->password)) {
            return redirect()->route('admin.login')->with('error', 'Mật khẩu không chính xác!')->withInput();
        }

        // CHẶN TUYỆT ĐỐI KHÁCH HÀNG / THÀNH VIÊN VÀO ADMIN
        if ($user->roles !== 'admin' && $user->roles !== 'employee') {
            return redirect()->route('admin.login')->with('error', 'Tài khoản này là Khách hàng / Thành viên, không có quyền truy cập trang quản trị!')->withInput();
        }

        // Kiểm tra trạng thái tài khoản
        if ($user->status != 1) {
            return redirect()->route('admin.login')->with('error', 'Tài khoản của bạn đang bị khóa hoặc ngừng kích hoạt!')->withInput();
        }

        // Đăng nhập hợp lệ
        Auth::login($user);

        // Điều hướng chính xác theo phân quyền:
        if ($user->roles === 'employee') {
            return redirect()->route('admin.table-order.index')->with('success', 'Đăng nhập thành công! Chào mừng nhân viên ' . $user->fullname . '.');
        }

        return redirect()->route('admin.dashboard')->with('success', 'Đăng nhập thành công với quyền Quản trị viên!');
    }

    function logout(){
        Auth::logout();
        return redirect()->route('admin.login');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
