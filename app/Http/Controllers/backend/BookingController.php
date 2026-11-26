<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Table;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Schema;

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

        $tables = Table::with('floor')->orderBy('sort_order')->orderBy('id')->get();

        return view('backend.booking.edit', compact('booking', 'tables'));
    }

    public function update(Request $request, $id)
    {
        $booking = Booking::find($id);
        if (!$booking) {
            return redirect()->route('admin.booking.index')->with('error', 'Đặt bàn không tồn tại');
        }

        $request->validate([
            'status' => 'required|integer|min:1|max:4',
            'table_id' => 'nullable|exists:tables,id',
        ]);

        $booking->status = $request->status;
        if (Schema::hasColumn('bookings', 'table_id')) {
            $booking->table_id = $request->filled('table_id') ? (int)$request->table_id : null;
        }
        $booking->save();

        // Bắn thông báo cập nhật kết quả đặt bàn cho khách hàng
        try {
            NotificationService::notifyBookingStatusUpdated($booking);
        } catch (\Exception $e) {
            \Log::error('Lỗi gửi notification cập nhật đặt bàn: ' . $e->getMessage());
        }

        return redirect()->route('admin.booking.index')->with('success', 'Cập nhật trạng thái thành công');
    }
    public function detail($id)
    {
        $booking = Booking::findOrFail($id);

        return view('backend.booking.bookingdetail', compact('booking'));
    }

}
