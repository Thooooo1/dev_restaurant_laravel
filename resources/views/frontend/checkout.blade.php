<x-layout-frontend>
    @php
        $cartKey = Auth::check() ? ('cart_' . Auth::id()) : ('cart_guest_' . session()->getId());
        $cart = $cart ?? session()->get($cartKey, session()->get('cart', []));
        $cartTotal = array_sum(array_map(fn($item) => $item['price'] * $item['qty'], $cart));
        $isTableOrder = (session('is_table_order') && session('table_id'));
    @endphp

    <main class="bg-[#FAF8F5] min-h-screen pb-16">
        <!-- Page Breadcrumb & Header -->
        <x-page-hero 
            :title="$isTableOrder ? 'Xác Nhận Gọi Món Tại Bàn' : 'Thanh Toán Đơn Hàng'" 
            :subtitle="$isTableOrder ? ('Xác nhận thực đơn để nhân viên chuẩn bị phục vụ tại bàn ' . session('table_name')) : 'Vui lòng xác nhận thông tin giao nhận và phương thức thanh toán.'" 
            :breadcrumbs="[
                ['name' => 'Giỏ hàng', 'url' => url('/gio-hang')],
                ['name' => 'Thanh toán', 'url' => '']
            ]" 
        />

        <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-7xl pt-8 sm:pt-10">
            <form action="{{ route('site.checkout') }}" method="POST" id="checkout-form">
                @csrf
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    
                    <!-- Left Column: Customer & Delivery Info + Payment Methods -->
                    <div class="lg:col-span-7 space-y-6">
                        
                        <!-- Customer Contact Card -->
                        <div class="bg-white rounded-none sm:rounded-sm border border-stone-200/90 p-6 sm:p-8 shadow-xs space-y-5">
                            <div class="flex items-center gap-2.5 pb-4 border-b border-stone-100">
                                <span class="w-6 h-6 rounded-none sm:rounded-sm bg-stone-900 text-amber-400 flex items-center justify-center text-xs font-bold">1</span>
                                <h2 class="font-serif text-lg sm:text-xl font-bold text-stone-900">
                                    {{ $isTableOrder ? 'Thông Tin Người Gọi Món' : 'Thông Tin Nhận Món' }}
                                </h2>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- Họ và tên -->
                                <div>
                                    <label for="fullname" class="block text-xs font-semibold text-stone-700 mb-1.5 uppercase tracking-wider">
                                        Họ và tên <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="text" 
                                           id="fullname" 
                                           name="name" 
                                           value="{{ old('name', Auth::user()->fullname ?? Auth::user()->name ?? '') }}" 
                                           required
                                           placeholder="Nguyễn Văn A"
                                           class="w-full px-3.5 py-2.5 rounded-none sm:rounded-sm border border-stone-300 text-stone-800 text-xs sm:text-sm focus:outline-none focus:border-[#C25E00] focus:ring-1 focus:ring-[#C25E00]">
                                    @error('name')
                                        <p class="text-rose-600 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Số điện thoại -->
                                <div>
                                    <label for="phone" class="block text-xs font-semibold text-stone-700 mb-1.5 uppercase tracking-wider">
                                        Số điện thoại <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="text" 
                                           id="phone" 
                                           name="phone" 
                                           value="{{ old('phone', Auth::user()->phone ?? '') }}" 
                                           required
                                           placeholder="09xx xxx xxx"
                                           class="w-full px-3.5 py-2.5 rounded-none sm:rounded-sm border border-stone-300 text-stone-800 text-xs sm:text-sm focus:outline-none focus:border-[#C25E00] focus:ring-1 focus:ring-[#C25E00]">
                                    @error('phone')
                                        <p class="text-rose-600 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Địa chỉ nhận hàng -->
                            <div>
                                <label for="address" class="block text-xs font-semibold text-stone-700 mb-1.5 uppercase tracking-wider">
                                    {{ $isTableOrder ? 'Vị trí bàn / Ghi chú bàn' : 'Địa chỉ giao nhận tận nơi' }} <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" 
                                       id="address" 
                                       name="address" 
                                       value="{{ old('address', $isTableOrder ? ('Bàn: ' . session('table_name')) : (Auth::user()->address ?? '')) }}" 
                                       required
                                       placeholder="{{ $isTableOrder ? 'Bàn phục vụ tại nhà hàng' : 'Số nhà, ngõ/đường, xã/phường, Phúc Thọ, Hà Nội' }}"
                                       class="w-full px-3.5 py-2.5 rounded-none sm:rounded-sm border border-stone-300 text-stone-800 text-xs sm:text-sm focus:outline-none focus:border-[#C25E00] focus:ring-1 focus:ring-[#C25E00]">
                                @error('address')
                                    <p class="text-rose-600 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Ghi chú cho nhà bếp -->
                            <div>
                                <label for="note" class="block text-xs font-semibold text-stone-700 mb-1.5 uppercase tracking-wider">
                                    Ghi chú cho đầu bếp (tùy chọn)
                                </label>
                                <textarea id="note" 
                                          name="note" 
                                          rows="2"
                                          placeholder="Ví dụ: Giảm cay, không bỏ hành, chuẩn bị thêm bát đũa trẻ em..."
                                          class="w-full p-3 rounded-none sm:rounded-sm border border-stone-300 text-stone-800 text-xs sm:text-sm focus:outline-none focus:border-[#C25E00] focus:ring-1 focus:ring-[#C25E00]">{{ old('note') }}</textarea>
                            </div>
                        </div>

                        <!-- Payment Method Card -->
                        <div class="bg-white rounded-none sm:rounded-sm border border-stone-200/90 p-6 sm:p-8 shadow-xs space-y-4">
                            <div class="flex items-center gap-2.5 pb-4 border-b border-stone-100">
                                <span class="w-6 h-6 rounded-none sm:rounded-sm bg-stone-900 text-amber-400 flex items-center justify-center text-xs font-bold">2</span>
                                <h2 class="font-serif text-lg sm:text-xl font-bold text-stone-900">
                                    Phương Thức Thanh Toán
                                </h2>
                            </div>

                            @if($isTableOrder)
                                <div class="p-4 rounded-none sm:rounded-sm bg-orange-50/40 border border-orange-200">
                                    <h4 class="font-bold text-stone-900 text-xs sm:text-sm">Thanh toán tại bàn (Bàn: {{ session('table_name') }})</h4>
                                    <p class="text-stone-600 text-[11px] sm:text-xs mt-0.5">Bạn sẽ thanh toán trực tiếp với nhân viên phục vụ sau khi dùng bữa.</p>
                                    <input type="hidden" name="payment_method" value="TaiBan">
                                </div>
                            @else
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="payment-options">
                                    <!-- VietQR Option (Chuyển khoản ngân hàng tự động) -->
                                    <label class="relative flex items-center gap-3 p-3.5 rounded-none sm:rounded-sm border border-stone-200 hover:border-[#F47716] cursor-pointer transition-colors bg-stone-50/50 has-[:checked]:bg-orange-50/40 has-[:checked]:border-[#F47716] has-[:checked]:shadow-2xs">
                                        <input type="radio" 
                                               name="payment_method" 
                                               value="VietQR" 
                                               class="accent-[#F47716] payment-radio shrink-0" 
                                               {{ old('payment_method', 'VietQR') === 'VietQR' ? 'checked' : '' }}>
                                        <div class="min-w-0">
                                            <span class="block font-bold text-stone-900 text-xs sm:text-sm truncate">Chuyển khoản VietQR</span>
                                            <span class="block text-[10px] text-stone-500 leading-tight">Quét mã ngân hàng (Duyệt tự động)</span>
                                        </div>
                                    </label>

                                    <!-- COD Option -->
                                    <label class="relative flex items-center gap-3 p-3.5 rounded-none sm:rounded-sm border border-stone-200 hover:border-[#F47716] cursor-pointer transition-colors bg-stone-50/50 has-[:checked]:bg-orange-50/40 has-[:checked]:border-[#F47716] has-[:checked]:shadow-2xs">
                                        <input type="radio" 
                                               name="payment_method" 
                                               value="COD" 
                                               class="accent-[#F47716] payment-radio shrink-0" 
                                               {{ old('payment_method') === 'COD' ? 'checked' : '' }}>
                                        <div class="min-w-0">
                                            <span class="block font-bold text-stone-900 text-xs sm:text-sm truncate">Tiền Mặt (COD)</span>
                                            <span class="block text-[10px] text-stone-500 leading-tight">Thanh toán khi nhận món</span>
                                        </div>
                                    </label>
                                </div>

                                @error('payment_method')
                                    <div class="text-rose-600 text-xs mt-2">{{ $message }}</div>
                                @enderror
                                <div id="payment-error" class="text-rose-600 text-xs mt-2 hidden">Vui lòng chọn phương thức thanh toán.</div>
                            @endif
                        </div>
                    </div>

                    <!-- Right Column: Sticky Order Summary & Submission -->
                    <div class="lg:col-span-5 sticky top-28 space-y-5">
                        <div class="bg-white rounded-none sm:rounded-sm border border-stone-200/90 p-6 shadow-xs space-y-5">
                            <h3 class="font-serif text-lg font-bold text-stone-900 pb-3 border-b border-stone-100">
                                Đơn Hàng Của Bạn ({{ count($cart) }} món)
                            </h3>

                            <!-- Dish list preview -->
                            <div class="max-h-64 overflow-y-auto divide-y divide-stone-100 pr-1 space-y-2">
                                @forelse ($cart as $item)
                                    <div class="flex items-center justify-between py-2 text-xs">
                                        <div class="flex items-center gap-2.5 min-w-0 pr-2">
                                            <span class="font-bold text-stone-900 shrink-0">{{ $item['qty'] }}x</span>
                                            <span class="text-stone-700 truncate">{{ $item['name'] }}</span>
                                        </div>
                                        <span class="font-bold text-stone-900 shrink-0">
                                            {{ number_format($item['price'] * $item['qty'], 0, ',', '.') }}₫
                                        </span>
                                    </div>
                                @empty
                                    <div class="py-4 text-center text-xs text-stone-400">Không có món ăn trong giỏ.</div>
                                @endforelse
                            </div>

                            <!-- Calculations -->
                            <div class="pt-4 border-t border-stone-100 space-y-2.5 text-xs sm:text-sm">
                                <div class="flex items-center justify-between text-stone-600">
                                    <span>Tạm tính món ăn:</span>
                                    <span class="font-bold text-stone-900">{{ number_format($cartTotal, 0, ',', '.') }}₫</span>
                                </div>
                                @if(!$isTableOrder)
                                    <div class="flex items-center justify-between text-stone-600">
                                        <span>Phí giao hàng:</span>
                                        <span class="text-emerald-700 font-semibold">Miễn phí giao (bán kính 5km)</span>
                                    </div>
                                @endif
                                <div class="pt-3 border-t border-stone-100 flex items-baseline justify-between">
                                    <span class="font-bold text-stone-900 text-sm">Tổng thanh toán:</span>
                                    <span class="font-serif text-2xl font-bold text-[#C25E00]">
                                        {{ number_format($cartTotal, 0, ',', '.') }}₫
                                    </span>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" 
                                    id="submit-order-btn"
                                    class="w-full py-3.5 rounded-none sm:rounded-sm bg-stone-900 hover:bg-[#F47716] text-white font-bold text-xs uppercase tracking-[0.2em] shadow-xs transition-colors flex items-center justify-center gap-2 cursor-pointer">
                                <span>{{ $isTableOrder ? 'Xác Nhận Gọi Món' : 'Hoàn Tất Đặt Đơn' }}</span>
                            </button>

                            <div class="pt-1 text-center">
                                <p class="text-[11px] text-stone-400">
                                    Nhà hàng sẽ liên hệ xác nhận trong vòng 15 phút.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById("checkout-form");
            const paymentError = document.getElementById("payment-error");
            const paymentRadios = document.querySelectorAll(".payment-radio");
            const isTableOrder = {{ $isTableOrder ? 'true' : 'false' }};

            if (form) {
                form.addEventListener("submit", function(event) {
                    if (!isTableOrder) {
                        const selectedPayment = document.querySelector('input[name="payment_method"]:checked');
                        if (!selectedPayment) {
                            event.preventDefault();
                            if (paymentError) paymentError.classList.remove("hidden");
                            const opt = document.getElementById("payment-options");
                            if (opt) opt.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            return false;
                        }
                    }
                });
            }

            paymentRadios.forEach(radio => {
                radio.addEventListener("change", function() {
                    if (paymentError) paymentError.classList.add("hidden");
                });
            });
        });
    </script>
</x-layout-frontend>
