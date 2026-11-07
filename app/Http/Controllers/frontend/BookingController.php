<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Mail\BookingNotification;
use App\Services\NotificationService;
use Mail;

class BookingController extends Controller
{
    public function index()
    {
        return view('frontend.booking');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'phone' => ['required', 'regex:/^0[35789][0-9]{8}$/'],
            'number_of_people' => 'required|integer|min:1|max:50',
            'booking_date' => 'required|date|after_or_equal:today|before_or_equal:+60 days',
            'booking_time' => ['required', 'string', 'regex:/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/'],
            'note' => 'nullable|string|max:500',
        ], [
            'booking_date.after_or_equal' => 'Ngày đặt bàn không thể ở trong quá khứ.',
            'booking_date.before_or_equal' => 'Nhà hàng chỉ nhận đặt bàn trước tối đa 60 ngày.',
            'phone.regex' => 'Số điện thoại không đúng định dạng di động Việt Nam (gồm 10 số, bắt đầu bằng 03, 05, 07, 08, 09).',
            'number_of_people.max' => 'Số lượng khách trực tuyến tối đa 50 người. Với đoàn lớn hơn, quý khách vui lòng liên hệ hotline để được phục vụ.',
            'booking_time.regex' => 'Định dạng giờ đặt bàn không hợp lệ (HH:mm).',
        ]);

        // Tạo đơn đặt bàn an toàn, chống mass-assignment
        $booking = Booking::create([
            'name' => strip_tags(trim($request->name)),
            'email' => filter_var(trim($request->email), FILTER_SANITIZE_EMAIL),
            'phone' => preg_replace('/[^0-9]/', '', $request->phone),
            'number_of_people' => (int)$request->number_of_people,
            'booking_date' => $request->booking_date,
            'booking_time' => $request->booking_time,
            'note' => strip_tags(trim($request->note ?? '')),
            'status' => 1, // 1: Chờ xác nhận
        ]);

        // Bắn thông báo thời gian thực cho Admin, Nhân viên và Khách hàng
        try {
            NotificationService::notifyNewBooking($booking);
        } catch (\Exception $e) {
            \Log::error('Lỗi gửi notification đặt bàn: ' . $e->getMessage());
        }

        // Gửi email xác nhận an toàn (ưu tiên email tài khoản nếu đã đăng nhập)
        try {
            $targetEmail = \Illuminate\Support\Facades\Auth::check() 
                ? (\Illuminate\Support\Facades\Auth::user()->email ?: $booking->email)
                : $booking->email;

            if (filter_var($targetEmail, FILTER_VALIDATE_EMAIL)) {
                Mail::to($targetEmail)->send(new BookingNotification($booking));
            }
        } catch (\Exception $e) {
            \Log::error('Lỗi gửi email đặt bàn: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Đặt bàn thành công! Nhà hàng sẽ liên hệ sớm để xác nhận giữ chỗ cho quý khách.');
    }
}
