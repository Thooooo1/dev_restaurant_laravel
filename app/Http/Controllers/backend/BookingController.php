<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $statusFilter = $request->query('status', 5);
        $perPage = 6;

        $bookings = Booking::
            when($statusFilter != 5, function ($query) use ($statusFilter) {
                return $query->where('status', $statusFilter);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return view('backend.booking.index', compact('bookings', 'statusFilter'));
    }

    public function edit($id)
    {
        $booking = Booking::find($id);
        if (!$booking) {
            return redirect()->route('admin.booking.index')->with('error', 'Đặt bàn không tồn tại');
        }

        return view('backend.booking.edit', compact('booking'));
    }

    public function update(Request $request, $id)
    {
        $booking = Booking::find($id);
        if (!$booking) {
            return redirect()->route('admin.booking.index')->with('error', 'Đặt bàn không tồn tại');
        }

        $request->validate([
            'status' => 'required|integer|min:1|max:3',
        ]);

        $booking->status = $request->status;
        $booking->save();

        return redirect()->route('admin.booking.index')->with('success', 'Cập nhật trạng thái thành công');
    }
    public function detail($id)
    {
        $booking = Booking::findOrFail($id);

        return view('backend.booking.bookingdetail', compact('booking'));
    }

}
