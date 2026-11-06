@php
    $hasDiscount = !empty($product->price_sale) && !empty($product->price_buy) && ($product->price_sale < $product->price_buy);
    $discountPercent = $hasDiscount ? round((($product->price_buy - $product->price_sale) / $product->price_buy) * 100) : 0;
    $imgSrc = !empty($product->image) && file_exists(public_path('images/product/' . $product->image))
        ? asset('images/product/' . $product->image)
        : asset('images/default-product.png');
@endphp

<div class="product-card group relative bg-white rounded-none sm:rounded-sm overflow-hidden border border-stone-150 hover:border-stone-300 shadow-2xs hover:shadow-md transition-all duration-300 flex flex-col justify-between h-full">
    <!-- Top Image Container -->
    <div class="relative aspect-[4/3] w-full overflow-hidden bg-stone-100">
        <a href="{{ route('site.product.detail', ['slug' => $product->slug]) }}" class="block w-full h-full">
            <img src="{{ $imgSrc }}" 
                 alt="{{ $product->name }}"
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out">
        </a>

        <!-- Badges -->
        <div class="absolute top-2.5 left-2.5 flex items-center gap-1.5 pointer-events-none">
            @if ($hasDiscount)
                <span class="bg-red-600 text-white font-extrabold text-[10px] px-2 py-0.5 rounded-none sm:rounded-2xs shadow-xs tracking-wider uppercase">
                    -{{ $discountPercent }}%
                </span>
            @endif
            @if ($product->new_p == 1)
                <span class="bg-amber-500 text-white font-extrabold text-[10px] px-2 py-0.5 rounded-none sm:rounded-2xs shadow-xs uppercase tracking-wider">
                    Bán chạy
                </span>
            @endif
        </div>

        <!-- Quick Action Overlay -->
        <div class="absolute inset-0 bg-stone-950/30 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center gap-2 pointer-events-none group-hover:pointer-events-auto p-3">
            <a href="{{ route('site.product.detail', ['slug' => $product->slug]) }}" 
               class="px-3 py-1.5 rounded-sm bg-white text-stone-900 hover:bg-stone-900 hover:text-white font-bold text-xs uppercase tracking-wider shadow-md transition-colors duration-200">
                Chi tiết
            </a>

            <form action="{{ route('site.addcart', ['id' => $product->id]) }}" method="POST" onsubmit="event.preventDefault(); window.addToCartGlobal ? window.addToCartGlobal({{ $product->id }}, this.querySelector('button')) : null;">
                @csrf
                <button type="button" 
                        onclick="window.addToCartGlobal ? window.addToCartGlobal({{ $product->id }}, this) : this.closest('form')?.submit()"
                        class="px-3 py-1.5 rounded-sm bg-[#C25E00] text-white hover:bg-[#A34E00] font-bold text-xs uppercase tracking-wider shadow-md transition-colors duration-200 cursor-pointer">
                    + Thêm món
                </button>
            </form>
        </div>
    </div>

    <!-- Dish Information -->
    <div class="p-3.5 sm:p-4 flex flex-col justify-between flex-grow">
        <div>
            @if ($product->category)
                <div class="text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-1">
                    {{ $product->category->name }}
                </div>
            @endif
            <h4 class="font-bold text-stone-900 text-sm sm:text-[15px] leading-snug line-clamp-2 min-h-[2.5rem] group-hover:text-orange-600 transition-colors mb-2">
                <a href="{{ route('site.product.detail', ['slug' => $product->slug]) }}">
                    {{ $product->name }}
                </a>
            </h4>
        </div>

        <div class="pt-2 border-t border-stone-100 flex items-center justify-between mt-auto">
            <div>
                @if ($hasDiscount)
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-orange-600 font-extrabold text-sm sm:text-base">
                            {{ number_format($product->price_sale, 0, ',', '.') }}₫
                        </span>
                        <span class="text-stone-400 text-xs line-through">
                            {{ number_format($product->price_buy, 0, ',', '.') }}₫
                        </span>
                    </div>
                @elseif (!empty($product->price_buy) && $product->price_buy > 0)
                    <span class="text-orange-600 font-extrabold text-sm sm:text-base">
                        {{ number_format($product->price_buy, 0, ',', '.') }}₫
                    </span>
                @else
                    <span class="text-orange-600 font-bold text-xs sm:text-sm">
                        Liên hệ giá
                    </span>
                @endif
            </div>

            <form action="{{ route('site.addcart', ['id' => $product->id]) }}" method="POST" onsubmit="event.preventDefault(); window.addToCartGlobal ? window.addToCartGlobal({{ $product->id }}, this.querySelector('button')) : null;">
                @csrf
                <button type="button" 
                        onclick="window.addToCartGlobal ? window.addToCartGlobal({{ $product->id }}, this) : this.closest('form')?.submit()"
                        class="px-3 py-1 rounded-sm bg-stone-900 hover:bg-[#C25E00] text-white font-bold text-xs uppercase tracking-wider transition-colors cursor-pointer shadow-2xs"
                        title="Thêm vào giỏ">
                    <span>+ Chọn</span>
                </button>
            </form>
        </div>
    </div>
</div>
