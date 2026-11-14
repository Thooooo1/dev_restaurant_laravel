<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $statusFilter = $request->query('status', 5);
        $perPage = 6;

        // Chỉ hiển thị đơn hàng giao tận nơi & mang đi (loại trừ các đơn ăn trực tiếp tại bàn)
        $orders = Order::whereNull('deleted_at')
            ->where(function ($query) {
                $query->whereNull('table_id')
                      ->orWhere('table_id', 0);
            })
            ->when($statusFilter != 5, function ($query) use ($statusFilter) {
                return $query->where('status', $statusFilter);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return view('backend.order.index', compact('orders', 'statusFilter'));
    }

    public function export(Request $request)
    {
        if (!\Illuminate\Support\Facades\Auth::user() || !\Illuminate\Support\Facades\Auth::user()->isAdmin()) {
            return redirect()->route('admin.order.index')->with('error', 'Chỉ Quản trị viên mới có quyền xuất dữ liệu đơn hàng!');
        }
        $status = $request->query('status');
        return \App\Services\ExcelExporter::download(new \App\Exports\OrderExport($status, true), 'danh-sach-don-hang.xls');
    }

    public function edit($id)
    {
        $order = Order::find($id);
        if (!$order) {
            return redirect()->route('admin.order.index')->with('error', 'Đơn hàng không tồn tại');
        }

        return view('backend.order.edit', compact('order'));
    }
    public function update(Request $request, $id)
    {
        $order = Order::find($id);
        if (!$order) {
            return redirect()->route('admin.order.index')->with('error', 'Đơn hàng không tồn tại');
        }

        $request->validate([
            'status' => 'required|integer|min:0|max:4',
        ]);

        $order->status = $request->status;
        if ($request->has('payment_method')) {
            $order->payment_method = $request->payment_method;
        }
        $order->save();

        // Release merged tables if order is completed or cancelled
        if (!in_array((int)$order->status, [\App\Enums\OrderStatus::PENDING, \App\Enums\OrderStatus::PROCESSING])) {
             \App\Models\Table::where('locked_order_id', $order->id)->update(['locked_order_id' => null]);
        }

        return redirect()->back()->with('success', 'Cập nhật thông tin đơn hàng thành công');
    }

    public function updateDelivery(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'shipper_name' => 'nullable|string|max:255',
            'shipper_phone' => 'nullable|string|max:50',
            'delivery_status' => 'required|string|in:waiting,picked_up,delivering,delivered,failed',
            'delivery_note' => $request->delivery_status === 'failed' ? 'required|string|max:1000' : 'nullable|string|max:1000',
        ], [
            'delivery_note.required' => 'Vui lòng nhập lý do giao hàng thất bại vào ghi chú.',
        ]);

        $fromDeliveryStatus = $order->delivery_status;
        $fromOrderStatus = (int)$order->status;

        try {
            $order->applyDeliveryStatusTransition($request->delivery_status, $request->delivery_note);
        } catch (\DomainException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }

        $order->shipper_name = $request->shipper_name;
        $order->shipper_phone = $request->shipper_phone;
        $order->delivery_note = $request->delivery_note;
        $order->updated_by = \Illuminate\Support\Facades\Auth::id();
        $order->save();

        // Ghi nhật ký tiến trình giao hàng (Audit Trail)
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('order_delivery_logs')) {
                \App\Models\OrderDeliveryLog::create([
                    'order_id' => $order->id,
                    'user_id' => \Illuminate\Support\Facades\Auth::id(),
                    'from_delivery_status' => $fromDeliveryStatus,
                    'to_delivery_status' => $order->delivery_status,
                    'from_order_status' => $fromOrderStatus,
                    'to_order_status' => (int)$order->status,
                    'shipper_name' => $order->shipper_name,
                    'shipper_phone' => $order->shipper_phone,
                    'note' => $order->delivery_note,
                    'created_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Lỗi lưu nhật ký giao hàng: ' . $e->getMessage());
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật tiến trình giao hàng thành công!',
                'delivery_status' => $order->delivery_status,
                'delivery_status_label' => $order->delivery_status_label,
                'order_status' => (int)$order->status,
                'order_status_label' => $order->status_label,
            ]);
        }

        return redirect()->back()->with('success', 'Cập nhật tiến trình giao hàng thành công!');
    }

    public function detail($id)
    {
        $order = Order::with(['orderDetails.product.category', 'table.floor', 'user', 'deliveryLogs.user'])->findOrFail($id);

        return view('backend.order.orderdetail', compact('order'));
    }

    public function print($id)
    {
        $order = Order::with(['orderDetails.product', 'table.floor'])->findOrFail($id);
        $paymentInfo = $order->payment_method_info;
        $paymentMethodText = $paymentInfo['name'];

        $createdAt = $order->created_at ? $order->created_at->format('H:i d/m/Y') : '';
        $customerName = $order->name ?? 'Khách lẻ';
        $customerPhone = $order->phone ?? '---';
        $floorName = $order->table->floor->name ?? '';
        $tableName = $order->table->name ?? '';

        $rows = '';
        foreach ($order->orderDetails as $idx => $detail) {
            $name = $detail->product->name ?? 'N/A';
            $price = (int) ($detail->price ?? 0);
            $qty = (int) ($detail->qty ?? 0);
            $amount = (int) ($detail->amount ?? ($price * $qty));
            $rows .= '<tr>'
                . '<td class="c">' . ($idx + 1) . '</td>'
                . '<td class="l">' . e($name) . '</td>'
                . '<td class="q">' . $qty . '</td>'
                . '<td class="p">' . number_format($price, 0, ',', '.') . ' đ</td>'
                . '<td class="a">' . number_format($amount, 0, ',', '.') . ' đ</td>'
                . '</tr>';
        }

        $total = (int) ($order->total_price ?? $order->orderDetails->sum('amount'));

        $html = '<!doctype html><html lang="vi"><head>'
            . '<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
            . '<title>In hóa đơn</title>'
            . '<style>'
            . '@media print { @page { size: 80mm auto; margin: 3mm; } }'
            . 'html, body { padding:0; margin:0; }'
            . 'body { font-family: Arial, sans-serif; font-size: 12px; width:80mm; }'
            . '.bill { width:74mm; margin:0 auto; }'
            . '.title { text-align:center; font-size:16px; font-weight:700; margin:4px 0 6px; }'
            . '.meta { font-size:11px; margin-bottom:4px; }'
            . '.line { border-top:1px dashed #000; margin:6px 0; }'
            . 'table { width:100%; border-collapse:collapse; font-size:11px; }'
            . 'th, td { padding:4px 0; vertical-align:top; }'
            . 'thead th { border-top:1px dashed #000; border-bottom:1px dashed #000; font-weight:700; }'
            . 'tbody tr td { border-bottom:1px dashed #000; }'
            . '.c { text-align:center; width:24px; }'
            . '.l { text-align:left; }'
            . '.q { text-align:right; white-space:nowrap; width:32px; }'
            . '.p { text-align:right; white-space:nowrap; width:62px; }'
            . '.a { text-align:right; white-space:nowrap; width:64px; }'
            . '.sum { font-weight:700; }'
            . '.footer { text-align:center; font-size:11px; margin-top:8px; }'
            . '</style>'
            . '<script>window.onload=function(){window.focus();window.print();setTimeout(function(){window.close();},200);};</script>'
            . '</head><body>'
            . '<div class="bill">'
            . '<div class="title">HÓA ĐƠN</div>'
            . '<div class="meta">' . ($floorName ? 'Tầng: <strong>' . e($floorName) . '</strong> | ' : '') . 'Bàn: <strong>' . e($tableName) . '</strong></div>'
            . '<div class="meta">Mã HĐ: <strong>#' . e((string) $order->id) . '</strong></div>'
            . '<div class="meta">Khách: <strong>' . e($customerName) . '</strong> | SĐT: <strong>' . e($customerPhone) . '</strong></div>'
            . '<div class="meta">Phương thức thanh toán: <strong>' . e($paymentMethodText) . '</strong></div>'
            . '<div class="meta">Thời gian: <strong>' . e($createdAt) . '</strong></div>'
            . '<div class="line"></div>'
            . '<table><thead><tr><th class="c">#</th><th class="l">Tên món ăn</th><th class="q">SL</th><th class="p">Đơn giá</th><th class="a">Thành tiền</th></tr></thead>'
            . '<tbody>' . $rows . '</tbody>'
            . '<tfoot><tr><td colspan="4" class="l sum">Tổng cộng</td><td class="a sum">' . number_format($total, 0, ',', '.') . ' đ</td></tr></tfoot>'
            . '</table>'
            . '<div class="footer">Cảm ơn quý khách!</div>'
            . '</div></body></html>';

        return response($html)->header('Content-Type', 'text/html; charset=UTF-8');
    }
    
}
