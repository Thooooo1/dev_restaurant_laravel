<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use App\Models\Orderdetail;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    function addcart($id)
    {
        if (!Auth::check()) {
            return redirect()->route('site.login')->with('error', 'Bạn cần đăng nhập.');
        }

        $userId = Auth::id();
        $product = Product::find($id);

        if (!$product) {
            return redirect()->back()->with('error', 'Sản phẩm không tồn tại.');
        }

        $cart = session()->get("cart_$userId", []);
        $qty = isset($cart[$id]) ? $cart[$id]['qty'] + 1 : 1;

        $cart[$id] = [
            'name' => $product->name,
            'slug' => $product->slug,
            'id' => $product->id,
            'price' => $product->price_sale,
            'qty' => $qty,
            'image' => $product->image,
            'discount' => 0
        ];

        session()->put("cart_$userId", $cart);

        return redirect()->back()->with('success', 'Thêm sản phẩm vào giỏ hàng thành công!');
    }


    function index()
    {
        if (!Auth::check()) {
            return redirect()->route('site.login')->with('error', 'Bạn cần đăng nhập để xem giỏ hàng.');
        }

        $userId = Auth::id();
        $cart = session()->get("cart_$userId", []);
        $tableOrder = null;
        $tableOrderDetails = collect();
        $kitchenItems = [];

        if (session()->has('table_id')) {
            $tableId = session('table_id');
            $user = Auth::user();

            $tableOrder = Order::where('table_id', $tableId)
                ->whereNull('deleted_at')
                ->whereIn('status', [0, 1])
                ->where(function ($query) use ($user) {
                    $query->where('user_id', $user->id)
                        ->orWhere('phone', $user->phone);
                })
                ->with(['orderDetails.product', 'table.floor', 'user'])
                ->orderByDesc('id')
                ->first();

            if ($tableOrder) {
                $tableOrderDetails = $tableOrder->orderDetails;

                foreach ($tableOrderDetails as $index => $detail) {
                    $kitchenItems[] = [
                        'stt' => $index + 1,
                        'name' => $detail->product->name ?? 'N/A',
                        'qty' => (int) ($detail->qty ?? 0),
                    ];
                }
            }
        }

        return view('frontend.cart', compact('cart', 'tableOrder', 'tableOrderDetails', 'kitchenItems'));
    }

    function updatecart(Request $request)
    {
        $userId = Auth::id();
        $cart = session()->get("cart_$userId", []);
        $qty = $request->qty;

        foreach ($qty as $id => $n) {
            if ($n < 1) {
                return redirect()->back()->with('error', 'Số lượng sản phẩm phải lớn hơn 0.');
            }
            $cart[$id]['qty'] = $n;
        }

        session()->put("cart_$userId", $cart);

        if (session()->has('table_id')) {
            $tableId = session('table_id');
            $user = Auth::user();

            $order = Order::where('table_id', $tableId)
                ->whereNull('deleted_at')
                ->whereIn('status', [0, 1])
                ->where(function ($query) use ($user) {
                    $query->where('user_id', $user->id)
                        ->orWhere('phone', $user->phone);
                })
                ->first();

            if (!$order) {
                $order = new Order();
                $order->user_id = $userId ?? 0;
                $order->name = $user->fullname ?? $user->name ?? 'Khách lẻ';
                $order->email = $user->email ?? '';
                $order->phone = $user->phone ?? '';
                $order->address = '';
                $order->table_id = $tableId;
                $order->orderStyle = 1;
                $order->status = 0;
                $order->created_by = $userId ?? 1;
                $order->order_turn = $order->order_turn ?? 1;
                $order->save();
            }

            $productIdsInCart = array_keys($cart);

            foreach ($cart as $productId => $details) {
                $detail = Orderdetail::where('order_id', $order->id)
                    ->where('product_id', $productId)
                    ->first();

                $price = $details['price'];
                $quantity = $details['qty'];
                $discount = $details['discount'] ?? 0;
                $amount = ($price - $discount) * $quantity;

                if ($detail) {
                    $detail->qty = $quantity;
                    $detail->price = $price;
                    $detail->discount = $discount;
                    $detail->amount = $amount;
                    $detail->save();
                } else {
                    Orderdetail::create([
                        'order_id' => $order->id,
                        'product_id' => $productId,
                        'qty' => $quantity,
                        'price' => $price,
                        'discount' => $discount,
                        'amount' => $amount,
                        'order_turn' => $order->order_turn ?? 1,
                    ]);
                }
            }

            session()->flash('kitchen_print_order_id', $order->id);

            session()->forget("cart_$userId");
        }

        return redirect()->back()->with('success', 'Xác nhận giỏ hàng thành công!');
    }

    function delcart($id = null)
    {
        $userId = Auth::id();
        $cart = session()->get("cart_$userId", []);

        if ($id == null) {
            session()->forget("cart_$userId");
        } else {
            if (array_key_exists($id, $cart)) {
                unset($cart[$id]);
                session()->put("cart_$userId", $cart);
            }
        }

        return redirect()->back()->with('success', 'Xóa thành công!');
    }

    public function checkoutForm()
    {
        if (!Auth::check()) {
            return redirect()->route('site.login')->with('error', 'Bạn cần đăng nhập.');
        }

        $userId = Auth::id();
        $cart = session()->get("cart_$userId", []);

        if (empty($cart)) {
            return redirect()->route('site.cart')->with('error', 'Giỏ hàng của bạn đang trống.');
        }

        return view('frontend.checkout', compact('cart'));
    }


    public function checkout(Request $request)
    {
        $userId = Auth::id();
        $cart = session()->get("cart_$userId", []);

        if (empty($cart)) {
            return redirect()->route('site.cart')->with('error', 'Giỏ hàng của bạn đang trống.');
        }

        $order = new Order();
        $order->user_id = $userId ?? 0;
        $order->name = $request->name;
        $order->email = $request->email;
        $order->phone = $request->phone;
        $order->note = $request->note;
        $order->payment_method = $request->payment_method;
        $order->address = $request->address;
        $order->created_by = $userId ?? 1;
        $order->status = 0;
        $order->orderStyle = 1;

        // Xử lý nếu là Order tại bàn
        if (session('table_id')) {
            $order->table_id = session('table_id');
            // Nếu phương thức thanh toán là 'TaiBan', có thể set status hoặc xử lý khác nếu cần
            if ($request->payment_method == 'TaiBan') {
                $order->payment_method = 2; // Giả sử 2 là thanh toán sau/tiền mặt/tại quầy
            }
        }

        $order->save();

        foreach ($cart as $productId => $details) {
            $orderDetail = new Orderdetail();
            $orderDetail->order_id = $order->id;
            $orderDetail->product_id = $productId;
            $orderDetail->qty = $details['qty'];
            $orderDetail->price = $details['price'];
            $orderDetail->discount = $details['discount'] ?? 0;
            $orderDetail->amount = $details['price'] * $details['qty'];

            if (isset($details['discount'])) {
                $orderDetail->amount -= $details['discount'] * $details['qty'];
            }

            if (!$orderDetail->save()) {
                return redirect()->back()->with('error', 'Không thể lưu chi tiết đơn hàng. Vui lòng thử lại.');
            }

            $product = Product::find($productId);
            if ($product) {
                $product->qty -= $details['qty']; 
                if ($product->qty < 0) {
                    return redirect()->back()->with('error', 'Sản phẩm "' . $product->name . '" Tạm thời hết món, xin chờ giây lát.');
                }
                $product->save();
            }
        }

        session()->forget("cart_$userId");

        return redirect()->route('site.thanks')->with('success', 'Đặt hàng thành công!');
    }


    public function thanks()
    {
        return view('frontend.thanks');
    }
}
