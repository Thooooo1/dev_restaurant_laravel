@if ($products->isEmpty())
    <!-- Empty State -->
    <div class="bg-white rounded-none sm:rounded-sm border border-stone-200 p-12 text-center my-4 shadow-2xs">
        <div class="w-14 h-14 rounded-none sm:rounded-sm bg-orange-50 text-[#F47716] flex items-center justify-center mx-auto text-2xl mb-4 border border-orange-100">
            <i class="fa-solid fa-utensils"></i>
        </div>
        <h3 class="text-base sm:text-lg font-bold text-stone-900 mb-1.5">Không tìm thấy món ăn phù hợp</h3>
        <p class="text-stone-500 text-xs sm:text-sm max-w-md mx-auto mb-6 leading-relaxed">
            Quý khách vui lòng thử tìm kiếm bằng từ khóa khác hoặc bấm xem toàn bộ thực đơn của nhà hàng.
        </p>
        <a href="{{ route('site.product.gridview') }}" 
           class="inline-flex items-center justify-center px-6 py-2.5 rounded-none sm:rounded-sm bg-[#F47716] hover:bg-[#EA580C] text-white font-bold text-xs uppercase tracking-wider transition-colors shadow-2xs">
            <span>Xem tất cả món ăn</span>
        </a>
    </div>

@elseif (($viewMode ?? 'grid') === 'list')

    <!-- ======================================================== -->
    <!-- VIEW MODE: LIST                                          -->
    <!-- ======================================================== -->
    <div class="space-y-3.5">
        @foreach ($products as $product)
            @php
                $hasDiscount = !empty($product->price_sale) && !empty($product->price_buy) && ($product->price_sale < $product->price_buy);
                $discountPercent = $hasDiscount ? round((($product->price_buy - $product->price_sale) / $product->price_buy) * 100) : 0;
                $imgSrc = !empty($product->image) && file_exists(public_path('images/product/' . $product->image))
                    ? asset('images/product/' . $product->image)
                    : asset('images/default-product.png');
                $isFavorited = auth()->check() && auth()->user()->favorites && auth()->user()->favorites->contains($product->id);
            @endphp

            <div class="group bg-white rounded-none sm:rounded-sm border border-stone-200 hover:border-stone-400 shadow-2xs transition-all duration-200 p-3.5 sm:p-4 flex flex-col md:flex-row items-center gap-4">
                
                <!-- Food Image -->
                <div class="relative aspect-[16/10] w-full md:w-48 shrink-0 rounded-none sm:rounded-sm overflow-hidden bg-stone-100">
                    <a href="{{ route('site.product.detail', ['slug' => $product->slug]) }}" class="block w-full h-full">
                        <img src="{{ $imgSrc }}" 
                             alt="{{ $product->name }}" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out">
                    </a>

                    @if ($hasDiscount)
                        <div class="absolute top-2 left-2 pointer-events-none">
                            <span class="bg-rose-500 text-white font-bold text-[10px] px-2 py-0.5 rounded-none sm:rounded-xs shadow-2xs uppercase tracking-wide">
                                -{{ $discountPercent }}%
                            </span>
                        </div>
                    @elseif ($product->new_p == 1)
                        <div class="absolute top-2 left-2 pointer-events-none">
                            <span class="bg-[#F47716] text-white font-bold text-[10px] px-2 py-0.5 rounded-none sm:rounded-xs shadow-2xs uppercase tracking-wide">
                                Bán chạy
                            </span>
                        </div>
                    @endif
                </div>

                <!-- Food Info -->
                <div class="flex-1 min-w-0 w-full">
                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                        @if ($product->category)
                            <span class="text-[10px] font-bold text-stone-400 uppercase tracking-widest">
                                {{ $product->category->name }}
                            </span>
                        @endif
                        @if ($product->reviews_count > 0)
                            <span class="text-stone-300 text-xs">•</span>
                            <span class="text-[11px] text-amber-600 font-semibold">
                                ★ {{ number_format($product->reviews_avg_rating ?? 5.0, 1) }} ({{ $product->reviews_count }})
                            </span>
                        @endif
                    </div>

                    <h3 class="font-bold text-stone-900 text-sm sm:text-base group-hover:text-[#F47716] transition-colors mb-1.5 leading-snug">
                        <a href="{{ route('site.product.detail', ['slug' => $product->slug]) }}">
                            {{ $product->name }}
                        </a>
                    </h3>

                    @if (!empty($product->description) && trim($product->description) !== '.' && strlen(trim($product->description)) > 2)
                        <p class="text-xs text-stone-500 line-clamp-2 leading-relaxed mb-2">
                            {{ $product->description }}
                        </p>
                    @endif
                </div>

                <!-- Price & Action Column -->
                <div class="w-full md:w-52 shrink-0 flex flex-col justify-center items-start md:items-end border-t md:border-t-0 md:border-l border-stone-100 pt-3 md:pt-0 md:pl-4 gap-2.5">
                    <div class="text-left md:text-right">
                        @if ($hasDiscount)
                            <div class="text-[#F47716] font-black text-base sm:text-lg leading-tight">
                                {{ number_format($product->price_sale, 0, ',', '.') }}đ
                            </div>
                            <div class="text-stone-400 text-[11px] line-through mt-0.5">
                                {{ number_format($product->price_buy, 0, ',', '.') }}đ
                            </div>
                        @else
                            <div class="text-stone-900 font-black text-base sm:text-lg leading-tight">
                                {{ number_format($product->price_buy, 0, ',', '.') }}đ
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center gap-2 w-full md:w-auto">
                        <!-- Add to Cart -->
                        <button type="button" 
                                onclick="window.addToCartAjax ? window.addToCartAjax({{ $product->id }}, this) : this.closest('form')?.submit()"
                                class="w-full md:w-auto px-4 py-2 rounded-none sm:rounded-sm bg-[#F47716] hover:bg-[#EA580C] text-white font-bold text-xs uppercase tracking-wider shadow-2xs transition-colors inline-flex items-center justify-center cursor-pointer">
                            <span>+ Chọn</span>
                        </button>

                        <!-- Detail Link -->
                        <a href="{{ route('site.product.detail', ['slug' => $product->slug]) }}" 
                           class="px-2.5 py-2 rounded-none sm:rounded-sm bg-stone-100 hover:bg-stone-200 text-stone-700 hover:text-stone-900 flex items-center justify-center transition-colors border border-stone-200 text-xs font-bold"
                           title="Xem chi tiết món">
                            <span>Chi tiết</span>
                        </a>

                        <!-- Favorite Toggle -->
                        @if ($isFavorited)
                            <form action="{{ route('site.favorites.remove', $product->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="px-2.5 py-2 rounded-none sm:rounded-sm bg-rose-50 text-rose-600 hover:bg-rose-100 transition-colors border border-rose-200 text-xs font-bold" 
                                        title="Bỏ yêu thích">
                                    <span>♥</span>
                                </button>
                            </form>
                        @else
                            <form action="{{ route('site.favorites.add', $product->id) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        class="px-2.5 py-2 rounded-none sm:rounded-sm bg-stone-100 text-stone-600 hover:text-rose-500 hover:bg-rose-50 transition-colors border border-stone-200 text-xs" 
                                        title="Yêu thích">
                                    <span>♡</span>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

            </div>
        @endforeach
    </div>

@else

    <!-- ======================================================== -->
    <!-- VIEW MODE: GRID                                          -->
    <!-- ======================================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5 sm:gap-6">
        @foreach ($products as $product)
            @php
                $hasDiscount = !empty($product->price_sale) && !empty($product->price_buy) && ($product->price_sale < $product->price_buy);
                $discountPercent = $hasDiscount ? round((($product->price_buy - $product->price_sale) / $product->price_buy) * 100) : 0;
                $imgSrc = !empty($product->image) && file_exists(public_path('images/product/' . $product->image))
                    ? asset('images/product/' . $product->image)
                    : asset('images/default-product.png');
                $isFavorited = auth()->check() && auth()->user()->favorites && auth()->user()->favorites->contains($product->id);
            @endphp

            <div class="group relative bg-white rounded-none sm:rounded-sm overflow-hidden border border-stone-200 hover:border-stone-400 shadow-2xs hover:shadow-xs transition-all duration-200 flex flex-col justify-between h-full">
                
                <!-- Food Presentation: Khung 4:3 đồng bộ -->
                <div class="relative aspect-[4/3] w-full overflow-hidden bg-stone-100">
                    <a href="{{ route('site.product.detail', ['slug' => $product->slug]) }}" class="block w-full h-full">
                        <img src="{{ $imgSrc }}" 
                             alt="{{ $product->name }}" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300 ease-out">
                    </a>

                    <!-- Badge Giảm giá hoặc Bán chạy -->
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

                    <!-- Wishlist Heart Floating Button -->
                    <div class="absolute top-2.5 right-2.5 z-10">
                        @if ($isFavorited)
                            <form action="{{ route('site.favorites.remove', $product->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="w-8 h-8 rounded-none sm:rounded-sm bg-white/95 backdrop-blur-xs text-rose-500 shadow-2xs flex items-center justify-center hover:bg-white transition-all cursor-pointer border border-stone-200/60" 
                                        title="Bỏ yêu thích">
                                    <i class="fa-solid fa-heart text-xs"></i>
                                </button>
                            </form>
                        @else
                            <form action="{{ route('site.favorites.add', $product->id) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        class="w-8 h-8 rounded-none sm:rounded-sm bg-white/90 backdrop-blur-xs text-stone-400 hover:text-rose-500 hover:bg-white shadow-2xs flex items-center justify-center transition-all cursor-pointer border border-stone-200/60" 
                                        title="Yêu thích món này">
                                    <i class="fa-regular fa-heart text-xs"></i>
                                </button>
                            </form>
                        @endif
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

                        <!-- Dòng Giá: Nằm riêng biệt bên dưới, to rõ và dễ đọc -->
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

                        <!-- Mô tả món chân thật hoặc để trống lịch sự -->
                        @if (!empty($product->description) && trim($product->description) !== '.' && strlen(trim($product->description)) > 2)
                            <p class="text-xs text-stone-500 line-clamp-2 leading-relaxed mb-3 min-h-[32px]">
                                {{ $product->description }}
                            </p>
                        @else
                            <div class="min-h-[16px] mb-2"></div>
                        @endif
                    </div>

                    <!-- Bottom Row: Đánh giá thật nếu có; nút + Chọn chuẩn POS -->
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
                                onclick="window.addToCartAjax ? window.addToCartAjax({{ $product->id }}, this) : this.closest('form')?.submit()"
                                class="px-4 py-2 rounded-none sm:rounded-sm bg-[#F47716] hover:bg-[#EA580C] text-white font-bold text-xs uppercase tracking-wide transition-all shadow-2xs hover:shadow-xs active:scale-95 cursor-pointer flex items-center gap-1">
                            <span>+ Chọn</span>
                        </button>
                    </div>
                </div>

            </div>
        @endforeach
    </div>

@endif

<!-- Phân trang Thực đơn: Đồng bộ thanh lịch, góc sắc nét, không bị vỡ giao diện -->
@if ($products->hasPages())
    <div class="flex justify-center items-center mt-10 mb-6">
        <nav class="inline-flex items-center gap-1.5 flex-wrap justify-center bg-stone-50 p-2 rounded-none sm:rounded-sm border border-stone-200 shadow-2xs">
            
            {{-- Nút Trang Trước --}}
            @if ($products->onFirstPage())
                <span class="px-3.5 py-2 text-xs font-bold text-stone-300 bg-white rounded-none sm:rounded-sm border border-stone-200 cursor-not-allowed inline-flex items-center gap-1">
                    <i class="fas fa-chevron-left text-[10px]"></i>
                    <span>Trước</span>
                </span>
            @else
                <a href="{{ $products->previousPageUrl() }}" 
                   class="px-3.5 py-2 text-xs font-bold text-stone-700 bg-white hover:bg-[#F47716] hover:text-white border border-stone-200 rounded-none sm:rounded-sm transition-colors shadow-2xs inline-flex items-center gap-1 cursor-pointer">
                    <i class="fas fa-chevron-left text-[10px]"></i>
                    <span>Trước</span>
                </a>
            @endif

            {{-- Các số trang (Hiển thị thông minh) --}}
            @php
                $startPage = max(1, $products->currentPage() - 2);
                $endPage = min($products->lastPage(), $products->currentPage() + 2);
                if ($endPage - $startPage < 4) {
                    if ($startPage == 1) {
                        $endPage = min($products->lastPage(), $startPage + 4);
                    } elseif ($endPage == $products->lastPage()) {
                        $startPage = max(1, $endPage - 4);
                    }
                }
            @endphp

            @if ($startPage > 1)
                <a href="{{ $products->url(1) }}" 
                   class="w-9 h-9 inline-flex items-center justify-center text-xs font-bold text-stone-700 bg-white hover:bg-stone-100 hover:text-[#F47716] border border-stone-200 rounded-none sm:rounded-sm transition-colors shadow-2xs">
                    1
                </a>
                @if ($startPage > 2)
                    <span class="w-6 text-center text-stone-400 text-xs font-bold select-none">...</span>
                @endif
            @endif

            @for ($p = $startPage; $p <= $endPage; $p++)
                @if ($p == $products->currentPage())
                    <span class="w-9 h-9 inline-flex items-center justify-center text-xs font-black bg-[#F47716] text-white rounded-none sm:rounded-sm shadow-2xs">
                        {{ $p }}
                    </span>
                @else
                    <a href="{{ $products->url($p) }}" 
                       class="w-9 h-9 inline-flex items-center justify-center text-xs font-bold text-stone-700 bg-white hover:bg-stone-100 hover:text-[#F47716] border border-stone-200 rounded-none sm:rounded-sm transition-colors shadow-2xs">
                        {{ $p }}
                    </a>
                @endif
            @endfor

            @if ($endPage < $products->lastPage())
                @if ($endPage < $products->lastPage() - 1)
                    <span class="w-6 text-center text-stone-400 text-xs font-bold select-none">...</span>
                @endif
                <a href="{{ $products->url($products->lastPage()) }}" 
                   class="w-9 h-9 inline-flex items-center justify-center text-xs font-bold text-stone-700 bg-white hover:bg-stone-100 hover:text-[#F47716] border border-stone-200 rounded-none sm:rounded-sm transition-colors shadow-2xs">
                    {{ $products->lastPage() }}
                </a>
            @endif

            {{-- Nút Trang Sau --}}
            @if ($products->hasMorePages())
                <a href="{{ $products->nextPageUrl() }}" 
                   class="px-3.5 py-2 text-xs font-bold text-stone-700 bg-white hover:bg-[#F47716] hover:text-white border border-stone-200 rounded-none sm:rounded-sm transition-colors shadow-2xs inline-flex items-center gap-1 cursor-pointer">
                    <span>Sau</span>
                    <i class="fas fa-chevron-right text-[10px]"></i>
                </a>
            @else
                <span class="px-3.5 py-2 text-xs font-bold text-stone-300 bg-white rounded-none sm:rounded-sm border border-stone-200 cursor-not-allowed inline-flex items-center gap-1">
                    <span>Sau</span>
                    <i class="fas fa-chevron-right text-[10px]"></i>
                </span>
            @endif

        </nav>
    </div>
@endif
