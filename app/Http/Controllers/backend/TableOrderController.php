<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Floor;
use App\Models\Table;
use App\Models\Order;
use App\Models\Orderdetail;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use App\Http\Requests\StoreTableOrderRequest;
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
        $orders = Order::whereNull('deleted_at')
            ->whereIn('status', [0, 1]) // Đang chờ xử lý hoặc đang vận chuyển
            ->whereNotNull('table_id')
            ->with('table')
            ->get();

        $activeOrders = $orders->keyBy('table_id');
        $ordersById = $orders->keyBy('id');

        // Calculate total seats for merged tables
        $lockedSeats = Table::whereNotNull('locked_order_id')
             ->selectRaw('locked_order_id, sum(seats) as total_locked_seats')
             ->groupBy('locked_order_id')
             ->pluck('total_locked_seats', 'locked_order_id');

        return view('backend.table-order.index', compact('floors', 'activeOrders', 'ordersById', 'lockedSeats'));
    }

    /**
     * Màn hình đặt món (Full page)
     */
    public function order($id)
    {
        $table = Table::with('floor')->findOrFail($id);
        
        // Lấy active order
        $order = Order::where('table_id', $table->id)
            ->whereNull('deleted_at')
            ->whereIn('status', [0, 1])
            ->with(['user'])
            ->first();

        // Nếu bàn chưa có order, redirect về trang chủ hoặc thông báo lỗi
        if (!$order) {
            return redirect()->route('admin.table-order.index')->with('error', 'Bàn chưa được đăng ký khách');
        }

        // Lấy chi tiết order của lần hiện tại
        $orderDetails = Orderdetail::where('order_id', $order->id)
            ->where('order_turn', $order->order_turn ?? 1)
            ->with('product')
            ->get();
        
        // Kiểm tra có bất kỳ món đã lưu trong DB hay chưa
        $hasAnyItems = Orderdetail::where('order_id', $order->id)->exists();
        
        // Lịch sử các lần order trước (accordion)
        $historyItems = Orderdetail::where('order_id', $order->id)
            ->where('order_turn', '<', $order->order_turn ?? 1)
            ->with('product')
            ->orderBy('order_turn', 'ASC')
            ->get();
        $historyByTurn = $historyItems->groupBy('order_turn');

        // Lấy danh mục sản phẩm
        $categories = Category::whereNull('deleted_at')->orderBy('name')->get();

        return view('backend.table-order.order', compact('table', 'order', 'orderDetails', 'categories', 'hasAnyItems', 'historyByTurn'));
    }

    /**
     * Ghép bàn
     */
    public function mergeTable(Request $request)
    {
        $request->validate([
            'source_table_id' => 'required|exists:tables,id',
            'target_table_id' => 'required|exists:tables,id',
        ]);

        if ($request->source_table_id == $request->target_table_id) {
            return response()->json(['success' => false, 'message' => 'Không thể ghép cùng một bàn']);
        }

        $sourceTable = Table::find($request->source_table_id);
        $targetTable = Table::find($request->target_table_id);

        // Validate source is empty or just locked without active order?
        // Source table must NOT have an active order of its own
        $sourceActiveOrder = Order::where('table_id', $sourceTable->id)
            ->whereNull('deleted_at')
            ->whereIn('status', [0, 1])
            ->first();
        
        if ($sourceActiveOrder) {
            return response()->json(['success' => false, 'message' => 'Bàn muốn ghép đang có khách']);
        }

        if ($sourceTable->locked_order_id) {
             return response()->json(['success' => false, 'message' => 'Bàn này đang được ghép với bàn khác']);
        }

        // Validate target has active order
        $targetActiveOrder = Order::where('table_id', $targetTable->id)
            ->whereNull('deleted_at')
            ->whereIn('status', [0, 1])
            ->first();

        if (!$targetActiveOrder) {
            return response()->json(['success' => false, 'message' => 'Bàn đích phải đang có khách']);
        }

        // Lock source table
        $sourceTable->locked_order_id = $targetActiveOrder->id;
        $sourceTable->save();

        return response()->json(['success' => true, 'message' => 'Ghép bàn thành công']);
    }

    /**
     * Hủy ghép bàn
     */
    public function unmergeTable(Request $request)
    {
        $request->validate([
            'table_id' => 'required|exists:tables,id',
        ]);

        $table = Table::find($request->table_id);

        if (!$table->locked_order_id) {
            return response()->json(['success' => false, 'message' => 'Bàn này chưa được ghép']);
        }

        $table->locked_order_id = null;
        $table->save();

        return response()->json(['success' => true, 'message' => 'Hủy ghép bàn thành công']);
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
    public function registerTable(StoreTableOrderRequest $request)
    {
        // Kiểm tra bàn đã có order đang active chưa
        $existingOrder = Order::where('table_id', $request->table_id)
            ->whereNull('deleted_at')
            ->whereIn('status', [0, 1])
            ->first();

        if ($existingOrder) {
            return back()->withErrors(['table_id' => 'Bàn này đang có khách. Vui lòng chọn bàn khác.']);
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
            'number_of_guests' => $request->number_of_guests,
            'orderStyle' => 2, // Nhân viên order
            'status' => 0, // Đang chờ xử lý
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.table-order.index')->with('success', 'Đăng ký bàn thành công');
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
     * Lấy thông tin order của bàn (POST)
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
     * Lấy chi tiết order theo table_id (GET)
     */
    public function getOrderDetails($id)
    {
        $tableId = $id;

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

    /**
     * Lưu toàn bộ order (Thay thế cho các action lẻ)
     */
    public function saveTableOrder(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:order,id',
            'items' => 'present|array', // items có thể rỗng nếu xóa hết
            'items.*.product_id' => 'required|exists:product,id',
            'items.*.qty' => 'required|integer|min:1',
            'note' => 'nullable|string',
        ]);

        $order = Order::findOrFail($request->order_id);
        
        if ($request->has('note')) {
            $order->note = $request->note;
            $order->save();
        }
        
        foreach ($request->items as $item) {
            $productId = $item['product_id'];
            $qty = $item['qty'];

            $product = Product::find($productId);
            $price = $product->price_sale ?? 0;
            $discount = 0;
            $amount = ($price - $discount) * $qty;

            // Luôn tạo bản ghi mới cho mỗi lần order, gắn theo order_turn hiện tại
            Orderdetail::create([
                'order_id' => $order->id,
                'product_id' => $productId,
                'qty' => $qty,
                'price' => $price,
                'discount' => $discount,
                'amount' => $amount,
                'order_turn' => $order->order_turn ?? 1,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lưu đơn hàng thành công'
        ]);
    }

    public function incrementOrderTurn(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:order,id',
        ]);
        $order = Order::findOrFail($request->order_id);
        $order->order_turn = ($order->order_turn ?? 1) + 1;
        $order->note = null; // Reset note after print/turn increment
        $order->save();
        return response()->json(['success' => true, 'order_turn' => $order->order_turn]);
    }

    public function payment($id)
    {
        $table = Table::with('floor')->findOrFail($id);
        $order = Order::where('table_id', $table->id)
            ->whereNull('deleted_at')
            ->whereIn('status', [0, 1])
            ->with(['user'])
            ->first();
        if (!$order) {
            return redirect()->route('admin.table-order.index')->with('error', 'Bàn chưa được đăng ký khách');
        }
        $orderDetails = Orderdetail::where('order_id', $order->id)->with('product')->get();
        $groupedDetails = $orderDetails->groupBy('product_id')->map(function($items) {
            $first = $items->first();
            $qty = $items->sum('qty');
            $price = $first->price ?? ($first->product->price_sale ?? 0);
            $amount = $price * $qty;
            return (object)[
                'product_id' => $first->product_id,
                'name' => optional($first->product)->name ?? 'N/A',
                'qty' => $qty,
                'price' => $price,
                'amount' => $amount,
            ];
        })->values();
        $totalAmount = $groupedDetails->sum('amount');
        return view('backend.table-order.payment', compact('table', 'order', 'groupedDetails', 'totalAmount'));
    }
}
