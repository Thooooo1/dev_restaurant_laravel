<div id="menuContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-8">
    @if ($products->isEmpty())
        <div class="menu-item bg-white rounded-lg shadow-md hover:shadow-lg transition-all p-4 group text-center ml-4">
            <p class="text-center text-gray-500 ">Tạm thời hết món ăn, xin lỗi vì sự bất tiện này.</p>
        </div>
    @else
        @foreach ($products as $product)
            <div class="menu-item bg-white rounded-lg shadow-md hover:shadow-lg transition-all p-4 group"
                data-brand="{{ $product->brand_id }}" data-category="{{ $product->category_id }}">
                <div class="relative">
                    <img src="{{ $product->image ? asset('/images/product/' . $product->image) : asset('/images/default-product.png') }}"
                        alt="{{ $product->name }}"
                        class="w-full h-64 object-cover transition-all duration-300 group-hover:opacity-60">
                    <div
                        class="absolute inset-0 flex justify-center items-center bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-all duration-300">
                        <div class="flex space-x-4">
                            <a href="{{ route('site.product.detail', ['slug' => $product->slug])}}"
                                class="flex justify-center items-center w-12 h-12 bg-white bg-opacity-20 text-white rounded-full hover:bg-opacity-40 transition transform hover:scale-110">
                                <i class="fas fa-eye"></i>
                            </a>

                            <form action="{{ route('site.addcart', ['id' => $product->id]) }}" method="POST" data-save-scroll="true">
                                @csrf
                                <button type="submit"
                                    class="flex justify-center items-center w-12 h-12 bg-white bg-opacity-20 text-white rounded-full hover:bg-opacity-40 transition transform hover:scale-110">
                                    <i class="fas fa-cart-plus"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="p-4">
                    <span class="text-red-500 font-semibold">-3%</span>
                    <span class="text-orange-500 font-semibold text-lg">
                        {{ $product->price_sale ? number_format($product->price_sale, 0, ',', '.') . ' đ' : 'Liên hệ' }}
                    </span>
                    @if ($product->price_buy)
                        <span class="line-through text-gray-400 ml-2">
                            {{ number_format($product->price_buy, 0, ',', '.') . ' đ' }}
                        </span>
                    @endif
                    <h4 class="text-lg font-semibold text-gray-800 h-10 overflow-hidden truncate">{{ $product->name }} 
                        @if ($product->new_p == 1)
                            <span class=" bg-red-500 text-white text-xs px-2 py-1 rounded-tl-md rounded-br-md ml-2">Mới</span>
                        @endif
                    </h4>
                    <span class="text-gray-600 text-sm">{{ $product->slug }}</span>
                    <div class="mt-4 flex items-center gap-2">
                        <a href="{{ route('site.product.detail', ['slug' => $product->slug])}}"
                            class="btn mt-2 bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600">Xem
                            chi tiết</a>
                            
                        <form action="{{ route('site.favorites.add', $product->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="bg-gray-200 text-black py-2 px-4 rounded hover:bg-gray-300 my-2 " style="margin-top: 15px">
                                <i class="fa-regular fa-heart"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    @endif


</div>
<div class="flex justify-center items-center mt-8">
    <div class="flex items-center space-x-2">
        @if ($products->onFirstPage())
            <button class="px-4 py-2 bg-gray-300 text-gray-400 rounded-lg cursor-not-allowed" disabled>
                Trước
            </button>
        @else
            <a href="{{ $products->previousPageUrl() }}&brand={{ request()->brand ?? 'all' }}&category={{ request()->category ?? 'all' }}&sort={{ request()->sort ?? 'default' }}"
                class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600">
                Trước
            </a>
        @endif
        @foreach ($products->getUrlRange(1, $products->lastPage()) as $page => $url)
            <a href="{{ $url }}&brand={{ request()->brand ?? 'all' }}&category={{ request()->category ?? 'all' }}&sort={{ request()->sort ?? 'default' }}"
                class="px-4 py-2 text-sm font-medium {{ $products->currentPage() == $page ? 'bg-orange-500 text-white' : 'bg-white text-gray-800 hover:bg-gray-100' }} rounded-lg">
                {{ $page }}
            </a>
        @endforeach
        @if ($products->hasMorePages())
            <a href="{{ $products->nextPageUrl() }}&brand={{ request()->brand ?? 'all' }}&category={{ request()->category ?? 'all' }}&sort={{ request()->sort ?? 'default' }}"
                class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600">
                Sau
            </a>
        @else
            <button class="px-4 py-2 bg-gray-300 text-gray-400 rounded-lg cursor-not-allowed" disabled>
                Sau
            </button>
        @endif
    </div>
</div>
