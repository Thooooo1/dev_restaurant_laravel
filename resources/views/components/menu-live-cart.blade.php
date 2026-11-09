@php
    $cartKey = Auth::check() ? 'cart_' . Auth::id() : 'cart_guest_' . session()->getId();
    $sessionCart = session()->get($cartKey, []);
    
    // Mode phục vụ: mặc định 'dine_in' cho trải nghiệm ăn tại quán, hoặc 'delivery'
    $diningMode = session('dining_mode', 'dine_in');
    $isTableOrder = ($diningMode === 'dine_in');
    
    $tableName = session('table_name');
    $tableId = session('table_id');
    $tableFloorName = session('table_floor_name');
    $tableOrderId = session('table_order_id');
    
    // Đảm bảo giỏ hàng sạch khi đơn hàng bàn này đã thanh toán (status 1 hoặc 2)
    if ($isTableOrder && $tableOrderId) {
        $chkOrder = \App\Models\Order::find($tableOrderId);
        if ($chkOrder && in_array($chkOrder->status, [1, 2]) && !empty($sessionCart)) {
            \App\Http\Controllers\frontend\CartController::clearCartSession();
            $sessionCart = [];
        }
    }
    
    // Đếm tổng số lượng món
    $cartCount = 0;
    $cartSubtotal = 0;
    foreach ($sessionCart as $it) {
        $q = (int)($it['qty'] ?? 1);
        $p = (float)($it['price'] ?? 0);
        $cartCount += $q;
        $cartSubtotal += ($p * $q);
    }
    
    $discountPercent = $isTableOrder ? 10 : 0;
    $discountAmount = $isTableOrder ? round($cartSubtotal * 0.1) : 0;
    $finalTotal = max(0, $cartSubtotal - $discountAmount);

    // Lấy món đã gửi vào bếp trước đó của bàn
    $kitchenItems = [];
    if ($isTableOrder && $tableOrderId) {
        $currOrder = \App\Models\Order::where('id', $tableOrderId)
            ->whereNull('deleted_at')
            ->with(['orderDetails.product'])
            ->first();
        if ($currOrder && in_array($currOrder->status, [0, 1])) {
            $kitchenItems = $currOrder->orderDetails;
        }
    }

    // Danh sách tầng & bàn để hiển thị trong modal chọn bàn (kèm trạng thái gọi món thực tế)
    $pickerFloors = \App\Models\Floor::with(['tables' => function($q) {
        $q->orderBy('sort_order', 'ASC')->orderBy('id', 'ASC');
    }, 'tables.orders' => function($q) {
        $q->whereIn('status', [0, 1])->whereNull('deleted_at');
    }])->orderBy('id', 'ASC')->get();
@endphp

<!-- ======================================================== -->
<!-- LIVE CART PANEL: DESKTOP STICKY (Ghim bên phải thực đơn)  -->
<!-- ======================================================== -->
<div id="menuLiveCartContainer" class="hidden lg:flex bg-white rounded-none sm:rounded-sm border border-stone-200 shadow-2xs overflow-hidden flex-col transition-all">
    
    <!-- 1. Header Giỏ Hàng (My Order) -->
    <div class="p-4 bg-white border-b border-stone-100 flex items-center justify-between">
        <div>
            <h3 class="text-base font-extrabold text-stone-900 tracking-tight">Đơn Hàng Của Bạn</h3>
            <p class="text-[11px] text-stone-400 font-medium">
                <span id="liveCartItemCountBadge">{{ $cartCount }}</span> món đã chọn
            </p>
        </div>

        <div class="flex items-center gap-1">
            <button type="button" 
                    onclick="clearLiveCart()" 
                    id="clearLiveCartBtn"
                    class="w-8 h-8 rounded-none sm:rounded-sm hover:bg-rose-50 text-stone-400 hover:text-rose-600 flex items-center justify-center transition-colors cursor-pointer {{ $cartCount == 0 ? 'hidden' : '' }}"
                    title="Làm trống toàn bộ giỏ">
                <i class="fa-regular fa-trash-can text-sm"></i>
            </button>
            <button type="button" 
                    onclick="window.toggleLiveCart ? window.toggleLiveCart(false) : null" 
                    id="collapseLiveCartBtn"
                    class="w-8 h-8 rounded-none sm:rounded-sm hover:bg-stone-100 text-stone-400 hover:text-stone-700 flex items-center justify-center transition-colors cursor-pointer"
                    title="Thu gọn giỏ hàng để xem menu rộng hơn">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>
    </div>

    <!-- 2. HÌNH THỨC PHỤC VỤ (Tại quán (-10%) | Giao hàng) -->
    <div class="px-4 py-3 bg-stone-50/70 border-b border-stone-100">
        <div class="grid grid-cols-2 p-1 bg-stone-200/60 rounded-none sm:rounded-sm text-xs font-bold gap-1">
            <button type="button" 
                    id="diningModeDineInBtn"
                    onclick="setDiningModeLive('dine_in')" 
                    class="py-2 px-2 rounded-none sm:rounded-sm flex items-center justify-center gap-1.5 transition-all cursor-pointer {{ $isTableOrder ? 'bg-[#F47716] text-white shadow-2xs' : 'text-stone-600 hover:text-stone-900' }}">
                <span>Tại quán</span>
                <span class="text-[9.5px] bg-white/25 text-white px-1.5 py-0.5 rounded-none font-bold leading-tight">-10%</span>
            </button>
            <button type="button" 
                    id="diningModeDeliveryBtn"
                    onclick="setDiningModeLive('delivery')" 
                    class="py-2 px-2 rounded-none sm:rounded-sm flex items-center justify-center gap-1.5 transition-all cursor-pointer {{ !$isTableOrder ? 'bg-[#F47716] text-white shadow-2xs' : 'text-stone-600 hover:text-stone-900' }}">
                <span>Giao hàng</span>
            </button>
        </div>
    </div>

    <!-- 3. THÔNG TIN BÀN ĂN (Hiển thị khi chọn hình thức Ăn tại quán) -->
    <div id="liveCartTableInfoContainer" class="{{ $isTableOrder ? '' : 'hidden' }}">
        @if ($tableName)
            <div id="liveCartTableInfoRow" class="px-4 py-2.5 bg-orange-50/80 border-b border-orange-200/80 flex items-center justify-between text-xs">
                <div class="text-stone-900 font-semibold truncate pr-2">
                    <span>
                        Vị trí: <strong class="text-[#F47716] font-black" id="liveCartTableNameText">{{ $tableName }}</strong>
                        @if($tableFloorName)
                            <span class="text-stone-500 font-normal text-[11px]" id="liveCartTableFloorText">({{ $tableFloorName }})</span>
                        @endif
                    </span>
                </div>
                <button type="button" 
                        onclick="openTablePickerModal()" 
                        class="text-xs text-[#F47716] hover:text-[#EA580C] font-bold hover:underline shrink-0 cursor-pointer">
                    [Đổi bàn]
                </button>
            </div>
        @else
            <div id="liveCartTableInfoRow" class="px-4 py-2.5 bg-amber-50 border-b border-amber-200 flex items-center justify-between text-xs">
                <div class="text-amber-900 font-medium">
                    <span id="liveCartTableNameText">Chưa chọn số bàn phục vụ</span>
                </div>
                <button type="button" 
                        onclick="openTablePickerModal()" 
                        class="px-2.5 py-1 bg-[#F47716] hover:bg-[#EA580C] text-white text-[11px] font-bold rounded-none sm:rounded-sm shadow-2xs transition-colors cursor-pointer">
                    + Chọn bàn (-10%)
                </button>
            </div>
        @endif
    </div>

    <!-- 4. Danh sách Món trong Giỏ (Scrollable) -->
    <div id="liveCartItemsList" class="p-3.5 space-y-3 max-h-[340px] overflow-y-auto divide-y divide-stone-100">
        @if (count($sessionCart) == 0)
            <div id="liveCartEmptyState" class="py-5 px-3 text-center">
                <div class="w-10 h-10 rounded-none sm:rounded-sm bg-orange-50 text-[#F47716] flex items-center justify-center mx-auto text-sm mb-2 border border-orange-100">
                    <i class="fa-solid fa-utensils"></i>
                </div>
                <p class="text-xs sm:text-sm font-bold text-stone-800">Đơn hàng đang trống</p>
                <p class="text-xs text-stone-500 mt-1 leading-snug">
                    Bấm <strong class="text-[#F47716] font-bold">+ Chọn</strong> ở từng món để thêm vào thực đơn gọi món
                </p>
            </div>
        @else
            @foreach ($sessionCart as $id => $item)
                @php
                    $imgSrc = !empty($item['image']) && file_exists(public_path('images/product/' . $item['image']))
                        ? asset('images/product/' . $item['image'])
                        : asset('images/default-product.png');
                    $itPrice = (float)($item['price'] ?? 0);
                    $itQty = (int)($item['qty'] ?? 1);
                    $itTotal = $itPrice * $itQty;
                @endphp
                <div class="pt-3 first:pt-0 flex items-center gap-3 group" id="live-cart-item-{{ $id }}">
                    <img src="{{ $imgSrc }}" alt="{{ $item['name'] }}" class="w-12 h-12 rounded-lg object-cover shrink-0 border border-stone-100">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-1">
                            <h4 class="text-xs font-bold text-stone-800 truncate" title="{{ $item['name'] }}">{{ $item['name'] }}</h4>
                            <button type="button" onclick="removeLiveCartItem({{ $id }})" class="text-stone-300 hover:text-rose-500 text-xs transition-colors p-0.5 cursor-pointer" title="Xóa món">
                                <i class="fa-regular fa-trash-can text-xs"></i>
                            </button>
                        </div>
                        <p class="text-[10.5px] text-stone-400">Phần tiêu chuẩn</p>

                        <div class="flex items-center justify-between mt-1.5">
                            <span class="text-xs font-black text-[#C25E00] tracking-tight">
                                {{ number_format($itTotal, 0, ',', '.') }}₫
                            </span>

                            <!-- Stepper Box 6px -->
                            <div class="inline-flex items-center border border-stone-200 rounded-md px-1.5 py-0.5 bg-stone-50 text-xs">
                                <button type="button" onclick="changeLiveCartQty({{ $id }}, {{ $itQty - 1 }})" class="w-5 h-5 flex items-center justify-center text-stone-500 hover:text-stone-900 transition-colors font-bold cursor-pointer">
                                    -
                                </button>
                                <span class="w-6 text-center font-bold text-stone-800 text-xs live-cart-qty-val">{{ $itQty < 10 ? '0' . $itQty : $itQty }}</span>
                                <button type="button" onclick="changeLiveCartQty({{ $id }}, {{ $itQty + 1 }})" class="w-5 h-5 flex items-center justify-center text-stone-500 hover:text-stone-900 transition-colors font-bold cursor-pointer">
                                    +
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <!-- 5. Tóm tắt chi phí & Phương thức thanh toán (Summary) -->
    <div class="p-4 bg-stone-50/80 border-t border-stone-200/80 space-y-2 text-xs">
        <div class="flex items-center justify-between text-stone-500">
            <span>Tạm tính (Items):</span>
            <span id="liveCartSubtotal" class="font-bold text-stone-800 tracking-tight">{{ number_format($cartSubtotal, 0, ',', '.') }}₫</span>
        </div>

        <div id="liveCartDiscountRow" class="flex items-center justify-between text-emerald-600 {{ $isTableOrder ? '' : 'hidden' }}">
            <span>Ưu đãi tại bàn (10%):</span>
            <span id="liveCartDiscount" class="font-bold text-emerald-600 tracking-tight">-{{ number_format($discountAmount, 0, ',', '.') }}₫</span>
        </div>

        <div class="pt-2 border-t border-stone-200 flex items-center justify-between">
            <span class="font-bold text-stone-900 text-sm">Tổng thanh toán:</span>
            <span id="liveCartFinalTotal" class="font-black text-[#F47716] text-xl tracking-tight">{{ number_format($finalTotal, 0, ',', '.') }}₫</span>
        </div>

        <!-- Phương thức thanh toán (Payments) - Chỉ hiển thị khi Giao hàng tận nơi -->
        <div id="liveCartPaymentMethodsWrapper" class="pt-3 border-t border-stone-200/60 {{ $isTableOrder ? 'hidden' : '' }}">
            <span class="text-[11px] font-bold text-stone-500 uppercase tracking-wider block mb-1.5">Phương thức thanh toán</span>
            <div class="grid grid-cols-2 gap-1.5">
                <button type="button" onclick="selectPaymentPill('cash', this)" class="payment-pill-btn py-1.5 px-1 rounded-lg text-center text-xs font-bold transition-all border border-stone-900 bg-stone-900 text-white shadow-xs cursor-pointer">
                    Tiền mặt
                </button>
                <button type="button" onclick="selectPaymentPill('vietqr', this)" class="payment-pill-btn py-1.5 px-1 rounded-lg text-center text-xs font-bold transition-all border border-stone-200 bg-white text-stone-700 hover:bg-stone-100 cursor-pointer">
                    VietQR
                </button>
            </div>
        </div>

        <!-- Nút hành động chính (CTA) -->
        <div class="pt-3 space-y-2">
            <!-- Nhóm CTA cho Ăn tại quán (Xác nhận gọi món) -->
            <div id="liveCartDineInActions" class="space-y-2 {{ $isTableOrder ? '' : 'hidden' }}">
                <button type="button" 
                        id="btnSendToKitchen" 
                        onclick="sendToKitchenLive()" 
                        {{ $cartCount == 0 ? 'disabled' : '' }}
                        class="w-full py-3.5 px-4 bg-[#F47716] hover:bg-[#EA580C] disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold text-xs uppercase tracking-wider rounded-none sm:rounded-sm shadow-2xs transition-all flex items-center justify-center cursor-pointer active:scale-[0.98]">
                    <span>XÁC NHẬN GỌI MÓN</span>
                </button>
            </div>

            <!-- Nhóm CTA cho Giao tận nơi -->
            <div id="liveCartDeliveryActions" class="{{ !$isTableOrder ? '' : 'hidden' }}">
                <a href="{{ route('site.checkoutForm') }}" 
                   id="btnCheckoutLive"
                   class="w-full py-3.5 px-4 bg-[#F47716] hover:bg-[#EA580C] text-white font-bold text-xs uppercase tracking-wider rounded-none sm:rounded-sm shadow-2xs transition-all flex items-center justify-center text-center {{ $cartCount == 0 ? 'pointer-events-none opacity-50' : '' }}">
                    <span>Tiến hành đặt hàng giao tận nơi</span>
                </a>
            </div>
        </div>
    </div>

    <!-- 6. Món đã gọi trước đó của Bàn (Accordion - Không dùng icon) -->
    <div id="liveCartKitchenHistoryWrapper" class="{{ ($isTableOrder && count($kitchenItems) > 0) ? '' : 'hidden' }} border-t border-stone-200/90 bg-stone-100/50">
        <button type="button" 
                onclick="toggleKitchenHistory()" 
                class="w-full p-3 flex items-center justify-between text-xs text-stone-700 hover:bg-stone-100 transition-colors font-bold">
            <span>Món đã gửi vào Bếp (<span id="kitchenHistoryCountBadge">{{ count($kitchenItems) }}</span> món)</span>
            <span class="text-[10px] text-stone-400 font-mono transition-transform duration-200" id="kitchenHistoryIcon">▼</span>
        </button>

        <div id="kitchenHistoryContent" class="hidden px-3 pb-3 max-h-[220px] overflow-y-auto divide-y divide-stone-200/60 text-xs">
            @foreach ($kitchenItems as $kItem)
                <div class="py-2 flex items-center justify-between">
                    <div class="min-w-0 pr-2">
                        <span class="font-semibold text-stone-800 block truncate">{{ $kItem->product->name ?? 'Món đã chọn' }}</span>
                        <span class="text-[10px] text-stone-500">Số lượng: x{{ $kItem->qty }} • {{ number_format($kItem->price, 0, ',', '.') }}₫</span>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold shrink-0 {{ $kItem->status == 'completed' ? 'bg-emerald-100 text-emerald-700' : ($kItem->status == 'cooking' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700') }}">
                        {{ $kItem->status == 'completed' ? 'Đã lên bàn' : ($kItem->status == 'cooking' ? 'Đang nấu' : 'Chờ bếp nhận') }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MOBILE FLOATING BOTTOM BAR (< 1024px - An toàn iOS/Android) -->
<!-- ======================================================== -->
<div id="mobileFloatingCartBar" 
     class="block lg:hidden fixed bottom-[58px] md:bottom-0 left-0 right-0 z-40 bg-stone-900 text-white shadow-2xl border-t border-stone-800 p-3 {{ $cartCount == 0 ? 'hidden' : '' }}"
     style="padding-bottom: calc(0.75rem + env(safe-area-inset-bottom, 0px));">
    <div class="container mx-auto flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5 cursor-pointer select-none" onclick="openMobileCartDrawer()">
            <div class="relative w-8 h-8 rounded-full bg-[#C25E00] flex items-center justify-center text-xs font-bold shadow-xs">
                <span id="mobileCartFloatingBadge">{{ $cartCount }}</span>
            </div>
            <div>
                <div class="text-[11px] text-stone-400">
                    <span id="mobileFloatingModeText">{{ $isTableOrder ? 'Ăn tại quán (-10%):' : 'Giao tận nơi:' }}</span>
                </div>
                <div id="mobileCartFloatingTotal" class="text-base font-black text-amber-300 tracking-tight">
                    {{ number_format($finalTotal, 0, ',', '.') }}₫
                </div>
            </div>
        </div>

        <button type="button" 
                onclick="openMobileCartDrawer()" 
                class="px-4 py-2 bg-[#C25E00] hover:bg-orange-600 text-white text-xs font-bold uppercase tracking-wider rounded shadow-md transition-colors cursor-pointer select-none active:scale-95">
            <span>{{ $isTableOrder ? 'Gọi Món' : 'Xem Giỏ Hàng' }}</span>
        </button>
    </div>
</div>

<!-- ======================================================== -->
<!-- MOBILE CART DRAWER (Bottom Sheet chuẩn iOS / Android)     -->
<!-- ======================================================== -->
<div id="mobileCartDrawerOverlay" class="fixed inset-0 bg-stone-950/60 z-[999998] backdrop-blur-xs hidden transition-opacity" style="z-index: 999998 !important;" onclick="closeMobileCartDrawer()"></div>
<div id="mobileCartDrawer" 
     class="fixed bottom-0 left-0 right-0 z-[999999] bg-white rounded-t-2xl shadow-2xl max-h-[85vh] max-h-[85dvh] flex flex-col transform translate-y-full transition-transform duration-300 ease-out lg:hidden"
     style="z-index: 999999 !important; padding-bottom: calc(1rem + env(safe-area-inset-bottom, 0px));">
    <!-- Header Drawer -->
    <div class="p-4 border-b border-stone-200 flex items-center justify-between bg-stone-50 rounded-t-2xl">
        <div>
            <h3 class="text-sm font-bold text-stone-900">Chi tiết giỏ hàng</h3>
            <p class="text-[11px] text-stone-500" id="mobileDrawerItemCount">{{ $cartCount }} món ăn đã chọn</p>
        </div>
        <button type="button" onclick="closeMobileCartDrawer()" class="w-8 h-8 rounded-full bg-stone-200 text-stone-600 flex items-center justify-center hover:bg-stone-300 font-bold cursor-pointer">
            ×
        </button>
    </div>

    <!-- Dining Mode Selector trên Mobile Drawer -->
    <div class="p-3 bg-stone-100 border-b border-stone-200">
        <div class="grid grid-cols-2 p-1 bg-stone-200 rounded text-xs font-bold gap-1">
            <button type="button" 
                    id="mobileDiningModeDineInBtn"
                    onclick="setDiningModeLive('dine_in')" 
                    class="py-1.5 rounded flex items-center justify-center gap-1 {{ $isTableOrder ? 'bg-white text-[#C25E00] shadow-xs' : 'text-stone-600' }}">
                <span>Ăn tại quán</span>
                <span class="text-[9px] bg-emerald-500 text-white px-1 rounded-full font-bold">-10%</span>
            </button>
            <button type="button" 
                    id="mobileDiningModeDeliveryBtn"
                    onclick="setDiningModeLive('delivery')" 
                    class="py-1.5 rounded flex items-center justify-center gap-1 {{ !$isTableOrder ? 'bg-white text-stone-900 shadow-xs' : 'text-stone-600' }}">
                <span>Giao tận nơi</span>
            </button>
        </div>

        <!-- Mobile Table Bar -->
        <div id="mobileDrawerTableBar" class="mt-2 flex items-center justify-between text-xs {{ $isTableOrder ? '' : 'hidden' }}">
            <span class="text-stone-700 font-semibold truncate">
                <span id="mobileDrawerTableNameText">{{ $tableName ? "Đang dùng tại: {$tableName}" : "Chưa chọn số bàn" }}</span>
            </span>
            <button type="button" onclick="openTablePickerModal()" class="text-xs text-[#C25E00] font-bold underline cursor-pointer">
                {{ $tableName ? '[Đổi bàn]' : '[+ Chọn bàn]' }}
            </button>
        </div>
    </div>

    <!-- Content Drawer -->
    <div id="mobileDrawerItemsList" class="p-4 space-y-3 overflow-y-auto flex-1 divide-y divide-stone-100">
        <!-- Render động từ JavaScript đồng bộ với Desktop Cart -->
    </div>

    <!-- Footer Drawer -->
    <div class="p-4 bg-stone-50 border-t border-stone-200 space-y-3">
        <div class="flex items-center justify-between text-sm">
            <span class="font-bold text-stone-700">Tổng thanh toán:</span>
            <span id="mobileDrawerFinalTotal" class="text-xl font-black text-[#C25E00] tracking-tight">{{ number_format($finalTotal, 0, ',', '.') }}₫</span>
        </div>

        <div id="mobileDrawerDineInActions" class="space-y-2 {{ $isTableOrder ? '' : 'hidden' }}">
            <button type="button" onclick="sendToKitchenLive()" class="w-full py-3.5 bg-[#F47716] hover:bg-[#EA580C] text-white font-bold text-xs uppercase tracking-wider rounded-none sm:rounded-sm shadow-2xs flex items-center justify-center cursor-pointer active:scale-[0.98]">
                <span>XÁC NHẬN GỌI MÓN</span>
            </button>
        </div>

        <div id="mobileDrawerDeliveryActions" class="{{ !$isTableOrder ? '' : 'hidden' }}">
            <a href="{{ route('site.checkoutForm') }}" class="w-full py-3.5 bg-[#F47716] hover:bg-[#EA580C] text-white font-bold text-xs uppercase tracking-wider rounded-none sm:rounded-sm shadow-2xs flex items-center justify-center text-center cursor-pointer active:scale-[0.98]">
                <span>Tiến hành đặt hàng giao tận nơi</span>
            </a>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL CHỌN BÀN ĂN PHỤC VỤ (Chuẩn Nghiệp Vụ Nhà Hàng)      -->
<!-- ======================================================== -->
<div id="tablePickerModalOverlay" class="fixed inset-0 bg-stone-950/60 z-[999999] backdrop-blur-xs hidden transition-opacity flex items-center justify-center p-4" style="z-index: 999999 !important;">
    <div id="tablePickerModal" class="relative z-[1000000] bg-white rounded-none sm:rounded-sm shadow-2xl max-w-2xl w-full max-h-[90vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150 border border-stone-300" style="z-index: 1000000 !important;">
        
        <!-- Modal Header: Sáng, rõ ràng, dễ đọc, chuẩn nghiệp vụ -->
        <div class="px-5 py-4 bg-white border-b border-stone-200 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-stone-900 tracking-tight">Chọn Bàn Phục Vụ</h3>
                <p class="text-xs text-stone-500">Quý khách vui lòng chọn bàn trống để nhà hàng phục vụ tại chỗ</p>
            </div>
            <button type="button" 
                    onclick="closeTablePickerModal()" 
                    class="w-8 h-8 rounded-none sm:rounded-sm text-stone-400 hover:text-stone-700 hover:bg-stone-100 flex items-center justify-center text-lg font-bold transition-colors cursor-pointer"
                    title="Đóng">
                ✕
            </button>
        </div>

        <!-- Chú thích trạng thái (Legend) & Bộ lọc Khu vực (Tầng) -->
        <div class="px-5 py-3 bg-stone-50 border-b border-stone-200 space-y-2.5">
            <!-- Chú thích trực quan -->
            <div class="flex items-center gap-4 text-xs font-medium text-stone-600">
                <span class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <span>Bàn trống</span>
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#F47716]"></span>
                    <span>Bàn đang chọn</span>
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-stone-400"></span>
                    <span>Đang có khách</span>
                </span>
            </div>

            <!-- Tab Khu vực / Tầng (Chuẩn phẳng, không bo tròn viên thuốc) -->
            <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pt-1">
                <button type="button" 
                        onclick="filterPickerFloor('all')" 
                        class="picker-floor-tab px-3.5 py-1.5 rounded-none sm:rounded-sm text-xs font-bold transition-all bg-[#F47716] text-white shadow-2xs" 
                        data-floor="all">
                    Tất cả khu vực
                </button>
                @foreach ($pickerFloors as $fl)
                    <button type="button" 
                            onclick="filterPickerFloor('{{ $fl->id }}')" 
                            class="picker-floor-tab px-3.5 py-1.5 rounded-none sm:rounded-sm text-xs font-medium transition-all bg-white text-stone-700 hover:bg-stone-100 border border-stone-200" 
                            data-floor="{{ $fl->id }}">
                        {{ $fl->name }} ({{ $fl->tables->count() }})
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Danh sách bàn theo từng tầng (Scrollable) -->
        <div class="p-5 overflow-y-auto max-h-[55vh] space-y-5 bg-[#F8FAFC]">
            @foreach ($pickerFloors as $fl)
                <div class="picker-floor-section" data-floor-section="{{ $fl->id }}">
                    <div class="mb-2.5 flex items-center justify-between text-xs font-bold text-stone-700 uppercase tracking-wider">
                        <span>{{ $fl->name }}</span>
                        <span class="text-[11px] font-normal text-stone-400 lowercase">{{ $fl->tables->count() }} bàn</span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        @foreach ($fl->tables as $t)
                            @php
                                $isCurrentSelected = ($tableId == $t->id);
                                $activeOrder = $t->orders->first();
                                $isOccupied = ($activeOrder && $t->id != $tableId && $activeOrder->id != $tableOrderId);
                                $isDisabled = ($t->status == 0);
                            @endphp

                            @if ($isDisabled)
                                <!-- Bàn tạm ngưng -->
                                <div class="p-3 rounded-none sm:rounded-sm border border-dashed border-stone-300 bg-stone-100 opacity-50 text-center cursor-not-allowed select-none">
                                    <div class="font-bold text-sm text-stone-400">{{ $t->name }}</div>
                                    <div class="text-xs text-stone-400 mt-0.5">{{ $t->seats }} ghế</div>
                                    <span class="inline-block px-2 py-0.5 mt-2 rounded-none text-[10px] font-semibold bg-stone-200 text-stone-500">
                                        Tạm ngưng
                                    </span>
                                </div>
                            @elseif ($isOccupied)
                                <!-- Bàn đang có khách (Màu xám, khóa bấm) -->
                                <div onclick="showCartToast('{{ addslashes($t->name) }} hiện đang có khách. Vui lòng chọn bàn trống khác!', 'warning')" 
                                     class="p-3 rounded-none sm:rounded-sm border border-stone-200 bg-stone-100/90 text-center cursor-not-allowed select-none transition-all opacity-70"
                                     title="Bàn đang phục vụ khách">
                                    <div class="font-bold text-sm text-stone-600">{{ $t->name }}</div>
                                    <div class="text-xs text-stone-400 mt-0.5">{{ $t->seats }} ghế</div>
                                    <span class="inline-block px-2 py-0.5 mt-2 rounded-none text-[10px] font-semibold bg-stone-200 text-stone-600">
                                        Có khách
                                    </span>
                                </div>
                            @elseif ($isCurrentSelected)
                                <!-- Bàn hiện tại đang chọn -->
                                <div class="p-3 rounded-none sm:rounded-sm border-2 border-[#F47716] bg-orange-50 text-center cursor-pointer transition-all shadow-2xs">
                                    <div class="font-bold text-sm text-[#F47716]">{{ $t->name }}</div>
                                    <div class="text-xs text-stone-600 mt-0.5">{{ $t->seats }} ghế</div>
                                    <span class="inline-block px-2 py-0.5 mt-2 rounded-none text-[10px] font-bold bg-[#F47716] text-white">
                                        Đang chọn
                                    </span>
                                </div>
                            @else
                                <!-- Bàn trống sẵn sàng phục vụ -->
                                <div onclick="selectDiningTable({{ $t->id }}, '{{ addslashes($t->name) }}', '{{ addslashes($fl->name) }}')"
                                     class="p-3 rounded-none sm:rounded-sm border border-stone-200 hover:border-[#F47716] hover:bg-orange-50/40 bg-white text-center cursor-pointer transition-all shadow-2xs group">
                                    <div class="font-bold text-sm text-stone-800 group-hover:text-[#F47716] transition-colors">{{ $t->name }}</div>
                                    <div class="text-xs text-stone-500 mt-0.5">{{ $t->seats }} ghế</div>
                                    <span class="inline-block px-2 py-0.5 mt-2 rounded-none text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                        Bàn trống
                                    </span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Modal Footer: Gọn gàng, thực tế -->
        <div class="px-5 py-3 bg-white border-t border-stone-200 flex items-center justify-between text-xs">
            <span class="text-stone-500">
                Nhấp vào bàn trống để chọn vị trí dùng bữa
            </span>
            <button type="button" 
                    onclick="closeTablePickerModal()" 
                    class="px-4 py-2 bg-stone-100 hover:bg-stone-200 text-stone-700 font-semibold text-xs rounded-none sm:rounded-sm transition-colors cursor-pointer border border-stone-200">
                Đóng
            </button>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- FLOATING CART BUTTON (Nút nổi giỏ hàng khi thu gọn)       -->
<!-- ======================================================== -->
<div id="floatingCartButton" 
     onclick="window.toggleLiveCart ? window.toggleLiveCart(true) : null"
     class="hidden fixed bottom-6 right-6 z-40 bg-[#1C1917] hover:bg-black text-white px-4 py-2.5 rounded-none sm:rounded-sm shadow-xl border border-stone-700 items-center gap-3 cursor-pointer transition-all hover:scale-105">
    <div class="relative">
        <i class="fa-solid fa-cart-shopping text-[#F47716] text-base"></i>
        <span id="floatingCartCountBadge" class="absolute -top-2 -right-2.5 bg-[#F47716] text-white text-[10px] font-black min-w-4 h-4 px-1 rounded-full flex items-center justify-center">
            {{ $cartCount }}
        </span>
    </div>
    <div class="text-left">
        <div class="text-[10px] text-stone-400 font-medium leading-tight">Đơn hàng của bạn</div>
        <div id="floatingCartTotalText" class="text-xs font-black text-[#F47716]">{{ number_format($finalTotal, 0, ',', '.') }}₫</div>
    </div>
    <i class="fa-solid fa-chevron-up text-xs text-stone-400 ml-1"></i>
</div>

<!-- ======================================================== -->
<!-- JAVASCRIPT XỬ LÝ LIVE CART & CHUYỂN ĐỔI BÀN ĂN / GIAO HÀNG-->
<!-- ======================================================== -->
<script>
    // URL API Endpoints
    const CART_ENDPOINTS = {
        add: '{{ url("/cart/add-ajax") }}',
        updateQty: '{{ route("site.cart.updateQtyAjax") }}',
        remove: '{{ url("/cart/remove-ajax") }}',
        clear: '{{ route("site.cart.clearAjax") }}',
        liveData: '{{ route("site.cart.liveData") }}',
        sendKitchen: '{{ route("site.cart.sendKitchenAjax") }}',
        payTableNow: '{{ route("site.cart.payTableNowAjax") }}',
        switchDiningMode: '{{ route("site.cart.switchDiningMode") }}',
        csrf: '{{ csrf_token() }}'
    };

    // State hiện tại của Giỏ hàng & Chế độ phục vụ
    let liveCartState = {
        count: {{ $cartCount }},
        subtotal: {{ $cartSubtotal }},
        total: {{ $finalTotal }},
        dining_mode: '{{ $diningMode }}',
        is_table_order: {{ $isTableOrder ? 'true' : 'false' }},
        table_id: {{ $tableId ? $tableId : 'null' }},
        table_name: '{{ addslashes($tableName ?? "") }}',
        floor_name: '{{ addslashes($tableFloorName ?? "") }}',
        items: []
    };

    // Cờ đánh dấu nếu người dùng bấm "Gửi Bếp Nấu" nhưng chưa chọn bàn
    let pendingActionAfterTableSelect = null;

    // Chuyển đổi giữa Ăn tại quán (dine_in) và Giao tận nơi (delivery)
    window.setDiningModeLive = function(mode, tableId = null) {
        fetch(CART_ENDPOINTS.switchDiningMode, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CART_ENDPOINTS.csrf,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ mode: mode, table_id: tableId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                renderLiveCartFromData(data);
                showCartToast(data.message || (mode === 'dine_in' ? 'Đã chuyển sang: Ăn tại quán (-10%)' : 'Đã chuyển sang: Giao tận nơi'), 'success');
            }
        })
        .catch(err => {
            console.error('Lỗi chuyển hình thức phục vụ:', err);
            showCartToast('Lỗi kết nối, vui lòng thử lại!', 'error');
        });
    };

    // Chọn phương thức thanh toán nhanh
    window.selectPaymentPill = function(method, btn) {
        document.querySelectorAll('.payment-pill-btn').forEach(b => {
            b.className = 'payment-pill-btn py-1.5 px-1 rounded-lg text-center text-xs font-bold transition-all border border-stone-200 bg-white text-stone-700 hover:bg-stone-100 cursor-pointer';
        });
        btn.className = 'payment-pill-btn py-1.5 px-1 rounded-lg text-center text-xs font-bold transition-all border border-stone-900 bg-stone-900 text-white shadow-xs cursor-pointer';
    };
    // Di chuyển modal & drawer ra ngoài document.body để thoát khỏi mọi stacking context z-index cục bộ
    function ensurePortalToBody(elementId) {
        const el = document.getElementById(elementId);
        if (el && el.parentElement !== document.body) {
            document.body.appendChild(el);
        }
        return el;
    }

    window.openTablePickerModal = function(pendingAction = null) {
        if (pendingAction) {
            pendingActionAfterTableSelect = pendingAction;
        }
        const modal = ensurePortalToBody('tablePickerModalOverlay');
        if (modal) modal.classList.remove('hidden');
    };

    window.closeTablePickerModal = function() {
        const modal = document.getElementById('tablePickerModalOverlay');
        if (modal) modal.classList.add('hidden');
        pendingActionAfterTableSelect = null;
    };

    // Chọn 1 bàn cụ thể từ Modal
    window.selectDiningTable = function(tableId, tableName, floorName) {
        const actionToResume = pendingActionAfterTableSelect;
        closeTablePickerModal();
        
        // Gọi backend lưu session bàn và áp dụng 10% chiết khấu
        fetch(CART_ENDPOINTS.switchDiningMode, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CART_ENDPOINTS.csrf,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ mode: 'dine_in', table_id: tableId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                renderLiveCartFromData(data);
                if (typeof window.syncServiceCallTable === 'function') {
                    window.syncServiceCallTable(tableId, tableName, floorName);
                }
                showCartToast(`Đã chọn ${tableName} (${floorName}). Áp dụng ưu đãi giảm 10%!`, 'success');

                // Nếu trước đó đang chờ gửi bếp hoặc thanh toán QR, tự động thực hiện tiếp ngay
                if (actionToResume === 'send_kitchen') {
                    setTimeout(() => sendToKitchenLive(), 350);
                } else if (actionToResume === 'pay_table_now') {
                    setTimeout(() => payTableNowLive(), 350);
                }
            } else {
                showCartToast(data.message || 'Không thể chọn bàn này. Vui lòng chọn bàn khác!', 'warning');
            }
        })
        .catch(err => {
            console.error('Lỗi chọn bàn:', err);
            showCartToast('Lỗi chọn bàn ăn, vui lòng thử lại!', 'error');
        });
    };

    // Lọc bàn theo tầng trong modal
    window.filterPickerFloor = function(floorId) {
        document.querySelectorAll('.picker-floor-tab').forEach(tab => {
            if (tab.getAttribute('data-floor') === String(floorId)) {
                tab.className = 'picker-floor-tab px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all bg-[#F47716] text-white shadow-xs';
            } else {
                tab.className = 'picker-floor-tab px-3.5 py-1.5 rounded-lg text-xs font-medium transition-all bg-white text-stone-700 hover:bg-stone-200 border border-stone-200';
            }
        });

        document.querySelectorAll('.picker-floor-section').forEach(sec => {
            if (floorId === 'all' || sec.getAttribute('data-floor-section') === String(floorId)) {
                sec.classList.remove('hidden');
            } else {
                sec.classList.add('hidden');
            }
        });
    };

    // Khách tại bàn bấm nút "Xác nhận gọi món" (Ăn xong thanh toán sau)
    window.sendToKitchenLive = function() {
        const btn = document.getElementById('btnSendToKitchen');
        let oldHtml = '';
        if (btn) {
            oldHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Đang gọi món...';
        }

        fetch(CART_ENDPOINTS.sendKitchen, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CART_ENDPOINTS.csrf,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = oldHtml;
            }

            if (data.need_table) {
                showCartToast(data.message || 'Vui lòng chọn số bàn ăn của bạn trước khi xác nhận gọi món!', 'info');
                openTablePickerModal('send_kitchen');
                return;
            }

            if (data.success) {
                renderLiveCartFromData(data);
                showCartToast(data.message || 'Đã xác nhận gọi món thành công! Nhà hàng đang chuẩn bị món ăn.', 'success');
                closeMobileCartDrawer();
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showCartToast(data.message || 'Không thể gửi yêu cầu gọi món, vui lòng thử lại.', 'error');
            }
        })
        .catch(err => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = oldHtml;
            }
            console.error('Lỗi gọi món:', err);
            showCartToast('Lỗi khi gửi yêu cầu gọi món, vui lòng thử lại!', 'error');
        });
    };

    // Khách tại bàn bấm nút "Quẹt VietQR Thanh Toán Luôn"
    window.payTableNowLive = function() {
        const btn = document.getElementById('btnPayTableNow');
        let oldHtml = '';
        if (btn) {
            oldHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = 'Đang mở mã VietQR...';
        }

        fetch(CART_ENDPOINTS.payTableNow, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CART_ENDPOINTS.csrf,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = oldHtml;
            }

            if (data.need_table) {
                showCartToast(data.message || 'Vui lòng chọn số bàn ăn trước khi thanh toán!', 'info');
                openTablePickerModal('pay_table_now');
                return;
            }

            if (data.success && data.redirect_url) {
                // Làm sạch giỏ hàng client trước khi chuyển trang
                renderLiveCartFromData({ count: 0, items: [], subtotal_formatted: '0₫', discount_formatted: '0₫', total_formatted: '0₫' });
                window.location.href = data.redirect_url;
            } else {
                showCartToast(data.message || 'Không thể tạo mã thanh toán.', 'error');
            }
        })
        .catch(err => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = oldHtml;
            }
            console.error('Lỗi thanh toán:', err);
            showCartToast('Lỗi kết nối, vui lòng thử lại!', 'error');
        });
    };

    // Hàm gọi thêm món AJAX
    window.addToCartAjax = function(productId, btnElement) {
        if (!productId) return;

        let originalContent = '';
        if (btnElement) {
            originalContent = btnElement.innerHTML;
            btnElement.disabled = true;
            btnElement.innerHTML = 'Đang thêm...';
        }

        fetch(CART_ENDPOINTS.add + '/' + productId, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CART_ENDPOINTS.csrf,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ajax: true })
        })
        .then(res => res.json())
        .then(data => {
            if (btnElement) {
                btnElement.disabled = false;
                btnElement.innerHTML = originalContent;
            }

            if (data.success) {
                renderLiveCartFromData(data);
                showCartToast(data.message || 'Đã thêm món vào giỏ hàng!', 'success');
            } else {
                showCartToast(data.message || 'Không thể thêm món vào giỏ', 'error');
            }
        })
        .catch(err => {
            console.error('Lỗi thêm món:', err);
            if (btnElement) {
                btnElement.disabled = false;
                btnElement.innerHTML = originalContent;
            }
            showCartToast('Lỗi kết nối mạng, vui lòng thử lại!', 'error');
        });
    };

    // Cập nhật số lượng món
    window.changeLiveCartQty = function(productId, newQty) {
        fetch(CART_ENDPOINTS.updateQty, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CART_ENDPOINTS.csrf,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ id: productId, qty: newQty })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                renderLiveCartFromData(data);
            }
        })
        .catch(err => console.error('Lỗi cập nhật số lượng:', err));
    };

    // Xóa 1 món khỏi giỏ
    window.removeLiveCartItem = function(productId) {
        fetch(CART_ENDPOINTS.remove + '/' + productId, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': CART_ENDPOINTS.csrf,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                renderLiveCartFromData(data);
                showCartToast(data.message || 'Đã xóa món', 'info');
            }
        })
        .catch(err => console.error('Lỗi xóa món:', err));
    };

    // Làm trống giỏ
    window.clearLiveCart = function() {
        if (!confirm('Quý khách có chắc muốn làm trống giỏ hàng này không?')) return;

        fetch(CART_ENDPOINTS.clear, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CART_ENDPOINTS.csrf,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                renderLiveCartFromData(data);
                showCartToast('Đã làm trống giỏ hàng', 'info');
            }
        })
        .catch(err => console.error('Lỗi làm trống giỏ:', err));
    };

    // Hàm render toàn bộ dữ liệu giỏ hàng và đồng bộ giao diện
    function renderLiveCartFromData(data) {
        liveCartState = data;
        const isTable = !!data.is_table_order;

        // 1. Cập nhật Badge số lượng
        const badge = document.getElementById('liveCartItemCountBadge');
        if (badge) badge.textContent = data.count || 0;
        if (typeof window.updateGlobalCartBadges === 'function') {
            window.updateGlobalCartBadges(data.count || 0);
        }

        const clearBtn = document.getElementById('clearLiveCartBtn');
        if (clearBtn) {
            if (data.count > 0) clearBtn.classList.remove('hidden');
            else clearBtn.classList.add('hidden');
        }

        // 2. Cập nhật Nút chuyển đổi Dining Mode (Desktop & Mobile)
        const btnDineIn = document.getElementById('diningModeDineInBtn');
        const btnDelivery = document.getElementById('diningModeDeliveryBtn');
        const mBtnDineIn = document.getElementById('mobileDiningModeDineInBtn');
        const mBtnDelivery = document.getElementById('mobileDiningModeDeliveryBtn');

        if (btnDineIn && btnDelivery) {
            if (isTable) {
                btnDineIn.className = 'py-1.5 px-1.5 rounded-lg flex items-center justify-center gap-1 transition-all cursor-pointer bg-[#F47716] text-white shadow-xs';
                btnDelivery.className = 'py-1.5 px-1.5 rounded-lg flex items-center justify-center gap-1 transition-all cursor-pointer text-stone-600 hover:text-stone-900';
            } else {
                btnDineIn.className = 'py-1.5 px-1.5 rounded-lg flex items-center justify-center gap-1 transition-all cursor-pointer text-stone-600 hover:text-stone-900';
                btnDelivery.className = 'py-1.5 px-1.5 rounded-lg flex items-center justify-center gap-1 transition-all cursor-pointer bg-[#F47716] text-white shadow-xs';
            }
        }
        if (mBtnDineIn && mBtnDelivery) {
            if (isTable) {
                mBtnDineIn.className = 'py-1.5 rounded flex items-center justify-center gap-1 bg-[#F47716] text-white shadow-xs';
                mBtnDelivery.className = 'py-1.5 rounded flex items-center justify-center gap-1 text-stone-600';
            } else {
                mBtnDineIn.className = 'py-1.5 rounded flex items-center justify-center gap-1 text-stone-600';
                mBtnDelivery.className = 'py-1.5 rounded flex items-center justify-center gap-1 bg-[#F47716] text-white shadow-xs';
            }
        }

        // 3. Cập nhật Khối thông tin bàn ăn
        const tableContainer = document.getElementById('liveCartTableInfoContainer');
        const mobileTableBar = document.getElementById('mobileDrawerTableBar');
        const tableNameText = document.getElementById('liveCartTableNameText');
        const mTableNameText = document.getElementById('mobileDrawerTableNameText');

        if (tableContainer) {
            if (isTable) {
                tableContainer.classList.remove('hidden');
                if (tableNameText) {
                    tableNameText.textContent = data.table_name ? data.table_name : 'Chưa chọn số bàn phục vụ';
                }
            } else {
                tableContainer.classList.add('hidden');
            }
        }
        if (mobileTableBar) {
            if (isTable) {
                mobileTableBar.classList.remove('hidden');
                if (mTableNameText) {
                    mTableNameText.textContent = data.table_name ? `Đang dùng tại: ${data.table_name}` : 'Chưa chọn số bàn';
                }
            } else {
                mobileTableBar.classList.add('hidden');
            }
        }

        // Đồng bộ trạng thái số bàn sang Chuông gọi phục vụ tại bàn
        if (isTable && data.table_id && typeof window.syncServiceCallTable === 'function') {
            window.syncServiceCallTable(data.table_id, data.table_name, data.floor_name);
        }

        // 4. Cập nhật Chi phí & Giảm giá
        const subtotalEl = document.getElementById('liveCartSubtotal');
        if (subtotalEl) subtotalEl.textContent = data.subtotal_formatted || '0₫';

        const discountRow = document.getElementById('liveCartDiscountRow');
        const discountEl = document.getElementById('liveCartDiscount');
        if (discountRow) {
            if (isTable) {
                discountRow.classList.remove('hidden');
                if (discountEl) discountEl.textContent = '-' + (data.discount_formatted || '0₫');
            } else {
                discountRow.classList.add('hidden');
            }
        }

        const totalEl = document.getElementById('liveCartFinalTotal');
        if (totalEl) totalEl.textContent = data.total_formatted || '0₫';

        // 5. Cập nhật Nhóm nút CTA (Desktop & Mobile)
        const dineInActions = document.getElementById('liveCartDineInActions');
        const deliveryActions = document.getElementById('liveCartDeliveryActions');
        const mDineInActions = document.getElementById('mobileDrawerDineInActions');
        const mDeliveryActions = document.getElementById('mobileDrawerDeliveryActions');

        if (dineInActions && deliveryActions) {
            if (isTable) {
                dineInActions.classList.remove('hidden');
                deliveryActions.classList.add('hidden');
            } else {
                dineInActions.classList.add('hidden');
                deliveryActions.classList.remove('hidden');
            }
        }
        if (mDineInActions && mDeliveryActions) {
            if (isTable) {
                mDineInActions.classList.remove('hidden');
                mDeliveryActions.classList.add('hidden');
            } else {
                mDineInActions.classList.add('hidden');
                mDeliveryActions.classList.remove('hidden');
            }
        }

        const payMethodsWrapper = document.getElementById('liveCartPaymentMethodsWrapper');
        if (payMethodsWrapper) {
            if (isTable) payMethodsWrapper.classList.add('hidden');
            else payMethodsWrapper.classList.remove('hidden');
        }

        // Vô hiệu hóa nút nếu giỏ trống
        const btnSend = document.getElementById('btnSendToKitchen');
        const btnCheckout = document.getElementById('btnCheckoutLive');

        if (btnSend) btnSend.disabled = (data.count === 0);
        if (btnCheckout) {
            if (data.count === 0) btnCheckout.classList.add('pointer-events-none', 'opacity-50');
            else btnCheckout.classList.remove('pointer-events-none', 'opacity-50');
        }

        // 6. Render danh sách món desktop
        const listContainer = document.getElementById('liveCartItemsList');
        if (listContainer) {
            if (!data.items || data.items.length === 0) {
                listContainer.innerHTML = `
                    <div id="liveCartEmptyState" class="py-8 px-2 text-center text-stone-400">
                        <div class="w-12 h-12 rounded-full bg-stone-100 flex items-center justify-center mx-auto text-stone-400 mb-2">
                            <i class="fa-solid fa-cart-shopping text-base"></i>
                        </div>
                        <p class="text-xs font-bold text-stone-700">Chưa có món nào được chọn</p>
                        <p class="text-[11px] text-stone-400 mt-1">Bấm nút "+ Chọn" tại các món ăn để thêm vào đơn hàng của bạn.</p>
                    </div>
                `;
            } else {
                let html = '';
                data.items.forEach(item => {
                    const qtyStr = item.qty < 10 ? '0' + item.qty : item.qty;
                    html += `
                        <div class="pt-3 first:pt-0 flex items-center gap-3 group" id="live-cart-item-${item.id}">
                            <img src="${item.image}" alt="${item.name}" class="w-12 h-12 rounded-lg object-cover shrink-0 border border-stone-100">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-1">
                                    <h4 class="text-xs font-bold text-stone-800 truncate" title="${item.name}">${item.name}</h4>
                                    <button type="button" onclick="removeLiveCartItem(${item.id})" class="text-stone-300 hover:text-rose-500 text-xs transition-colors p-0.5 cursor-pointer" title="Xóa món">
                                        <i class="fa-regular fa-trash-can text-xs"></i>
                                    </button>
                                </div>
                                <p class="text-[10.5px] text-stone-400">Phần tiêu chuẩn</p>
                                
                                <div class="flex items-center justify-between mt-1.5">
                                    <span class="text-xs font-black text-[#C25E00] tracking-tight">
                                        ${item.total_formatted}
                                    </span>
                                    <div class="inline-flex items-center border border-stone-200 rounded-md px-1.5 py-0.5 bg-stone-50 text-xs">
                                        <button type="button" onclick="changeLiveCartQty(${item.id}, ${item.qty - 1})" class="w-5 h-5 flex items-center justify-center text-stone-500 hover:text-stone-900 transition-colors font-bold cursor-pointer">
                                            -
                                        </button>
                                        <span class="w-6 text-center font-bold text-stone-800 text-xs">${qtyStr}</span>
                                        <button type="button" onclick="changeLiveCartQty(${item.id}, ${item.qty + 1})" class="w-5 h-5 flex items-center justify-center text-stone-500 hover:text-stone-900 transition-colors font-bold cursor-pointer">
                                            +
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
                listContainer.innerHTML = html;
            }
        }

        // 7. Cập nhật Mobile Floating Bar & Mobile Drawer
        const mobileBar = document.getElementById('mobileFloatingCartBar');
        const mobileBadge = document.getElementById('mobileCartFloatingBadge');
        const mobileTotal = document.getElementById('mobileCartFloatingTotal');
        const mobileDrawerCount = document.getElementById('mobileDrawerItemCount');
        const mobileDrawerTotal = document.getElementById('mobileDrawerFinalTotal');
        const mFloatModeText = document.getElementById('mobileFloatingModeText');

        if (mobileBar) {
            if (data.count > 0) mobileBar.classList.remove('hidden');
            else mobileBar.classList.add('hidden');
        }
        if (mobileBadge) mobileBadge.textContent = data.count || 0;
        if (mobileTotal) mobileTotal.textContent = data.total_formatted || '0₫';
        if (mFloatModeText) {
            mFloatModeText.textContent = isTable ? 'Ăn tại quán (-10%):' : 'Giao tận nơi:';
        }
        if (mobileDrawerCount) mobileDrawerCount.textContent = (data.count || 0) + ' món ăn đã chọn';
        if (mobileDrawerTotal) mobileDrawerTotal.textContent = data.total_formatted || '0₫';

        const drawerList = document.getElementById('mobileDrawerItemsList');
        if (drawerList && listContainer) {
            drawerList.innerHTML = listContainer.innerHTML;
        }

        // 8. Cập nhật badge trên menu tab (nếu có trên trang)
        const tabCartBadge = document.getElementById('tabCartCountBadge');
        if (tabCartBadge) tabCartBadge.textContent = data.count || 0;

        // 9. TỰ ĐỘNG BẬT/TẮT GIỎ HÀNG THỰC ĐƠN THEO TRẠNG THÁI ORDER
        const foodCol = document.getElementById('menuProductListColumn');
        const cartSlot = document.getElementById('menuLiveCartSlot');
        const gridWrapper = document.getElementById('menuProductGridWrapper');
        const floatingBtn = document.getElementById('floatingCartButton');
        const fCountBadge = document.getElementById('floatingCartCountBadge');
        const fTotalText = document.getElementById('floatingCartTotalText');

        if (data.count > 0) {
            // Khi có món ăn được chọn: Mở giỏ hàng sang layout 8 - 4
            if (foodCol && cartSlot) {
                foodCol.classList.remove('lg:col-span-12');
                foodCol.classList.add('lg:col-span-8', 'xl:col-span-8');
                cartSlot.classList.remove('hidden');
            }
            if (gridWrapper) {
                gridWrapper.classList.remove('xl:grid-cols-4');
                gridWrapper.classList.add('xl:grid-cols-3');
            }
            if (floatingBtn) {
                floatingBtn.classList.add('hidden'); // Đã mở giỏ thì ẩn nút float
            }
            if (fCountBadge) fCountBadge.textContent = data.count;
            if (fTotalText) fTotalText.textContent = data.total_formatted || '0₫';
        } else {
            // Khi chưa chọn món nào (0 món): Tự động mở rộng danh sách món full 12 cột
            if (foodCol && cartSlot) {
                foodCol.classList.remove('lg:col-span-8', 'xl:col-span-8');
                foodCol.classList.add('lg:col-span-12');
                cartSlot.classList.add('hidden');
            }
            if (gridWrapper) {
                gridWrapper.classList.remove('xl:grid-cols-3');
                gridWrapper.classList.add('xl:grid-cols-4');
            }
            if (floatingBtn) {
                floatingBtn.classList.add('hidden');
            }
        }
    }

    // Hàm chuyển đổi bật/tắt thủ công giỏ hàng desktop
    window.toggleLiveCart = function(forceOpen) {
        const foodCol = document.getElementById('menuProductListColumn');
        const cartSlot = document.getElementById('menuLiveCartSlot');
        const gridWrapper = document.getElementById('menuProductGridWrapper');
        const floatingBtn = document.getElementById('floatingCartButton');
        
        if (!foodCol || !cartSlot) return;

        let shouldOpen = (forceOpen !== undefined) ? forceOpen : cartSlot.classList.contains('hidden');

        if (shouldOpen) {
            foodCol.classList.remove('lg:col-span-12');
            foodCol.classList.add('lg:col-span-8', 'xl:col-span-8');
            cartSlot.classList.remove('hidden');
            if (gridWrapper) {
                gridWrapper.classList.remove('xl:grid-cols-4');
                gridWrapper.classList.add('xl:grid-cols-3');
            }
            if (floatingBtn) floatingBtn.classList.add('hidden');
        } else {
            foodCol.classList.remove('lg:col-span-8', 'xl:col-span-8');
            foodCol.classList.add('lg:col-span-12');
            cartSlot.classList.add('hidden');
            if (gridWrapper) {
                gridWrapper.classList.remove('xl:grid-cols-3');
                gridWrapper.classList.add('xl:grid-cols-4');
            }
            if (floatingBtn && liveCartState && liveCartState.count > 0) {
                floatingBtn.classList.remove('hidden');
            }
        }
    };

    // Toast nhỏ góc dưới
    function showCartToast(message, type = 'success') {
        const existing = document.getElementById('liveCartToastNotification');
        if (existing) existing.remove();

        const toast = document.createElement('div');
        toast.id = 'liveCartToastNotification';
        toast.className = `fixed bottom-20 right-5 z-[999999] px-4 py-2.5 rounded shadow-xl text-xs font-bold flex items-center gap-2 transform transition-all duration-300 ease-out translate-y-2 opacity-0 ${
            type === 'success' ? 'bg-emerald-800 text-white border border-emerald-600' :
            (type === 'error' ? 'bg-red-800 text-white border border-red-600' : 'bg-stone-900 text-white border border-stone-700')
        }`;
        toast.style.zIndex = '999999';
        toast.innerHTML = `<span>${message}</span>`;
        document.body.appendChild(toast);

        setTimeout(() => toast.classList.remove('translate-y-2', 'opacity-0'), 10);
        setTimeout(() => {
            toast.classList.add('translate-y-2', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, 2600);
    }

    // Toggle lịch sử món đã gửi bếp
    window.toggleKitchenHistory = function() {
        const content = document.getElementById('kitchenHistoryContent');
        const icon = document.getElementById('kitchenHistoryIcon');
        if (content && icon) {
            content.classList.toggle('hidden');
            icon.textContent = content.classList.contains('hidden') ? '▼' : '▲';
        }
    };

    // Mobile Drawer Controls
    window.openMobileCartDrawer = function() {
        const overlay = ensurePortalToBody('mobileCartDrawerOverlay');
        const drawer = ensurePortalToBody('mobileCartDrawer');
        if (overlay && drawer) {
            overlay.classList.remove('hidden');
            drawer.classList.remove('translate-y-full');
            const listContainer = document.getElementById('liveCartItemsList');
            const drawerList = document.getElementById('mobileDrawerItemsList');
            if (drawerList && listContainer) {
                drawerList.innerHTML = listContainer.innerHTML;
            }
        }
    };

    window.closeMobileCartDrawer = function() {
        const overlay = document.getElementById('mobileCartDrawerOverlay');
        const drawer = document.getElementById('mobileCartDrawer');
        if (overlay && drawer) {
            overlay.classList.add('hidden');
            drawer.classList.add('translate-y-full');
        }
    };

    // Tự động chặn các form "Thêm vào giỏ" để gọi AJAX & Chuyển modal ra ngoài body ngay khi DOM sẵn sàng
    document.addEventListener('DOMContentLoaded', function() {
        ensurePortalToBody('tablePickerModalOverlay');
        ensurePortalToBody('mobileCartDrawerOverlay');
        ensurePortalToBody('mobileCartDrawer');
        document.body.addEventListener('submit', function(e) {
            const form = e.target;
            if (form && form.action && form.action.includes('/addcart/')) {
                e.preventDefault();
                const match = form.action.match(/\/addcart\/(\d+)/);
                if (match && match[1]) {
                    const btn = form.querySelector('button[type="submit"]');
                    window.addToCartAjax(match[1], btn);
                }
            }
        });
    });

    // Luôn đồng bộ dữ liệu giỏ hàng mới nhất khi trang được tải hoặc quay lại từ lịch sử trình duyệt (BFCache)
    window.addEventListener('pageshow', function() {
        fetch(CART_ENDPOINTS.liveData, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (data && data.success) {
                renderLiveCartFromData(data);
            }
        })
        .catch(() => {});
    });
</script>
