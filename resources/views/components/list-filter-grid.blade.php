<div class="max-w-[1200px] mx-auto px-4 sm:px-6 w-full">
    
    <!-- ======================================================== -->
    <!-- 1. TOP HEADER: Tiêu đề sạch sẽ & Thanh Tìm Kiếm + Sắp Xếp  -->
    <!-- ======================================================== -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-4 border-b border-stone-200">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-stone-900 tracking-tight">
                Thực đơn món ăn
            </h1>
            <p class="text-xs sm:text-sm text-stone-500 mt-0.5 font-medium">
                Đặc sản lẩu nướng & mâm cơm thuần Việt chế biến tươi nóng tại Phúc Thọ
            </p>
        </div>

        <!-- Search Bar & Sort Filter -->
        <div class="flex items-center gap-2.5 w-full md:w-auto">
            <div class="relative flex-1 md:w-80">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400 text-xs"></i>
                <input type="text" 
                       id="menuSearchInput" 
                       value="{{ request('search') }}" 
                       placeholder="Tìm món: Lẩu, nướng, bắp bò, nầm, đồ uống..." 
                       class="w-full pl-9 pr-8 py-2 text-xs sm:text-sm bg-white border border-stone-200 rounded-none sm:rounded-sm focus:outline-none focus:border-[#F47716] focus:ring-1 focus:ring-orange-200 shadow-2xs transition-all">
                @if (request('search'))
                    <a href="{{ request()->fullUrlWithQuery(['search' => null, 'page' => null]) }}" 
                       class="absolute right-3 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-700 text-xs font-bold" 
                       title="Xóa tìm kiếm">
                        ✕
                    </a>
                @endif
            </div>

            <!-- Sort Select Filter -->
            <div class="shrink-0">
                <select id="menuSortSelect" 
                        class="text-xs sm:text-sm bg-white border border-stone-200 text-stone-700 py-2 px-3 rounded-none sm:rounded-sm focus:outline-none focus:border-[#F47716] shadow-2xs transition font-semibold cursor-pointer">
                    <option value="default" {{ request('sort') == 'default' ? 'selected' : '' }}>Mặc định</option>
                    <option value="priceAsc" {{ request('sort') == 'priceAsc' ? 'selected' : '' }}>Giá: Thấp → Cao</option>
                    <option value="priceDesc" {{ request('sort') == 'priceDesc' ? 'selected' : '' }}>Giá: Cao → Thấp</option>
                    <option value="alphabet" {{ request('sort') == 'alphabet' ? 'selected' : '' }}>Tên: A → Z</option>
                    <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Món mới nhất</option>
                </select>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- 2. SECTION TITLE: Khám phá món & Tổng số món             -->
    <!-- ======================================================== -->
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-base sm:text-lg font-bold text-stone-900 tracking-tight">
            Khám phá theo danh mục
        </h2>
        <a href="{{ route('site.product.gridview') }}" class="text-xs sm:text-sm font-bold text-[#F47716] hover:underline">
            Xem tất cả ({{ $totalAllProducts }})
        </a>
    </div>

    <!-- ======================================================== -->
    <!-- 3. HORIZONTAL CATEGORY TABS (Chuẩn phẳng, không bo viên thuốc)-->
    <!-- ======================================================== -->
    <div class="relative mb-6">
        <div class="overflow-x-auto scroll-smooth py-1 -mx-2 px-2" style="-webkit-overflow-scrolling: touch; scrollbar-width: thin;">
            <div class="flex items-center gap-2 min-w-max pb-1">
                <a href="{{ request()->fullUrlWithQuery(['category' => 'all', 'page' => null]) }}" 
                   class="px-3.5 py-2 rounded-none sm:rounded-sm text-xs sm:text-sm font-semibold transition-all shrink-0 {{ $categoryitem === 'all' ? 'bg-[#F47716] text-white border border-[#F47716] shadow-2xs' : 'bg-white text-stone-700 hover:text-[#F47716] hover:bg-stone-50 border border-stone-200' }}">
                    <span>Tất cả</span>
                </a>

                @foreach ($categories as $cat)
                    @if ($cat->products_count > 0)
                        <a href="{{ request()->fullUrlWithQuery(['category' => $cat->id, 'page' => null]) }}" 
                           class="px-3.5 py-2 rounded-none sm:rounded-sm text-xs sm:text-sm font-semibold transition-all shrink-0 {{ $categoryitem == $cat->id ? 'bg-[#F47716] text-white border border-[#F47716] shadow-2xs' : 'bg-white text-stone-700 hover:text-[#F47716] hover:bg-stone-50 border border-stone-200' }}">
                            <span>{{ $cat->name }}</span>
                        </a>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    <!-- Active Filter Tags (Hiện khi đang tìm kiếm hoặc lọc) -->
    @if ($categoryitem !== 'all' || request('search') || request('price_min') || request('price_max'))
        <div class="flex flex-wrap items-center gap-2 mb-5 text-xs">
            <span class="text-stone-400 text-xs font-medium">Đang lọc:</span>

            @if ($categoryitem !== 'all')
                @php
                    $curCat = $categories->firstWhere('id', $categoryitem);
                @endphp
                @if ($curCat)
                    <a href="{{ request()->fullUrlWithQuery(['category' => 'all', 'page' => null]) }}" 
                       class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-orange-50 border border-orange-200 text-[#F47716] font-semibold hover:bg-orange-100 transition">
                        <span>{{ $curCat->name }}</span>
                        <span class="text-[10px]">✕</span>
                    </a>
                @endif
            @endif

            @if (request('search'))
                <a href="{{ request()->fullUrlWithQuery(['search' => null, 'page' => null]) }}" 
                   class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-orange-50 border border-orange-200 text-[#F47716] font-semibold hover:bg-orange-100 transition">
                    <span>"{{ request('search') }}"</span>
                    <span class="text-[10px]">✕</span>
                </a>
            @endif

            <a href="{{ route('site.product.gridview') }}" 
               class="text-stone-400 hover:text-[#F47716] ml-1 text-xs underline font-medium">
                Xóa tất cả bộ lọc
            </a>
        </div>
    @endif

    <!-- ======================================================== -->
    <!-- 4. MAIN ORDER GRID: Left Food Grid + Right My Order      -->
    <!-- ======================================================== -->
    @php
        $cartKey = Auth::check() ? 'cart_' . Auth::id() : 'cart_guest_' . session()->getId();
        $sessionCart = session()->get($cartKey, []);
        $initCartCount = 0;
        foreach ($sessionCart as $it) {
            $initCartCount += (int)($it['qty'] ?? 1);
        }
        $hasItemsInCart = ($initCartCount > 0);
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start" id="menuMainGridLayout">
        
        <!-- CỘT TRÁI: DANH SÁCH MÓN ĂN (12 cột khi chưa chọn món, 8 cột khi đã có món) -->
        <div id="menuProductListColumn" class="{{ $hasItemsInCart ? 'lg:col-span-8 xl:col-span-8' : 'lg:col-span-12' }} transition-all duration-300">
            <x-list-product-grid :categoryitem="$categoryitem" :branditem="$branditem" />
        </div>

        <!-- CỘT PHẢI: MY ORDER / GIỎ HÀNG THỜI GIAN THỰC (4 cột - Chỉ hiện khi có món hoặc khi bấm mở) -->
        <div id="menuLiveCartSlot" class="lg:col-span-4 xl:col-span-4 sticky top-24 z-20 {{ $hasItemsInCart ? '' : 'hidden' }} transition-all duration-300">
            <x-menu-live-cart />
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var searchInput = document.getElementById('menuSearchInput');
        var sortSelect = document.getElementById('menuSortSelect');

        function updateQuery(param, value) {
            var url = new URL(window.location.href);
            if (value && value !== 'default' && value !== 'all') {
                url.searchParams.set(param, value);
            } else {
                url.searchParams.delete(param);
            }
            url.searchParams.delete('page');
            window.location.href = url.toString();
        }

        if (searchInput) {
            searchInput.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    updateQuery('search', searchInput.value.trim());
                }
            });
        }

        if (sortSelect) {
            sortSelect.addEventListener('change', function () {
                updateQuery('sort', this.value);
            });
        }
    });
</script>
