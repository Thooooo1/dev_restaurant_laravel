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

        $orders = Order::whereNull('deleted_at')
            ->when($statusFilter != 5, function ($query) use ($statusFilter) {
                return $query->where('status', $statusFilter);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return view('backend.order.index', compact('orders', 'statusFilter'));
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
            'status' => 'required|integer|min:1|max:4',
        ]);

        $order->status = $request->status;
        $order->save();

        // Release merged tables if order is completed or cancelled
        if (!in_array($order->status, [0, 1])) {
             \App\Models\Table::where('locked_order_id', $order->id)->update(['locked_order_id' => null]);
        }

        return redirect()->route('admin.order.index')->with('success', 'Cập nhật trạng thái thành công');
    }

    public function detail($id)
    {
        $order = Order::with('orderDetails.product')->findOrFail($id);

        return view('backend.order.orderdetail', compact('order'));
    }

    public function print($id)
    {
        $order = Order::with(['orderDetails.product', 'table.floor'])->findOrFail($id);
        $paymentMethodText = 'Khác';
        if ((int) $order->payment_method === 1) {
            $paymentMethodText = 'Tiền mặt';
        } elseif ((int) $order->payment_method === 2) {
            $paymentMethodText = 'Chuyển khoản NH';
        }

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
