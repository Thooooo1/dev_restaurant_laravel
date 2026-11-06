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

            <!-- Search Grid hiển thị toàn bộ món khớp tìm kiếm -->
            <div id="home-search-grid" class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                @if (isset($allProducts))
                    @foreach ($allProducts as $product)
                        @php
                            $cleanName = mb_strtolower($product->name, 'UTF-8');
                            $asciiName = Str::lower(Str::ascii($product->name));
                            $catName = $product->category ? mb_strtolower($product->category->name, 'UTF-8') : '';
                        @endphp
                        <div class="home-search-item" 
                             data-name="{{ $cleanName }}" 
                             data-ascii="{{ $asciiName }}"
                             data-cat="{{ $catName }}">
                            <x-product-card :productitem="$product" />
                        </div>
                    @endforeach
                @endif
            </div>

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
                        <x-home-product-category :categoryitem="$categoryitem" />
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
        const searchItems = document.querySelectorAll('.home-search-item');
        const clearBtn = document.getElementById('home-search-clear-btn');
        const resetBtn = document.getElementById('home-search-reset-btn');

        function executeSearch(query) {
            const rawQ = (query || '').toLowerCase().trim();
            const asciiQ = removeVietnameseTones(rawQ);

            if (!rawQ) {
                clearSearch();
                return;
            }

            // Tạm ẩn danh mục mặc định
            if (catTabs) catTabs.classList.add('hidden');
            if (catPanesWrapper) catPanesWrapper.classList.add('hidden');

            // Bật khung kết quả tìm kiếm
            if (searchResultsPane) searchResultsPane.classList.remove('hidden');
            if (searchQueryText) searchQueryText.textContent = `"${query}"`;

            let matchCount = 0;
            searchItems.forEach(item => {
                const rawName = item.getAttribute('data-name') || '';
                const asciiName = item.getAttribute('data-ascii') || '';
                const catName = item.getAttribute('data-cat') || '';
                const asciiCat = removeVietnameseTones(catName);

                const isMatch = rawName.includes(rawQ) || 
                                asciiName.includes(asciiQ) || 
                                catName.includes(rawQ) || 
                                asciiCat.includes(asciiQ);

                if (isMatch) {
                    item.style.display = '';
                    matchCount++;
                } else {
                    item.style.display = 'none';
                }
            });

            if (searchCountText) searchCountText.textContent = `${matchCount} món`;

            if (matchCount === 0) {
                if (searchGrid) searchGrid.classList.add('hidden');
                if (searchEmpty) searchEmpty.classList.remove('hidden');
            } else {
                if (searchGrid) searchGrid.classList.remove('hidden');
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
