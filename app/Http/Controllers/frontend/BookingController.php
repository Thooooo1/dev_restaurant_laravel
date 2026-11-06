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
            'name' => 'required|max:100',
            'email' => 'required|email|max:100',
            'phone' => 'required|max:15',
            'number_of_people' => 'required|integer|min:1',
            'booking_date' => 'required|date',
            'booking_time' => 'required',
            'note' => 'nullable|string',
        ]);

        $booking = Booking::create($request->all());

        // Bắn thông báo thời gian thực cho Admin, Nhân viên và Khách hàng
        try {
            NotificationService::notifyNewBooking($booking);
        } catch (\Exception $e) {
            \Log::error('Lỗi gửi notification đặt bàn: ' . $e->getMessage());
        }

        try {
            Mail::to($request->email)->send(new BookingNotification($booking));
        } catch (\Exception $e) {
            \Log::error('Lỗi gửi email đặt bàn: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Đặt bàn thành công!');
    }
}
