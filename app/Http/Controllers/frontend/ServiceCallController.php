<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServiceCall;
use App\Models\Table;
use App\Models\Order;
use App\Models\Floor;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ServiceCallController extends Controller
{
    /**
     * Khách hàng gửi yêu cầu gọi nhân viên phục vụ
     */
    public function store(Request $request)
    {
        // 1. Lấy table_id từ request hoặc từ session (khi đã quét mã QR tại bàn)
        $tableId = $request->input('table_id') ?: session('table_id');

        if (!$tableId) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng chọn số bàn của bạn để nhân viên biết bàn cần phục vụ.'
            ], 422);
        }

        $table = Table::with('floor')->find($tableId);
        if (!$table) {
            return response()->json([
                'success' => false,
                'message' => 'Bàn không tồn tại trên hệ thống.'
            ], 404);
        }

        $callType = $request->input('call_type', 'call_waiter');
        $types = ServiceCall::TYPES;
        $callTypeLabel = $request->input('call_type_label') ?: ($types[$callType]['label'] ?? 'Gọi nhân viên phục vụ');
        $note = $request->input('note');

        // Tìm active order nếu có
        $activeOrder = Order::where('table_id', $tableId)
            ->whereNull('deleted_at')
            ->whereIn('status', [0, 1])
            ->first();

        // Chống spam: Nếu bàn này vừa gửi cùng loại yêu cầu trong vòng 30 giây thì ghi nhận mà không tạo lặp
        $recentCall = ServiceCall::where('table_id', $tableId)
            ->where('call_type', $callType)
            ->where('status', ServiceCall::STATUS_PENDING)
            ->where('created_at', '>=', Carbon::now()->subSeconds(30))
            ->first();

        if ($recentCall) {
            return response()->json([
                'success' => true,
                'message' => 'Nhân viên đã nhận được yêu cầu của bạn từ ' . $table->name . ' và đang đến ngay!',
                'already_pending' => true,
                'call_id' => $recentCall->id
            ]);
        }

        // Tạo yêu cầu gọi phục vụ mới
        $serviceCall = ServiceCall::create([
            'table_id' => $tableId,
            'order_id' => $activeOrder ? $activeOrder->id : null,
            'customer_name' => $request->input('customer_name') ?: (Auth::check() ? Auth::user()->name : ($activeOrder ? $activeOrder->name : null)),
            'phone' => $request->input('phone') ?: (Auth::check() ? Auth::user()->phone : ($activeOrder ? $activeOrder->phone : null)),
            'call_type' => $callType,
            'call_type_label' => $callTypeLabel,
            'note' => $note,
            'status' => ServiceCall::STATUS_PENDING,
        ]);

        try {
            NotificationService::notifyServiceCall($serviceCall);
        } catch (\Exception $e) {
            \Log::error('Lỗi bắn notification service call: ' . $e->getMessage());
        }

        $tableNameWithFloor = $table->floor ? "{$table->name} ({$table->floor->name})" : $table->name;

        return response()->json([
            'success' => true,
            'message' => "Đã gửi yêu cầu từ {$tableNameWithFloor}! Nhân viên CIVILIZE sẽ có mặt ngay.",
            'call' => [
                'id' => $serviceCall->id,
                'table_name' => $table->name,
                'type_label' => $callTypeLabel,
            ]
        ]);
    }

    /**
     * Lấy danh sách các tầng và bàn để khách chọn nhanh khi gọi phục vụ
     */
    public function getTables(Request $request)
    {
        $floors = Floor::with(['tables' => function ($query) {
            $query->orderBy('sort_order', 'ASC')->orderBy('id', 'ASC');
        }])->orderBy('order', 'ASC')->orderBy('id', 'ASC')->get();

        return response()->json([
            'success' => true,
            'floors' => $floors,
            'current_table_id' => session('table_id')
        ]);
    }
}
