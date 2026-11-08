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
            <div class="relative flex-1 md:w-80" id="menuSearchInputWrapper">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400 text-xs pointer-events-none"></i>
                <input type="text" 
                       id="menuSearchInput" 
                       value="{{ request('search') }}" 
                       autocomplete="off"
                       placeholder="Tìm món: Lẩu, nướng, bắp bò, nầm, đồ uống..." 
                       class="w-full pl-9 pr-8 py-2 text-xs sm:text-sm bg-white border border-stone-200 rounded-none sm:rounded-sm focus:outline-none focus:border-[#F47716] focus:ring-1 focus:ring-orange-200 shadow-2xs transition-all">
                
                <button type="button" 
                        id="menuSearchClearBtn" 
                        class="{{ request('search') ? '' : 'hidden' }} absolute right-2.5 top-1/2 -translate-y-1/2 w-5 h-5 flex items-center justify-center text-stone-400 hover:text-stone-700 text-xs font-bold rounded-full hover:bg-stone-100 transition-colors cursor-pointer" 
                        title="Xóa tìm kiếm">
                    ✕
                </button>

                <!-- ======================================================== -->
                <!-- FLOATING LIVE SEARCH AUTOCOMPLETE DROPDOWN (Tức thì)     -->
                <!-- ======================================================== -->
                <div id="menuSearchDropdown" 
                     class="hidden absolute left-0 right-0 top-full mt-1.5 bg-white border border-stone-200 rounded-md shadow-xl z-50 overflow-hidden max-h-96 flex flex-col">
                    <div class="px-3.5 py-2 bg-stone-50 border-b border-stone-100 flex items-center justify-between text-xs text-stone-500 font-semibold shrink-0">
                        <span>Gợi ý món ăn (<span id="menuDropdownCount">0</span>)</span>
                        <span class="text-[11px] text-stone-400">ESC để đóng</span>
                    </div>
                    <div id="menuDropdownItems" class="overflow-y-auto max-h-72 divide-y divide-stone-100">
                        <!-- Populated dynamically via JS -->
                    </div>
                    <div id="menuDropdownEmpty" class="hidden p-5 text-center text-xs text-stone-500">
                        <i class="fa-solid fa-utensils text-stone-300 text-xl mb-1.5 block"></i>
                        <span>Không tìm thấy món ăn phù hợp với từ khóa</span>
                    </div>
                </div>
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
    <div id="menuCategoryTitleSection" class="flex items-center justify-between mb-3">
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
    <div id="menuCategoryTabsSection" class="relative mb-6">
        <div class="overflow-x-auto scroll-smooth py-1 -mx-2 px-2" style="-webkit-overflow-scrolling: touch; scrollbar-width: thin;">
            <div class="flex items-center gap-2 min-w-max pb-1">
                <a href="{{ request()->fullUrlWithQuery(['category' => 'all', 'page' => null]) }}" 
                   class="category-filter-tab px-3.5 py-2 rounded-none sm:rounded-sm text-xs sm:text-sm font-semibold transition-all shrink-0 {{ $categoryitem === 'all' ? 'bg-[#F47716] text-white border border-[#F47716] shadow-2xs' : 'bg-white text-stone-700 hover:text-[#F47716] hover:bg-stone-50 border border-stone-200' }}">
                    <span>Tất cả</span>
                </a>

                @foreach ($categories as $cat)
                    @if ($cat->products_count > 0)
                        <a href="{{ request()->fullUrlWithQuery(['category' => $cat->id, 'page' => null]) }}" 
                           class="category-filter-tab px-3.5 py-2 rounded-none sm:rounded-sm text-xs sm:text-sm font-semibold transition-all shrink-0 {{ $categoryitem == $cat->id ? 'bg-[#F47716] text-white border border-[#F47716] shadow-2xs' : 'bg-white text-stone-700 hover:text-[#F47716] hover:bg-stone-50 border border-stone-200' }}">
                            <span>{{ $cat->name }}</span>
                        </a>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    <!-- Active Filter Tags Container (Cập nhật mượt qua AJAX) -->
    <div id="menuActiveFilterTagsWrapper">
        @if ($categoryitem !== 'all' || request('price_min') || request('price_max'))
            <div id="menuActiveFilterTags" class="flex flex-wrap items-center gap-2 mb-5 text-xs">
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

                <a href="{{ route('site.product.gridview') }}" 
                   class="text-stone-400 hover:text-[#F47716] ml-1 text-xs underline font-medium">
                    Xóa tất cả bộ lọc
                </a>
            </div>
        @endif
    </div>

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
            
            <!-- 4.1 Khung Kết Quả Tìm Kiếm Tức Thì (Hiện ngay khi gõ từ khóa, không cần Enter) -->
            <div id="menuLiveSearchResultsPane" class="hidden">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-stone-200 flex-wrap gap-2">
                    <div class="text-stone-900 font-bold text-sm sm:text-base flex items-center gap-2 flex-wrap">
                        <i class="fa-solid fa-magnifying-glass text-[#F47716]"></i>
                        <span>Kết quả tìm kiếm cho: <span class="text-[#F47716]" id="menuLiveSearchQueryText">""</span></span>
                        <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-orange-50 border border-orange-200 text-[#F47716]" id="menuLiveSearchCountText">0 món</span>
                    </div>
                    <button type="button" id="menuLiveSearchCloseBtn" class="text-xs font-semibold text-stone-600 hover:text-[#F47716] flex items-center gap-1.5 px-3 py-1.5 rounded-sm border border-stone-200 bg-white hover:bg-stone-50 transition cursor-pointer">
                        ✕ <span>Xem lại thực đơn</span>
                    </button>
                </div>

                <!-- Grid hiển thị các món khớp tìm kiếm -->
                <div id="menuLiveSearchGrid" class="grid grid-cols-1 sm:grid-cols-2 {{ $hasItemsInCart ? 'lg:grid-cols-3 xl:grid-cols-3' : 'lg:grid-cols-3 xl:grid-cols-4' }} gap-4 sm:gap-5 transition-all">
                    @if (isset($allProducts))
                        @foreach ($allProducts as $product)
                            @php
                                $cleanName = mb_strtolower($product->name, 'UTF-8');
                                $asciiName = Str::lower(Str::ascii($product->name));
                                $catName = $product->category ? mb_strtolower($product->category->name, 'UTF-8') : '';
                                $hasDiscount = !empty($product->price_sale) && !empty($product->price_buy) && ($product->price_sale < $product->price_buy);
                                $discountPercent = $hasDiscount ? round((($product->price_buy - $product->price_sale) / $product->price_buy) * 100) : 0;
                                $imgSrc = !empty($product->image) && file_exists(public_path('images/product/' . $product->image))
                                    ? asset('images/product/' . $product->image)
                                    : asset('images/default-product.png');
                                $effectivePrice = $hasDiscount ? $product->price_sale : $product->price_buy;
                            @endphp
                            <div class="menu-live-search-card group relative bg-white rounded-none sm:rounded-sm overflow-hidden border border-stone-200 hover:border-stone-400 shadow-2xs hover:shadow-xs transition-all duration-200 flex flex-col justify-between h-full"
                                 data-name="{{ $cleanName }}"
                                 data-ascii="{{ $asciiName }}"
                                 data-cat="{{ $catName }}"
                                 data-id="{{ $product->id }}"
                                 data-slug="{{ $product->slug }}"
                                 data-price="{{ (float)$effectivePrice }}">
                                
                                <!-- Khung hình ảnh món ăn -->
                                <div class="relative aspect-[4/3] w-full overflow-hidden bg-stone-100">
                                    <a href="{{ route('site.product.detail', ['slug' => $product->slug]) }}" class="block w-full h-full">
                                        <img src="{{ $imgSrc }}" 
                                             alt="{{ $product->name }}" 
                                             loading="lazy"
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300 ease-out">
                                    </a>

                                    @if ($hasDiscount)
                                        <div class="absolute top-2.5 left-2.5 z-10 pointer-events-none">
                                            <span class="bg-rose-500 text-white font-bold text-[11px] px-2 py-0.5 rounded-none sm:rounded-xs shadow-2xs uppercase tracking-wide">
                                                -{{ $discountPercent }}%
                                            </span>
                                        </div>
                                    @elseif ($product->new_p == 1)
                                        <div class="absolute top-2.5 left-2.5 z-10 pointer-events-none">
                                            <span class="bg-[#F47716] text-white font-bold text-[11px] px-2 py-0.5 rounded-none sm:rounded-xs shadow-2xs uppercase tracking-wide">
                                                Bán chạy
                                            </span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Thông tin món & Giá -->
                                <div class="p-4 flex-grow flex flex-col justify-between">
                                    <div>
                                        <div class="text-[10px] font-bold text-stone-400 uppercase tracking-widest mb-1 truncate">
                                            {{ $product->category ? $product->category->name : 'Món ngon' }}
                                        </div>
                                        <h3 class="font-bold text-stone-900 text-sm sm:text-base leading-snug line-clamp-2 min-h-[2.5rem] group-hover:text-[#F47716] transition-colors mb-2">
                                            <a href="{{ route('site.product.detail', ['slug' => $product->slug]) }}">
                                                {{ $product->name }}
                                            </a>
                                        </h3>
                                    </div>

                                    <div class="pt-3 border-t border-stone-100 flex items-center justify-between gap-2 mt-auto">
                                        <div>
                                            @if ($hasDiscount)
                                                <div class="text-[#F47716] font-black text-sm sm:text-base leading-tight">
                                                    {{ number_format($product->price_sale, 0, ',', '.') }}đ
                                                </div>
                                                <div class="text-stone-400 text-[10px] sm:text-[11px] line-through">
                                                    {{ number_format($product->price_buy, 0, ',', '.') }}đ
                                                </div>
                                            @else
                                                <div class="text-stone-900 font-black text-sm sm:text-base leading-tight">
                                                    {{ number_format($product->price_buy, 0, ',', '.') }}đ
                                                </div>
                                            @endif
                                        </div>

                                        <button type="button" 
                                                onclick="window.addToCartAjax ? window.addToCartAjax({{ $product->id }}, this) : null"
                                                class="px-3.5 py-1.5 rounded-none sm:rounded-sm bg-[#F47716] hover:bg-[#EA580C] text-white font-bold text-xs uppercase tracking-wider shadow-2xs transition-colors inline-flex items-center justify-center cursor-pointer">
                                            <span>+ Chọn</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                <!-- Empty State khi không tìm thấy món -->
                <div id="menuLiveSearchEmpty" class="hidden bg-white rounded-none sm:rounded-sm border border-stone-200 p-10 text-center my-4 shadow-2xs">
                    <div class="w-12 h-12 rounded-full bg-orange-50 text-[#F47716] flex items-center justify-center mx-auto text-xl mb-3 border border-orange-100">
                        <i class="fa-solid fa-utensils"></i>
                    </div>
                    <h3 class="text-base font-bold text-stone-900 mb-1">Không tìm thấy món ăn phù hợp</h3>
                    <p class="text-stone-500 text-xs sm:text-sm max-w-md mx-auto mb-5 leading-relaxed">
                        Không có món ăn nào khớp với từ khóa đã tìm. Quý khách vui lòng thử tìm từ khóa khác như "lẩu", "nướng", "bắp bò", "gà"...
                    </p>
                    <button type="button" id="menuLiveSearchResetBtn" class="inline-flex items-center justify-center px-5 py-2 bg-[#F47716] hover:bg-[#EA580C] text-white font-bold text-xs uppercase tracking-wider rounded-none sm:rounded-sm transition-colors cursor-pointer shadow-2xs">
                        <span>Xem toàn bộ thực đơn</span>
                    </button>
                </div>
            </div>

            <!-- 4.2 Khung Thực Đơn Mặc Định Phân Trang (Chỉ load phần này khi chọn sắp xếp hoặc danh mục) -->
            <div id="menuDefaultGridWrapper">
                <x-list-product-grid :categoryitem="$categoryitem" :branditem="$branditem" />
            </div>

        </div>

        <!-- CỘT PHẢI: MY ORDER / GIỎ HÀNG THỜI GIAN THỰC (4 cột - Chỉ hiện khi có món hoặc khi bấm mở) -->
        <div id="menuLiveCartSlot" class="lg:col-span-4 xl:col-span-4 sticky top-24 z-20 {{ $hasItemsInCart ? '' : 'hidden' }} transition-all duration-300">
            <x-menu-live-cart />
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // =========================================================
        // DỮ LIỆU MÓN ĂN TOÀN BỘ PHỤC VỤ TÌM KIẾM TỨC THÌ (0ms LATENCY)
        // =========================================================
        var allProductsData = [
            @if (isset($allProducts))
                @foreach ($allProducts as $p)
                    @php
                        $hasDiscount = !empty($p->price_sale) && !empty($p->price_buy) && ($p->price_sale < $p->price_buy);
                        $imgSrc = !empty($p->image) && file_exists(public_path('images/product/' . $p->image))
                            ? asset('images/product/' . $p->image)
                            : asset('images/default-product.png');
                        $pDisplay = number_format($hasDiscount ? $p->price_sale : $p->price_buy, 0, ',', '.') . 'đ';
                        $pOld = $hasDiscount ? number_format($p->price_buy, 0, ',', '.') . 'đ' : '';
                    @endphp
                    {
                        id: {{ $p->id }},
                        name: {!! json_encode($p->name) !!},
                        rawName: {!! json_encode(mb_strtolower($p->name, 'UTF-8')) !!},
                        asciiName: {!! json_encode(Str::lower(Str::ascii($p->name))) !!},
                        category: {!! json_encode($p->category ? $p->category->name : '') !!},
                        rawCategory: {!! json_encode($p->category ? mb_strtolower($p->category->name, 'UTF-8') : '') !!},
                        asciiCategory: {!! json_encode($p->category ? Str::lower(Str::ascii($p->category->name)) : '') !!},
                        image: {!! json_encode($imgSrc) !!},
                        price: {!! json_encode($pDisplay) !!},
                        oldPrice: {!! json_encode($pOld) !!},
                        url: {!! json_encode(route('site.product.detail', ['slug' => $p->slug])) !!}
                    },
                @endforeach
            @endif
        ];

        // Chuẩn hóa xóa dấu tiếng Việt chính xác 100%
        function removeVietnameseTones(str) {
            if (!str) return '';
            str = str.toLowerCase();
            str = str.replace(/à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ/g, "a");
            str = str.replace(/è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ/g, "e");
            str = str.replace(/ì|í|ị|ỉ|ĩ/g, "i");
            str = str.replace(/ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ/g, "o");
            str = str.replace(/ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ/g, "u");
            str = str.replace(/ỳ|ý|ỵ|ỷ|ỹ/g, "y");
            str = str.replace(/đ/g, "d");
            str = str.replace(/\u0300|\u0301|\u0303|\u0309|\u0323/g, "");
            str = str.replace(/\u02C6|\u0306|\u031B/g, "");
            return str.trim();
        }

        var searchInput = document.getElementById('menuSearchInput');
        var searchClearBtn = document.getElementById('menuSearchClearBtn');
        var sortSelect = document.getElementById('menuSortSelect');
        var searchDropdown = document.getElementById('menuSearchDropdown');
        var dropdownCount = document.getElementById('menuDropdownCount');
        var dropdownItems = document.getElementById('menuDropdownItems');
        var dropdownEmpty = document.getElementById('menuDropdownEmpty');

        var liveResultsPane = document.getElementById('menuLiveSearchResultsPane');
        var liveQueryText = document.getElementById('menuLiveSearchQueryText');
        var liveCountText = document.getElementById('menuLiveSearchCountText');
        var liveGrid = document.getElementById('menuLiveSearchGrid');
        var liveEmpty = document.getElementById('menuLiveSearchEmpty');
        var liveCloseBtn = document.getElementById('menuLiveSearchCloseBtn');
        var liveResetBtn = document.getElementById('menuLiveSearchResetBtn');

        var defaultGridWrapper = document.getElementById('menuDefaultGridWrapper');
        var categoryTitleSection = document.getElementById('menuCategoryTitleSection');
        var categoryTabsSection = document.getElementById('menuCategoryTabsSection');
        var activeFilterTagsWrapper = document.getElementById('menuActiveFilterTagsWrapper');

        // Hàm chuyển đổi URL tuyệt đối sang đường dẫn tương đối an toàn
        // Tuyệt đối không bị lỗi Mixed Content (http vs https) hay CORS
        function getSafeRelativeUrl(rawUrl) {
            if (!rawUrl) return '';
            try {
                var parsed = new URL(rawUrl, window.location.origin);
                return parsed.pathname + parsed.search;
            } catch (e) {
                return rawUrl;
            }
        }

        // =========================================================
        // HÀM TẢI MƯỢT DANH SÁCH MÓN ĂN QUA AJAX (LƯỚT SANG, KHÔNG LOAD TRANG)
        // =========================================================
        var isAjaxLoading = false;

        function loadProductsAjax(targetUrl, direction) {
            var gridWrapper = document.getElementById('menuDefaultGridWrapper');
            if (!gridWrapper) return;

            if (isAjaxLoading) return;
            isAjaxLoading = true;

            var safeUrl = getSafeRelativeUrl(targetUrl);

            // Hiệu ứng "lướt sang" (slide transition):
            // direction: 'next' (lướt sang trái), 'prev' (lướt sang phải), hoặc 'fade'
            var exitTransform = 'none';
            var enterTransform = 'none';
            if (direction === 'next') {
                exitTransform = 'translateX(-28px)';
                enterTransform = 'translateX(28px)';
            } else if (direction === 'prev') {
                exitTransform = 'translateX(28px)';
                enterTransform = 'translateX(-28px)';
            }

            gridWrapper.style.transition = 'transform 0.2s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.2s ease';
            gridWrapper.style.opacity = '0.2';
            if (exitTransform !== 'none') {
                gridWrapper.style.transform = exitTransform;
            }

            fetch(safeUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html,application/xhtml+xml,application/xml'
                }
            })
            .then(function (res) {
                if (!res.ok) throw new Error('HTTP error ' + res.status);
                return res.text();
            })
            .then(function (html) {
                var parser = new DOMParser();
                var doc = parser.parseFromString(html, 'text/html');

                // Chỉ thay đổi nội dung khung món ăn bên dưới
                var newGrid = doc.getElementById('menuDefaultGridWrapper');
                if (newGrid) {
                    gridWrapper.innerHTML = newGrid.innerHTML;
                }

                // Cập nhật lại thẻ lọc đang chọn nếu có
                var newTagsWrapper = doc.getElementById('menuActiveFilterTagsWrapper');
                var curTagsWrapper = document.getElementById('menuActiveFilterTagsWrapper');
                if (curTagsWrapper && newTagsWrapper) {
                    curTagsWrapper.innerHTML = newTagsWrapper.innerHTML;
                }

                // Cuộn nhẹ nhàng đến đầu danh sách món ăn nếu người dùng đang ở quá xa phía dưới
                var rect = gridWrapper.getBoundingClientRect();
                if (rect.top < 60) {
                    window.scrollTo({
                        top: window.pageYOffset + rect.top - 80,
                        behavior: 'smooth'
                    });
                }

                // Thiết lập vị trí bắt đầu lướt vào cho trang mới
                gridWrapper.style.transition = 'none';
                gridWrapper.style.opacity = '0';
                if (enterTransform !== 'none') {
                    gridWrapper.style.transform = enterTransform;
                }

                // Buộc trình duyệt tính toán lại layout (reflow)
                void gridWrapper.offsetWidth;

                // Lướt sang mượt mà hoàn hảo
                gridWrapper.style.transition = 'transform 0.28s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.28s ease';
                gridWrapper.style.opacity = '1';
                gridWrapper.style.transform = 'translateX(0)';

                // Cập nhật URL trên thanh địa chỉ mà không reload trang
                window.history.pushState({}, '', safeUrl);

                isAjaxLoading = false;
            })
            .catch(function (err) {
                console.warn('Loi tai AJAX thuc don:', err);
                gridWrapper.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
                gridWrapper.style.opacity = '1';
                gridWrapper.style.transform = 'none';
                isAjaxLoading = false;
            });
        }

        // Bắt sự kiện click phân trang bằng Event Delegation để 100% không bao giờ reload trang
        document.addEventListener('click', function (e) {
            var pageLink = e.target.closest('#menuDefaultGridWrapper nav a[href], #menuDefaultGridWrapper a[href*="page="]');
            if (!pageLink) return;

            e.preventDefault();
            e.stopPropagation();

            var href = pageLink.getAttribute('href');
            if (!href) return;

            // Xác định hướng lướt sang: Trang trước ('prev') hay Trang sau ('next')
            var direction = 'next';
            var linkText = (pageLink.textContent || '').trim().toLowerCase();
            if (linkText.includes('trước') || pageLink.querySelector('.fa-chevron-left')) {
                direction = 'prev';
            } else {
                var targetPage = parseInt(linkText, 10);
                var activePageEl = document.querySelector('#menuDefaultGridWrapper nav span.bg-stone-900, #menuDefaultGridWrapper nav span[style*="background-color"]');
                var curPage = activePageEl ? parseInt(activePageEl.textContent.trim(), 10) : 1;
                if (!isNaN(targetPage) && !isNaN(curPage) && targetPage < curPage) {
                    direction = 'prev';
                }
            }

            loadProductsAjax(href, direction);
        });

        // Bắt sự kiện click tab danh mục bằng Event Delegation
        document.addEventListener('click', function (e) {
            var tab = e.target.closest('.category-filter-tab');
            if (!tab) return;

            e.preventDefault();
            e.stopPropagation();

            var href = tab.getAttribute('href');
            if (!href) return;

            var allTabs = document.querySelectorAll('.category-filter-tab');
            allTabs.forEach(function (t) {
                t.className = 'category-filter-tab px-3.5 py-2 rounded-none sm:rounded-sm text-xs sm:text-sm font-semibold transition-all shrink-0 bg-white text-stone-700 hover:text-[#F47716] hover:bg-stone-50 border border-stone-200';
            });
            tab.className = 'category-filter-tab px-3.5 py-2 rounded-none sm:rounded-sm text-xs sm:text-sm font-semibold transition-all shrink-0 bg-[#F47716] text-white border border-[#F47716] shadow-2xs';

            loadProductsAjax(href, 'fade');
        });

        // Sắp xếp các thẻ món ăn tìm kiếm tức thì (khi người dùng đang gõ tìm kiếm)
        function sortLiveSearchCards(sortVal) {
            var grid = document.getElementById('menuLiveSearchGrid');
            if (!grid) return;
            var cards = Array.from(grid.querySelectorAll('.menu-live-search-card'));

            cards.sort(function (a, b) {
                var priceA = parseFloat(a.getAttribute('data-price') || '0');
                var priceB = parseFloat(b.getAttribute('data-price') || '0');
                var nameA = (a.getAttribute('data-name') || '').trim();
                var nameB = (b.getAttribute('data-name') || '').trim();
                var idA = parseInt(a.getAttribute('data-id') || '0', 10);
                var idB = parseInt(b.getAttribute('data-id') || '0', 10);

                if (sortVal === 'priceAsc') {
                    return priceA - priceB;
                } else if (sortVal === 'priceDesc') {
                    return priceB - priceA;
                } else if (sortVal === 'alphabet') {
                    return nameA.localeCompare(nameB, 'vi');
                } else if (sortVal === 'newest') {
                    return idB - idA;
                }
                return 0;
            });

            cards.forEach(function (card) {
                grid.appendChild(card);
            });
        }

        // =========================================================
        // HÀM TÌM KIẾM TỨC THÌ (DROPDOWN + LƯỚI MÓN ĂN)
        // =========================================================
        function executeSearch(query) {
            var rawQ = (query || '').toLowerCase().trim();
            var asciiQ = removeVietnameseTones(rawQ);

            if (!rawQ) {
                clearSearchState();
                return;
            }

            // Hiện nút Xóa tìm kiếm
            if (searchClearBtn) searchClearBtn.classList.remove('hidden');

            // 1. CẬP NHẬT GỢI Ý DROPDOWN TỨC THÌ DƯỚI THANH TÌM KIẾM
            var matchedProducts = allProductsData.filter(function (p) {
                return p.rawName.includes(rawQ) ||
                       p.asciiName.includes(asciiQ) ||
                       p.rawCategory.includes(rawQ) ||
                       p.asciiCategory.includes(asciiQ);
            });

            // Sắp xếp ưu tiên: món có tên bắt đầu hoặc chứa từ khóa lên đầu
            matchedProducts.sort(function (a, b) {
                var aStarts = a.rawName.startsWith(rawQ) || a.asciiName.startsWith(asciiQ);
                var bStarts = b.rawName.startsWith(rawQ) || b.asciiName.startsWith(asciiQ);
                if (aStarts && !bStarts) return -1;
                if (!aStarts && bStarts) return 1;
                var aNameMatch = a.rawName.includes(rawQ) || a.asciiName.includes(asciiQ);
                var bNameMatch = b.rawName.includes(rawQ) || b.asciiName.includes(asciiQ);
                if (aNameMatch && !bNameMatch) return -1;
                if (!aNameMatch && bNameMatch) return 1;
                return 0;
            });

            if (dropdownCount) dropdownCount.textContent = matchedProducts.length;

            if (matchedProducts.length === 0) {
                if (dropdownItems) dropdownItems.innerHTML = '';
                if (dropdownEmpty) dropdownEmpty.classList.remove('hidden');
            } else {
                if (dropdownEmpty) dropdownEmpty.classList.add('hidden');
                if (dropdownItems) {
                    var html = matchedProducts.slice(0, 8).map(function (item) {
                        return '<div class="p-2.5 flex items-center gap-3 hover:bg-orange-50/70 transition-colors cursor-pointer group" onclick="window.location.href=\'' + item.url + '\'">' +
                                   '<img src="' + item.image + '" alt="' + item.name + '" class="w-12 h-12 object-cover rounded-md border border-stone-200 shrink-0">' +
                                   '<div class="flex-1 min-w-0">' +
                                       '<div class="text-xs sm:text-sm font-bold text-stone-900 group-hover:text-[#F47716] truncate transition-colors">' + item.name + '</div>' +
                                       '<div class="text-[11px] text-stone-400 truncate">' + (item.category || 'Món ngon') + '</div>' +
                                   '</div>' +
                                   '<div class="shrink-0 text-right flex items-center gap-2" onclick="event.stopPropagation()">' +
                                       '<div>' +
                                           '<div class="text-xs sm:text-sm font-black text-[#F47716]">' + item.price + '</div>' +
                                           (item.oldPrice ? '<div class="text-[10px] text-stone-400 line-through">' + item.oldPrice + '</div>' : '') +
                                       '</div>' +
                                       '<button type="button" onclick="window.addToCartAjax ? window.addToCartAjax(' + item.id + ', this) : null" class="px-2.5 py-1 bg-[#F47716] hover:bg-[#EA580C] text-white text-[11px] font-bold rounded-none sm:rounded-xs transition-colors shadow-2xs cursor-pointer">' +
                                           '+ Chọn' +
                                       '</button>' +
                                   '</div>' +
                               '</div>';
                    }).join('');

                    if (matchedProducts.length > 8) {
                        html += '<div class="p-2 bg-stone-50 text-center border-t border-stone-100">' +
                                    '<span class="text-xs text-stone-500 font-medium">Đang hiển thị 8 / ' + matchedProducts.length + ' món</span>' +
                                '</div>';
                    }

                    dropdownItems.innerHTML = html;
                }
            }
            if (searchDropdown) searchDropdown.classList.remove('hidden');

            // 2. CẬP NHẬT KHUNG THỰC ĐƠN CHÍNH TRÊN TRANG (TỨC THÌ, KHÔNG CẦN ENTER)
            if (categoryTitleSection) categoryTitleSection.classList.add('hidden');
            if (categoryTabsSection) categoryTabsSection.classList.add('hidden');
            if (activeFilterTagsWrapper) activeFilterTagsWrapper.classList.add('hidden');
            if (defaultGridWrapper) defaultGridWrapper.classList.add('hidden');

            if (liveResultsPane) liveResultsPane.classList.remove('hidden');
            if (liveQueryText) liveQueryText.textContent = '"' + query + '"';

            var gridMatchCount = 0;
            var searchCards = document.querySelectorAll('.menu-live-search-card');
            searchCards.forEach(function (card) {
                var cName = card.getAttribute('data-name') || '';
                var cAscii = card.getAttribute('data-ascii') || '';
                var cCat = card.getAttribute('data-cat') || '';
                var cAsciiCat = removeVietnameseTones(cCat);

                var isCardMatch = cName.includes(rawQ) ||
                                  cAscii.includes(asciiQ) ||
                                  cCat.includes(rawQ) ||
                                  cAsciiCat.includes(asciiQ);

                if (isCardMatch) {
                    card.style.display = '';
                    gridMatchCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            if (liveCountText) liveCountText.textContent = gridMatchCount + ' món';

            if (gridMatchCount === 0) {
                if (liveGrid) liveGrid.classList.add('hidden');
                if (liveEmpty) liveEmpty.classList.remove('hidden');
            } else {
                if (liveGrid) liveGrid.classList.remove('hidden');
                if (liveEmpty) liveEmpty.classList.add('hidden');
                // Áp dụng thứ tự sắp xếp hiện tại nếu có
                if (sortSelect && sortSelect.value !== 'default') {
                    sortLiveSearchCards(sortSelect.value);
                }
            }
        }

        // Xóa trạng thái tìm kiếm và trả về thực đơn phân trang ban đầu
        function clearSearchState() {
            if (searchInput) searchInput.value = '';
            if (searchClearBtn) searchClearBtn.classList.add('hidden');
            if (searchDropdown) searchDropdown.classList.add('hidden');

            if (liveResultsPane) liveResultsPane.classList.add('hidden');
            if (categoryTitleSection) categoryTitleSection.classList.remove('hidden');
            if (categoryTabsSection) categoryTabsSection.classList.remove('hidden');
            if (activeFilterTagsWrapper) activeFilterTagsWrapper.classList.remove('hidden');
            if (defaultGridWrapper) defaultGridWrapper.classList.remove('hidden');

            // Cập nhật lại thanh địa chỉ URL nếu trước đó có param ?search=
            var url = new URL(window.location.href);
            if (url.searchParams.has('search')) {
                url.searchParams.delete('search');
                url.searchParams.delete('page');
                window.history.replaceState({}, '', url.toString());
            }
        }

        // Bắt sự kiện gõ phím trực tiếp: gõ "lau" hay "lẩu" là hiện ảnh ngay tức thì
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                executeSearch(this.value);
            });

            searchInput.addEventListener('focus', function () {
                if (this.value.trim().length > 0) {
                    if (searchDropdown) searchDropdown.classList.remove('hidden');
                }
            });

            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    if (searchDropdown) searchDropdown.classList.add('hidden');
                    var url = new URL(window.location.href);
                    if (this.value.trim()) {
                        url.searchParams.set('search', this.value.trim());
                    } else {
                        url.searchParams.delete('search');
                    }
                    url.searchParams.delete('page');
                    window.history.replaceState({}, '', url.toString());
                } else if (e.key === 'Escape') {
                    if (searchDropdown) searchDropdown.classList.add('hidden');
                }
            });
        }

        if (searchClearBtn) {
            searchClearBtn.addEventListener('click', function () {
                clearSearchState();
                if (searchInput) searchInput.focus();
            });
        }

        if (liveCloseBtn) {
            liveCloseBtn.addEventListener('click', function () {
                clearSearchState();
            });
        }

        if (liveResetBtn) {
            liveResetBtn.addEventListener('click', function () {
                clearSearchState();
            });
        }

        // Đóng dropdown khi bấm ra ngoài thanh tìm kiếm
        document.addEventListener('click', function (e) {
            var wrapper = document.getElementById('menuSearchInputWrapper');
            if (wrapper && !wrapper.contains(e.target)) {
                if (searchDropdown) searchDropdown.classList.add('hidden');
            }
        });

        // =========================================================
        // XỬ LÝ BỘ LỌC SẮP XẾP: CHỈ LOAD LẠI MÓN ĂN BÊN DƯỚI
        // =========================================================
        if (sortSelect) {
            sortSelect.addEventListener('change', function () {
                var sortVal = this.value;

                // Nếu đang trong chế độ tìm kiếm: sắp xếp tức thì trên DOM các thẻ kết quả
                if (searchInput && searchInput.value.trim().length > 0) {
                    sortLiveSearchCards(sortVal);
                    return;
                }

                // Nếu đang xem thực đơn mặc định: chỉ tải lại phần danh sách món ăn bên dưới qua AJAX
                var url = new URL(window.location.href);
                if (sortVal && sortVal !== 'default') {
                    url.searchParams.set('sort', sortVal);
                } else {
                    url.searchParams.delete('sort');
                }
                url.searchParams.delete('page');

                loadProductsAjax(url.toString(), 'fade');
            });
        }

        // Hỗ trợ nút Back/Forward của trình duyệt không bị giật trang
        window.addEventListener('popstate', function () {
            var currentUrl = window.location.href;
            var u = new URL(currentUrl);
            var sortP = u.searchParams.get('sort') || 'default';
            if (sortSelect) sortSelect.value = sortP;
            loadProductsAjax(currentUrl, 'fade');
        });

        // Khởi động nếu trên URL đã có sẵn từ khóa tìm kiếm (?search=...)
        if (searchInput && searchInput.value.trim()) {
            executeSearch(searchInput.value.trim());
        }
    });
</script>
