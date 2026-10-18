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
        // 1. Kiểm tra bàn tồn tại
        $table = Table::find($id);
        if (!$table) {
            return redirect()->route('site.home')->with('error', 'Bàn không tồn tại.');
        }

        // 2. Lưu table_id và is_table_order vào session (quan trọng, để biết đang ở bàn nào)
        session([
            'table_id' => $id,
            'is_table_order' => true
        ]);

        // 3. Hiển thị form nhập số điện thoại cho tất cả mọi người
        return view('frontend.table-scan', compact('table'));
    }

    // Xác thực số điện thoại và kiểm tra order
    public function verify(Request $request, $id)
    {
        $request->validate([
            'phone' => 'required|numeric|digits_between:10,15',
        ], [
            'phone.required' => 'Vui lòng nhập số điện thoại',
            'phone.numeric' => 'Số điện thoại chỉ được chứa các chữ số',
            'phone.digits_between' => 'Số điện thoại phải có từ 10 đến 15 chữ số',
        ]);

        // Kiểm tra bàn tồn tại
        $table = Table::find($id);
        if (!$table) {
            return redirect()->route('site.home')->with('error', 'Bàn không tồn tại.');
        }

        // Debug: Log để xem tìm được order không (bạn có thể xem trong storage/logs/laravel.log)
        \Log::info('Đang tìm order với: table_id = ' . $id . ', phone = ' . $request->phone);

        // Tìm order đang phục vụ của bàn này với số điện thoại khớp
        $activeOrder = Order::where('table_id', $id)
            ->where('phone', $request->phone)
            ->whereNull('deleted_at')
            ->whereIn('status', [0, 1]) // 0=Pending, 1=Confirmed (đang phục vụ)
            ->first();

        if (!$activeOrder) {
            \Log::info('Không tìm thấy order!');
            return redirect()->back()->with('error', 'Không tìm thấy đơn hàng đang phục vụ tại bàn này với số điện thoại bạn nhập. Vui lòng kiểm tra lại hoặc liên hệ lễ tân.');
        }

        \Log::info('Tìm thấy order: ' . $activeOrder->id);

        // 4. Lưu thông tin vào session (rất quan trọng, để CartController biết dùng order nào)
        session([
            'table_id' => $id, 
            'table_order_id' => $activeOrder->id,
            'table_name' => $table->name,
            'is_table_order' => true // FLAG QUAN TRỌNG: ĐỂ PHÂN BIỆT LÀ GỌI MÓN TẠI BÀN
        ]);

        // 5. Đăng nhập (nếu có user tương ứng với số điện thoại này)
        $user = \App\Models\User::where('phone', $request->phone)->first();
        if ($user && !Auth::check()) {
            Auth::login($user);
        }

        // 6. Vào thẳng trang gọi món (giỏ hàng)
        return redirect()->route('site.cart')->with('success', 'Xác thực thành công! Bạn đang ngồi tại ' . $table->name . '. Hãy chọn món!');
    }
    
    // Kết thúc gọi món (xóa session bàn ăn)
    public function endTableOrder()
    {
        // Xóa tất cả session liên quan đến bàn ăn
        session()->forget('table_id');
        session()->forget('table_order_id');
        session()->forget('table_name');
        session()->forget('is_table_order');
        
        return redirect()->route('site.home')->with('success', 'Kết thúc gọi món thành công!');
    }
}
