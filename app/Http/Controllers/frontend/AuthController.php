<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Order;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;

use Carbon\Carbon;

use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    function login()
    {
        return view("frontend.login");
    }
    function dologin(Request $request)
    {
        $rawInput = trim($request->phone ?? $request->email ?? $request->username ?? '');
        $phone = preg_replace('/[^0-9]/', '', $rawInput);
        $password = $request->password;
        $redirectUrl = $request->input('redirect');

        if (empty($rawInput)) {
             return redirect()->route('site.login')->with('error', 'Vui lòng nhập email, số điện thoại hoặc tên đăng nhập!')->withInput();
        }

        if (empty($password)) {
             return redirect()->route('site.login')->with('error', 'Vui lòng nhập mật khẩu!')->withInput();
        }

        $noSpaceInput = str_replace(' ', '', $rawInput);

        // Tìm user theo email, số điện thoại đã chuẩn hóa hoặc tên đăng nhập
        $user = User::where('status', 1)
            ->where(function($q) use ($phone, $rawInput, $noSpaceInput) {
                $q->where('email', $rawInput)
                  ->orWhere('email', $noSpaceInput)
                  ->orWhere('username', $rawInput)
                  ->orWhere('username', $noSpaceInput);
                if (in_array(strtolower($rawInput), ['khachhang', 'khachhang 1', 'khachhang1'])) {
                    $q->orWhere('username', 'khachhang1');
                }
                if (!empty($phone) && strlen($phone) >= 9) {
                    $q->orWhere('phone', $phone);
                }
            })->first();

        if (!$user) {
            return redirect()->route('site.login')
                ->with('error', 'Tài khoản chưa được đăng ký trong hệ thống. Vui lòng bấm Đăng ký bên dưới!')
                ->withInput();
        }

        if (empty($user->password)) {
            return redirect()->route('site.login')
                ->with('error', 'Tài khoản chưa thiết lập mật khẩu. Vui lòng liên hệ nhà hàng!')
                ->withInput();
        }

        if (!Hash::check($password, $user->password)) {
            return redirect()->route('site.login')->with('error', 'Mật khẩu không chính xác!')->withInput();
        }

        // Đăng nhập thành công
        Auth::loginUsingId($user->id);
        session()->put('user_site', $user);
        
        // Luôn xóa session bàn nếu đăng nhập bình thường
        if (!session()->has('is_table_order')) {
            session()->forget('table_id');
            session()->forget('table_order_id');
            session()->forget('table_name');
            session()->forget('is_table_order');
        }
        
        if ($redirectUrl) {
            return redirect($redirectUrl)->with('success', 'Đăng nhập thành công!');
        }
        return redirect()->route('site.home')->with('success', 'Đăng nhập thành công!');
    }

    public function checkPhone(Request $request)
    {
        $phone = preg_replace('/[^0-9]/', '', $request->phone);
        
        if (empty($phone)) {
            return response()->json(['status' => 'error', 'message' => 'Vui lòng nhập số điện thoại!']);
        }

        // Priority 1: Check Order
        // Find order with normalized phone
        $orders = Order::where('status', 0)->whereNotNull('table_id')->get();
        $order = null;
        foreach ($orders as $o) {
            $dbPhone = preg_replace('/[^0-9]/', '', $o->phone);
            if ($dbPhone === $phone) {
                $order = $o;
                break;
            }
        }

        if ($order && $order->user_id) {
            $user = User::find($order->user_id);
            if ($user) {
                Auth::login($user);
                session()->put('user_site', $user);
                
                // LUÔN XÓA TẤT CẢ SESSION BÀN KHI ĐĂNG NHẬP BÌNH THƯỜNG, CHỈ GIỮ KHI QUA QR CODE
                if (!session()->has('is_table_order')) {
                    session()->forget('table_id');
                    session()->forget('table_order_id');
                    session()->forget('table_name');
                    session()->forget('is_table_order');
                }
                
                return response()->json([
                    'status' => 'success',
                    'redirect' => route('site.home')
                ]);
            }
        }

        // Priority 2: Check User
        $users = User::where('status', 1)->get();
        $user = null;
        foreach ($users as $u) {
            $dbPhone = preg_replace('/[^0-9]/', '', $u->phone);
            if ($dbPhone === $phone) {
                $user = $u;
                break;
            }
        }

        if ($user) {
            if (!empty($user->password)) {
                return response()->json([
                    'status' => 'require_password',
                    'fullname' => $user->fullname ?? 'Quý khách'
                ]);
            } else {
                return response()->json([
                    'status' => 'require_activate',
                    'fullname' => $user->fullname ?? 'Quý khách',
                    'phone' => $phone
                ]);
            }
        } else {
            return response()->json([
                'status' => 'not_registered',
                'phone' => $phone,
                'message' => 'Số điện thoại này chưa có tài khoản tại nhà hàng.'
            ]);
        }
    }


    function register()
    {
        return view("frontend.register");
    }
    function doregister(Request $request)
    {
        $request->validate([
            'fullname' => 'required|string|max:255',
            'username' => 'required|string|min:3|max:50|alpha_dash|unique:user,username',
            'phone' => 'required|string|digits_between:9,15|unique:user,phone',
            'email' => 'nullable|email|unique:user,email',
            'password' => 'required|string|min:6|max:50',
        ], [
            'fullname.required' => 'Vui lòng nhập họ và tên của bạn.',
            'username.required' => 'Vui lòng nhập tên người dùng.',
            'username.min' => 'Tên người dùng phải có ít nhất 3 ký tự.',
            'username.max' => 'Tên người dùng không được vượt quá 50 ký tự.',
            'username.alpha_dash' => 'Tên người dùng chỉ bao gồm chữ cái, số, dấu gạch nối và gạch dưới (không khoảng trắng).',
            'username.unique' => 'Tên người dùng này đã tồn tại, vui lòng chọn tên khác.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.digits_between' => 'Số điện thoại phải có từ 9 đến 15 chữ số.',
            'phone.unique' => 'Số điện thoại này đã được đăng ký tài khoản.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email này đã tồn tại trong hệ thống.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.max' => 'Mật khẩu không được vượt quá 50 ký tự.',
        ]);

        $phone = preg_replace('/[^0-9]/', '', $request->phone);

        $user = new User();
        $user->fullname = trim($request->fullname);
        $user->username = trim($request->username);
        $user->phone = $phone;
        $user->email = !empty($request->email) ? trim($request->email) : null;
        $user->password = Hash::make($request->password);
        $user->gender = 'Khác';
        $user->image = 'non_image.png';
        $user->roles = 'customer';
        $user->address = null;
        $user->created_by = Auth::id() ?? 1;
        $user->status = 1;

        $user->save();

        // Tự động đăng nhập cho khách hàng ngay sau khi đăng ký
        Auth::loginUsingId($user->id);
        session()->put('user_site', $user);

        return redirect()->route('site.home')->with('success', 'Đăng ký thành viên thành công! Chào mừng ' . $user->fullname . ' đến với CIVILIZE.');
    }


    public function profile()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('site.login')->with('error', 'Bạn cần đăng nhập để xem trang cá nhân.');
        }
        return view('frontend.profile', compact('user'));
    }

    public function syncTableSessionForUser(User $user): void
    {
        // Chỉ sync nếu không phải đang ở luồng quét QR (không có is_table_order)
        if (session()->has('is_table_order')) {
            return;
        }

        $activeOrder = Order::whereNull('deleted_at')
            ->whereIn('status', [0, 1])
            ->whereNotNull('table_id')
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('phone', $user->phone);
            })
            ->with('table')
            ->latest('created_at')
            ->first();

        if ($activeOrder && $activeOrder->table) {
            session([
                'table_id' => $activeOrder->table_id,
                'table_name' => $activeOrder->table->name
            ]);
        } else {
            session()->forget('table_id');
            session()->forget('table_name');
        }
    }



    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('site.login')->with('error', 'Bạn cần đăng nhập để cập nhật thông tin!');
        }

        $request->validate([
            'fullname' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:500',
            'gender' => 'nullable|in:0,1',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user->fullname = $request->fullname;
        $user->address = $request->address;
        $user->phone = $request->phone;
        $user->gender = $request->gender;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = date('YmdHis') . '.' . $file->extension();
            $file->move(public_path('images/user'), $filename);
            $user->image = $filename;
        }

        $user->save();

        return redirect()->route('site.profile')->with('success', 'Thông tin đã được cập nhật!');
    }

    public function logout()
    {
        $userId = Auth::id();
        session()->forget("cart_$userId");
        session()->forget('table_id');
        session()->forget('table_name');
        Auth::logout();
        return redirect()->route(route: 'site.login')->with('success', 'Đăng xuất thành công!');
    }
    public function forgotPassword()
    {
        return view('frontend.forgot_password');
    }

    // public function sendOtp(Request $request)
    // {
    //     $request->validate([
    //         'email' => 'required|email|exists:user,email',
    //     ]);

    //     $user = User::where('email', $request->email)->first();

    //     $otpCode = rand(100000, 999999);
    //     $user->otp_code = $otpCode;
    //     $user->otp_expires_at = Carbon::now()->addMinutes(10);
    //     $user->save();

    //     try {
    //         Mail::raw("Mã OTP của bạn là: $otpCode", function ($message) use ($user) {
    //             $message->to($user->email)
    //                 ->subject('Mã OTP xác thực');
    //         });
    //         return view('frontend.verify_otp', ['email' => $user->email])->with('success', 'OTP đã được gửi đến email của bạn.');
    //     } catch (\Exception $e) {
    //         \Log::error('Lỗi gửi email: ' . $e->getMessage());
    //         return back()->with('error', 'Không thể gửi OTP. Vui lòng thử lại sau.');
    //     }
    // }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|exists:user,phone',
        ]);

        /** @var \App\Models\User $user */
        $user = User::where('phone', $request->phone)->first();
        $otpCode = rand(100000, 999999);
        $user->otp_code = $otpCode;
        $user->otp_expires_at = Carbon::now()->addMinutes(10);
        $user->save();

        // Tạm thời hiển thị OTP trực tiếp để test (sau này thay bằng gửi SMS)
        return view('frontend.verify_otp', [
            'phone' => $user->phone,
            'otp_code' => $otpCode,
            'otp_display' => $otpCode
        ]);
    }



    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|exists:user,phone',
            'otp_code' => 'required|numeric',
        ]);

        /** @var \App\Models\User $user */
        $user = User::where('phone', $request->phone)->first();

        if ($user->otp_code !== $request->otp_code) {
            return back()->with('error', 'Mã OTP không đúng.');
        }

        if (Carbon::now()->greaterThan($user->otp_expires_at)) {
            return back()->with('error', 'Mã OTP đã hết hạn.');
        }

        session()->put('reset_password_phone', $user->phone);
        return redirect()->route('site.show_reset_password')->with('success', 'OTP xác thực thành công. Vui lòng đặt lại mật khẩu.');

    }
    public function showResetPasswordForm()
    {
        return view('frontend.reset_password');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|confirmed|min:6',
        ]);

        $phone = session()->get('reset_password_phone');

        if (!$phone) {
            return redirect()->route('site.forgot_password')->with('error', 'Không có yêu cầu đặt lại mật khẩu nào.');
        }

        /** @var \App\Models\User $user */
        $user = User::where('phone', $phone)->first();
        $user->password = Hash::make($request->password);
        $user->otp_code = null;
        $user->otp_expires_at = null;
        $user->save();

        session()->forget('reset_password_phone');
        return redirect()->route('site.login')->with('success', 'Mật khẩu đã được đặt lại thành công.');
    }

}
