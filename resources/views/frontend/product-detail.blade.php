<x-layout-frontend>
    <main class="bg-[#FAF8F5] min-h-screen pb-16">
        <!-- Page Breadcrumb & Header -->
        <x-page-hero 
            :title="$product->name" 
            :subtitle="$product->category ? ('Thuộc thực đơn ' . $product->category->name . ' tại CIVILIZE Restaurant') : 'Món ăn đặc sắc chế biến tươi mới mỗi ngày.'" 
            :breadcrumbs="[
                ['name' => 'Thực đơn', 'url' => route('site.product.gridview')],
                ['name' => $product->name, 'url' => '']
            ]" 
        />

        <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-7xl pt-8 sm:pt-12">
            <!-- 2-Column Dish Presentation Section -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-14 items-start bg-white rounded-none sm:rounded-sm border border-stone-200/90 p-6 sm:p-10 shadow-xs mb-14">
                
                <!-- Left Column: Large authentic dish photo -->
                <div class="lg:col-span-6">
                    <div class="relative aspect-[4/3] w-full rounded-none sm:rounded-sm overflow-hidden bg-stone-100 border border-stone-200/70 shadow-2xs group">
                        @php
                            $imgSrc = !empty($product->image) && file_exists(public_path('images/product/' . $product->image))
                                ? asset('images/product/' . $product->image)
                                : asset('images/default-product.png');
                            $hasDiscount = !empty($product->price_sale) && !empty($product->price_buy) && ($product->price_sale < $product->price_buy);
                            $discountAmount = $hasDiscount ? ($product->price_buy - $product->price_sale) : 0;
                            $discountPercent = $hasDiscount ? round(($discountAmount / $product->price_buy) * 100) : 0;
                        @endphp
                        <img src="{{ $imgSrc }}" 
                             alt="{{ $product->name }}" 
                             class="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-105">

                        <!-- Badge: Mới hoặc Bán chạy -->
                        <div class="absolute top-4 left-4 flex flex-col gap-2 pointer-events-none">
                            @if ($product->new_p == 1)
                                <span class="inline-flex items-center px-3 py-1 rounded-none sm:rounded-sm bg-[#C25E00] text-white text-xs font-bold uppercase tracking-wider shadow-xs">
                                    Món Mới
                                </span>
                            @endif
                            @if ($hasDiscount)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-none sm:rounded-sm bg-rose-600 text-white text-xs font-extrabold uppercase tracking-wide shadow-xs">
                                    -{{ $discountPercent }}%
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Favorite Button & Share -->
                    <div class="flex items-center justify-between mt-4 pt-3 border-t border-stone-100">
                        <form action="{{ route('site.favorites.add', $product->id) }}" method="POST">
                            @csrf
                            <button type="submit" 
                                    class="inline-flex items-center gap-2 text-xs font-semibold text-stone-600 hover:text-rose-600 transition-colors py-1.5 px-3 rounded-none sm:rounded-sm hover:bg-stone-50 border border-stone-200/80 cursor-pointer">
                                <i class="fa-regular fa-heart text-stone-400 group-hover:text-rose-500"></i>
                                <span>Lưu vào danh sách yêu thích</span>
                            </button>
                        </form>

                        <div class="flex items-center gap-2 text-xs text-stone-400">
                            <span>Mã món: <strong class="text-stone-700 font-mono">#{{ $product->id }}</strong></span>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Dish Info, Pricing, Ordering Form -->
                <div class="lg:col-span-6 flex flex-col justify-between space-y-6">
                    <div>
                        <!-- Category Subheading -->
                        @if ($product->category)
                            <div class="text-xs uppercase font-bold tracking-[0.2em] text-[#C25E00] mb-2">
                                {{ $product->category->name }}
                            </div>
                        @endif

                        <!-- Dish Title in Luxury Serif -->
                        <h1 class="font-serif text-3xl sm:text-4xl font-bold text-stone-900 tracking-tight leading-tight mb-4">
                            {{ $product->name }}
                        </h1>

                        <!-- Pricing Presentation -->
                        <div class="flex items-baseline gap-3 mb-5 pb-5 border-b border-stone-100">
                            @if ($hasDiscount)
                                <span class="text-3xl font-bold text-[#C25E00] tracking-tight">
                                    {{ number_format($product->price_sale, 0, ',', '.') }}₫
                                </span>
                                <span class="text-stone-400 line-through text-lg">
                                    {{ number_format($product->price_buy, 0, ',', '.') }}₫
                                </span>
                                <span class="text-xs font-bold text-rose-700 bg-rose-50 border border-rose-200 px-2 py-0.5 rounded">
                                    Tiết kiệm {{ number_format($discountAmount, 0, ',', '.') }}₫
                                </span>
                            @elseif (!empty($product->price_buy) && $product->price_buy > 0)
                                <span class="text-3xl font-bold text-[#C25E00] tracking-tight">
                                    {{ number_format($product->price_buy, 0, ',', '.') }}₫
                                </span>
                            @else
                                <span class="text-2xl font-bold text-[#C25E00]">
                                    Liên hệ báo giá
                                </span>
                            @endif
                        </div>

                        <!-- Short Description / Appetizing summary -->
                        @if (!empty($product->description) && trim($product->description) !== '.' && strlen(trim($product->description)) > 2)
                            <div class="text-stone-600 text-sm sm:text-[15px] leading-relaxed mb-6 font-normal">
                                {{ $product->description }}
                            </div>
                        @endif

                        <!-- Culinary Guarantees -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6 p-4 rounded-none sm:rounded-sm bg-[#FAF8F5] border border-stone-200/90 text-xs text-stone-700">
                            <div class="flex items-center gap-2.5">
                                <i class="fa-solid fa-leaf text-[#C25E00] text-sm shrink-0"></i>
                                <span>Thực phẩm sạch nhập mới mỗi ngày</span>
                            </div>
                            <div class="flex items-center gap-2.5">
                                <i class="fa-solid fa-fire-burner text-[#C25E00] text-sm shrink-0"></i>
                                <span>Chế biến nóng hổi ngay khi gọi món</span>
                            </div>
                            <div class="flex items-center gap-2.5">
                                <i class="fa-solid fa-shield-halved text-[#C25E00] text-sm shrink-0"></i>
                                <span>Đảm bảo tiêu chuẩn vệ sinh ATTP</span>
                            </div>
                            <div class="flex items-center gap-2.5">
                                <i class="fa-solid fa-chair text-[#C25E00] text-sm shrink-0"></i>
                                <span>Phục vụ tại bàn ấm cúng Phúc Thọ</span>
                            </div>
                        </div>

                        <!-- Order Form: Quantity Stepper & Dual CTAs -->
                        <form action="{{ route('site.addcart', ['id' => $product->id]) }}" method="POST" data-save-scroll="true" class="space-y-5">
                            @csrf
                            
                            <!-- Quantity selector -->
                            <div class="flex items-center gap-4">
                                <span class="text-xs font-bold uppercase tracking-wider text-stone-600">Số lượng:</span>
                                <div class="inline-flex items-center border border-stone-300 rounded-none sm:rounded-sm overflow-hidden bg-white shadow-2xs">
                                    <button type="button" 
                                            class="w-10 h-10 flex items-center justify-center text-stone-600 hover:bg-stone-100 hover:text-stone-900 transition-colors text-sm font-bold cursor-pointer"
                                            onclick="updateQuantity(-1)">
                                        −
                                    </button>
                                    <input type="number" 
                                           name="qty" 
                                           value="1" 
                                           min="1" 
                                           class="w-14 h-10 text-center border-0 text-stone-900 font-bold text-sm focus:outline-none" 
                                           id="quantity">
                                    <button type="button" 
                                            class="w-10 h-10 flex items-center justify-center text-stone-600 hover:bg-stone-100 hover:text-stone-900 transition-colors text-sm font-bold cursor-pointer"
                                            onclick="updateQuantity(1)">
                                        +
                                    </button>
                                </div>
                            </div>

                            <!-- CTAs: Add to cart & Book table -->
                            <div class="flex flex-col sm:flex-row gap-3 pt-2">
                                <button type="submit"
                                        class="flex-1 inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-none sm:rounded-sm bg-stone-900 hover:bg-[#C25E00] text-white font-bold text-xs uppercase tracking-[0.15em] shadow-xs transition-colors cursor-pointer">
                                    <i class="fa-solid fa-cart-plus text-xs"></i>
                                    <span>Thêm Vào Giỏ Hàng</span>
                                </button>
                                <a href="{{ route('site.booking') }}"
                                   class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-none sm:rounded-sm bg-white hover:bg-stone-50 text-stone-800 font-bold text-xs uppercase tracking-[0.15em] border border-stone-300 transition-colors cursor-pointer shadow-2xs">
                                    <span>Đặt Bàn Thưởng Thức</span>
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tabs Section: Detailed Content, Reviews, Policies -->
            <div class="bg-white rounded-none sm:rounded-sm border border-stone-200/90 p-6 sm:p-10 shadow-xs mb-14" x-data="{ activeTab: 'description' }">
                <!-- Tab Headers -->
                <div class="flex items-center gap-8 border-b border-stone-200 pb-px mb-8 overflow-x-auto text-sm">
                    <button type="button" 
                            @click="activeTab = 'description'"
                            :class="activeTab === 'description' ? 'border-[#C25E00] text-[#C25E00] font-bold' : 'border-transparent text-stone-600 hover:text-stone-900 font-medium'"
                            class="pb-3.5 border-b-2 transition-colors whitespace-nowrap cursor-pointer">
                        Mô Tả & Hương Vị Món Ăn
                    </button>
                    <button type="button" 
                            @click="activeTab = 'reviews'"
                            :class="activeTab === 'reviews' ? 'border-[#C25E00] text-[#C25E00] font-bold' : 'border-transparent text-stone-600 hover:text-stone-900 font-medium'"
                            class="pb-3.5 border-b-2 transition-colors whitespace-nowrap cursor-pointer flex items-center gap-2">
                        <span>Đánh Giá Từ Thực Khách</span>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-stone-100 text-stone-600">({{ $reviews->count() }})</span>
                    </button>
                    <button type="button" 
                            @click="activeTab = 'policy'"
                            :class="activeTab === 'policy' ? 'border-[#C25E00] text-[#C25E00] font-bold' : 'border-transparent text-stone-600 hover:text-stone-900 font-medium'"
                            class="pb-3.5 border-b-2 transition-colors whitespace-nowrap cursor-pointer">
                        Cam Kết Vệ Sinh & Phục Vụ
                    </button>
                </div>

                <!-- Tab 1: Detailed Description -->
                <div x-show="activeTab === 'description'" x-transition class="space-y-4">
                    <h3 class="font-serif text-xl font-bold text-stone-900">Chi Tiết Về Món Ăn</h3>
                    @if (!empty($product->content) && trim(strip_tags($product->content)) !== '')
                        <div class="prose max-w-none text-stone-700 leading-relaxed text-sm sm:text-base">
                            {!! $product->content !!}
                        </div>
                    @else
                        <p class="text-stone-600 text-sm leading-relaxed">
                            Món <strong class="text-stone-900">{{ $product->name }}</strong> tại CIVILIZE Restaurant được chuẩn bị từ nguồn nguyên liệu tuyển chọn tươi sạch mỗi ngày, nêm nếm theo công thức độc quyền của bếp trưởng giúp giữ trọn vẹn vị ngọt thanh tự nhiên và hương vị đậm đà truyền thống.
                        </p>
                    @endif
                </div>

                <!-- Tab 2: Reviews -->
                <div x-cloak x-show="activeTab === 'reviews'" x-transition style="display: none;" class="space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-stone-100">
                        <div>
                            <h3 class="font-serif text-xl font-bold text-stone-900">Đánh Giá Từ Khách Hàng</h3>
                        </div>
                        <div class="flex items-center gap-3 bg-[#FAF8F5] px-4 py-2.5 rounded-none sm:rounded-sm border border-stone-200/90">
                            <span class="text-2xl font-bold text-amber-500">
                                {{ round($reviews->avg('rating') ?: 5, 1) }}
                            </span>
                            <div>
                                <div class="text-amber-400 text-xs">★★★★★</div>
                                <span class="text-[11px] text-stone-500">{{ $reviews->count() }} lượt đánh giá</span>
                            </div>
                        </div>
                    </div>

                    <!-- Reviews List -->
                    @if ($reviews->isEmpty())
                        <div class="text-center py-8 text-stone-500 text-sm">
                            <i class="fa-regular fa-comment-dots text-3xl text-stone-300 mb-2 block"></i>
                            <span>Chưa có đánh giá nào cho món này. Hãy là người đầu tiên chia sẻ cảm nhận!</span>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach ($reviews as $item)
                                <div class="p-4 sm:p-5 rounded-none sm:rounded-sm bg-stone-50 border border-stone-200/90 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2.5">
                                            <span class="w-7 h-7 rounded-none sm:rounded-sm bg-stone-900 text-amber-400 font-serif font-black flex items-center justify-center text-xs">
                                                {{ mb_strtoupper(mb_substr($item->user->fullname ?? 'K', 0, 1)) }}
                                            </span>
                                            <div>
                                                <span class="font-semibold text-xs text-stone-900 block">{{ $item->user->fullname ?? 'Khách hàng' }}</span>
                                                <span class="text-[11px] text-stone-400">{{ $item->created_at ? $item->created_at->format('d/m/Y') : '' }}</span>
                                            </div>
                                        </div>
                                        <div class="text-amber-400 text-xs">
                                            {{ str_repeat('★', $item->rating) }}{{ str_repeat('☆', 5 - $item->rating) }}
                                        </div>
                                    </div>
                                    <p class="text-xs sm:text-sm text-stone-700 leading-relaxed">{{ $item->comment }}</p>

                                    @if (!empty($item->response))
                                        <div class="mt-2.5 ml-4 p-3 rounded-none sm:rounded-sm bg-white border border-stone-200/90 text-xs text-stone-600 space-y-1">
                                            <span class="font-bold text-[#C25E00]">Phản hồi từ CIVILIZE Restaurant:</span>
                                            <p>{{ $item->response }}</p>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Review Form -->
                    @php
                        $hasReviewed = false;
                        if (Auth::check()) {
                            $hasReviewed = \App\Models\Review::where('user_id', Auth::id())
                                ->where('product_id', $product->id)
                                ->exists();
                        }
                    @endphp

                    @if (Auth::check() && !$hasReviewed)
                        <form method="POST" action="{{ route('reviews.store') }}" class="pt-6 border-t border-stone-100 space-y-4">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <h4 class="font-bold text-stone-900 text-sm">Gửi đánh giá của bạn</h4>
                            
                            <div>
                                <label class="block text-xs text-stone-600 mb-1.5 font-medium">Mức độ hài lòng:</label>
                                <div class="flex items-center gap-3">
                                    @foreach (range(5, 1) as $r)
                                        <label class="flex items-center gap-1 text-xs cursor-pointer text-stone-700">
                                            <input type="radio" name="rating" value="{{ $r }}" {{ $r == 5 ? 'checked' : '' }} class="accent-[#C25E00]">
                                            <span class="text-amber-500">{{ $r }}★</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div>
                                <label for="comment" class="block text-xs text-stone-600 mb-1.5 font-medium">Nhận xét chi tiết:</label>
                                <textarea id="comment" 
                                          name="comment" 
                                          rows="3" 
                                          placeholder="Hương vị món ăn, cách trình bày, thời gian phục vụ..."
                                          class="w-full p-3 rounded-none sm:rounded-sm border border-stone-300 text-xs sm:text-sm text-stone-800 placeholder-stone-400 focus:outline-none focus:border-[#C25E00] focus:ring-1 focus:ring-[#C25E00]" 
                                          required></textarea>
                            </div>

                            <button type="submit" 
                                    class="px-7 py-3 rounded-none sm:rounded-sm bg-stone-900 hover:bg-[#C25E00] text-white font-bold text-xs uppercase tracking-[0.15em] shadow-xs transition-colors cursor-pointer">
                                Gửi Đánh Giá
                            </button>
                        </form>
                    @elseif (!Auth::check())
                        <div class="p-4 rounded-none sm:rounded-sm bg-stone-50 border border-stone-200/90 text-xs text-stone-600">
                            Vui lòng <a href="{{ route('site.login') }}" class="text-[#C25E00] font-bold hover:underline">đăng nhập</a> để gửi đánh giá món ăn này.
                        </div>
                    @endif
                </div>

                <!-- Tab 3: Policies -->
                <div x-cloak x-show="activeTab === 'policy'" x-transition style="display: none;" class="space-y-4 text-xs sm:text-sm text-stone-700 leading-relaxed">
                    <h3 class="font-serif text-xl font-bold text-stone-900">Cam Kết Chất Lượng Dịch Vụ</h3>
                    <ul class="space-y-2.5 list-disc list-inside">
                        <li><strong>An toàn thực phẩm:</strong> 100% nguyên liệu có nguồn gốc xuất xứ rõ ràng, kiểm định vệ sinh ATTP định kỳ.</li>
                        <li><strong>Đổi trả / Đổi món:</strong> Nếu món ăn không đúng chất lượng hoặc có vấn đề phát sinh, nhà hàng cam kết đổi món mới ngay lập tức cho quý khách.</li>
                        <li><strong>Giờ phục vụ:</strong> Bếp nhận đơn từ 07:30 đến 22:00 hàng ngày (kể cả Thứ 7, Chủ Nhật và ngày Lễ).</li>
                        <li><strong>Hỗ trợ đặt tiệc:</strong> Nhận chế biến riêng set menu cho đoàn tiệc gia đình, sinh nhật, hội nghị theo yêu cầu.</li>
                    </ul>
                </div>
            </div>

            <!-- Related Dishes Section -->
            @if ($relatedProducts && $relatedProducts->count() > 0)
                <div class="mt-12">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <span class="block text-xs uppercase font-bold tracking-[0.2em] text-[#C25E00] mb-1">GỢI Ý MÓN NGON</span>
                            <h2 class="font-serif text-2xl sm:text-3xl font-bold text-stone-900 tracking-tight">
                                Món Ăn Cùng Thực Đơn
                            </h2>
                        </div>
                        <a href="{{ route('site.product.gridview') }}" 
                           class="inline-flex items-center gap-1.5 text-xs font-bold text-[#C25E00] hover:text-[#A34E00] transition-colors">
                            <span>Xem tất cả</span>
                            <i class="fas fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                        @foreach ($relatedProducts->take(4) as $relItem)
                            <x-product-card :productitem="$relItem" />
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </main>

    <script>
        function updateQuantity(delta) {
            const qtyInput = document.getElementById('quantity');
            if (!qtyInput) return;
            let current = parseInt(qtyInput.value) || 1;
            qtyInput.value = Math.max(1, current + delta);
        }
    </script>
</x-layout-frontend>
