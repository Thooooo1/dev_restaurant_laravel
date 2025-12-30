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

        return redirect()->route('admin.order.index')->with('success', 'Cập nhật trạng thái thành công');
    }

    public function detail($id)
    {
        $order = Order::with('orderDetails.product')->findOrFail($id);

        return view('backend.order.orderdetail', compact('order'));
    }

    public function print($id)
    {
        $order = Order::with('orderDetails.product')->findOrFail($id);
        return view('backend.order.printorder', compact('order'));
    }
    
}

