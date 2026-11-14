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
use App\Models\OrderItemLog;
use App\Enums\OrderStatus;
use App\Http\Requests\StoreTableOrderRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            ->whereIn('status', [OrderStatus::PENDING, OrderStatus::PROCESSING]) // Đang chờ xử lý hoặc đang phục vụ
            ->whereNotNull('table_id')
            ->with(['table', 'orderDetails.product', 'user'])
            ->get();

        $activeOrders = $orders->keyBy('table_id');
        $ordersById = $orders->keyBy('id');

        // Calculate total seats for merged tables
        $lockedSeats = Table::whereNotNull('locked_order_id')
             ->selectRaw('locked_order_id, sum(seats) as total_locked_seats')
             ->groupBy('locked_order_id')
             ->pluck('total_locked_seats', 'locked_order_id');

        // Lấy các yêu cầu gọi phục vụ đang chờ xử lý theo bàn
        $activeServiceCalls = \App\Models\ServiceCall::where('status', \App\Models\ServiceCall::STATUS_PENDING)
             ->get()
             ->keyBy('table_id');

        $totalTables = Table::count();
        $occupiedCount = $orders->count();
        $emptyCount = max(0, $totalTables - $occupiedCount);
        $totalServingAmount = 0;
        foreach ($orders as $ord) {
            $sum = $ord->orderDetails->sum('amount');
            $totalServingAmount += ($ord->total_price > 0 ? $ord->total_price : $sum);
        }

        $posStats = [
            'total' => $totalTables,
            'occupied' => $occupiedCount,
            'empty' => $emptyCount,
            'serving_revenue' => $totalServingAmount,
            'service_calls' => $activeServiceCalls->count(),
        ];

        return view('backend.table-order.index', compact('floors', 'activeOrders', 'ordersById', 'lockedSeats', 'activeServiceCalls', 'posStats'));
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

        // Reset has_new_items về false khi nhân viên xem đơn hàng
        if ($order->has_new_items) {
            $order->has_new_items = false;
            $order->save();
        }

        // Lấy toàn bộ chi tiết món hợp lệ (không bao gồm món đã hủy)
        $orderDetails = Orderdetail::where('order_id', $order->id)
            ->where('status', '!=', 'cancelled')
            ->with('product')
            ->orderBy('id', 'ASC')
            ->get();
        
        // Lọc món chờ xác nhận
        $pendingItems = $orderDetails->filter(function($item) {
            return $item->status === 'pending';
        });
        
        // Có bất kỳ món đã lưu chưa
        $hasAnyItems = $orderDetails->isNotEmpty();
        
        // Kiểm tra có món nào chưa lên món không
        $hasUnservedItems = $orderDetails->where('status', '!=', 'served')->isNotEmpty();

        // Lấy danh mục sản phẩm
        $categories = Category::whereNull('deleted_at')->orderBy('name')->get();

        // Lấy danh sách bàn trống (chưa có khách, chưa bị khóa)
        $floors = Floor::with(['tables' => function($query) {
            $query->orderBy('sort_order', 'ASC');
        }])->orderBy('order', 'ASC')->get();
        
        // Lấy danh sách order đang active để kiểm tra
        $activeOrders = Order::whereNull('deleted_at')
            ->whereIn('status', [0, 1])
            ->whereNotNull('table_id')
            ->pluck('table_id')
            ->toArray();
        
        // Lọc bàn trống
        $availableTables = [];
        foreach ($floors as $floor) {
            foreach ($floor->tables as $tbl) {
                if (!in_array($tbl->id, $activeOrders) && is_null($tbl->locked_order_id)) {
                    $availableTables[] = $tbl;
                }
            }
        }

        return view('backend.table-order.order', compact('table', 'order', 'orderDetails', 'categories', 'hasAnyItems', 'hasUnservedItems', 'availableTables', 'pendingItems'));
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
             // Kiểm tra xem order của locked_order_id còn đang hoạt động không
             $lockedOrder = Order::where('id', $sourceTable->locked_order_id)
                 ->whereNull('deleted_at')
                 ->whereIn('status', [0, 1])
                 ->first();
             
             if ($lockedOrder) {
                 return response()->json(['success' => false, 'message' => 'Bàn này đang được ghép với bàn khác']);
             } else {
                 // Order đã không còn hoạt động, reset lại locked_order_id
                 $sourceTable->locked_order_id = null;
                 $sourceTable->save();
             }
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
            $newUser->admin_lever = null;
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
     * Thêm món vào order (Transaction + Pessimistic Lock + Audit Log)
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

        return DB::transaction(function () use ($request) {
            // Khóa hàng Order để tuần tự hóa các thao tác đồng thời trên cùng một hóa đơn
            $order = Order::where('id', $request->order_id)->lockForUpdate()->firstOrFail();

            if (!in_array((int)$order->status, [0, 1])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hóa đơn bàn này đã hoàn tất hoặc đã hủy, không thể thêm món.'
                ], 422);
            }

            $product = Product::where('id', $request->product_id)
                ->whereNull('deleted_at')
                ->where('status', 1)
                ->firstOrFail();

            $price = $product->price_sale > 0 ? $product->price_sale : $product->price_buy;
            $discount = 0;
            $userId = Auth::id();

            // Khóa dòng chi tiết món tương ứng nếu đã tồn tại
            $existingDetail = Orderdetail::where('order_id', $order->id)
                ->where('product_id', $product->id)
                ->where('status', '!=', 'cancelled')
                ->lockForUpdate()
                ->first();

            if ($existingDetail) {
                $oldQty = $existingDetail->qty;
                $newQty = $oldQty + $request->qty;
                $existingDetail->qty = $newQty;
                $existingDetail->amount = ($price - $discount) * $newQty;
                $existingDetail->save();

                // Ghi audit log thay đổi số lượng món
                OrderItemLog::create([
                    'order_id' => $order->id,
                    'order_detail_id' => $existingDetail->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'action' => 'quantity_changed',
                    'old_qty' => $oldQty,
                    'new_qty' => $newQty,
                    'reason' => 'Gọi thêm món tại POS',
                    'user_id' => $userId,
                    'kitchen_status' => $existingDetail->status,
                ]);
            } else {
                $newDetail = Orderdetail::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'qty' => $request->qty,
                    'price' => $price,
                    'discount' => $discount,
                    'amount' => ($price - $discount) * $request->qty,
                    'status' => 'pending',
                ]);

                // Ghi audit log tạo món mới
                OrderItemLog::create([
                    'order_id' => $order->id,
                    'order_detail_id' => $newDetail->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'action' => 'created',
                    'old_qty' => 0,
                    'new_qty' => $request->qty,
                    'reason' => 'Thêm món mới tại POS',
                    'user_id' => $userId,
                    'kitchen_status' => 'pending',
                ]);
            }

            // Tính lại tổng tiền chính xác trong transaction qua PricingService
            $order->total_price = \App\Services\PricingService::getOrderSubtotal($order->id);
            $order->has_new_items = true;
            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Thêm món thành công'
            ]);
        });
    }

    /**
     * Xóa / Hủy món khỏi order (Transaction + Audit Log + Bảo toàn món đã gửi bếp)
     */
    public function removeProductFromOrder(Request $request)
    {
        $request->validate([
            'order_detail_id' => 'required|exists:orderdetail,id',
            'reason' => 'nullable|string|max:500',
        ]);

        return DB::transaction(function () use ($request) {
            $orderDetail = Orderdetail::where('id', $request->order_detail_id)->lockForUpdate()->firstOrFail();
            $order = Order::where('id', $orderDetail->order_id)->lockForUpdate()->firstOrFail();

            if (!in_array((int)$order->status, [0, 1])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hóa đơn bàn này đã hoàn tất hoặc đã hủy, không thể chỉnh sửa.'
                ], 422);
            }

            $userId = Auth::id();
            $kitchenStatus = $orderDetail->status ?? 'pending';
            $productName = $orderDetail->product->name ?? ('Món #' . $orderDetail->product_id);
            $reason = trim($request->reason ?? ($kitchenStatus === 'pending' ? 'Khách đổi ý trước khi chế biến' : 'Hủy món theo yêu cầu bàn'));

            // Ghi audit log hủy món trong cùng transaction
            OrderItemLog::create([
                'order_id' => $order->id,
                'order_detail_id' => $orderDetail->id,
                'product_id' => $orderDetail->product_id,
                'product_name' => $productName,
                'action' => 'cancelled',
                'old_qty' => $orderDetail->qty,
                'new_qty' => 0,
                'reason' => $reason,
                'user_id' => $userId,
                'kitchen_status' => $kitchenStatus,
            ]);

            // Nếu món đã gửi bếp (cooking / served / confirmed): KHÔNG xóa cứng, chỉ đổi status='cancelled' và amount=0
            if (in_array($kitchenStatus, ['cooking', 'served', 'confirmed'])) {
                $orderDetail->status = 'cancelled';
                $orderDetail->amount = 0;
                $orderDetail->save();
            } else {
                $orderDetail->delete();
            }

            // Tính lại tổng tiền hóa đơn trong transaction qua PricingService
            $order->total_price = \App\Services\PricingService::getOrderSubtotal($order->id);
            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Hủy món thành công'
            ]);
        });
    }

    /**
     * Cập nhật số lượng món (Transaction + Lock + Audit Log)
     */
    public function updateProductQty(Request $request)
    {
        $request->validate([
            'order_detail_id' => 'required|exists:orderdetail,id',
            'qty' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:500',
        ]);

        return DB::transaction(function () use ($request) {
            $orderDetail = Orderdetail::where('id', $request->order_detail_id)->lockForUpdate()->firstOrFail();
            $order = Order::where('id', $orderDetail->order_id)->lockForUpdate()->firstOrFail();

            if (!in_array((int)$order->status, [0, 1])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hóa đơn bàn này đã hoàn tất hoặc đã hủy, không thể chỉnh sửa.'
                ], 422);
            }

            $oldQty = $orderDetail->qty;
            $newQty = (int)$request->qty;

            if ($oldQty !== $newQty) {
                $productName = $orderDetail->product->name ?? ('Món #' . $orderDetail->product_id);
                $reason = trim($request->reason ?? 'Điều chỉnh số lượng tại POS');

                // Ghi audit log thay đổi số lượng
                OrderItemLog::create([
                    'order_id' => $order->id,
                    'order_detail_id' => $orderDetail->id,
                    'product_id' => $orderDetail->product_id,
                    'product_name' => $productName,
                    'action' => 'quantity_changed',
                    'old_qty' => $oldQty,
                    'new_qty' => $newQty,
                    'reason' => $reason,
                    'user_id' => Auth::id(),
                    'kitchen_status' => $orderDetail->status,
                ]);

                $orderDetail->qty = $newQty;
                $orderDetail->amount = ($orderDetail->price - $orderDetail->discount) * $newQty;
                $orderDetail->save();

                // Tính lại tổng tiền hóa đơn trong transaction qua PricingService
                $order->total_price = \App\Services\PricingService::getOrderSubtotal($order->id);
                $order->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật số lượng thành công'
            ]);
        });
    }

    /**
     * Cập nhật số lượng món (Batch Update có Transaction + Lock)
     */
    public function updateTableOrderQuantities(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:order,id',
            'items' => 'required|array',
            'items.*.id' => 'required|exists:orderdetail,id',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($request) {
            $order = Order::where('id', $request->order_id)->lockForUpdate()->firstOrFail();

            if (!in_array((int)$order->status, [0, 1])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hóa đơn bàn này đã hoàn tất hoặc đã hủy, không thể chỉnh sửa.'
                ], 422);
            }

            $userId = Auth::id();

            foreach ($request->items as $item) {
                $detail = Orderdetail::where('id', $item['id'])
                    ->where('order_id', $order->id)
                    ->lockForUpdate()
                    ->first();

                if ($detail && (int)$detail->qty !== (int)$item['qty']) {
                    $oldQty = $detail->qty;
                    $newQty = (int)$item['qty'];
                    $productName = $detail->product->name ?? ('Món #' . $detail->product_id);

                    OrderItemLog::create([
                        'order_id' => $order->id,
                        'order_detail_id' => $detail->id,
                        'product_id' => $detail->product_id,
                        'product_name' => $productName,
                        'action' => 'quantity_changed',
                        'old_qty' => $oldQty,
                        'new_qty' => $newQty,
                        'reason' => 'Cập nhật hàng loạt số lượng tại POS',
                        'user_id' => $userId,
                        'kitchen_status' => $detail->status,
                    ]);

                    $detail->qty = $newQty;
                    $detail->amount = ($detail->price - $detail->discount) * $newQty;
                    $detail->save();
                }
            }

            // Tính lại tổng tiền qua PricingService
            $order->total_price = \App\Services\PricingService::getOrderSubtotal($order->id);
            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật số lượng thành công'
            ]);
        });
    }

    /**
     * Lưu toàn bộ order (Batch Save có Transaction + Lock + Audit Log)
     */
    public function saveTableOrder(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:order,id',
            'items' => 'present|array',
            'items.*.product_id' => 'required|exists:product,id',
            'items.*.qty' => 'required|integer|min:1',
            'note' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request) {
            $order = Order::where('id', $request->order_id)->lockForUpdate()->firstOrFail();

            if (!in_array((int)$order->status, [0, 1])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hóa đơn bàn này đã hoàn tất hoặc đã hủy, không thể chỉnh sửa.'
                ], 422);
            }

            if ($request->has('note')) {
                $order->note = $request->note;
            }

            $userId = Auth::id();

            foreach ($request->items as $item) {
                $productId = $item['product_id'];
                $qty = (int)$item['qty'];

                $product = Product::where('id', $productId)->whereNull('deleted_at')->first();
                if (!$product) continue;

                $price = $product->price_sale > 0 ? $product->price_sale : $product->price_buy;
                $discount = 0;
                $amount = ($price - $discount) * $qty;

                $existing = Orderdetail::where('order_id', $order->id)
                    ->where('product_id', $productId)
                    ->where('status', '!=', 'cancelled')
                    ->lockForUpdate()
                    ->first();

                if ($existing) {
                    $oldQty = $existing->qty;
                    $newQty = $oldQty + $qty;
                    $existing->qty = $newQty;
                    $existing->price = $price;
                    $existing->discount = $discount;
                    $existing->amount = ($price - $discount) * $newQty;
                    $existing->save();

                    OrderItemLog::create([
                        'order_id' => $order->id,
                        'order_detail_id' => $existing->id,
                        'product_id' => $productId,
                        'product_name' => $product->name,
                        'action' => 'quantity_changed',
                        'old_qty' => $oldQty,
                        'new_qty' => $newQty,
                        'reason' => 'Thêm món từ lưu bàn',
                        'user_id' => $userId,
                        'kitchen_status' => $existing->status,
                    ]);
                } else {
                    $detail = Orderdetail::create([
                        'order_id' => $order->id,
                        'product_id' => $productId,
                        'qty' => $qty,
                        'price' => $price,
                        'discount' => $discount,
                        'amount' => $amount,
                        'order_turn' => $order->order_turn ?? 1,
                        'status' => 'confirmed',
                    ]);

                    OrderItemLog::create([
                        'order_id' => $order->id,
                        'order_detail_id' => $detail->id,
                        'product_id' => $productId,
                        'product_name' => $product->name,
                        'action' => 'created',
                        'old_qty' => 0,
                        'new_qty' => $qty,
                        'reason' => 'Tạo món từ lưu bàn',
                        'user_id' => $userId,
                        'kitchen_status' => 'confirmed',
                    ]);
                }
            }

            // Tính lại tổng tiền qua PricingService
            $order->total_price = \App\Services\PricingService::getOrderSubtotal($order->id);
            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Lưu đơn hàng thành công'
            ]);
        });
    }

    public function incrementOrderTurn(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:order,id',
        ]);
        $order = Order::findOrFail($request->order_id);
        
        // Xác nhận tất cả món pending trước khi in
        Orderdetail::where('order_id', $order->id)
            ->where('status', 'pending')
            ->update(['status' => 'confirmed']);
        
        // Cập nhật trạng thái các món confirmed thành cooking khi in
        Orderdetail::where('order_id', $order->id)
            ->where('status', 'confirmed')
            ->update(['status' => 'cooking']);
        
        $order->order_turn = ($order->order_turn ?? 1) + 1;
        $order->note = null; // Reset note after print/turn increment
        $order->has_new_items = false; // Clear new items flag when printing
        $order->save();
        return response()->json(['success' => true, 'order_turn' => $order->order_turn]);
    }

    public function clearNewItems(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:order,id',
        ]);
        $order = Order::findOrFail($request->order_id);
        $order->has_new_items = false;
        $order->save();
        return response()->json(['success' => true]);
    }

    public function checkNewOrders()
    {
        // Get all active orders with has_new_items = true
        $newOrders = Order::whereNull('deleted_at')
            ->whereIn('status', [0, 1])
            ->where('has_new_items', true)
            ->with(['table', 'orderDetails.product'])
            ->get();

        $serviceCalls = \App\Models\ServiceCall::with(['table.floor'])
            ->where('status', \App\Models\ServiceCall::STATUS_PENDING)
            ->get();

        return response()->json([
            'success' => true,
            'new_orders' => $newOrders,
            'service_calls' => $serviceCalls
        ]);
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
        $orderDetails = Orderdetail::where('order_id', $order->id)
            ->where('status', '!=', 'cancelled')
            ->with('product')
            ->orderBy('id', 'ASC')
            ->get();
        $totalAmount = $orderDetails->sum('amount');
        $categories = Category::whereNull('deleted_at')->orderBy('name')->get();
        return view('backend.table-order.payment', compact('table', 'order', 'orderDetails', 'totalAmount', 'categories'));
    }

    public function processPayment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:order,id',
            'payment_method' => 'required|in:1,2,3',
            'total_price' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($request) {
            $order = Order::where('id', $request->order_id)->lockForUpdate()->firstOrFail();

            // Chống thanh toán 2 lần & chỉ cho phép thanh toán hóa đơn đang mở (status 0 hoặc 1)
            if (!in_array((int)$order->status, [OrderStatus::PENDING, OrderStatus::PROCESSING])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hóa đơn này không ở trạng thái hợp lệ để thanh toán hoặc đã hoàn tất trước đó.'
                ], 400);
            }

            // Tính lại chính xác tổng tiền từ các chi tiết món hợp lệ trong DB (tuyệt đối không tin tưởng client)
            $actualTotal = (float) Orderdetail::where('order_id', $order->id)
                ->where('status', '!=', 'cancelled')
                ->sum('amount');

            $order->status = OrderStatus::COMPLETED; // Đã thanh toán / Hoàn tất dùng bữa
            $order->payment_method = $request->payment_method;
            $order->total_price = $actualTotal;
            $order->has_new_items = false;
            $order->save();

            // Clear table lock cho các bàn liên kết
            Table::where('locked_order_id', $order->id)->update(['locked_order_id' => null]);

            // 1. Thông báo nghiệp vụ cho Nhân viên & Quản trị viên
            try {
                $tableName = $order->table ? $order->table->name : "Bàn #{$order->table_id}";
                $actualTotalFormatted = number_format($actualTotal, 0, ',', '.') . '₫';
                $pmText = $request->payment_method == 2 ? 'Chuyển khoản VietQR' : ($request->payment_method == 3 ? 'Quẹt thẻ POS' : 'Tiền mặt');
                \App\Services\NotificationService::send([
                    'role'    => 'employee',
                    'type'    => 'payment',
                    'title'   => "{$tableName} đã thanh toán {$pmText} ({$actualTotalFormatted})",
                    'message' => "Hóa đơn {$tableName} (#{$order->id}) đã được thanh toán thành công qua {$pmText}.",
                    'url'     => route('admin.table-order.payment', $order->table_id),
                    'icon'    => 'fa-receipt',
                    'color'   => '#10B981',
                    'data'    => [
                        'order_id' => $order->id,
                        'table_id' => $order->table_id,
                        'is_vietqr' => $request->payment_method == 2
                    ],
                ]);
            } catch (\Exception $e) {}

            // 2. Thông báo riêng cho khách hàng sở hữu đơn hàng (chỉ gửi đích danh cho tài khoản của khách này)
            if ($order->user_id) {
                try {
                    \App\Services\NotificationService::send([
                        'user_id' => $order->user_id,
                        'role'    => 'customer',
                        'type'    => 'payment',
                        'title'   => 'Thanh toán hoàn tất! Cảm ơn quý khách',
                        'message' => 'Hóa đơn đơn hàng #' . $order->id . ' đã được thanh toán thành công. Cảm ơn bạn đã lựa chọn dùng bữa tại CIVILIZE!',
                        'url'     => route('site.orders'),
                        'icon'    => 'fa-receipt',
                        'color'   => '#10B981',
                    ]);
                } catch (\Exception $e) {}
            }

            return response()->json(['success' => true, 'message' => 'Thanh toán thành công']);
        });
    }

    /**
     * Huỷ bàn
     */
    public function cancelTable(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:order,id',
        ]);

        $order = Order::findOrFail($request->order_id);

        // Kiểm tra xem order có món ăn không
        $orderDetails = Orderdetail::where('order_id', $order->id)->count();
        if ($orderDetails > 0) {
            return response()->json(['success' => false, 'message' => 'Không thể huỷ bàn vì đã có món ăn']);
        }

        // Xoá order
        $order->delete();

        return response()->json(['success' => true, 'message' => 'Huỷ bàn thành công']);
    }

    /**
     * Đổi bàn
     */
    public function changeTable(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:order,id',
            'new_table_id' => 'required|exists:tables,id',
        ]);

        $order = Order::findOrFail($request->order_id);
        $newTable = Table::findOrFail($request->new_table_id);

        // Kiểm tra xem order có món ăn không
        $orderDetails = Orderdetail::where('order_id', $order->id)->count();
        if ($orderDetails > 0) {
            return response()->json(['success' => false, 'message' => 'Không thể đổi bàn vì đã có món ăn']);
        }

        // Kiểm tra xem bàn mới có trống không
        $activeOrder = Order::where('table_id', $newTable->id)
            ->whereNull('deleted_at')
            ->whereIn('status', [0, 1])
            ->first();
        
        if ($activeOrder || !is_null($newTable->locked_order_id)) {
            return response()->json(['success' => false, 'message' => 'Bàn này đã có người ngồi hoặc bị khóa']);
        }

        // Cập nhật order sang bàn mới
        $order->table_id = $newTable->id;
        $order->save();

        return response()->json(['success' => true, 'message' => 'Đổi bàn thành công']);
    }

    public function markItemAsServed(Request $request)
    {
        $request->validate([
            'order_detail_id' => 'required|exists:orderdetail,id',
        ]);

        $orderDetail = Orderdetail::findOrFail($request->order_detail_id);
        $orderDetail->status = 'served';
        $orderDetail->save();

        return response()->json(['success' => true, 'message' => 'Đã đánh dấu món ăn là đã lên món']);
    }
}
