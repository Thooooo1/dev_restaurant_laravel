<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Floor;
use App\Models\Table;
use App\Models\Order;
use App\Models\Orderdetail;
use App\Models\Product;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TableController extends Controller
{
    public function index(Request $request)
    {
        // Gộp tính năng Quản lý bàn & phòng vào Bán hàng (POS)
        return redirect()->route('admin.table-order.index');
    }

    /**
     * API Lấy danh sách món ăn hiện tại của bàn (dành cho modal Xem Món & Thanh toán)
     */
    public function getTableDishes($id)
    {
        $table = Table::with('floor')->find($id);
        if (!$table) {
            return response()->json([
                'success' => false,
                'message' => 'Bàn không tồn tại trên hệ thống.'
            ], 404);
        }

        $order = Order::where('table_id', $table->id)
            ->whereNull('deleted_at')
            ->whereIn('status', [0, 1])
            ->with(['orderDetails.product', 'user'])
            ->orderBy('id', 'DESC')
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'is_empty' => true,
                'message' => 'Bàn này hiện đang trống, chưa có món ăn nào được gọi.',
                'table' => [
                    'id' => $table->id,
                    'name' => $table->name,
                    'floor_name' => $table->floor->name ?? 'Khu vực chung',
                    'seats' => $table->seats,
                ],
                'order_url' => route('admin.table-order.order', $table->id),
            ]);
        }

        // Chuẩn hóa danh sách món ăn
        $dishes = [];
        $subtotal = 0;
        foreach ($order->orderDetails as $detail) {
            $product = $detail->product;
            $imgName = $product ? ($product->image ?? $product->thumbnail) : null;
            $thumb = ($imgName && file_exists(public_path('images/product/' . $imgName))) 
                ? asset('images/product/' . $imgName) 
                : asset('images/default-product.png');

            $statusText = 'Chờ bếp';
            $statusBadge = 'bg-amber-100 text-amber-700 border-amber-200';
            if ($detail->status == 1) {
                $statusText = 'Đang chế biến';
                $statusBadge = 'bg-blue-100 text-blue-700 border-blue-200';
            } elseif ($detail->status == 2) {
                $statusText = 'Đã lên bàn';
                $statusBadge = 'bg-emerald-100 text-emerald-700 border-emerald-200';
            }

            $amount = $detail->amount > 0 ? $detail->amount : ($detail->price * $detail->qty);
            $subtotal += $amount;

            $dishes[] = [
                'id' => $detail->id,
                'product_id' => $detail->product_id,
                'name' => $product ? $product->name : ('Món #' . $detail->product_id),
                'thumbnail' => $thumb,
                'price' => (float)$detail->price,
                'price_formatted' => number_format($detail->price) . '₫',
                'qty' => (int)$detail->qty,
                'discount' => (float)$detail->discount,
                'amount' => (float)$amount,
                'amount_formatted' => number_format($amount) . '₫',
                'status_raw' => $detail->status,
                'status_text' => $statusText,
                'status_badge' => $statusBadge,
                'order_turn' => $detail->order_turn ?? 1,
            ];
        }

        // Tạm tính và tổng
        $discountAmount = 0;
        if ($order->total_price > 0 && $order->total_price < $subtotal) {
            $discountAmount = $subtotal - $order->total_price;
        }

        $finalTotal = $order->total_price > 0 ? $order->total_price : $subtotal;

        return response()->json([
            'success' => true,
            'is_empty' => false,
            'table' => [
                'id' => $table->id,
                'name' => $table->name,
                'floor_name' => $table->floor->name ?? 'Khu vực chung',
                'seats' => $table->seats,
            ],
            'order' => [
                'id' => $order->id,
                'status' => $order->status,
                'status_text' => $order->status == 1 ? 'Đã tạm tính / Đang phục vụ' : 'Chưa thanh toán',
                'created_at' => $order->created_at ? $order->created_at->format('H:i d/m/Y') : 'Vừa xong',
                'time_seated' => $order->created_at ? $order->created_at->locale('vi')->diffForHumans() : 'Vừa xong',
                'customer_name' => $order->customer_name,
                'phone' => ($order->customer_phone && $order->customer_phone !== '---') ? $order->customer_phone : '—',
                'note' => $order->note,
            ],
            'dishes' => $dishes,
            'summary' => [
                'dishes_count' => count($dishes),
                'total_qty' => array_sum(array_column($dishes, 'qty')),
                'subtotal' => $subtotal,
                'subtotal_formatted' => number_format($subtotal) . '₫',
                'discount' => $discountAmount,
                'discount_formatted' => number_format($discountAmount) . '₫',
                'final_total' => $finalTotal,
                'final_total_formatted' => number_format($finalTotal) . '₫',
            ],
            'urls' => [
                'order_url' => route('admin.table-order.order', $table->id),
                'payment_url' => route('admin.table-order.payment', $table->id),
                'bill_url' => route('site.table.bill', $table->id),
                'qr_url' => route('admin.table.qr', $table->id),
            ]
        ]);
    }

    public function create()
    {
        $floors = Floor::orderBy('id', 'ASC')->get();

        return view('backend.table.create', compact('floors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'floor_id'   => 'required|exists:floors,id',
            'name'       => 'required|string|max:100',
            'seats'      => 'required|integer|min:1',
            'sort_order' => 'nullable|integer',
            'status'     => 'required|in:1,2',
        ], [
            'floor_id.required' => 'Vui lòng chọn tầng.',
            'floor_id.exists' => 'Tầng không hợp lệ.',
            'name.required' => 'Tên bàn không được để trống.',
            'name.string' => 'Tên bàn phải là chuỗi ký tự.',
            'name.max' => 'Tên bàn không được vượt quá 100 ký tự.',
            'seats.required' => 'Số lượng chỗ ngồi không được để trống.',
            'seats.integer' => 'Số lượng chỗ ngồi phải là số nguyên.',
            'seats.min' => 'Số lượng chỗ ngồi phải lớn hơn 0.',
            'sort_order.integer' => 'Thứ tự phải là số nguyên.',
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',
        ]);

        Table::create([
            'floor_id'   => $request->floor_id,
            'name'       => $request->name,
            'seats'      => $request->seats,
            'sort_order' => $request->sort_order ?? 0,
            'status'     => $request->status,
        ]);

        return redirect()->route('admin.table-order.index')
            ->with('success', 'Thêm bàn ăn thành công');
    }

    public function edit(string $id)
    {
        $table = Table::find($id);
        if (! $table) {
            return redirect()->route('admin.table-order.index')
                ->with('error', 'Bàn ăn không tồn tại');
        }

        $floors = Floor::orderBy('id', 'ASC')->get();

        return view('backend.table.edit', compact('table', 'floors'));
    }

    public function update(Request $request, string $id)
    {
        $table = Table::find($id);
        if (! $table) {
            return redirect()->route('admin.table-order.index')
                ->with('error', 'Bàn ăn không tồn tại');
        }

        $request->validate([
            'floor_id'   => 'required|exists:floors,id',
            'name'       => 'required|string|max:100',
            'seats'      => 'required|integer|min:1',
            'sort_order' => 'nullable|integer',
            'status'     => 'required|in:1,2',
        ], [
            'floor_id.required' => 'Vui lòng chọn tầng.',
            'floor_id.exists' => 'Tầng không hợp lệ.',
            'name.required' => 'Tên bàn không được để trống.',
            'name.string' => 'Tên bàn phải là chuỗi ký tự.',
            'name.max' => 'Tên bàn không được vượt quá 100 ký tự.',
            'seats.required' => 'Số lượng chỗ ngồi không được để trống.',
            'seats.integer' => 'Số lượng chỗ ngồi phải là số nguyên.',
            'seats.min' => 'Số lượng chỗ ngồi phải lớn hơn 0.',
            'sort_order.integer' => 'Thứ tự phải là số nguyên.',
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',
        ]);

        $table->floor_id   = $request->floor_id;
        $table->name       = $request->name;
        $table->seats      = $request->seats;
        $table->sort_order = $request->sort_order ?? 0;
        $table->status     = $request->status;

        $table->save();

        return redirect()->route('admin.table-order.index')
            ->with('success', 'Cập nhật bàn ăn thành công');
    }

    public function status(string $id)
    {
        $table = Table::find($id);
        if ($table) {
            $table->status = $table->status == 1 ? 2 : 1;
            $table->save();

            return redirect()->route('admin.table-order.index')
                ->with('success', 'Đã đổi trạng thái bàn ăn');
        }

        return redirect()->route('admin.table-order.index')
            ->with('error', 'Bàn ăn không tồn tại');
    }

    public function delete(string $id)
    {
        $table = Table::find($id);
        if ($table) {
            $table->delete();

            return redirect()->route('admin.table-order.index')
                ->with('success', 'Đã chuyển bàn ăn vào thùng rác');
        }

        return redirect()->route('admin.table-order.index')
            ->with('error', 'Bàn ăn không tồn tại');
    }

    public function trash()
    {
        $tables = Table::onlyTrashed()
            ->with('floor')
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('backend.table.trash', compact('tables'));
    }

    public function restore(string $id)
    {
        $table = Table::withTrashed()->where('id', $id)->first();
        if ($table) {
            $table->restore();

            return redirect()->route('admin.table.trash')
                ->with('success', 'Khôi phục bàn ăn thành công');
        }

        return redirect()->route('admin.table.trash')
            ->with('error', 'Bàn ăn không tồn tại');
    }

    public function downloadQr($id)
    {
        $table = Table::with('floor')->find($id);
        if (!$table) {
            return redirect()->back()->with('error', 'Bàn không tồn tại.');
        }

        // Lấy đơn hàng đang hoạt động của bàn (nếu có)
        $activeOrder = Order::where('table_id', $id)
            ->whereNull('deleted_at')
            ->whereIn('status', [0, 1])
            ->with(['orderDetails.product', 'user'])
            ->latest('id')
            ->first();

        // Hướng người dùng đến trang quét QR của bàn đó
        $url = route('site.table.scan', $id);
        
        // Tạo QR Code kích thước vừa vặn cho Standee để bàn (160px)
        $qrcode = QrCode::size(160)->generate($url);
        
        return view('backend.table.qr', compact('table', 'qrcode', 'url', 'activeOrder'));
    }

    public function destroy(string $id)
    {
        $table = Table::withTrashed()->where('id', $id)->first();
        if ($table) {
            $table->forceDelete();

            return redirect()->route('admin.table.trash')
                ->with('success', 'Xóa vĩnh viễn bàn ăn thành công');
        }

        return redirect()->route('admin.table.trash')
            ->with('error', 'Bàn ăn không tồn tại');
    }
}

