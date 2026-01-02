<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Floor;
use App\Models\Table;
use App\Models\Order;
use App\Models\Orderdetail;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TableOrderController extends Controller
{
    /**
     * Hiển thị giao diện quản lý bàn (POS style)
     */
    public function index()
    {
        $floors = Floor::with(['tables' => function($query) {
            $query->orderBy('sort_order', 'ASC');
        }])->orderBy('order', 'ASC')->get();

        // Lấy danh sách order đang active (chưa thanh toán) để hiển thị trạng thái bàn
        $activeOrders = Order::whereNull('deleted_at')
            ->whereIn('status', [0, 1]) // Đang chờ xử lý hoặc đang vận chuyển
            ->whereNotNull('table_id')
            ->with('table')
            ->get()
            ->keyBy('table_id');

        return view('backend.table-order.index', compact('floors', 'activeOrders'));
    }

    /**
     * API: Check user theo số điện thoại
     */
    public function checkUserByPhone(Request $request)
    {
        $phone = $request->input('phone');
        
        if (!$phone) {
            return response()->json(['success' => false, 'message' => 'Số điện thoại không được để trống']);
        }

        $user = User::where('phone', $phone)->first();

        if ($user) {
            $genderVal = null;
            if (!is_null($user->gender)) {
                $g = $user->gender;
                if (is_numeric($g)) {
                    $genderVal = (int) $g;
                } else {
                    $t = strtolower($g);
                    if (in_array($t, ['male', 'nam', 'm'])) {
                        $genderVal = 1;
                    } elseif (in_array($t, ['female', 'nu', 'nữ', 'f'])) {
                        $genderVal = 0;
                    }
                }
            }
            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'fullname' => $user->fullname,
                    'phone' => $user->phone,
                    'email' => $user->email ?? '',
                    'gender' => $genderVal,
                ]
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Không tìm thấy khách hàng']);
    }

    /**
     * Đăng ký bàn cho khách
     */
    public function registerTable(Request $request)
    {
        $request->validate([
            'table_id' => 'required|exists:tables,id',
            'phone' => 'required|string|max:20',
            'customer_name' => 'required|string|max:255',
            'gender' => 'nullable|in:0,1',
        ], [
            'table_id.required' => 'Vui lòng chọn bàn',
            'table_id.exists' => 'Bàn không tồn tại',
            'phone.required' => 'Số điện thoại không được để trống',
            'customer_name.required' => 'Tên khách hàng không được để trống',
        ]);

        // Kiểm tra bàn đã có order đang active chưa
        $existingOrder = Order::where('table_id', $request->table_id)
            ->whereNull('deleted_at')
            ->whereIn('status', [0, 1])
            ->first();

        if ($existingOrder) {
            return response()->json([
                'success' => false,
                'message' => 'Bàn này đang có khách. Vui lòng chọn bàn khác.'
            ], 400);
        }

        // Tìm user theo số điện thoại
        $user = User::where('phone', $request->phone)->first();
        $userId = $user ? $user->id : null;

        if (!$user) {
            $newUser = new User();
            $newUser->fullname = $request->customer_name;
            $newUser->gender = $request->input('gender', '1');
            $newUser->phone = $request->phone;
            $newUser->roles = 'customer';
            $newUser->admin_lever = 2;
            $newUser->status = 1;
            $newUser->created_by = Auth::id() ?? 1;
            $newUser->save();
            $user = $newUser;
            $userId = $newUser->id;
        }

        // Tạo order mới với orderStyle = 2 (nhân viên order)
        $order = Order::create([
            'user_id' => $userId,
            'name' => $request->customer_name,
            'phone' => $request->phone,
            'email' => $user->email ?? '',
            'address' => '',
            'table_id' => $request->table_id,
            'orderStyle' => 2, // Nhân viên order
            'status' => 0, // Đang chờ xử lý
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đăng ký bàn thành công',
            'order_id' => $order->id,
            'table_id' => $order->table_id,
        ]);
    }

    /**
     * Lấy danh sách sản phẩm để order
     */
    public function getProducts(Request $request)
    {
        $categoryId = $request->input('category_id');
        
        $query = Product::whereNull('deleted_at')
            ->where('status', 1); // Chỉ lấy sản phẩm đang hoạt động

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->with('category')
            ->orderBy('name', 'ASC')
            ->get();

        return response()->json([
            'success' => true,
            'products' => $products
        ]);
    }

    /**
     * Lấy thông tin order của bàn
     */
    public function getTableOrder(Request $request)
    {
        $tableId = $request->input('table_id');
        
        if (!$tableId) {
            return response()->json(['success' => false, 'message' => 'Vui lòng chọn bàn']);
        }

        $order = Order::where('table_id', $tableId)
            ->whereNull('deleted_at')
            ->whereIn('status', [0, 1])
            ->with(['orderDetails.product', 'table', 'user'])
            ->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Bàn chưa có order']);
        }

        return response()->json([
            'success' => true,
            'order' => $order,
            'order_details' => $order->orderDetails
        ]);
    }

    /**
     * Thêm món vào order
     */
    public function addProductToOrder(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:order,id',
            'product_id' => 'required|exists:product,id',
            'qty' => 'required|integer|min:1',
        ], [
            'order_id.required' => 'Order không hợp lệ',
            'product_id.required' => 'Vui lòng chọn sản phẩm',
            'qty.required' => 'Số lượng không được để trống',
            'qty.min' => 'Số lượng phải lớn hơn 0',
        ]);

        $order = Order::findOrFail($request->order_id);
        $product = Product::findOrFail($request->product_id);

        // Tính giá (có thể có discount)
        $price = $product->price_sale ?? 0;
        $discount = 0;
        $amount = ($price - $discount) * $request->qty;

        // Kiểm tra xem sản phẩm đã có trong order chưa
        $existingDetail = Orderdetail::where('order_id', $order->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existingDetail) {
            // Cập nhật số lượng
            $existingDetail->qty += $request->qty;
            $existingDetail->amount = ($price - $discount) * $existingDetail->qty;
            $existingDetail->save();
        } else {
            // Tạo mới
            Orderdetail::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'qty' => $request->qty,
                'price' => $price,
                'discount' => $discount,
                'amount' => $amount,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Thêm món thành công'
        ]);
    }

    /**
     * Xóa món khỏi order
     */
    public function removeProductFromOrder(Request $request)
    {
        $request->validate([
            'order_detail_id' => 'required|exists:orderdetail,id',
        ]);

        $orderDetail = Orderdetail::findOrFail($request->order_detail_id);
        $orderDetail->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa món thành công'
        ]);
    }

    /**
     * Cập nhật số lượng món
     */
    public function updateProductQty(Request $request)
    {
        $request->validate([
            'order_detail_id' => 'required|exists:orderdetail,id',
            'qty' => 'required|integer|min:1',
        ]);

        $orderDetail = Orderdetail::findOrFail($request->order_detail_id);
        $orderDetail->qty = $request->qty;
        $orderDetail->amount = ($orderDetail->price - $orderDetail->discount) * $request->qty;
        $orderDetail->save();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật số lượng thành công'
        ]);
    }
}
