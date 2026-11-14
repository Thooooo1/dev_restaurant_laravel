<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use App\Models\Orderdetail;
use App\Models\Table;
use App\Models\Floor;
use Illuminate\Support\Facades\Auth;
use App\Services\MomoService;

class CartController extends Controller
{
    /**
     * Lấy khóa lưu giỏ hàng trong Session (hỗ trợ cả tài khoản đăng nhập và khách vãng lai / quét QR tại bàn)
     */
    protected function getCartKey()
    {
        if (Auth::check()) {
            return 'cart_' . Auth::id();
        }
        return 'cart_guest_' . session()->getId();
    }

    /**
     * Gộp giỏ hàng khách vãng lai (guest) vào giỏ hàng tài khoản sau khi đăng nhập/đăng ký
     */
    public static function mergeGuestCartToUser($userId, $guestSessionId = null)
    {
        if (empty($userId)) {
            return;
        }

        $sessionId = $guestSessionId ?: session()->getId();
        $guestKey = 'cart_guest_' . $sessionId;
        $userKey = 'cart_' . $userId;

        $guestCart = session()->get($guestKey, []);
        if (empty($guestCart) || !is_array($guestCart)) {
            return;
        }

        $userCart = session()->get($userKey, []);
        if (!is_array($userCart)) {
            $userCart = [];
        }

        $productIds = array_keys($guestCart);
        $validProducts = Product::whereIn('id', $productIds)
            ->whereNull('deleted_at')
            ->where('status', 1)
            ->get()
            ->keyBy('id');

        $hasMerged = false;

        foreach ($guestCart as $productId => $item) {
            if (!isset($validProducts[$productId])) {
                continue;
            }

            $qty = isset($item['qty']) ? (int)$item['qty'] : 0;
            if ($qty <= 0) {
                continue;
            }

            $product = $validProducts[$productId];
            $currentPrice = $product->price_sale > 0 ? $product->price_sale : $product->price_buy;

            if (isset($userCart[$productId])) {
                $userCart[$productId]['qty'] = (int)$userCart[$productId]['qty'] + $qty;
                $userCart[$productId]['price'] = $currentPrice;
            } else {
                $userCart[$productId] = [
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'id' => $product->id,
                    'price' => $currentPrice,
                    'qty' => $qty,
                    'image' => $product->image,
                    'discount' => 0,
                ];
            }
            $hasMerged = true;
        }

        if ($hasMerged) {
            session()->put($userKey, $userCart);
        }

        session()->forget($guestKey);
        session()->save();
    }

    /**
     * Dọn dẹp triệt để tất cả các session giỏ hàng (cả guest, auth user và legacy key)
     */
    public static function clearCartSession()
    {
        session()->forget('cart');
        session()->forget('cart_guest_' . session()->getId());
        if (Auth::check()) {
            session()->forget('cart_' . Auth::id());
        }
        // Xóa tất cả các key bắt đầu bằng cart_ trong session
        foreach (session()->all() as $k => $v) {
            if (str_starts_with($k, 'cart_') || $k === 'cart') {
                session()->forget($k);
            }
        }
        session()->save();
    }

    /**
     * Thêm món vào giỏ hàng (Hỗ trợ cả Form POST thông thường và AJAX trực tiếp trên Thực đơn)
     */
    public function addcart(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            if ($request->wantsJson() || $request->ajax() || $request->input('ajax') || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json(['success' => false, 'message' => 'Món ăn không tồn tại hoặc đã ngừng kinh doanh.'], 404);
            }
            return redirect()->back()->with('error', 'Sản phẩm không tồn tại.');
        }

        $cartKey = $this->getCartKey();
        $cart = session()->get($cartKey, []);
        $addQty = max(1, (int)$request->input('qty', 1));
        $qty = isset($cart[$id]) ? $cart[$id]['qty'] + $addQty : $addQty;

        $cart[$id] = [
            'name' => $product->name,
            'slug' => $product->slug,
            'id' => $product->id,
            'price' => $product->price_sale > 0 ? $product->price_sale : $product->price_buy,
            'qty' => $qty,
            'image' => $product->image,
            'discount' => 0
        ];

        session()->put($cartKey, $cart);

        if ($request->wantsJson() || $request->ajax() || $request->input('ajax') || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return $this->getLiveCartResponse('Đã thêm "' . $product->name . '" vào giỏ hàng!');
        }

        return redirect()->back()->with('success', 'Thêm sản phẩm vào giỏ hàng thành công!');
    }

    /**
     * Alias endpoint cho AJAX thêm món
     */
    public function addcartAjax(Request $request, $id)
    {
        return $this->addcart($request, $id);
    }

    /**
     * Cập nhật số lượng món bằng AJAX (+ / -)
     */
    public function updateQtyAjax(Request $request)
    {
        $id = $request->input('id');
        $qty = (int)$request->input('qty', 1);

        $cartKey = $this->getCartKey();
        $cart = session()->get($cartKey, []);

        if (!isset($cart[$id])) {
            return response()->json(['success' => false, 'message' => 'Món không có trong giỏ hàng.'], 404);
        }

        if ($qty <= 0) {
            unset($cart[$id]);
            session()->put($cartKey, $cart);
            return $this->getLiveCartResponse('Đã xóa món khỏi giỏ hàng.');
        }

        $cart[$id]['qty'] = $qty;
        session()->put($cartKey, $cart);

        return $this->getLiveCartResponse('Đã cập nhật số lượng món.');
    }

    /**
     * Xóa 1 món khỏi giỏ hàng bằng AJAX
     */
    public function removeItemAjax(Request $request, $id)
    {
        $cartKey = $this->getCartKey();
        $cart = session()->get($cartKey, []);

        if (isset($cart[$id])) {
            $name = $cart[$id]['name'] ?? 'món';
            unset($cart[$id]);
            session()->put($cartKey, $cart);
            return $this->getLiveCartResponse('Đã bỏ món "' . $name . '" khỏi giỏ.');
        }

        return $this->getLiveCartResponse('Món không còn trong giỏ.');
    }

    /**
     * Xóa toàn bộ giỏ hàng
     */
    public function clearCartAjax(Request $request)
    {
        self::clearCartSession();
        return $this->getLiveCartResponse('Đã làm trống giỏ hàng.');
    }

    /**
     * Lấy dữ liệu giỏ hàng thời gian thực (JSON) cho Live Cart trên Thực đơn
     */
    public function getLiveCart(Request $request)
    {
        return $this->getLiveCartResponse();
    }

    /**
     * Helper sinh dữ liệu giỏ hàng chuẩn JSON
     */
    protected function getLiveCartResponse($message = null)
    {
        $cartKey = $this->getCartKey();
        $cart = session()->get($cartKey, []);
        $isTableOrder = session('is_table_order') ? true : false;

        $pricing = \App\Services\PricingService::calculateCartSummary($cart, $isTableOrder);
        $items = [];

        foreach ($cart as $id => $item) {
            $price = (float)($item['price'] ?? 0);
            $qty = (int)($item['qty'] ?? 1);
            $itemTotal = $price * $qty;

            $imgSrc = !empty($item['image']) && file_exists(public_path('images/product/' . $item['image']))
                ? asset('images/product/' . $item['image'])
                : asset('images/default-product.png');

            $items[] = [
                'id' => $id,
                'name' => $item['name'],
                'slug' => $item['slug'] ?? '',
                'price' => $price,
                'price_formatted' => number_format($price, 0, ',', '.') . '₫',
                'qty' => $qty,
                'total' => $itemTotal,
                'total_formatted' => number_format($itemTotal, 0, ',', '.') . '₫',
                'image' => $imgSrc,
            ];
        }

        // Kiểm tra bàn ăn hiện tại
        $isTableOrder = session('is_table_order') ? true : false;
        $tableId = session('table_id');
        $tableName = session('table_name');
        $floorName = null;
        $kitchenItems = [];
        $tableOrderId = session('table_order_id');

        if ($isTableOrder && $tableId) {
            $table = Table::with('floor')->find($tableId);
            if ($table) {
                $tableName = $table->name;
                $floorName = $table->floor ? $table->floor->name : null;
            }

            // Lấy món đã gửi vào bếp trước đó
            if ($tableOrderId) {
                $currentOrder = Order::where('id', $tableOrderId)
                    ->where('table_id', $tableId)
                    ->whereNull('deleted_at')
                    ->with(['orderDetails.product'])
                    ->first();

                if ($currentOrder && in_array($currentOrder->status, [0, 1])) {
                    foreach ($currentOrder->orderDetails as $index => $detail) {
                        $kitchenItems[] = [
                            'stt' => $index + 1,
                            'name' => $detail->product->name ?? 'Món đã xóa',
                            'qty' => (int)$detail->qty,
                            'price' => (float)$detail->price,
                            'price_formatted' => number_format($detail->price, 0, ',', '.') . '₫',
                            'amount_formatted' => number_format($detail->amount, 0, ',', '.') . '₫',
                            'status' => $detail->status ?? 'pending',
                        ];
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'items' => $items,
            'count' => $pricing['count'],
            'subtotal' => $pricing['subtotal'],
            'subtotal_formatted' => $pricing['subtotal_formatted'],
            'discount_percent' => $isTableOrder ? 10 : 0,
            'discount_amount' => $pricing['discount'],
            'discount_formatted' => $pricing['discount_formatted'],
            'shipping_fee' => $pricing['shipping_fee'],
            'shipping_fee_formatted' => $pricing['shipping_fee_formatted'],
            'total' => $pricing['total'],
            'total_formatted' => $pricing['total_formatted'],
            'is_table_order' => $isTableOrder,
            'dining_mode' => session('dining_mode', $isTableOrder ? 'dine_in' : 'delivery'),
            'table_id' => $tableId,
            'table_name' => $tableName,
            'floor_name' => $floorName,
            'full_table_title' => $floorName ? "{$tableName} ({$floorName})" : $tableName,
            'kitchen_items' => $kitchenItems,
            'kitchen_count' => count($kitchenItems),
            'bill_url' => $tableId ? route('site.table.bill', $tableId) : null,
            'payment_qr_url' => $tableId ? route('site.table.payment.qr', $tableId) : null,
            'checkout_url' => route('site.checkoutForm'),
        ]);
    }

    /**
     * Chuyển đổi giữa hình thức Ăn tại bàn (Phục vụ tại quán) và Giao hàng tận nơi (Mang về)
     */
    public function switchDiningMode(Request $request)
    {
        $mode = $request->input('mode', 'dine_in'); // 'dine_in' hoặc 'delivery'
        $tableId = $request->input('table_id');

        if ($mode === 'dine_in') {
            if ($tableId) {
                $table = Table::with(['floor', 'orders' => function($q) {
                    $q->whereIn('status', [0, 1])->whereNull('deleted_at');
                }])->find($tableId);

                if (!$table) {
                    return response()->json(['success' => false, 'message' => 'Không tìm thấy bàn ăn trên hệ thống.'], 404);
                }

                if ($table->status == 0) {
                    return response()->json(['success' => false, 'message' => "Bàn {$table->name} hiện đang tạm khóa hoặc bảo trì. Vui lòng chọn bàn khác!"], 422);
                }

                // Kiểm tra xem bàn có đang bị khách khác sử dụng không
                $currentTableId = session('table_id');
                $currentOrderId = session('table_order_id');
                $activeOrder = $table->orders->first();

                if ($activeOrder && $table->id != $currentTableId && $activeOrder->id != $currentOrderId) {
                    return response()->json([
                        'success' => false, 
                        'message' => "Bàn {$table->name} hiện đang có khách ngồi dùng bữa. Quý khách vui lòng chọn một bàn trống khác!"
                    ], 422);
                }

                session([
                    'table_id' => $table->id,
                    'table_name' => $table->name,
                    'table_floor_name' => $table->floor ? $table->floor->name : '',
                    'dining_mode' => 'dine_in',
                    'is_table_order' => true,
                ]);
            } else {
                session([
                    'dining_mode' => 'dine_in',
                    'is_table_order' => true,
                ]);
            }
        } else {
            session([
                'dining_mode' => 'delivery',
                'is_table_order' => false,
            ]);
            session()->forget(['table_id', 'table_name', 'table_order_id', 'table_floor_name']);
        }

        $tableName = session('table_name');
        $msg = $mode === 'dine_in'
            ? ($tableName ? "Đã chọn phục vụ tại {$tableName} (Ăn tại quán)" : "Đã chuyển sang hình thức Ăn tại quán (Phục vụ trực tiếp)")
            : "Đã chuyển sang hình thức Giao hàng tận nơi (Mang về)";

        return $this->getLiveCartResponse($msg);
    }

    /**
     * Lấy danh sách các bàn ăn theo tầng để khách chọn trực tiếp trên thực đơn (kèm trạng thái khả dụng thực tế)
     */
    public function getAvailableTables()
    {
        $currentTableId = session('table_id');
        $currentOrderId = session('table_order_id');

        $floors = Floor::with(['tables' => function ($query) {
            $query->orderBy('sort_order', 'ASC')->orderBy('id', 'ASC');
        }, 'tables.orders' => function ($query) {
            $query->whereIn('status', [0, 1])->whereNull('deleted_at');
        }])->orderBy('id', 'ASC')->get();

        $formattedFloors = $floors->map(function ($floor) use ($currentTableId, $currentOrderId) {
            return [
                'id' => $floor->id,
                'name' => $floor->name,
                'tables' => $floor->tables->map(function ($t) use ($currentTableId, $currentOrderId) {
                    $activeOrder = $t->orders->first();
                    $isOccupied = ($activeOrder && $t->id != $currentTableId && $activeOrder->id != $currentOrderId);
                    $isMine = ($t->id == $currentTableId);
                    $isDisabled = ($t->status == 0);

                    return [
                        'id' => $t->id,
                        'name' => $t->name,
                        'seats' => $t->seats,
                        'status' => $t->status,
                        'is_occupied' => $isOccupied,
                        'is_mine' => $isMine,
                        'is_disabled' => $isDisabled,
                        'is_available' => (!$isOccupied && !$isDisabled),
                    ];
                }),
            ];
        });

        return response()->json([
            'success' => true,
            'floors' => $formattedFloors,
            'current_table_id' => $currentTableId,
            'current_table_name' => session('table_name'),
            'is_table_order' => session('is_table_order', false),
            'dining_mode' => session('dining_mode', session('is_table_order') ? 'dine_in' : 'delivery'),
        ]);
    }

    /**
     * Khách tại bàn bấm nút "Gửi gọi món cho Bếp" trực tiếp trên Thực đơn qua AJAX
     */
    public function sendToKitchenAjax(Request $request)
    {
        $cartKey = $this->getCartKey();
        $cart = session()->get($cartKey, []);

        if (empty($cart)) {
            return response()->json(['success' => false, 'message' => 'Giỏ hàng của bạn đang trống. Vui lòng chọn món trước.'], 400);
        }

        // Lấy số bàn: từ request hoặc từ session
        $tableId = $request->input('table_id') ?: session('table_id');
        if (!$tableId) {
            return response()->json([
                'success' => false,
                'need_table' => true,
                'message' => 'Vui lòng chọn số bàn ăn của quý khách để gửi món vào bếp phục vụ trực tiếp tại quán!'
            ], 200);
        }

        $table = Table::with('floor')->find($tableId);
        if (!$table) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy bàn ăn trên hệ thống.'], 404);
        }

        // Tự động kích hoạt session bàn phục vụ tại quán
        session([
            'table_id' => $table->id,
            'table_name' => $table->name,
            'table_floor_name' => $table->floor ? $table->floor->name : '',
            'is_table_order' => true,
            'dining_mode' => 'dine_in',
        ]);

        // Sử dụng TableOrderService để lấy hoặc tạo đơn hàng duy nhất cho bàn, gộp món an toàn
        $items = [];
        foreach ($cart as $productId => $details) {
            $items[] = [
                'product_id' => $productId,
                'qty' => (int)$details['qty'],
                'price' => (float)$details['price'],
            ];
        }

        $order = \App\Services\TableOrderService::addItemsToTableOrder($tableId, $items);
        session(['table_order_id' => $order->id]);

        try {
            \App\Services\NotificationService::notifyNewOrder($order);
        } catch (\Exception $e) {}

        // Xóa giỏ hàng sau khi gửi bếp thành công
        self::clearCartSession();

        return $this->getLiveCartResponse('Đã gửi món vào bếp thành công! Nhân viên sẽ chuẩn bị món ngay cho quý khách.');
    }

    /**
     * Khách hàng tại bàn bấm "Thanh toán luôn bằng VietQR" trên Thực đơn
     */
    public function payTableOrderNowAjax(Request $request)
    {
        $cartKey = $this->getCartKey();
        $cart = session()->get($cartKey, []);

        $tableId = $request->input('table_id') ?: session('table_id');
        if (!$tableId) {
            return response()->json([
                'success' => false,
                'need_table' => true,
                'message' => 'Vui lòng chọn số bàn ăn của quý khách trước khi thanh toán tại bàn!'
            ], 200);
        }

        $table = Table::with('floor')->find($tableId);
        if (!$table) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy bàn ăn.'], 404);
        }

        session([
            'table_id' => $table->id,
            'table_name' => $table->name,
            'table_floor_name' => $table->floor ? $table->floor->name : '',
            'is_table_order' => true,
            'dining_mode' => 'dine_in',
        ]);

        // Nếu giỏ có món, đồng bộ món vào order của bàn trước khi chuyển sang thanh toán
        if (!empty($cart)) {
            $items = [];
            foreach ($cart as $productId => $details) {
                $items[] = [
                    'product_id' => $productId,
                    'qty' => (int)$details['qty'],
                    'price' => (float)$details['price'],
                ];
            }

            $order = \App\Services\TableOrderService::addItemsToTableOrder($tableId, $items);
            session(['table_order_id' => $order->id]);

            // Xóa session giỏ hàng
            self::clearCartSession();
        } else {
            $order = \App\Services\TableOrderService::getOrCreateActiveOrder($tableId);
            session(['table_order_id' => $order->id]);
        }

        return response()->json([
            'success' => true,
            'redirect_url' => route('site.table.payment.qr', $tableId)
        ]);
    }

    /**
     * Trang xem giỏ hàng riêng biệt (/gio-hang)
     */
    public function index()
    {
        // Nhân viên và Quản trị viên không dùng giỏ hàng cá nhân khách hàng, chuyển thẳng về POS
        if (Auth::check() && (Auth::user()->isAdmin() || Auth::user()->isEmployee())) {
            session()->forget([
                'table_id',
                'table_order_id',
                'table_name',
                'table_floor_name',
                'is_table_order',
                'dining_mode',
                'customer_phone',
                'customer_name'
            ]);
            return redirect()->route('admin.table-order.index')->with('info', 'Tài khoản nhân viên thao tác phục vụ và bán hàng tại Màn hình POS.');
        }

        $cartKey = $this->getCartKey();
        $cart = session()->get($cartKey, []);
        $tableOrder = null;
        $tableOrderDetails = collect();
        $kitchenItems = [];

        if (session()->has('table_id') && session()->has('table_order_id')) {
            $tableId = session('table_id');
            $tableOrderId = session('table_order_id');

            $tableOrder = Order::where('id', $tableOrderId)
                ->where('table_id', $tableId)
                ->whereNull('deleted_at')
                ->with(['orderDetails.product', 'table.floor', 'user'])
                ->first();

            if ($tableOrder && in_array($tableOrder->status, [0, 1])) {
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

        $suggestedProducts = Product::where('status', 1)
            ->whereNull('deleted_at')
            ->orderBy('id', 'desc')
            ->take(4)
            ->get();

        return view('frontend.cart', compact('cart', 'tableOrder', 'tableOrderDetails', 'kitchenItems', 'suggestedProducts'));
    }

    /**
     * Cập nhật giỏ hàng từ Form cổ điển
     */
    public function updatecart(Request $request)
    {
        $cartKey = $this->getCartKey();
        $cart = session()->get($cartKey, []);
        $qty = $request->qty;

        if (is_array($qty)) {
            foreach ($qty as $id => $n) {
                if ($n < 1) {
                    return redirect()->back()->with('error', 'Số lượng sản phẩm phải lớn hơn 0.');
                }
                if (isset($cart[$id])) {
                    $cart[$id]['qty'] = $n;
                }
            }
            session()->put($cartKey, $cart);
        }

        if (session()->has('table_id')) {
            return $this->sendToKitchenClassic();
        }

        return redirect()->back()->with('success', 'Đã cập nhật giỏ hàng!');
    }

    protected function sendToKitchenClassic()
    {
        $cartKey = $this->getCartKey();
        $cart = session()->get($cartKey, []);
        $tableId = session('table_id');

        if (!$tableId) {
            return redirect()->route('site.home')->with('error', 'Không xác định được bàn ăn.');
        }

        $items = [];
        foreach ($cart as $productId => $details) {
            $items[] = [
                'product_id' => $productId,
                'qty' => (int)($details['qty'] ?? 1),
                'price' => (float)($details['price'] ?? 0),
            ];
        }

        $order = \App\Services\TableOrderService::addItemsToTableOrder($tableId, $items);
        session(['table_order_id' => $order->id]);
        session()->forget($cartKey);

        return redirect()->back()->with('success', 'Đã gửi thực đơn cho nhân viên! Vui lòng đợi xác nhận.');
    }

    public function delcart($id = null)
    {
        $cartKey = $this->getCartKey();
        $cart = session()->get($cartKey, []);

        if ($id == null) {
            session()->forget($cartKey);
        } else {
            if (array_key_exists($id, $cart)) {
                unset($cart[$id]);
                session()->put($cartKey, $cart);
            }
        }

        return redirect()->back()->with('success', 'Xóa thành công!');
    }

    private function ensureDeliveryAuth()
    {
        // Bắt buộc đăng nhập khi đặt hàng giao về (khách ngồi tại bàn quét QR vẫn đặt món bình thường)
        if (!session('table_id') && !Auth::check()) {
            session(['url.intended' => route('site.checkoutForm')]);
            return redirect()->route('site.login', ['redirect' => route('site.checkoutForm')])
                ->with('warning', 'Quý khách vui lòng đăng nhập tài khoản để tiến hành đặt hàng giao về.');
        }
        return null;
    }

    public function checkoutForm()
    {
        if ($authRedirect = $this->ensureDeliveryAuth()) {
            return $authRedirect;
        }

        $cartKey = $this->getCartKey();
        $cart = session()->get($cartKey, []);

        if (empty($cart)) {
            return redirect()->route('site.product.gridview')->with('error', 'Giỏ hàng của bạn đang trống. Hãy chọn món ăn yêu thích!');
        }

        return view('frontend.checkout', compact('cart'));
    }

    public function checkout(Request $request)
    {
        if ($authRedirect = $this->ensureDeliveryAuth()) {
            return $authRedirect;
        }

        $cartKey = $this->getCartKey();
        $cart = session()->get($cartKey, []);

        if (empty($cart)) {
            return redirect()->route('site.product.gridview')->with('error', 'Giỏ hàng của bạn đang trống.');
        }

        if (!session('table_id')) {
            $request->validate([
                'name' => 'required|string|max:100',
                'phone' => ['required', 'regex:/^0[35789][0-9]{8}$/'],
                'address' => 'required|string|max:255',
                'email' => 'nullable|email|max:100',
                'note' => 'nullable|string|max:500',
                'payment_method' => 'required|in:COD,VietQR',
            ], [
                'name.required' => 'Vui lòng nhập họ và tên người nhận hàng.',
                'phone.required' => 'Vui lòng nhập số điện thoại nhận hàng.',
                'phone.regex' => 'Số điện thoại không đúng định dạng di động Việt Nam (10 số, bắt đầu bằng 03, 05, 07, 08, 09).',
                'address.required' => 'Vui lòng cung cấp địa chỉ giao hàng chi tiết.',
                'payment_method.required' => 'Vui lòng chọn phương thức thanh toán.',
                'payment_method.in' => 'Phương thức thanh toán không hợp lệ.',
            ]);

            $distanceService = new \App\Services\DistanceService();
            $distanceCheck = $distanceService->isWithinDeliveryRange($request->address);
            
            if (!$distanceCheck['success']) {
                return redirect()->back()->withInput()->with('error', $distanceCheck['message']);
            }
        }

        try {
            $order = \Illuminate\Support\Facades\DB::transaction(function () use ($request, $cart) {
                // 1. Khóa và kiểm tra tồn kho (Tránh Race Condition và bán vượt tồn)
                $lockedProducts = [];
                foreach ($cart as $productId => $details) {
                    $product = Product::where('id', $productId)->lockForUpdate()->first();
                    if (!$product) {
                        throw new \Exception("Món ăn ID #{$productId} không tồn tại hoặc đã ngừng kinh doanh.");
                    }
                    if ($product->qty < $details['qty']) {
                        throw new \Exception("Món '{$product->name}' hiện chỉ còn {$product->qty} phần trong kho, không đủ số lượng bạn đặt ({$details['qty']} phần).");
                    }
                    $lockedProducts[$productId] = $product;
                }

                // 2. Tính toán tổng tiền và phí giao hàng qua PricingService
                $pricing = \App\Services\PricingService::calculateCartSummary($cart, !empty(session('table_id')));
                $subtotal = $pricing['subtotal'];
                $shippingFee = $pricing['shipping_fee'];
                $finalTotal = $pricing['total'];

                $userId = Auth::id() ?? 0;
                $tableId = session('table_id');

                // Nếu đang ngồi tại bàn, kiểm tra đơn hàng đang hoạt động để gộp món (Single Active Table Order)
                $existingOrder = $tableId ? \App\Services\TableOrderService::getActiveOrder($tableId) : null;

                if ($existingOrder) {
                    $order = $existingOrder;
                    if ($request->payment_method == 'TaiBan') {
                        $order->payment_method = 'TaiBan';
                    }
                    $order->channel = \App\Models\Order::CHANNEL_DINE_IN;

                    // Thêm hoặc gộp số lượng món ăn vào đơn hàng hiện tại
                    foreach ($cart as $productId => $details) {
                        $existingDetail = \App\Models\Orderdetail::where('order_id', $order->id)
                            ->where('product_id', $productId)
                            ->first();

                        if ($existingDetail) {
                            $existingDetail->qty += $details['qty'];
                            $existingDetail->amount = ($existingDetail->price - ($existingDetail->discount ?? 0)) * $existingDetail->qty;
                            $existingDetail->save();
                        } else {
                            $orderDetail = new \App\Models\Orderdetail();
                            $orderDetail->order_id = $order->id;
                            $orderDetail->product_id = $productId;
                            $orderDetail->qty = $details['qty'];
                            $orderDetail->price = $details['price'];
                            $orderDetail->discount = $details['discount'] ?? 0;
                            $orderDetail->amount = ($details['price'] - ($details['discount'] ?? 0)) * $details['qty'];
                            $orderDetail->order_turn = ($order->order_turn ?? 1) + 1;
                            $orderDetail->status = 'pending';
                            $orderDetail->save();
                        }

                        // Trừ tồn kho sản phẩm đã lock
                        $lockedProduct = $lockedProducts[$productId];
                        $lockedProduct->qty = max(0, $lockedProduct->qty - $details['qty']);
                        $lockedProduct->save();
                    }

                    $order->total_price = \App\Services\PricingService::recalculateOrderTotal($order->id, true);
                    $order->has_new_items = true;
                    $order->save();

                    session(['table_order_id' => $order->id]);
                    return $order;
                }

                $newOrder = new Order();
                $newOrder->user_id = $userId;
                $newOrder->name = strip_tags(trim($request->name ?? (Auth::user()->fullname ?? Auth::user()->username ?? 'Khách gọi món')));
                $newOrder->email = filter_var($request->email ?? (Auth::user()->email ?? ''), FILTER_SANITIZE_EMAIL);
                $newOrder->phone = preg_replace('/[^0-9]/', '', $request->phone ?? (Auth::user()->phone ?? ''));
                $newOrder->note = strip_tags(trim($request->note ?? ''));
                $newOrder->payment_method = $request->payment_method;
                $newOrder->address = strip_tags(trim($request->address ?? (session('table_name') ?: 'Tại quán')));
                $newOrder->created_by = $userId ?: 1;
                $newOrder->status = 0; // 0: Chờ xử lý / Tiếp nhận
                $newOrder->payment_status = \App\Models\Order::PAYMENT_PENDING;

                if ($tableId) {
                    $newOrder->table_id = $tableId;
                    $newOrder->orderStyle = 0;
                    $newOrder->channel = \App\Models\Order::CHANNEL_DINE_IN;
                    if ($request->payment_method == 'TaiBan') {
                        $newOrder->payment_method = 'TaiBan';
                    }
                } else {
                    $reqStyle = (int)($request->orderStyle ?? 0);
                    $diningMode = session('dining_mode');
                    if ($reqStyle === \App\Models\Order::STYLE_TAKEAWAY || $diningMode === 'takeaway') {
                        $newOrder->orderStyle = \App\Models\Order::STYLE_TAKEAWAY;
                        $newOrder->channel = \App\Models\Order::CHANNEL_TAKEAWAY;
                    } elseif ($reqStyle === \App\Models\Order::STYLE_POS) {
                        $newOrder->orderStyle = \App\Models\Order::STYLE_POS;
                        $newOrder->channel = \App\Models\Order::CHANNEL_POS;
                    } else {
                        $newOrder->orderStyle = \App\Models\Order::STYLE_DELIVERY;
                        $newOrder->channel = \App\Models\Order::CHANNEL_DELIVERY;
                    }
                }

                $newOrder->total_price = $finalTotal;
                $newOrder->save();

                // 4. Lưu chi tiết đơn hàng và trừ kho an toàn
                foreach ($cart as $productId => $details) {
                    $orderDetail = new Orderdetail();
                    $orderDetail->order_id = $newOrder->id;
                    $orderDetail->product_id = $productId;
                    $orderDetail->qty = $details['qty'];
                    $orderDetail->price = $details['price'];
                    $orderDetail->discount = $details['discount'] ?? 0;
                    $orderDetail->amount = ($details['price'] - ($details['discount'] ?? 0)) * $details['qty'];
                    $orderDetail->save();

                    // Trừ tồn kho sản phẩm đã lock
                    $lockedProduct = $lockedProducts[$productId];
                    $lockedProduct->qty = max(0, $lockedProduct->qty - $details['qty']);
                    $lockedProduct->save();
                }

                if ($tableId) {
                    session(['table_order_id' => $newOrder->id]);
                }

                return $newOrder;
            });

            // Bắn thông báo đơn hàng mới
            try {
                \App\Services\NotificationService::notifyNewOrder($order);
            } catch (\Exception $e) {
                \Log::error('Lỗi gửi notification new order: ' . $e->getMessage());
            }

            if ($request->payment_method == 'VietQR') {
                self::clearCartSession();
                return redirect()->route('site.vietqr.pay', $order->id);
            }

            self::clearCartSession();
            return redirect()->route('site.thanks')->with('success', 'Đặt hàng thành công!');

        } catch (\Throwable $e) {
            \Log::error('Lỗi trong quá trình thanh toán checkout: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function vietqrPay($orderId, \App\Services\VietQRService $vietQRService)
    {
        $order = Order::with('orderDetails.product')->findOrFail($orderId);
        $memo = 'DH' . $order->id;
        $qrUrl = $vietQRService->generateQrUrl($order->total_price, $memo);
        $bankInfo = $vietQRService->getBankInfo();

        return view('frontend.vietqr_pay', compact('order', 'qrUrl', 'memo', 'bankInfo'));
    }

    public function momoSandboxPay($orderId)
    {
        $order = Order::findOrFail($orderId);
        return view('frontend.momo_sandbox', compact('order'));
    }

    public function momoSandboxConfirm(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);
        $order->status = 1;
        $order->payment_method = 'Momo';
        $order->save();

        self::clearCartSession();

        return redirect()->route('site.thanks')->with('success', 'Thanh toán MoMo thành công! Đơn hàng đã được chuyển đến nhà bếp chuẩn bị.');
    }

    public function momoReturn(Request $request, MomoService $momoService)
    {
        $data = $request->all();
        if (isset($data['signature']) && $momoService->verifyPayment($data)) {
            $order = Order::find($data['orderId']);
            if ($order && $data['resultCode'] == 0) {
                $order->status = 1;
                $order->payment_method = 'Momo';
                $order->save();
                self::clearCartSession();
                return redirect()->route('site.thanks')->with('success', 'Thanh toán Momo thành công!');
            }
        }
        return redirect()->route('site.product.gridview')->with('error', 'Thanh toán Momo thất bại!');
    }

    public function momoIpn(Request $request, MomoService $momoService)
    {
        $data = $request->all();
        if (isset($data['signature']) && $momoService->verifyPayment($data)) {
            $order = Order::find($data['orderId']);
            if ($order && $data['resultCode'] == 0 && $order->status != 1) {
                $order->status = 1;
                $order->payment_method = 'Momo';
                $order->save();
            }
            return response()->json(['message' => 'Success']);
        }
        return response()->json(['message' => 'Error'], 400);
    }

    public function thanks()
    {
        return view('frontend.thanks');
    }

    public function clearKitchenSession()
    {
        session()->forget('kitchen_print_order_id');
        return response()->json(['success' => true]);
    }
}
