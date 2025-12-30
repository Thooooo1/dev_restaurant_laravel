<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;

use Carbon\Carbon;

use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    function login()
    {
        return view("frontend.login");
    }
    function dologin(Request $request)
    {
        $username = $request->email;
        $password = $request->password;
        $args = [
            ['status', '=', 1],
        ];

        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $args[] = ['email', '=', $username];
        } else {
            $args[] = ['username', '=', $username];
        }
        $user = User::where($args)->first();
        if ($user != null) {
            if (Hash::check($password, $user->password)) {
                session()->put('user_site', $user);
                Auth::login($user);
                return redirect()->route('site.home')->with('success', 'Đăng nhập thành công!');
                // echo"thanh cong";
            } else {
                return redirect()->route('site.login')->with('error', 'Mật khẩu không đúng!');
                // echo"mk sai ";
            }
        } else {
            return redirect()->route('site.login')->with('error', 'Tên đăng nhập hoặc email không tồn tại!');
            // echo"khong dung tk hoac email";
        }
    }


    function register()
    {
        return view("frontend.register");
    }
    function doregister(Request $request)
    {


        $user = new User();
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = date('YmdHis') . '.' . $file->extension();
            $file->move(public_path('images/user'), $filename);
            $user->image = $filename;
        }

        $user->username = $request->username;
        $user->password = Hash::make($request->password);
        $user->fullname = $request->fullname;
        $user->gender = $request->gender;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->roles = 'customer';
        $user->address = $request->address;
        $user->created_by = Auth::id() ?? 1;
        $user->status = 1;

        $user->save();

        return redirect()->route('site.login')->with('success', 'Đăng ký thành công!');
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $email = $googleUser->getEmail();
            $fullname = $googleUser->getName();
            $googleId = $googleUser->getId();
            $picture = $googleUser->getAvatar();

            $user = User::where('google_id', $googleId)->orWhere('email', $email)->first();

            if ($user) {
                if (!$user->google_id) {
                    $user->google_id = $googleId;
                }

                $user->save();
            } else {
                $user = User::create([
                    'username' => 'google_' . $googleId,
                    'fullname' => $fullname,
                    'email' => $email,
                    'password' => Hash::make(Str::random(16)),
                    'image' => $picture,
                    'address' => 'Đang cập nhật',
                    'roles' => 'customer',
                    'google_id' => $googleId,
                    'gender' => 'Đang cập nhật',
                    'phone' => 'Đang cập nhật',
                    'created_by' => Auth::id() ?? 1,
                    'status' => 1,
                ]);
            }

            Auth::login($user);
            session()->put('user_site', $user);

            return redirect()->route('site.home')->with('success', 'Đăng nhập bằng Google thành công!');
        } catch (\Exception $e) {
            return redirect()->route('site.login')->with('error', 'Đăng nhập bằng Google thất bại! Vui lòng thử lại.');
        }
    }
    public function profile()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('site.login')->with('error', 'Bạn cần đăng nhập để xem trang cá nhân.');
        }
        return view('frontend.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

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
            'email' => 'required|email|exists:user,email',
        ]);

        $user = User::where('email', $request->email)->first();
        $otpCode = rand(100000, 999999);
        $user->otp_code = $otpCode;
        $user->otp_expires_at = Carbon::now()->addMinutes(10);
        $user->save();

        try {
            Mail::to($user->email)->send(new OtpMail($otpCode));
            return view('frontend.verify_otp', ['email' => $user->email])->with('success', 'OTP đã được gửi đến email của bạn.');
        } catch (\Exception $e) {
            \Log::error('Lỗi gửi email: ' . $e->getMessage());
            return back()->with('error', 'Không thể gửi OTP. Vui lòng thử lại sau.');
        }
    }



    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:user,email',
            'otp_code' => 'required|numeric',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user->otp_code !== $request->otp_code) {
            return back()->with('error', 'Mã OTP không đúng.');
        }

        if (Carbon::now()->greaterThan($user->otp_expires_at)) {
            return back()->with('error', 'Mã OTP đã hết hạn.');
        }

        session()->put('reset_password_email', $user->email);
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

        $email = session()->get('reset_password_email');

        if (!$email) {
            return redirect()->route('site.forgot_password')->with('error', 'Không có yêu cầu đặt lại mật khẩu nào.');
        }

        $user = User::where('email', $email)->first();
        $user->password = Hash::make($request->password);
        $user->otp_code = null;
        $user->otp_expires_at = null;
        $user->save();

        session()->forget('reset_password_email');
        return redirect()->route('site.login')->with('success', 'Mật khẩu đã được đặt lại thành công.');
    }

}
