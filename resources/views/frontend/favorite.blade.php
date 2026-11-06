<x-layout-frontend>
    <main class="bg-[#F8FAFC] min-h-screen py-6 sm:py-8">
        <div class="max-w-[1200px] mx-auto px-4 sm:px-6 w-full">
            
            <!-- ======================================================== -->
            <!-- 1. TOP HEADER: Tiêu đề sạch sẽ & Tìm kiếm               -->
            <!-- ======================================================== -->
            <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-4 border-b border-stone-200">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-stone-900 tracking-tight">
                        Món ăn yêu thích
                    </h1>
                    <p class="text-xs sm:text-sm text-stone-500 mt-0.5 font-medium">
                        Danh sách các món ăn đã lưu để bạn dễ dàng gọi lại khi ghé quán
                    </p>
                </div>

                <!-- Search Bar & Sort Filter -->
                <div class="flex items-center gap-2.5 w-full md:w-auto">
                    <div class="relative flex-1 md:w-80">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400 text-xs"></i>
                        <input type="text" 
                               id="favSearchInput" 
                               value="{{ request('search') }}" 
                               placeholder="Tìm trong món yêu thích..." 
                               class="w-full pl-9 pr-8 py-2 text-xs sm:text-sm bg-white border border-stone-200 rounded-lg focus:outline-none focus:border-[#F47716] focus:ring-2 focus:ring-orange-100 shadow-xs transition-all">
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
                        <select id="favSortSelect" 
                                class="text-xs sm:text-sm bg-white border border-stone-200 text-stone-700 py-2 px-3 rounded-lg focus:outline-none focus:border-[#F47716] shadow-xs transition font-semibold cursor-pointer">
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
            <!-- 2. SECTION TITLE                                         -->
            <!-- ======================================================== -->
            <div class="flex items-center justify-between mb-3.5">
                <div class="flex items-center gap-3">
                    <h2 class="text-base sm:text-lg font-bold text-stone-900 tracking-tight">
                        Món đã lưu
                    </h2>
                    @if ($totalAllFavorites > 0)
                        <span class="text-xs px-2.5 py-0.5 rounded-full bg-orange-100 text-[#F47716] font-bold">
                            {{ $totalAllFavorites }} món
                        </span>
                    @endif
                </div>
                <a href="{{ route('site.product.gridview') }}" class="text-xs sm:text-sm font-bold text-[#F47716] hover:underline flex items-center gap-1">
                    <span>Xem toàn bộ thực đơn</span>
                    <i class="fa-solid fa-arrow-right text-[11px]"></i>
                </a>
            </div>

            <!-- ======================================================== -->
            <!-- 3. HORIZONTAL CATEGORY TABS                              -->
            <!-- ======================================================== -->
            @if ($categories->isNotEmpty())
                <div class="relative mb-6">
                    <div class="overflow-x-auto scroll-smooth py-1 -mx-2 px-2" style="-webkit-overflow-scrolling: touch; scrollbar-width: thin;">
                        <div class="flex items-center gap-2 min-w-max pb-1">
                            <a href="{{ request()->fullUrlWithQuery(['category' => 'all', 'page' => null]) }}" 
                               class="px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold transition-all shrink-0 {{ $categoryitem === 'all' ? 'bg-[#F47716] text-white border border-[#F47716] shadow-xs' : 'bg-white text-stone-700 hover:text-[#F47716] hover:bg-orange-50/50 border border-stone-200' }}">
                                <span>Tất cả</span>
                            </a>

                            @foreach ($categories as $cat)
                                @if ($cat->products_count > 0)
                                    <a href="{{ request()->fullUrlWithQuery(['category' => $cat->id, 'page' => null]) }}" 
                                       class="px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold transition-all shrink-0 {{ $categoryitem == $cat->id ? 'bg-[#F47716] text-white border border-[#F47716] shadow-xs' : 'bg-white text-stone-700 hover:text-[#F47716] hover:bg-orange-50/50 border border-stone-200' }}">
                                        <span>{{ $cat->name }}</span>
                                        <span class="ml-1 text-[11px] opacity-80">({{ $cat->products_count }})</span>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Active Filter Tags -->
            @if ($categoryitem !== 'all' || request('search'))
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

                    <a href="{{ route('site.favorites') }}" 
                       class="text-stone-400 hover:text-[#F47716] ml-1 text-xs underline font-medium">
                        Xóa tất cả bộ lọc
                    </a>
                </div>
            @endif

            <!-- ======================================================== -->
            <!-- 4. MAIN ORDER GRID: Left Food Grid + Right My Order      -->
            <!-- ======================================================== -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                
                <!-- CỘT TRÁI: DANH SÁCH MÓN ĂN (8 CỘT) -->
                <div class="lg:col-span-8 xl:col-span-8">
                    @if (!auth()->check())
                        <div class="bg-white rounded-xl border border-stone-200/90 p-12 text-center my-4 shadow-sm">
                            <div class="w-16 h-16 rounded-full bg-orange-50 text-[#F47716] flex items-center justify-center mx-auto text-2xl mb-4">
                                <i class="fa-regular fa-heart"></i>
                            </div>
                            <h3 class="text-lg font-bold text-stone-900 mb-2">Đăng nhập để xem món yêu thích</h3>
                            <p class="text-stone-500 text-xs sm:text-sm max-w-md mx-auto mb-6 leading-relaxed">
                                Quý khách vui lòng đăng nhập tài khoản thành viên để xem lại danh sách các món ăn đã yêu thích và gọi món trực tiếp.
                            </p>
                            <a href="{{ route('site.login') }}" 
                               class="inline-flex items-center justify-center px-6 py-2.5 rounded-lg bg-[#F47716] hover:bg-[#EA580C] text-white font-bold text-xs uppercase tracking-wider transition-colors shadow-xs">
                                <span>Đăng nhập ngay</span>
                            </a>
                        </div>
                    @elseif ($products->isEmpty())
                        <div class="bg-white rounded-xl border border-stone-200/90 p-12 text-center my-4 shadow-sm">
                            <div class="w-16 h-16 rounded-full bg-orange-50 text-[#F47716] flex items-center justify-center mx-auto text-2xl mb-4">
                                <i class="fa-regular fa-heart"></i>
                            </div>
                            <h3 class="text-lg font-bold text-stone-900 mb-2">Chưa có món ăn yêu thích nào</h3>
                            <p class="text-stone-500 text-xs sm:text-sm max-w-md mx-auto mb-6 leading-relaxed">
                                Quý khách chưa lưu món ăn nào vào danh sách yêu thích. Hãy khám phá thực đơn đa dạng của CIVILIZE và nhấn biểu tượng trái tim để lưu lại nhé!
                            </p>
                            <a href="{{ route('site.product.gridview') }}" 
                               class="inline-flex items-center justify-center px-6 py-2.5 rounded-lg bg-[#F47716] hover:bg-[#EA580C] text-white font-bold text-xs uppercase tracking-wider transition-colors shadow-xs">
                                <span>Khám phá thực đơn</span>
                            </a>
                        </div>
                    @else
                        <!-- GRID MÓN YÊU THÍCH -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5 sm:gap-6" id="favoriteProductsContainer">
                            @foreach ($products as $product)
                                @php
                                    $hasDiscount = !empty($product->price_sale) && !empty($product->price_buy) && ($product->price_sale < $product->price_buy);
                                    $discountPercent = $hasDiscount ? round((($product->price_buy - $product->price_sale) / $product->price_buy) * 100) : 0;
                                    $imgSrc = !empty($product->image) && file_exists(public_path('images/product/' . $product->image))
                                        ? asset('images/product/' . $product->image)
                                        : asset('images/default-product.png');
                                @endphp

                                <div class="group relative bg-white rounded-xl overflow-hidden border border-stone-200/90 shadow-2xs hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between h-full" id="fav-item-{{ $product->id }}">
                                    
                                    <!-- Food Presentation: 4:3 -->
                                    <div class="relative aspect-[4/3] w-full overflow-hidden bg-stone-100">
                                        <a href="{{ route('site.product.detail', ['slug' => $product->slug]) }}" class="block w-full h-full">
                                            <img src="{{ $imgSrc }}" 
                                                 alt="{{ $product->name }}" 
                                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300 ease-out">
                                        </a>

                                        @if ($hasDiscount)
                                            <div class="absolute top-2.5 left-2.5 z-10 pointer-events-none">
                                                <span class="bg-rose-500 text-white font-bold text-[11px] px-2 py-0.5 rounded shadow-xs uppercase tracking-wide">
                                                    -{{ $discountPercent }}%
                                                </span>
                                            </div>
                                        @elseif ($product->new_p == 1)
                                            <div class="absolute top-2.5 left-2.5 z-10 pointer-events-none">
                                                <span class="bg-[#F47716] text-white font-bold text-[11px] px-2 py-0.5 rounded shadow-xs uppercase tracking-wide">
                                                    Bán chạy
                                                </span>
                                            </div>
                                        @endif

                                        <!-- Wishlist Heart Button -->
                                        <div class="absolute top-2.5 right-2.5 z-10">
                                            <form action="{{ route('site.favorites.remove', $product->id) }}" method="POST" onsubmit="handleRemoveFavorite(event, {{ $product->id }})">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="w-8 h-8 rounded-lg bg-white/95 backdrop-blur-xs text-rose-500 shadow-xs flex items-center justify-center hover:bg-white hover:scale-105 transition-all cursor-pointer" 
                                                        title="Bỏ yêu thích">
                                                    <i class="fa-solid fa-heart text-xs"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    <!-- Food Info: Tên 2 dòng không bị cắt + Giá nằm dòng riêng -->
                                    <div class="p-4 flex-grow flex flex-col justify-between">
                                        <div>
                                            <!-- Tên món: Dành trọn 2 dòng (min-h 42px), KHÔNG bị cắt cụt ba chấm -->
                                            <h4 class="font-bold text-stone-900 text-sm sm:text-[15px] leading-snug line-clamp-2 min-h-[42px] group-hover:text-[#F47716] transition-colors mb-1.5" title="{{ $product->name }}">
                                                <a href="{{ route('site.product.detail', ['slug' => $product->slug]) }}">
                                                    {{ $product->name }}
                                                </a>
                                            </h4>

                                            <!-- Dòng Giá: Nằm riêng biệt bên dưới -->
                                            <div class="flex items-baseline gap-2 mb-2">
                                                <span class="font-extrabold text-base sm:text-lg text-[#F47716] tracking-tight">
                                                    {{ number_format($product->price_sale ?: $product->price_buy, 0, ',', '.') }}đ
                                                </span>
                                                @if ($hasDiscount)
                                                    <span class="text-xs text-stone-400 line-through">
                                                        {{ number_format($product->price_buy, 0, ',', '.') }}đ
                                                    </span>
                                                @endif
                                            </div>

                                            @if (!empty($product->description) && trim($product->description) !== '.' && strlen(trim($product->description)) > 2)
                                                <p class="text-xs text-stone-500 line-clamp-2 leading-relaxed mb-3 min-h-[32px]">
                                                    {{ $product->description }}
                                                </p>
                                            @else
                                                <div class="min-h-[16px] mb-2"></div>
                                            @endif
                                        </div>

                                        <!-- Bottom Row: Bỏ 5.0 ảo, nút + Chọn chuẩn -->
                                        <div class="pt-3 border-t border-stone-100 flex items-center justify-between mt-auto">
                                            <div>
                                                @if ($product->reviews_count > 0)
                                                    <div class="flex items-center gap-1.5 text-xs font-semibold text-stone-600">
                                                        <i class="fa-solid fa-star text-amber-400 text-xs"></i>
                                                        <span>{{ number_format($product->reviews_avg_rating ?? 5.0, 1) }}</span>
                                                        <span class="text-stone-400 font-normal">({{ $product->reviews_count }})</span>
                                                    </div>
                                                @elseif ($product->category)
                                                    <span class="text-[11px] font-medium text-stone-400">
                                                        {{ $product->category->name }}
                                                    </span>
                                                @else
                                                    <span class="text-[11px] font-medium text-stone-400">Phục vụ nóng</span>
                                                @endif
                                            </div>

                                            <button type="button" 
                                                    onclick="window.addToCartAjax ? window.addToCartAjax({{ $product->id }}, this) : null"
                                                    class="px-4 py-2 rounded-lg bg-[#F47716] hover:bg-[#EA580C] text-white font-bold text-xs uppercase tracking-wide transition-all shadow-xs hover:shadow-sm active:scale-95 cursor-pointer flex items-center gap-1">
                                                <span>+ Chọn</span>
                                            </button>
                                        </div>
                                    </div>

                                </div>
                            @endforeach
                        </div>

                        <!-- PHÂN TRANG -->
                        @if ($products->hasPages())
                            <div class="flex justify-center items-center mt-10 mb-4">
                                {{ $products->appends(request()->query())->links() }}
                            </div>
                        @endif
                    @endif
                </div>

                <!-- CỘT PHẢI: MY ORDER / GIỎ HÀNG THỜI GIAN THỰC (4 CỘT) -->
                <div class="lg:col-span-4 xl:col-span-4 sticky top-24 z-20" id="favoriteLiveCartSlot">
                    <x-menu-live-cart />
                </div>
            </div>

        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var searchInput = document.getElementById('favSearchInput');
            var sortSelect = document.getElementById('favSortSelect');

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

        // Xử lý bỏ yêu thích trực tiếp mượt mà
        function handleRemoveFavorite(event, productId) {
            event.preventDefault();
            var form = event.currentTarget;
            var itemCard = document.getElementById('fav-item-' + productId);

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: new URLSearchParams(new FormData(form))
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    if (itemCard) {
                        itemCard.style.transition = 'all 0.3s ease-out';
                        itemCard.style.opacity = '0';
                        itemCard.style.transform = 'scale(0.9)';
                        setTimeout(() => {
                            itemCard.remove();
                            var remaining = document.querySelectorAll('#favoriteProductsContainer > div');
                            if (remaining.length === 0) {
                                window.location.reload();
                            }
                        }, 300);
                    }
                } else {
                    window.location.reload();
                }
            })
            .catch(() => {
                form.submit();
            });
        }
    </script>
</x-layout-frontend>
