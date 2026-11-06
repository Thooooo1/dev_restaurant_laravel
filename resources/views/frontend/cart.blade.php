<x-layout-frontend>
    @php
        $cart = session()->get('cart_' . Auth::id(), []);
        $cartTotal = array_sum(array_map(fn($item) => $item['price'] * $item['qty'], $cart));
    @endphp

    <main class="bg-[#FAF8F5] min-h-screen pb-16">
        <!-- Page Breadcrumb & Header -->
        <x-page-hero 
            :title="session('is_table_order') ? ('Gọi Món — ' . session('table_name')) : 'Giỏ Hàng Món Ăn'" 
            :subtitle="session('is_table_order') ? 'Các món ăn bạn đã chọn cho bàn hiện tại.' : 'Danh sách món ăn quý khách đã chọn tại CIVILIZE Restaurant.'" 
            :breadcrumbs="[
                ['name' => session('is_table_order') ? ('Gọi món ' . session('table_name')) : 'Giỏ hàng', 'url' => '']
            ]" 
        />

        <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-7xl pt-8 sm:pt-10">
            @if(session('is_table_order'))
                <div class="mb-6 p-4 rounded-none sm:rounded-sm bg-amber-50 border border-amber-200 flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#C25E00] animate-pulse"></span>
                        <div>
                            <span class="text-xs uppercase font-bold tracking-wider text-[#C25E00]">BÀN PHỤC VỤ</span>
                            <h3 class="font-bold text-stone-900 text-sm sm:text-base">{{ session('table_name') }}</h3>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" 
                                onclick="document.getElementById('openServiceCallBtn')?.click()"
                                class="text-xs font-bold text-white bg-[#C25E00] hover:bg-[#A34E00] px-3.5 py-1.5 rounded-none sm:rounded-sm shadow-xs transition-colors flex items-center cursor-pointer">
                            <span>Gọi phục vụ</span>
                        </button>
                        <a href="{{ route('site.table.end') }}" 
                           class="text-xs font-semibold text-rose-700 hover:text-rose-800 bg-white hover:bg-rose-50 px-3 py-1.5 rounded-none sm:rounded-sm border border-rose-200 transition-colors">
                            Kết thúc gọi món
                        </a>
                    </div>
                </div>
            @endif

            @if(!Auth::check())
                <div class="bg-white rounded-none sm:rounded-sm border border-stone-200/90 p-8 sm:p-12 text-center max-w-xl mx-auto shadow-2xs">
                    <span class="inline-block px-3 py-1 bg-orange-100 text-[#C25E00] text-xs font-bold uppercase tracking-wider rounded-sm mb-3">
                        Tài khoản thành viên
                    </span>
                    <h2 class="text-2xl font-bold font-serif text-stone-900 mb-6">Đăng Nhập Tài Khoản</h2>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                        <a href="{{ route('site.login') }}" 
                           class="w-full sm:w-auto inline-flex items-center justify-center px-7 py-3 rounded-none sm:rounded-sm bg-[#C25E00] hover:bg-[#A34E00] text-white font-bold text-sm transition-colors shadow-xs">
                            Đăng Nhập Ngay
                        </a>
                        <a href="{{ route('site.register') }}" 
                           class="w-full sm:w-auto inline-flex items-center justify-center px-7 py-3 rounded-none sm:rounded-sm border border-stone-300 hover:border-stone-900 text-stone-800 hover:text-stone-900 font-bold text-sm transition-colors bg-white">
                            Tạo Tài Khoản
                        </a>
                    </div>
                </div>
            @elseif(count($cart) == 0)
                <!-- 1. Hộp thông báo giỏ trống ấm cúng, tinh tế, không viền cam cứng đờ -->
                <div class="bg-white rounded-none sm:rounded-sm border border-stone-200/90 p-8 sm:p-12 text-center max-w-2xl mx-auto shadow-2xs mb-12">
                    <span class="inline-block px-3 py-1 bg-stone-100 text-stone-600 text-xs font-bold uppercase tracking-wider rounded-sm mb-3">
                        Giỏ hàng
                    </span>
                    
                    <h2 class="text-2xl sm:text-[26px] font-black font-serif text-stone-900 mb-2.5">
                        Giỏ Hàng Trống
                    </h2>
                    
                    <p class="text-stone-600 text-sm sm:text-[15px] max-w-lg mx-auto mb-7 leading-relaxed">
                        Quý khách vui lòng chọn các món ăn từ thực đơn của nhà hàng để thêm vào giỏ, hoặc đặt bàn trước để nhân viên chuẩn bị chu đáo nhất.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3.5">
                        <a href="{{ route('site.product.gridview') }}" 
                           class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3.5 rounded-none sm:rounded-sm bg-[#C25E00] hover:bg-[#A34E00] text-white font-bold text-sm transition-all shadow-xs">
                            <span>Khám Phá Thực Đơn Quán</span>
                        </a>
                        <a href="{{ route('site.booking') }}" 
                           class="w-full sm:w-auto inline-flex items-center justify-center px-7 py-3.5 rounded-none sm:rounded-sm bg-white hover:bg-stone-50 border border-stone-300 text-stone-800 font-bold text-sm transition-all shadow-2xs">
                            <span>Đặt Bàn Trực Tuyến</span>
                        </a>
                    </div>
                </div>

                <!-- 2. Phần gợi ý: MÓN NGON NỔI BẬT NÊN THỬ (Xóa bỏ cảm giác trống hoác "AI hóa") -->
                @php
                    $suggestedList = $suggestedProducts ?? \App\Models\Product::where('status', 1)->take(4)->get();
                @endphp
                @if($suggestedList->isNotEmpty())
                    <div class="mb-14">
                        <div class="flex items-end justify-between mb-6 pb-3 border-b border-stone-200">
                            <div>
                                <h3 class="text-lg sm:text-xl font-bold font-serif text-stone-900">
                                    Gợi Ý Món Ngon Phải Thử Tại CIVILIZE
                                </h3>
                                <p class="text-stone-500 text-xs sm:text-sm mt-0.5">
                                    Các món ăn đặc sắc được thực khách yêu thích và gọi nhiều nhất
                                </p>
                            </div>
                            <a href="{{ route('site.product.gridview') }}" 
                               class="text-xs sm:text-sm font-bold text-[#C25E00] hover:underline flex items-center gap-1 shrink-0">
                                <span>Xem toàn bộ thực đơn</span>
                                <i class="fa-solid fa-chevron-right text-[11px]"></i>
                            </a>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                            @foreach($suggestedList as $suggested)
                                @php
                                    $sPrice = $suggested->price_sale ?? $suggested->price_buy;
                                    $sImg = !empty($suggested->image) && file_exists(public_path('images/product/' . $suggested->image))
                                        ? asset('images/product/' . $suggested->image)
                                        : asset('images/product/20260106145148.jpg');
                                @endphp
                                <div class="bg-white rounded-none sm:rounded-sm border border-stone-200/90 overflow-hidden shadow-2xs hover:shadow-md transition-all duration-300 flex flex-col group">
                                    <a href="{{ route('site.product.detail', $suggested->slug) }}" class="relative aspect-[4/3] overflow-hidden bg-stone-100 block">
                                        <img src="{{ $sImg }}" 
                                             alt="{{ $suggested->name }}" 
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                    </a>
                                    <div class="p-4 flex flex-col justify-between flex-1">
                                        <div>
                                            <h4 class="font-bold text-stone-900 text-sm sm:text-[15px] group-hover:text-[#C25E00] transition-colors line-clamp-1 mb-1.5">
                                                <a href="{{ route('site.product.detail', $suggested->slug) }}">{{ $suggested->name }}</a>
                                            </h4>
                                            <div class="flex items-baseline gap-2 mb-3">
                                                <span class="font-bold text-stone-900 text-sm sm:text-base">
                                                    {{ number_format($sPrice, 0, ',', '.') }}₫
                                                </span>
                                                @if(!empty($suggested->price_sale) && $suggested->price_sale < $suggested->price_buy)
                                                    <span class="text-xs text-stone-400 line-through">
                                                        {{ number_format($suggested->price_buy, 0, ',', '.') }}₫
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <a href="{{ route('site.addcart', ['id' => $suggested->id]) }}" 
                                           class="w-full py-2 px-3 rounded-none sm:rounded-sm bg-stone-900 hover:bg-[#C25E00] text-white font-bold text-xs uppercase tracking-wider flex items-center justify-center transition-colors cursor-pointer">
                                            <span>+ Thêm vào giỏ</span>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif


            @else
                <form action="{{ route('site.updatecart') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                        
                        <!-- Left Column: Cart items table -->
                        <div class="lg:col-span-8 bg-white rounded-none sm:rounded-sm border border-stone-200/90 p-5 sm:p-8 shadow-xs">
                            <div class="flex items-center justify-between pb-4 border-b border-stone-100 mb-6">
                                <h2 class="font-serif text-xl sm:text-2xl font-bold text-stone-900">Danh Sách Món Ăn ({{ count($cart) }})</h2>
                                <a href="{{ route('site.product.gridview') }}" 
                                   class="text-xs font-semibold text-[#C25E00] hover:text-[#A34E00] transition-colors flex items-center">
                                    <span>+ Chọn thêm món</span>
                                </a>
                            </div>

                            <div class="divide-y divide-stone-100">
                                @foreach ($cart as $id => $item)
                                    <div class="py-4 sm:py-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                                         data-cart-row="true"
                                         data-id="{{ $id }}"
                                         data-price="{{ $item['price'] }}">
                                        
                                        <!-- Dish info -->
                                        <div class="flex items-center gap-4 flex-1 min-w-0">
                                            <a href="{{ route('site.product.detail', $item['slug']) }}" class="shrink-0 w-20 h-20 rounded-none sm:rounded-sm overflow-hidden bg-stone-100 border border-stone-200/80">
                                                <img src="{{ asset('images/product/' . $item['image']) }}" 
                                                     alt="{{ $item['name'] }}" 
                                                     class="w-full h-full object-cover">
                                            </a>
                                            <div class="min-w-0 flex-1">
                                                <h3 class="font-bold text-stone-900 text-sm sm:text-base truncate hover:text-[#C25E00] transition-colors">
                                                    <a href="{{ route('site.product.detail', $item['slug']) }}">{{ $item['name'] }}</a>
                                                </h3>
                                                <p class="text-xs text-stone-500 mt-0.5">
                                                    Đơn giá: <span class="font-semibold text-stone-700">{{ number_format($item['price'], 0, ',', '.') }}₫</span>
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Quantity stepper & line total -->
                                        <div class="flex items-center justify-between sm:justify-end gap-5 shrink-0">
                                            <!-- Stepper -->
                                            <div class="inline-flex items-center border border-stone-300 rounded-none sm:rounded-sm overflow-hidden bg-white shadow-2xs">
                                                <button type="button" 
                                                        class="w-8 h-8 flex items-center justify-center text-stone-600 hover:bg-stone-100 transition-colors text-xs font-bold btn-qty-minus cursor-pointer" 
                                                        data-id="{{ $id }}">
                                                    −
                                                </button>
                                                <input type="number" 
                                                       name="qty[{{ $id }}]" 
                                                       value="{{ $item['qty'] }}" 
                                                       min="1" 
                                                       class="w-12 h-8 text-center border-0 text-stone-900 font-bold text-xs focus:outline-none qty-input" 
                                                       data-id="{{ $id }}">
                                                <button type="button" 
                                                        class="w-8 h-8 flex items-center justify-center text-stone-600 hover:bg-stone-100 transition-colors text-xs font-bold btn-qty-plus cursor-pointer" 
                                                        data-id="{{ $id }}">
                                                    +
                                                </button>
                                            </div>

                                            <!-- Line amount -->
                                            <div class="text-right min-w-[90px]">
                                                <span class="font-bold text-stone-900 text-sm sm:text-base item-amount">
                                                    {{ number_format($item['price'] * $item['qty'], 0, ',', '.') }}₫
                                                </span>
                                            </div>

                                            <!-- Remove button -->
                                            <a href="{{ route('site.delcart', $id) }}" 
                                               class="w-7 h-7 rounded flex items-center justify-center text-stone-400 hover:text-rose-600 hover:bg-rose-50 text-sm font-bold transition-colors" 
                                               title="Xóa món">
                                                ×
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Right Column: Order Summary & Action -->
                        <div class="lg:col-span-4 sticky top-28 space-y-5">
                            <div class="bg-white rounded-none sm:rounded-sm border border-stone-200/90 p-6 shadow-xs space-y-5">
                                <h3 class="font-serif text-lg font-bold text-stone-900 pb-3 border-b border-stone-100">
                                    Tóm Tắt Đơn Hàng
                                </h3>

                                <div class="space-y-3 text-xs sm:text-sm">
                                    <div class="flex items-center justify-between text-stone-600">
                                        <span>Tạm tính món:</span>
                                        <span id="cart-total-amount" class="font-bold text-stone-900">
                                            {{ number_format($cartTotal, 0, ',', '.') }}₫
                                        </span>
                                    </div>
                                    @if(!session('is_table_order'))
                                        <div class="flex items-center justify-between text-stone-600">
                                            <span>Phí vận chuyển:</span>
                                            <span class="text-stone-500">Tính khi thanh toán</span>
                                        </div>
                                    @endif
                                </div>

                                @if (!session('is_table_order'))
                                    <!-- Discount coupon input -->
                                    <div class="pt-3 border-t border-stone-100">
                                        <label for="discount" class="block text-xs font-semibold text-stone-700 mb-1.5">Mã ưu đãi / Giảm giá:</label>
                                        <div class="flex items-center gap-1.5">
                                            <input type="text" 
                                                   id="discount" 
                                                   name="discount" 
                                                   placeholder="Nhập mã ưu đãi..."
                                                   class="flex-1 px-3 py-2 text-xs border border-stone-300 rounded-none sm:rounded-sm focus:outline-none focus:border-[#C25E00]">
                                            <button type="button" 
                                                    id="apply_discount"
                                                    class="px-4 py-2 bg-stone-100 hover:bg-stone-200 text-stone-800 font-semibold text-xs rounded-none sm:rounded-sm transition-colors cursor-pointer shrink-0">
                                                Áp dụng
                                            </button>
                                        </div>
                                        <p id="discount_message" class="text-xs text-emerald-600 mt-1.5 hidden"></p>
                                    </div>
                                @endif

                                <!-- Grand total -->
                                <div class="pt-4 border-t border-stone-100 flex items-baseline justify-between">
                                    <span class="font-bold text-sm text-stone-800">Tổng thanh toán:</span>
                                    <span class="font-serif text-2xl font-bold text-[#C25E00]">
                                        {{ number_format($cartTotal, 0, ',', '.') }}₫
                                    </span>
                                </div>

                                <!-- Primary Button -->
                                @if (session('is_table_order') && session('table_name'))
                                    <button type="submit" 
                                            onclick="return confirm('Gửi thực đơn cho nhân viên nhà bếp để xác nhận?')" 
                                            class="w-full py-4 rounded-none sm:rounded-sm bg-stone-900 hover:bg-[#C25E00] text-white font-bold text-xs uppercase tracking-[0.2em] shadow-xs hover:shadow-md transition-all flex items-center justify-center cursor-pointer">
                                        <span>Gửi Thực Đơn Cho Bếp</span>
                                    </button>
                                @else
                                    <a href="{{ route('site.checkoutForm') }}" 
                                       class="w-full py-3.5 rounded-none sm:rounded-sm bg-[#C25E00] hover:bg-[#A34E00] text-white font-bold text-sm tracking-wide shadow-xs hover:shadow-md transition-all flex items-center justify-center text-center cursor-pointer">
                                        <span>Tiến Hành Đặt Đơn</span>
                                    </a>
                                @endif

                                <div class="pt-2 text-center">
                                    <p class="text-xs text-stone-500 font-medium">
                                        Món ăn luôn chế biến nóng sốt • Phục vụ tận tâm
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            @endif

            <!-- Live Table Order Status Section -->
            @if(isset($tableOrder) && $tableOrder && isset($tableOrderDetails) && $tableOrderDetails->count() > 0)
                @php
                    $orderTotal = 0;
                    foreach($tableOrderDetails as $detail) {
                        $price = $detail->price ?? ($detail->product->price_sale ?? 0);
                        $orderTotal += $price * $detail->qty;
                    }
                @endphp
                <div class="mt-12 bg-white rounded-none sm:rounded-sm border border-stone-200/90 p-6 sm:p-8 shadow-xs">
                    <div class="flex items-center justify-between pb-4 border-b border-stone-100 mb-6">
                        <div>
                            <span class="text-xs uppercase font-bold tracking-wider text-[#C25E00]">TIẾN ĐỘ THỰC HIỆN</span>
                            <h3 class="font-serif text-xl sm:text-2xl font-bold text-stone-900">Món Đang Phục Vụ Tại Bàn</h3>
                        </div>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-200">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            Bếp đang xử lý
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-xs sm:text-sm">
                            <thead>
                                <tr class="border-b border-stone-100 text-stone-400 uppercase tracking-wider text-[11px]">
                                    <th class="py-3 text-left font-bold">Món Ăn</th>
                                    <th class="py-3 text-center font-bold">Số Lượng</th>
                                    <th class="py-3 text-right font-bold">Đơn Giá</th>
                                    <th class="py-3 text-right font-bold">Thành Tiền</th>
                                    <th class="py-3 text-center font-bold">Trạng Thái Bếp</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100">
                                @foreach($tableOrderDetails as $detail)
                                    @php
                                        $price = $detail->price ?? ($detail->product->price_sale ?? 0);
                                        $total = $price * $detail->qty;
                                        $statusBadge = match($detail->status) {
                                            'confirmed' => ['text' => 'Đã xác nhận', 'class' => 'bg-amber-50 text-amber-700 border border-amber-200'],
                                            'cooking' => ['text' => 'Đang chế biến', 'class' => 'bg-orange-50 text-orange-700 border border-orange-200'],
                                            'served' => ['text' => 'Đã lên món', 'class' => 'bg-emerald-50 text-emerald-700 border border-emerald-200'],
                                            default => ['text' => 'Chờ xác nhận', 'class' => 'bg-stone-100 text-stone-700 border border-stone-200'],
                                        };
                                    @endphp
                                    <tr>
                                        <td class="py-3.5 text-stone-900 font-semibold">
                                            {{ $detail->product->name ?? 'N/A' }}
                                        </td>
                                        <td class="py-3.5 text-center text-stone-700 font-bold">
                                            {{ $detail->qty }}
                                        </td>
                                        <td class="py-3.5 text-right text-stone-600">
                                            {{ number_format($price, 0, ',', '.') }}₫
                                        </td>
                                        <td class="py-3.5 text-right text-stone-900 font-bold">
                                            {{ number_format($total, 0, ',', '.') }}₫
                                        </td>
                                        <td class="py-3.5 text-center">
                                            <span class="inline-block px-2.5 py-0.5 rounded text-xs font-semibold {{ $statusBadge['class'] }}">
                                                {{ $statusBadge['text'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="border-t-2 border-stone-200">
                                    <td colspan="3" class="py-4 text-right font-bold text-stone-800 text-sm">Tổng cộng tại bàn:</td>
                                    <td class="py-4 text-right font-serif font-bold text-lg text-[#C25E00]">
                                        {{ number_format($orderTotal, 0, ',', '.') }}₫
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.addEventListener('click', function (event) {
                var minusButton = event.target.closest('.btn-qty-minus');
                var plusButton = event.target.closest('.btn-qty-plus');

                if (!minusButton && !plusButton) return;

                var button = minusButton || plusButton;
                var wrapper = button.parentElement;
                var input = wrapper.querySelector('.qty-input');
                if (!input) return;

                var current = parseInt(input.value, 10);
                if (isNaN(current) || current < 1) current = 1;

                if (plusButton) current += 1;
                else if (minusButton) current = Math.max(current - 1, 1);

                input.value = current;

                var row = button.closest('[data-cart-row="true"]');
                if (row) {
                    var price = parseInt(row.getAttribute('data-price'), 10) || 0;
                    var amountElement = row.querySelector('.item-amount');
                    if (amountElement) {
                        amountElement.textContent = formatCurrency(price * current) + '₫';
                    }
                }
                updateCartTotal();
            });

            function formatCurrency(value) {
                if (typeof Intl !== 'undefined' && Intl.NumberFormat) {
                    return new Intl.NumberFormat('vi-VN').format(value);
                }
                return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }

            function updateCartTotal() {
                var rows = document.querySelectorAll('[data-cart-row="true"]');
                var total = 0;
                rows.forEach(function (row) {
                    var price = parseInt(row.getAttribute('data-price'), 10) || 0;
                    var input = row.querySelector('.qty-input');
                    if (!input) return;
                    var qty = parseInt(input.value, 10);
                    if (isNaN(qty) || qty < 1) qty = 1;
                    total += price * qty;
                });
                var totalElement = document.getElementById('cart-total-amount');
                if (totalElement) totalElement.textContent = formatCurrency(total) + '₫';
            }

            @if(session('is_table_order'))
            setInterval(function() {
                window.location.reload();
            }, 8000);
            @endif
        });
    </script>
</x-layout-frontend>
