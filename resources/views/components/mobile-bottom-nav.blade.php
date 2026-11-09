@php
    $isHome = request()->is('/');
    $isMenu = request()->is('thuc-don*') || request()->is('san-pham*') || request()->routeIs('site.product.gridview') || request()->routeIs('site.product.detail');
    $isBooking = request()->is('dat-ban*') || request()->routeIs('site.booking');
    $isCart = request()->is('gio-hang*') || request()->is('thanh-toan*') || request()->routeIs('site.cart') || request()->routeIs('site.checkoutForm');
    $isAccount = request()->is('tai-khoan*') || request()->is('dang-nhap*') || request()->is('dang-ky*') || request()->is('don-hang*') || request()->is('admin*');

    $cartKey = Auth::check() ? ('cart_' . Auth::id()) : ('cart_guest_' . session()->getId());
    $userCart = session()->get($cartKey, []);
    $cartCount = is_array($userCart) ? array_sum(array_column($userCart, 'qty')) : 0;
    
    $isStaffUser = Auth::check() && (Auth::user()->isAdmin() || Auth::user()->isEmployee());
@endphp

<!-- ======================================================== -->
<!-- CIVILIZE MOBILE APP BOTTOM NAVIGATION BAR (Screens < 768px) -->
<!-- ======================================================== -->
<nav id="civilizeMobileBottomNav" 
     class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-stone-200/90 shadow-[0_-4px_20px_rgba(0,0,0,0.08)] select-none print:hidden transition-transform duration-200"
     style="padding-bottom: env(safe-area-inset-bottom, 0px);">
    <div class="grid grid-cols-5 h-[58px] items-center text-center">
        
        <!-- 1. Trang chủ -->
        <a href="{{ url('/') }}" 
           class="flex flex-col items-center justify-center h-full py-1 transition-colors {{ $isHome ? 'text-[#C25E00]' : 'text-stone-500 hover:text-stone-800' }}">
            <i class="fa-solid fa-house text-[17px] mb-1"></i>
            <span class="text-[10px] font-bold tracking-tight {{ $isHome ? 'font-extrabold text-[#C25E00]' : '' }}">Trang chủ</span>
        </a>

        <!-- 2. Thực đơn -->
        <a href="{{ route('site.product.gridview') }}" 
           class="flex flex-col items-center justify-center h-full py-1 transition-colors {{ $isMenu ? 'text-[#C25E00]' : 'text-stone-500 hover:text-stone-800' }}">
            <i class="fa-solid fa-utensils text-[17px] mb-1"></i>
            <span class="text-[10px] font-bold tracking-tight {{ $isMenu ? 'font-extrabold text-[#C25E00]' : '' }}">Thực đơn</span>
        </a>

        <!-- 3. Đặt bàn (Nút trọng tâm nổi bật) -->
        <a href="{{ route('site.booking') }}" 
           class="flex flex-col items-center justify-center h-full py-1 relative group">
            <div class="w-10 h-10 -mt-4 rounded-full bg-[#C25E00] text-white flex items-center justify-center shadow-md shadow-orange-600/30 group-active:scale-95 transition-transform">
                <i class="fa-solid fa-calendar-check text-sm"></i>
            </div>
            <span class="text-[10px] font-bold tracking-tight mt-1 {{ $isBooking ? 'font-extrabold text-[#C25E00]' : 'text-stone-600' }}">Đặt bàn</span>
        </a>

        <!-- 4. Giỏ hàng -->
        <a href="{{ url('/gio-hang') }}" 
           class="flex flex-col items-center justify-center h-full py-1 relative transition-colors notranslate {{ $isCart ? 'text-[#C25E00]' : 'text-stone-500 hover:text-stone-800' }}"
           translate="no">
            <div class="relative inline-block notranslate" translate="no">
                <i class="fa-solid fa-bag-shopping text-[17px] mb-1"></i>
                <span class="global-cart-badge-bottom {{ $cartCount > 0 ? '' : 'hidden' }} absolute -top-1.5 -right-2.5 bg-[#C25E00] text-white text-[9px] font-extrabold px-1.5 py-0.2 rounded-full min-w-[16px] leading-tight text-center">
                    {{ $cartCount }}
                </span>
            </div>
            <span class="text-[10px] font-bold tracking-tight whitespace-nowrap notranslate {{ $isCart ? 'font-extrabold text-[#C25E00]' : '' }}" translate="no">Giỏ hàng</span>
        </a>

        <!-- 5. Tài khoản / POS -->
        @if ($isStaffUser)
            <a href="{{ route('admin.table-order.index') }}" 
               class="flex flex-col items-center justify-center h-full py-1 transition-colors {{ $isAccount ? 'text-[#C25E00]' : 'text-stone-500 hover:text-stone-800' }}">
                <i class="fa-solid fa-cash-register text-[17px] mb-1"></i>
                <span class="text-[10px] font-bold tracking-tight {{ $isAccount ? 'font-extrabold text-[#C25E00]' : '' }}">Bán hàng</span>
            </a>
        @elseif (Auth::check())
            <a href="{{ route('site.profile') }}" 
               class="flex flex-col items-center justify-center h-full py-1 transition-colors {{ $isAccount ? 'text-[#C25E00]' : 'text-stone-500 hover:text-stone-800' }}">
                <i class="fa-solid fa-user text-[17px] mb-1"></i>
                <span class="text-[10px] font-bold tracking-tight {{ $isAccount ? 'font-extrabold text-[#C25E00]' : '' }}">Tài khoản</span>
            </a>
        @else
            <a href="{{ route('site.login') }}" 
               class="flex flex-col items-center justify-center h-full py-1 transition-colors {{ $isAccount ? 'text-[#C25E00]' : 'text-stone-500 hover:text-stone-800' }}">
                <i class="fa-solid fa-user text-[17px] mb-1"></i>
                <span class="text-[10px] font-bold tracking-tight {{ $isAccount ? 'font-extrabold text-[#C25E00]' : '' }}">Tài khoản</span>
            </a>
        @endif

    </div>
</nav>

<!-- Đồng bộ số lượng badge giỏ hàng thời gian thực qua event live cart -->
<script>
    document.addEventListener('live-cart-updated', function(e) {
        if (!e.detail) return;
        const count = e.detail.count || 0;
        document.querySelectorAll('.global-cart-badge-bottom').forEach(badge => {
            badge.textContent = count;
            if (count > 0) {
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        });
    });
</script>
