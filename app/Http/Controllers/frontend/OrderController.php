<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Orderdetail;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        if (!$userId) {
            return redirect()->route('site.login')->with('error', 'Bạn cần đăng nhập để xem đơn hàng.');
        }

        $ordersByStatus = [
            'all' => Order::with('orderDetails')->where('user_id', $userId)->orderBy('created_at', 'desc')->get(),
            'pending' => Order::with('orderDetails')->where('user_id', $userId)->where('status', 0)->orderBy('created_at', 'desc')->get(),
            'confirmed' => Order::with('orderDetails')->where('user_id', $userId)->where('status', 1)->orderBy('created_at', 'desc')->get(),
            'shipped' => Order::with('orderDetails')->where('user_id', $userId)->where('status', 2)->orderBy('created_at', 'desc')->get(),
            'cancelled' => Order::with('orderDetails')->where('user_id', $userId)->where('status', 3)->orderBy('created_at', 'desc')->get(),
        ];

        $statusCounts = [
            // 'all' => Order::where('user_id', $userId)->count(),
            'pending' => Order::where('user_id', $userId)->where('status', 0)->count(),
            'confirmed' => Order::where('user_id', $userId)->where('status', 1)->count(),
            'shipped' => Order::where('user_id', $userId)->where('status', 2)->count(),
            // 'cancelled' => Order::where('user_id', $userId)->where('status', 3)->count(),
        ];
        return view('frontend.order', compact('ordersByStatus', 'statusCounts'));
    }


    public function detail($id)
    {
        $userId = Auth::id();

        if (!$userId) {
            return redirect()->route('site.login')->with('error', 'Bạn cần đăng nhập để xem chi tiết đơn hàng.');
        }

        $order = Order::with('orderDetails')->where('id', $id)->where('user_id', $userId)->first();
        // $order = Order::with('orderDetails')->where('id', $id)->where('user_id', $userId)->first();

        if (!$order) {
            return redirect()->route('site.orders')->with('error', 'Đơn hàng không tồn tại.');
        }

        $orderDetails = Orderdetail::where('order_id', $id)->get();

        return view('frontend.orderdetail', compact('order', 'orderDetails'));
    }
    public function cancel(Request $request, $id)
    {
        $userId = Auth::id();

        if (!$userId) {
            return redirect()->route('site.login')->with('error', 'Bạn cần đăng nhập để hủy đơn hàng.');
        }

        $order = Order::where('id', $id)->where('user_id', $userId)->first();

        if (!$order) {
            return redirect()->route('site.orders')->with('error', 'Đơn hàng không tồn tại.');
        }

        if ($order->status != 0) {
            return redirect()->route('site.orders.detail', $id)->with('error', 'Đơn hàng không thể hủy vì đã được xử lý.');
        }

        $reasons = $request->input('reasons', []);
        $otherReason = $request->input('other_reason', '');

        $reasonText = implode(', ', $reasons);
        if ($otherReason && in_array('Lý do khác', $reasons)) {
            $reasonText .= " ($otherReason)";
        }

        $order->status = 3; 
        $order->note = $reasonText; 
        $order->save();

        return redirect()->route('site.orders')->with('success', 'Đơn hàng đã được hủy.');
    }


    public function reorder($id)
    {
        $userId = Auth::id();

        if (!$userId) {
            return redirect()->route('site.login')->with('error', 'Bạn cần đăng nhập để đặt lại đơn hàng.');
        }

        $order = Order::with('orderDetails')->where('id', $id)->where('user_id', $userId)->first();

        if (!$order) {
            return redirect()->route('site.orders')->with('error', 'Đơn hàng không tồn tại.');
        }

        if ($order->status != 2 && $order->status != 3) {
            return redirect()->route('site.orders.detail', $id)->with('error', 'Đơn hàng không thể đặt lại.');
        }

        $cart = session()->get("cart_$userId", []);

        foreach ($order->orderDetails as $detail) {
            $cart[$detail->product_id] = [
                'name' => $detail->product->name,
                'slug' => $detail->product->slug,
                'id' => $detail->product_id,
                'price' => $detail->price,
                'qty' => $detail->qty,
                'image' => $detail->product->image,
                'discount' => $detail->discount,
            ];
        }

        session()->put("cart_$userId", $cart);

        return redirect()->route('site.cart')->with('success', 'Đã thêm vào giỏ hàng.');
    }



}
