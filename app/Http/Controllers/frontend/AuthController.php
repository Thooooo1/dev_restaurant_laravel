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
        $redirectUrl = $request->input('redirect') ?: session()->pull('url.intended');

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
        $oldSessionId = session()->getId();
        Auth::loginUsingId($user->id);
        session()->put('user_site', $user);

        // Đồng bộ giỏ hàng khách vãng lai (guest) vào tài khoản người dùng
        \App\Http\Controllers\frontend\CartController::mergeGuestCartToUser($user->id, $oldSessionId);
        $request->session()->regenerate();
        
        // Nếu là nhân viên hoặc admin, xóa triệt để session bàn để không bị dính chế độ khách tại bàn
        if ($user->isAdmin() || $user->isEmployee()) {
            session()->forget([
                'table_id',
                'table_order_id',
                'table_name',
                'table_floor_name',
                'is_table_order',
                'dining_mode',
                'customer_phone',
                'customer_name'
            ]);
        }
        
        // Chống lỗ hổng Open Redirect: Chỉ chấp nhận đường dẫn nội bộ hợp lệ
        if ($redirectUrl && is_string($redirectUrl)) {
            if (str_starts_with($redirectUrl, '/') && !str_starts_with($redirectUrl, '//') && !str_contains($redirectUrl, '\\') && !str_contains($redirectUrl, ':')) {
                return redirect($redirectUrl);
            }
        }
        return redirect()->route('site.home');
    }

    public function checkPhone(Request $request)
    {
        $phone = preg_replace('/[^0-9]/', '', $request->phone);
        
        if (empty($phone)) {
            return response()->json(['status' => 'error', 'message' => 'Vui lòng nhập số điện thoại!']);
        }

        // Priority 1: Check if user exists by phone
        $users = User::where('status', 1)->get();
        $user = null;
        foreach ($users as $u) {
            $dbPhone = preg_replace('/[^0-9]/', '', (string)$u->phone);
            if ($dbPhone === $phone) {
                $user = $u;
                break;
            }
        }

        // P0-03: Tuyệt đối không tự động đăng nhập Auth::login() khi chỉ nhập số điện thoại
        // Mọi tài khoản (kể cả khách hàng đang có đơn tại bàn) đều phải xác thực mật khẩu
        if ($user) {
            if ($user->roles !== 'customer' || !empty($user->password)) {
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
        }

        return response()->json([
            'status' => 'not_registered',
            'phone' => $phone,
            'message' => 'Số điện thoại này chưa có tài khoản tại nhà hàng.'
        ]);
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
        $oldSessionId = session()->getId();
        Auth::loginUsingId($user->id);
        session()->put('user_site', $user);

        // Đồng bộ giỏ hàng khách vãng lai (guest) vào tài khoản vừa tạo
        \App\Http\Controllers\frontend\CartController::mergeGuestCartToUser($user->id, $oldSessionId);
        $request->session()->regenerate();

        $redirectUrl = $request->input('redirect') ?: session()->pull('url.intended');
        // Chống lỗ hổng Open Redirect sau đăng ký: Chỉ chấp nhận đường dẫn nội bộ hợp lệ
        if ($redirectUrl && is_string($redirectUrl)) {
            if (str_starts_with($redirectUrl, '/') && !str_starts_with($redirectUrl, '//') && !str_contains($redirectUrl, '\\') && !str_contains($redirectUrl, ':')) {
                return redirect($redirectUrl)->with('success', 'Đăng ký thành viên thành công! Chào mừng ' . $user->fullname . ' đến với CIVILIZE.');
            }
        }

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
            'phone' => [
                'required',
                'string',
                'regex:/^(0[35789])[0-9]{8}$/',
                \Illuminate\Validation\Rule::unique('user', 'phone')->ignore($user->id),
            ],
            'address' => 'nullable|string|max:500',
            'gender' => 'nullable|in:0,1',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'phone.regex' => 'Số điện thoại không đúng định dạng (10 chữ số, đầu số Việt Nam 03, 05, 07, 08, 09).',
            'phone.unique' => 'Số điện thoại này đã được sử dụng bởi một tài khoản khác.',
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
        return redirect()->route('site.login');
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

        $phone = preg_replace('/[^0-9]/', '', (string)$request->phone);

        // V4-P1-39: Rate limit OTP theo số điện thoại (chống spam từ nhiều phiên khác nhau)
        $throttleKey = 'otp_request_throttle_' . $phone;
        $attempts = (int) \Illuminate\Support\Facades\Cache::get($throttleKey, 0);
        if ($attempts >= 5) {
            return back()->with('error', 'Bạn đã yêu cầu gửi OTP quá 5 lần cho số điện thoại này. Vui lòng thử lại sau 15 phút.');
        }

        // Rate limit OTP theo IP
        $ipThrottleKey = 'otp_request_ip_' . $request->ip();
        $ipAttempts = (int) \Illuminate\Support\Facades\Cache::get($ipThrottleKey, 0);
        if ($ipAttempts >= 10) {
            return back()->with('error', 'Địa chỉ IP của bạn đã gửi quá nhiều yêu cầu OTP. Vui lòng thử lại sau 15 phút.');
        }

        \Illuminate\Support\Facades\Cache::put($throttleKey, $attempts + 1, now()->addMinutes(15));
        \Illuminate\Support\Facades\Cache::put($ipThrottleKey, $ipAttempts + 1, now()->addMinutes(15));

        /** @var \App\Models\User $user */
        $user = User::where('phone', $request->phone)->first();

        // CHẶN BẢO MẬT A2: Tuyệt đối không cho phép reset mật khẩu Admin / Nhân viên qua form OTP công khai
        if ($user && $user->roles !== 'customer') {
            return back()->with('error', 'Chức năng quên mật khẩu qua OTP chỉ áp dụng cho tài khoản Khách hàng. Quản trị viên/Nhân viên vui lòng liên hệ bộ phận kỹ thuật.');
        }

        // Tạo OTP bảo mật bằng random_int (chuẩn mật mã)
        $otpCode = random_int(100000, 999999);
        $expiresAt = Carbon::now()->addMinutes(10);

        if (\Illuminate\Support\Facades\Schema::hasColumn('user', 'otp_code')) {
            $user->otp_code = $otpCode;
            $user->otp_expires_at = $expiresAt;
            $user->save();
        } else {
            \Illuminate\Support\Facades\Cache::put('otp_' . $user->phone, [
                'code' => (string)$otpCode,
                'expires_at' => $expiresAt,
            ], 600);
        }

        session()->put('otp_attempts', 0);

        // Gửi OTP qua email (out-of-band) nếu tài khoản có email
        if ($user->email) {
            try {
                \Illuminate\Support\Facades\Mail::raw("Mã OTP xác thực đặt lại mật khẩu của bạn tại CIVILIZE Restaurant là: {$otpCode}. Mã có hiệu lực trong vòng 10 phút.", function ($msg) use ($user) {
                    $msg->to($user->email)->subject('[CIVILIZE] Mã OTP Đặt Lại Mật Khẩu');
                });
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Không thể gửi email OTP: ' . $e->getMessage());
            }
        }

        // P0-01 & V4-P1-39: Chỉ hiển thị nhãn mô phỏng SMS OTP khi chạy môi trường local/testing kín VÀ không phải production
        $isLocalDemo = (!app()->isProduction() && app()->environment('local', 'testing') && config('payment.mode') === 'demo');
        $viewData = [
            'phone' => $user->phone,
        ];
        if ($isLocalDemo) {
            $viewData['otp_display'] = (string)$otpCode;
        }

        return view('frontend.verify_otp', $viewData);
    }



    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|exists:user,phone',
            'otp_code' => 'required|numeric',
        ]);

        /** @var \App\Models\User $user */
        $user = User::where('phone', $request->phone)->first();

        if ($user && $user->roles !== 'customer') {
            return redirect()->route('site.login')->with('error', 'Thao tác không được phép với tài khoản quản trị.');
        }

        $phone = preg_replace('/[^0-9]/', '', (string)$request->phone);

        // V4-P1-39: Giới hạn số lần thử OTP chống brute-force qua Cache theo số điện thoại (bảo vệ trên mọi phiên)
        $verifyThrottleKey = 'otp_verify_attempts_' . $phone;
        $attempts = (int) \Illuminate\Support\Facades\Cache::get($verifyThrottleKey, 0);
        if ($attempts >= 5) {
            return redirect()->route('site.forgot_password')->with('error', 'Bạn đã nhập sai mã OTP quá 5 lần. Vui lòng yêu cầu mã mới.');
        }

        $storedCode = null;
        $expiresAt = null;

        if (\Illuminate\Support\Facades\Schema::hasColumn('user', 'otp_code')) {
            $storedCode = (string)$user->otp_code;
            $expiresAt = $user->otp_expires_at ? Carbon::parse($user->otp_expires_at) : null;
        } else {
            $cached = \Illuminate\Support\Facades\Cache::get('otp_' . $user->phone);
            if ($cached) {
                $storedCode = (string)$cached['code'];
                $expiresAt = Carbon::parse($cached['expires_at']);
            }
        }

        if ($storedCode === null || $storedCode !== (string)$request->otp_code) {
            \Illuminate\Support\Facades\Cache::put($verifyThrottleKey, $attempts + 1, now()->addMinutes(15));
            session()->put('otp_attempts', $attempts + 1);
            $remaining = 5 - ($attempts + 1);
            return back()->with('error', "Mã OTP không đúng. Bạn còn {$remaining} lần thử.");
        }

        if ($expiresAt && Carbon::now()->greaterThan($expiresAt)) {
            return back()->with('error', 'Mã OTP đã hết hạn.');
        }

        // V4-P1-39: Hủy ngay mã OTP đã xác thực thành công (Single-use)
        if (\Illuminate\Support\Facades\Schema::hasColumn('user', 'otp_code')) {
            $user->otp_code = null;
            $user->otp_expires_at = null;
            $user->save();
        }
        \Illuminate\Support\Facades\Cache::forget('otp_' . $user->phone);
        \Illuminate\Support\Facades\Cache::forget($verifyThrottleKey);
        session()->forget('otp_attempts');
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
        if (\Illuminate\Support\Facades\Schema::hasColumn('user', 'otp_code')) {
            $user->otp_code = null;
            $user->otp_expires_at = null;
        } else {
            \Illuminate\Support\Facades\Cache::forget('otp_' . $user->phone);
        }
        $user->save();

        session()->forget('reset_password_phone');
        return redirect()->route('site.login')->with('success', 'Mật khẩu đã được đặt lại thành công.');
    }

}
