<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Table;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class TableOrderController extends Controller
{
    public function scan($id)
    {
        // 1. Kiểm tra đăng nhập
        if (!Auth::check()) {
            return redirect()->route('site.login', ['redirect' => route('site.table.scan', $id)])
                ->with('error', 'Vui lòng đăng nhập để gọi món tại bàn.');
        }

        // 2. Kiểm tra bàn tồn tại
        $table = Table::find($id);
        if (!$table) {
            return redirect()->route('site.home')->with('error', 'Bàn không tồn tại.');
        }

        $user = Auth::user();

        // 3. Kiểm tra bàn có đang phục vụ khách khác không
        // Lấy đơn hàng gần nhất đang active (chưa hoàn thành/hủy) tại bàn này
        // Status: 0=Pending, 1=Confirmed, 2=Completed, 3=Cancelled
        // Giả sử 0, 1 là đang phục vụ.
        $activeOrder = Order::where('table_id', $id)
            ->whereIn('status', [0, 1])
            ->first();

        if ($activeOrder) {
            // Nếu có đơn hàng, kiểm tra xem có phải của User này không
            // Kiểm tra User ID
            $isOwner = false;
            
            if ($activeOrder->user_id == $user->id) {
                $isOwner = true;
            }
            
            // Kiểm tra Số điện thoại (theo yêu cầu user)
            // "số điện thoại có khớp với số đt của người dùng móc với ID_user trong bảng Order không"
            if (!$isOwner && $activeOrder->phone == $user->phone) {
                $isOwner = true;
            }

            if (!$isOwner) {
                return redirect()->route('site.home')
                    ->with('error', 'Bàn này đang được sử dụng bởi khách hàng khác (Số điện thoại không khớp).');
            }
        }

        // 4. Lưu thông tin bàn vào Session
        session(['table_id' => $id]);
        session(['table_name' => $table->name]);

        // 5. Chuyển hướng đến trang thực đơn
        return redirect()->route('site.product')->with('success', 'Bạn đang ngồi tại ' . $table->name . '. Hãy chọn món!');
    }
}
