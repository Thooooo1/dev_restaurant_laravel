<section id="tim-kiem-mon-an" class="gallery-area py-10 md:py-14 bg-white relative scroll-mt-20 sm:scroll-mt-24">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 w-full">
        <!-- Search & Hot Keywords Strip -->
        <div class="max-w-2xl mx-auto mb-8">
            <form action="{{ route('site.product.gridview') }}" method="GET" id="home-search-form" class="relative group">
                <i class="fas fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-stone-400 group-focus-within:text-[#F47716] transition-colors text-sm"></i>
                <input type="search" 
                       id="home-search-input"
                       name="search" 
                       autocomplete="off"
                       placeholder="Tìm món: Lẩu thái, bắp bò nhúng dấm, gà, đồ nướng, hải sản..." 
                       class="w-full pl-11 pr-24 py-3 rounded-lg bg-stone-50/80 border border-stone-200 text-stone-800 placeholder-stone-400 focus:outline-none focus:border-[#F47716] text-xs sm:text-sm transition-all shadow-xs" />
                <button type="submit" 
                        id="home-search-submit-btn"
                        class="absolute right-1.5 top-1/2 -translate-y-1/2 px-5 py-2 rounded-lg bg-[#F47716] hover:bg-[#EA580C] text-white font-bold text-xs uppercase tracking-wider transition-colors cursor-pointer">
                    Tìm kiếm
                </button>
            </form>
            <div class="mt-3">
                <x-key-word-hot />
            </div>
        </div>

        <style>
            .cat-tabs-scroll::-webkit-scrollbar {
                display: none !important;
                height: 0px !important;
            }
            .cat-tabs-scroll {
                -ms-overflow-style: none !important;
                scrollbar-width: none !important;
            }
        </style>

        <!-- Category Tabs (Sẽ tạm ẩn khi đang có kết quả tìm kiếm) -->
        <div class="cat-tabs-scroll flex items-center justify-start md:justify-center gap-2 sm:gap-2.5 overflow-x-auto pb-2 mb-8 -mx-4 px-4 md:mx-0 md:px-0 md:overflow-visible scroll-smooth">
            <!-- Tab 1: Món Nổi Bật -->
            <button type="button"
                    class="category-tab-btn active-cat whitespace-nowrap px-4 sm:px-5 py-2.5 rounded-none sm:rounded-sm text-xs sm:text-sm font-bold transition-all duration-200 cursor-pointer flex items-center bg-[#F47716] text-white shadow-2xs shrink-0"
                    data-target="cat-pane-featured">
                <span>Món nổi bật</span>
            </button>

            <!-- 6 Tab Danh Mục Cốt Lõi -->
            @foreach ($categories as $categoryitem)
                <button type="button"
                        class="category-tab-btn whitespace-nowrap px-4 sm:px-5 py-2.5 rounded-none sm:rounded-sm text-xs sm:text-sm font-semibold transition-all duration-200 cursor-pointer flex items-center bg-stone-100 hover:bg-orange-50 text-stone-700 hover:text-[#F47716] border border-stone-200 shrink-0"
                        data-target="cat-pane-{{ $categoryitem->id }}">
                    <span>{{ $categoryitem->name }}</span>
                </button>
            @endforeach
        </div>

        <!-- Search Results Pane: Xuất hiện tức thì ngay tại trang chủ khi người dùng gõ tìm món -->
        <div id="home-search-results-pane" class="hidden min-h-[320px] mb-8">
            <div class="flex items-center justify-between mb-6 pb-3 border-b border-stone-200 flex-wrap gap-2">
                <div class="text-stone-800 text-sm sm:text-base font-bold flex items-center gap-2 flex-wrap">
                    <i class="fas fa-magnifying-glass text-[#F47716] text-sm"></i>
                    <span>Kết quả tìm kiếm cho: <span class="text-[#F47716]" id="home-search-query-text">""</span></span>
                    <span class="text-stone-600 font-medium text-xs bg-stone-100 px-2.5 py-0.5 rounded-full border border-stone-200" id="home-search-count-text">0 món</span>
                </div>
                <button type="button" id="home-search-clear-btn" class="text-xs text-stone-600 hover:text-[#F47716] font-semibold flex items-center gap-1.5 transition-colors cursor-pointer px-3 py-1.5 rounded-sm hover:bg-orange-50 border border-stone-200 bg-white">
                    <i class="fas fa-xmark"></i>
                    <span>Xem lại danh mục món</span>
                </button>
            </div>

            <!-- Search Grid: Render động bằng JS khi gõ tìm kiếm (tránh tải trước 100+ ảnh ẩn khi mới vào trang) -->
            <div id="home-search-grid" class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6"></div>
            <script type="application/json" id="home-products-json">@json($searchCatalog ?? [])</script>

            <!-- Empty Search State khi không có món nào khớp -->
            <div id="home-search-empty" class="hidden text-center py-12 px-4 bg-[#FAF7F2] rounded-none sm:rounded-sm border border-stone-200">
                <i class="fas fa-utensils text-4xl text-stone-300 mb-3 block"></i>
                <h4 class="text-stone-900 font-bold text-base mb-1">Không tìm thấy món ăn phù hợp</h4>
                <p class="text-stone-500 text-xs sm:text-sm mb-5 max-w-md mx-auto">
                    Không có món ăn nào khớp với từ khóa đã tìm. Quý khách vui lòng thử tìm từ khóa khác như "gà", "bò", "lẩu", "nướng"...
                </p>
                <button type="button" id="home-search-reset-btn" class="px-6 py-2.5 bg-[#F47716] hover:bg-[#EA580C] text-white font-bold text-xs uppercase tracking-wider rounded-none sm:rounded-sm transition-colors cursor-pointer shadow-2xs">
                    Xem lại món nổi bật
                </button>
            </div>
        </div>

        <!-- Dishes Grid per Category Tab (Trạng thái mặc định ban đầu) -->
        <div class="category-panes-wrapper min-h-[300px]">
            <!-- Pane 1: 8 Món chính nổi bật -->
            <div id="cat-pane-featured" class="category-pane-content transition-opacity duration-300 block">
                <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                    @foreach ($featuredProducts as $product)
                        <x-product-card :productitem="$product" />
                    @endforeach
                </div>
            </div>

            <!-- Các Pane Danh Mục Cụ Thể -->
            @foreach ($categories as $categoryitem)
                <div id="cat-pane-{{ $categoryitem->id }}" 
                     class="category-pane-content transition-opacity duration-300 hidden">
                    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                        <x-home-product-category :categoryitem="$categoryitem" :products="isset($categoryProductsMap) ? ($categoryProductsMap[$categoryitem->id] ?? null) : null" />
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Bottom Menu CTA -->
        <div class="mt-10 sm:mt-12 text-center">
            <a href="{{ route('site.product.gridview') }}" 
               class="inline-flex items-center justify-center px-8 py-3 rounded-none sm:rounded-sm bg-[#F47716] hover:bg-[#EA580C] text-white font-bold text-xs uppercase tracking-wider shadow-2xs transition-colors cursor-pointer">
                <span>Xem toàn bộ thực đơn</span>
                <i class="fas fa-arrow-right text-[11px] ml-2"></i>
            </a>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Tab switching logic
        const tabBtns = document.querySelectorAll('.category-tab-btn');
        const panes = document.querySelectorAll('.category-pane-content');

        tabBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const targetId = this.getAttribute('data-target');

                tabBtns.forEach(b => {
                    b.classList.remove('active-cat', 'bg-[#F47716]', 'text-white', 'shadow-xs');
                    b.classList.add('bg-stone-100', 'text-stone-700');
                });

                this.classList.remove('bg-stone-100', 'text-stone-700');
                this.classList.add('active-cat', 'bg-[#F47716]', 'text-white', 'shadow-xs');

                panes.forEach(pane => {
                    pane.classList.add('hidden');
                    pane.classList.remove('block');
                });

                const targetPane = document.getElementById(targetId);
                if (targetPane) {
                    targetPane.classList.remove('hidden');
                    targetPane.classList.add('block');
                }
            });
        });

        // =========================================================
        // TÌM KIẾM TỨC THÌ NGAY TẠI TRANG CHỦ (Cả có dấu & không dấu)
        // =========================================================
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

        const searchInput = document.getElementById('home-search-input');
        const searchForm = document.getElementById('home-search-form');
        const catTabs = document.querySelector('.cat-tabs-scroll');
        const catPanesWrapper = document.querySelector('.category-panes-wrapper');
        const searchResultsPane = document.getElementById('home-search-results-pane');
        const searchQueryText = document.getElementById('home-search-query-text');
        const searchCountText = document.getElementById('home-search-count-text');
        const searchEmpty = document.getElementById('home-search-empty');
        const searchGrid = document.getElementById('home-search-grid');
        const clearBtn = document.getElementById('home-search-clear-btn');
        const resetBtn = document.getElementById('home-search-reset-btn');

        let homeProductsCatalog = [];
        try {
            const jsonEl = document.getElementById('home-products-json');
            if (jsonEl && jsonEl.textContent) {
                homeProductsCatalog = JSON.parse(jsonEl.textContent);
            }
        } catch (e) {
            homeProductsCatalog = [];
        }

        function escapeHomeHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function buildHomeProductCard(p) {
            const safeName = escapeHomeHtml(p.name);
            const safeCat = escapeHomeHtml(p.cat);
            const safeUrl = escapeHomeHtml(p.url);
            const safeImg = escapeHomeHtml(p.img);
            let badges = '';
            if (p.has_discount) {
                badges += `<span class="bg-red-600 text-white font-extrabold text-[10px] px-2 py-0.5 rounded-none sm:rounded-2xs shadow-xs tracking-wider uppercase">-${p.discount_pct}%</span>`;
            }
            if (p.new_p === 1) {
                badges += `<span class="bg-amber-500 text-white font-extrabold text-[10px] px-2 py-0.5 rounded-none sm:rounded-2xs shadow-xs uppercase tracking-wider">Bán chạy</span>`;
            }

            let priceHtml = '';
            if (p.has_discount) {
                priceHtml = `<div class="flex items-baseline gap-1.5"><span class="text-orange-600 font-extrabold text-sm sm:text-base">${escapeHomeHtml(p.price_fmt)}</span><span class="text-stone-400 text-xs line-through">${escapeHomeHtml(p.old_price_fmt)}</span></div>`;
            } else if (p.has_price) {
                priceHtml = `<span class="text-orange-600 font-extrabold text-sm sm:text-base">${escapeHomeHtml(p.price_fmt)}</span>`;
            } else {
                priceHtml = `<span class="text-orange-600 font-bold text-xs sm:text-sm">Liên hệ giá</span>`;
            }

            return `
                <div class="home-search-item">
                    <div class="product-card group relative bg-white rounded-none sm:rounded-sm overflow-hidden border border-stone-150 hover:border-stone-300 shadow-2xs hover:shadow-md transition-all duration-300 flex flex-col justify-between h-full">
                        <div class="relative aspect-[4/3] w-full overflow-hidden bg-stone-100">
                            <a href="${safeUrl}" class="block w-full h-full">
                                <img src="${safeImg}" alt="${safeName}" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out">
                            </a>
                            <div class="absolute top-2.5 left-2.5 flex items-center gap-1.5 pointer-events-none">${badges}</div>
                            <div class="absolute inset-0 bg-stone-950/35 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center pointer-events-none group-hover:pointer-events-auto p-3">
                                <a href="${safeUrl}" class="px-3.5 py-1.5 rounded-none sm:rounded-sm bg-white/95 text-stone-900 hover:bg-[#C25E00] hover:text-white font-bold text-xs uppercase tracking-wider shadow-md transition-all duration-200 inline-flex items-center gap-1.5">
                                    <i class="fa-regular fa-eye text-xs"></i>
                                    <span>Xem chi tiết</span>
                                </a>
                            </div>
                        </div>
                        <div class="p-3.5 sm:p-4 flex flex-col justify-between flex-grow">
                            <div>
                                ${safeCat ? `<div class="text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-1">${safeCat}</div>` : ''}
                                <h4 class="font-bold text-stone-900 text-sm sm:text-[15px] leading-snug line-clamp-2 min-h-[2.5rem] group-hover:text-orange-600 transition-colors mb-2">
                                    <a href="${safeUrl}">${safeName}</a>
                                </h4>
                            </div>
                            <div class="pt-2 border-t border-stone-100 flex items-center justify-between mt-auto">
                                <div>${priceHtml}</div>
                                <button type="button" onclick="window.addToCartGlobal ? window.addToCartGlobal(${p.id}, this) : null" class="px-3 py-1 rounded-sm bg-stone-900 hover:bg-[#C25E00] text-white font-bold text-xs uppercase tracking-wider transition-colors cursor-pointer shadow-2xs" title="Thêm vào giỏ">
                                    <span>+ Chọn</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>`;
        }

        function executeSearch(query) {
            const rawQ = (query || '').toLowerCase().trim();
            const asciiQ = removeVietnameseTones(rawQ);

            if (!rawQ) {
                clearSearch();
                return;
            }

            if (catTabs) catTabs.classList.add('hidden');
            if (catPanesWrapper) catPanesWrapper.classList.add('hidden');

            if (searchResultsPane) searchResultsPane.classList.remove('hidden');
            if (searchQueryText) searchQueryText.textContent = `"${query}"`;

            const matched = homeProductsCatalog.filter(p =>
                (p.raw && p.raw.includes(rawQ)) ||
                (p.ascii && p.ascii.includes(asciiQ)) ||
                (p.cat_raw && p.cat_raw.includes(rawQ)) ||
                (p.cat_ascii && p.cat_ascii.includes(asciiQ))
            );

            if (searchCountText) searchCountText.textContent = `${matched.length} món`;

            if (matched.length === 0) {
                if (searchGrid) {
                    searchGrid.innerHTML = '';
                    searchGrid.classList.add('hidden');
                }
                if (searchEmpty) searchEmpty.classList.remove('hidden');
            } else {
                if (searchGrid) {
                    searchGrid.innerHTML = matched.map(buildHomeProductCard).join('');
                    searchGrid.classList.remove('hidden');
                }
                if (searchEmpty) searchEmpty.classList.add('hidden');
            }
        }

        function clearSearch() {
            if (searchInput) searchInput.value = '';
            if (searchResultsPane) searchResultsPane.classList.add('hidden');
            if (catTabs) catTabs.classList.remove('hidden');
            if (catPanesWrapper) catPanesWrapper.classList.remove('hidden');
        }

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                executeSearch(this.value);
            });

            searchInput.addEventListener('search', function () {
                if (!this.value) clearSearch();
            });
        }

        if (searchForm) {
            searchForm.addEventListener('submit', function (e) {
                e.preventDefault();
                executeSearch(searchInput.value);
                const anchor = document.getElementById('tim-kiem-mon-an');
                if (anchor) {
                    anchor.scrollIntoView({ behavior: 'smooth' });
                }
            });
        }

        if (clearBtn) clearBtn.addEventListener('click', clearSearch);
        if (resetBtn) resetBtn.addEventListener('click', clearSearch);

        // Gợi ý món ăn: Bấm vào là lập tức tìm kiếm và hiện món ngay tại trang chủ
        document.querySelectorAll('.hot-keyword-item').forEach(el => {
            el.addEventListener('click', function (e) {
                e.preventDefault();
                const kw = this.getAttribute('data-keyword') || this.innerText.trim();
                if (searchInput) {
                    searchInput.value = kw;
                    executeSearch(kw);
                    const anchor = document.getElementById('tim-kiem-mon-an');
                    if (anchor) {
                        anchor.scrollIntoView({ behavior: 'smooth' });
                    }
                }
            });
        });
    });
</script>
