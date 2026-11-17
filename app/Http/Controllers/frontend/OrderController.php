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

        return \Illuminate\Support\Facades\DB::transaction(function () use ($id, $userId, $request) {
            $order = Order::with('orderDetails.product')
                ->where('id', $id)
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->first();

            if (!$order) {
                return redirect()->route('site.orders')->with('error', 'Đơn hàng không tồn tại.');
            }

            if ((int)$order->status !== 0) {
                return redirect()->route('site.orders.detail', $id)->with('error', 'Đơn hàng không thể hủy vì đã được xử lý hoặc đã bị hủy trước đó.');
            }

            $reasons = $request->input('reasons', []);
            $otherReason = $request->input('other_reason', '');

            $reasonText = implode(', ', $reasons);
            if ($otherReason && in_array('Lý do khác', $reasons)) {
                $reasonText .= " ($otherReason)";
            }

            $order->status = \App\Enums\OrderStatus::CANCELLED; 
            $order->note = $reasonText; 
            $order->save();

            // Hoàn trả tồn kho cho tất cả sản phẩm trong đơn đã bị trừ kho khi đặt hàng
            foreach ($order->orderDetails as $detail) {
                if ($detail->product) {
                    $detail->product->increment('qty', $detail->qty);
                }
            }

            return redirect()->route('site.orders')->with('success', 'Đơn hàng đã được hủy và hoàn trả tồn kho.');
        });
    }


    public function reorder($id)
    {
        $userId = Auth::id();

        if (!$userId) {
            return redirect()->route('site.login')->with('error', 'Bạn cần đăng nhập để đặt lại đơn hàng.');
        }

        $order = Order::with('orderDetails.product')->where('id', $id)->where('user_id', $userId)->first();

        if (!$order) {
            return redirect()->route('site.orders')->with('error', 'Đơn hàng không tồn tại.');
        }

        if ($order->status != 2 && $order->status != 3) {
            return redirect()->route('site.orders.detail', $id)->with('error', 'Đơn hàng không thể đặt lại.');
        }

        $cartKey = "cart_$userId";
        $cart = session()->get($cartKey, []);
        $addedCount = 0;
        $skippedCount = 0;

        foreach ($order->orderDetails as $detail) {
            $product = $detail->product;
            // Bỏ qua nếu món không còn tồn tại hoặc đã ngừng kinh doanh
            if (!$product || (int)$product->status !== 1) {
                $skippedCount++;
                continue;
            }

            // Đồng bộ giá mới nhất từ cơ sở dữ liệu thay vì dùng giá cũ trong lịch sử đơn
            $currentPrice = (float)$product->price;
            $qtyToAdd = max(1, (int)$detail->qty);

            if (isset($cart[$product->id])) {
                $cart[$product->id]['qty'] += $qtyToAdd;
                $cart[$product->id]['price'] = $currentPrice;
            } else {
                $cart[$product->id] = [
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'id' => $product->id,
                    'price' => $currentPrice,
                    'qty' => $qtyToAdd,
                    'image' => $product->image,
                    'discount' => 0,
                ];
            }
            $addedCount++;
        }

        session()->put($cartKey, $cart);

        if ($addedCount === 0) {
            return redirect()->route('site.orders.detail', $id)->with('error', 'Tất cả món trong đơn này hiện đã tạm ngừng phục vụ hoặc không còn kinh doanh.');
        }

        $message = "Đã thêm {$addedCount} món vào giỏ hàng với giá niêm yết hiện tại.";
        if ($skippedCount > 0) {
            $message .= " ({$skippedCount} món tạm ngừng phục vụ đã được bỏ qua).";
        }

        return redirect()->route('site.cart')->with('success', $message);
    }



}
