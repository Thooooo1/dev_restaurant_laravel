<div id="menuContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    @if ($products->isEmpty())
        <div class="col-span-full bg-white rounded-none sm:rounded-sm border border-stone-200/90 p-12 text-center">
            <p class="text-sm font-serif font-bold text-stone-800">Tạm thời chưa có món ăn trong danh mục này</p>
            <p class="text-xs text-stone-500 mt-1">Quý khách vui lòng chọn danh mục khác hoặc liên hệ nhân viên để được hỗ trợ.</p>
        </div>
    @else
        @foreach ($products as $product)
            <div class="menu-item bg-white rounded-none sm:rounded-sm border border-stone-200/90 shadow-2xs hover:border-[#C25E00]/40 transition-all p-4 group flex flex-col justify-between"
                data-brand="{{ $product->brand_id }}" data-category="{{ $product->category_id }}">
                <div>
                    <div class="relative overflow-hidden rounded-none sm:rounded-sm bg-stone-100 aspect-square">
                        <img src="{{ $product->image ? asset('/images/product/' . $product->image) : asset('/images/default-product.png') }}"
                            alt="{{ $product->name }}"
                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                        
                        @if ($product->new_p == 1)
                            <span class="absolute top-2 left-2 bg-[#C25E00] text-white text-[10px] uppercase font-black px-2 py-0.5 rounded-none sm:rounded-sm tracking-wider shadow-xs">
                                Mới
                            </span>
                        @endif

                        <div class="absolute inset-0 flex justify-center items-center bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-200 p-3">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('site.product.detail', ['slug' => $product->slug])}}"
                                    class="px-3 py-1.5 bg-white text-stone-900 hover:bg-[#C25E00] hover:text-white rounded-none sm:rounded-sm transition-colors shadow-sm text-xs font-bold uppercase tracking-wider"
                                    title="Xem chi tiết món">
                                    Chi tiết
                                </a>

                                <form action="{{ route('site.addcart', ['id' => $product->id]) }}" method="POST" onsubmit="event.preventDefault(); window.addToCartGlobal ? window.addToCartGlobal({{ $product->id }}, this.querySelector('button')) : null;">
                                    @csrf
                                    <button type="button"
                                        onclick="window.addToCartGlobal ? window.addToCartGlobal({{ $product->id }}, this) : this.closest('form')?.submit()"
                                        class="px-3 py-1.5 bg-[#C25E00] text-white hover:bg-[#A34E00] rounded-none sm:rounded-sm transition-colors shadow-sm cursor-pointer text-xs font-bold uppercase tracking-wider"
                                        title="Thêm vào giỏ hàng">
                                        + Thêm
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="pt-3.5 pb-2">
                        <span class="text-[10px] uppercase font-bold tracking-widest text-[#C25E00] block mb-1">
                            {{ $product->category->name ?? 'Món ngon CIVILIZE' }}
                        </span>
                        <h4 class="text-sm font-bold font-serif text-stone-900 group-hover:text-[#C25E00] transition-colors line-clamp-1">
                            <a href="{{ route('site.product.detail', ['slug' => $product->slug])}}">
                                {{ $product->name }}
                            </a>
                        </h4>
                        <div class="mt-2 flex items-baseline gap-2">
                            <span class="text-stone-900 font-black text-base">
                                {{ $product->price_sale ? number_format($product->price_sale, 0, ',', '.') . ' đ' : 'Liên hệ' }}
                            </span>
                            @if ($product->price_buy && $product->price_buy > $product->price_sale)
                                <span class="line-through text-xs text-stone-400 font-medium">
                                    {{ number_format($product->price_buy, 0, ',', '.') . ' đ' }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-stone-100 flex items-center gap-2">
                    <a href="{{ route('site.product.detail', ['slug' => $product->slug])}}"
                        class="flex-1 bg-stone-900 hover:bg-[#C25E00] text-white text-center py-2 px-3 rounded-none sm:rounded-sm text-xs font-bold uppercase tracking-wider transition-colors">
                        Xem chi tiết
                    </a>
                    <form action="{{ route('site.favorites.add', $product->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="border border-stone-200 text-stone-600 hover:text-rose-600 hover:bg-rose-50 py-2 px-2.5 rounded-none sm:rounded-sm transition-colors cursor-pointer text-xs" title="Lưu yêu thích">
                            ♡
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    @endif
</div>

@if ($products->hasPages())
    <div class="flex justify-center items-center mt-10">
        <div class="flex items-center gap-1.5">
            @if ($products->onFirstPage())
                <button class="px-3.5 py-2 bg-stone-100 text-stone-400 rounded-none sm:rounded-sm text-xs font-bold uppercase tracking-wider cursor-not-allowed" disabled>
                    Trước
                </button>
            @else
                <a href="{{ $products->previousPageUrl() }}&brand={{ request()->brand ?? 'all' }}&category={{ request()->category ?? 'all' }}&sort={{ request()->sort ?? 'default' }}"
                    class="px-3.5 py-2 bg-white border border-stone-200 hover:border-[#C25E00] text-stone-700 hover:text-[#C25E00] rounded-none sm:rounded-sm text-xs font-bold uppercase tracking-wider transition-colors">
                    Trước
                </a>
            @endif

            @foreach ($products->getUrlRange(1, $products->lastPage()) as $page => $url)
                <a href="{{ $url }}&brand={{ request()->brand ?? 'all' }}&category={{ request()->category ?? 'all' }}&sort={{ request()->sort ?? 'default' }}"
                    class="w-9 h-9 flex items-center justify-center text-xs font-bold {{ $products->currentPage() == $page ? 'bg-[#C25E00] text-white shadow-xs' : 'bg-white border border-stone-200 text-stone-700 hover:border-[#C25E00] hover:text-[#C25E00]' }} rounded-none sm:rounded-sm transition-colors">
                    {{ $page }}
                </a>
            @endforeach

            @if ($products->hasMorePages())
                <a href="{{ $products->nextPageUrl() }}&brand={{ request()->brand ?? 'all' }}&category={{ request()->category ?? 'all' }}&sort={{ request()->sort ?? 'default' }}"
                    class="px-3.5 py-2 bg-white border border-stone-200 hover:border-[#C25E00] text-stone-700 hover:text-[#C25E00] rounded-none sm:rounded-sm text-xs font-bold uppercase tracking-wider transition-colors">
                    Sau
                </a>
            @else
                <button class="px-3.5 py-2 bg-stone-100 text-stone-400 rounded-none sm:rounded-sm text-xs font-bold uppercase tracking-wider cursor-not-allowed" disabled>
                    Sau
                </button>
            @endif
        </div>
    </div>
@endif
