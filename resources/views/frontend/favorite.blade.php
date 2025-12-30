<x-layout-frontend>
    <div class="container mx-auto px-4 py-3">
        <div class="breadcrumb flex items-center text-gray-600 text-sm  mt-4">
            <span class="mr-4">Bạn đang ở đây:</span>
            <a href="{{ url('/') }}" class="hover:text-orange-500">Trang chủ</a>
            <span class="mx-2">></span>
            <span class="font-semibold text-gray-800">Danh sách món ăn yêu thích</span>
        </div>
    </div>
    <div class="container mx-auto px-4 flex gap-4">
        <div class="w-1/4 bg-white rounded-lg shadow p-4">
            {{-- <h2 class="text-lg font-bold mb-4">Lọc Sản Phẩm</h2> --}}
            <form method="GET" action="{{ url()->current() }}">
                <h3 class="text-md font-bold mb-2">Danh mục</h3>
                <ul class="space-y-2">
                    <li>
                        <label class="flex items-center">
                            <input type="radio" name="category" value="all"
                                {{ request('category') === 'all' ? 'checked' : '' }}>
                            <span class="ml-2 text-gray-700">Tất cả</span>
                        </label>
                    </li>
                    @foreach ($categories as $category)
                        <li>
                            <label class="flex items-center">
                                <input type="radio" name="category" value="{{ $category->id }}"
                                    {{ request('category') == $category->id ? 'checked' : '' }}>
                                <span class="ml-2 text-gray-700">{{ $category->name }}</span>
                            </label>
                        </li>
                    @endforeach
                </ul>

                <h3 class="text-md font-bold mt-4 mb-2">Thương hiệu</h3>
                <ul class="space-y-2">
                    <li>
                        <label class="flex items-center">
                            <input type="radio" name="brand" value="all"
                                {{ request('brand') === 'all' ? 'checked' : '' }}>
                            <span class="ml-2 text-gray-700">Tất cả</span>
                        </label>
                    </li>
                    @foreach ($brands as $brand)
                        <li>
                            <label class="flex items-center">
                                <input type="radio" name="brand" value="{{ $brand->id }}"
                                    {{ request('brand') == $brand->id ? 'checked' : '' }}>
                                <span class="ml-2 text-gray-700">{{ $brand->name }}</span>
                            </label>
                        </li>
                    @endforeach
                </ul>

                <button type="submit" class="mt-4 w-full bg-orange-600 text-white py-2 rounded hover:bg-orange-500">
                    Lọc
                </button>
            </form>
        </div>

        <div class="w-3/4">
            <div class="flex flex-col space-y-4">
                @if ($favorites->isEmpty())
                    <p class="text-gray-500 text-center">Bạn chưa thêm sản phẩm nào vào danh sách yêu thích.</p>
                @else
                <span class="text-gray-700">{{ $favoriteCount }} Món ăn</span>

                    @foreach ($favorites as $favorite)
                        <div class="flex items-center bg-white rounded-lg shadow-md overflow-hidden p-4">
                            <div class="w-1/4">
                                <img src="{{ asset('/images/product/' . $favorite->product->image) }}"
                                    alt="{{ $favorite->product->name }}" class="w-full h-32 object-cover rounded">
                            </div>
                            <div class="flex-1 px-4">
                                <h2 class="text-lg font-bold text-orange-600">{{ $favorite->product->name }} 
                                    @if ($favorite->product->new_p == 1)
                                    <span class=" bg-red-500 text-white text-xs px-2 py-1 rounded-tl-md rounded-br-md ml-2">Mới</span>
                                @endif</h2>
                                <p class="text-sm text-gray-500">{{ $favorite->product->slug }}</p>
                                <div class="flex items-center gap-2 mt-2 text-sm text-gray-500">
                                    <span class="px-2 py-1 border rounded">Đã xác minh</span>
                                    <span class="px-2 py-1 border rounded"> Số lượng : {{ $favorite->product->qty }}</span>
                                    <span class="px-2 py-1 border rounded"> {{ $favorite->product->reviews_count }} Đánh giá</span>
                                    <span class="px-2 py-1 border rounded">{{ $favorite->product->brand->name }}</span>
                                </div>
                                <p class="mt-2 text-sm text-gray-600">{{ $favorite->product->description }}</p>
                            </div>
                            <div class="flex flex-col items-end space-y-2">
                                <div>
                                    <span
                                        class="text-gray-400 line-through">{{ number_format($favorite->product->price_buy, 0, ',', '.') }}
                                        VND</span>
                                    <span
                                        class="text-orange-500 font-semibold text-lg ml-2">{{ number_format($favorite->product->price_sale, 0, ',', '.') }}
                                        VND</span>
                                </div>
                                <div class="flex space-x-2">
                                    <form action="{{ route('site.addcart', ['id' => $favorite->product->id]) }}"
                                        method="POST">
                                        @csrf
                                        <button type="submit"
                                            class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600">
                                            Thêm vào giỏ hàng
                                        </button>
                                    </form>
                                    <a href="{{ route('site.product.detail', ['slug'=>$favorite->product->slug])}}" class="bg-gray-300 text-black py-2 px-4 rounded hover:bg-gray-400">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <form action="{{ route('site.favorites.remove', $favorite->product->id) }}"
                                        method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300">
                                            <i class="fas fa-heart"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
            
        </div>
    </div>
    <div class="flex justify-center items-center mt-8 mb-6">
        <div class="flex items-center space-x-2">
            @if ($favorites->onFirstPage())
                <button class="px-4 py-2 bg-gray-300 text-gray-400 rounded-lg cursor-not-allowed" disabled>
                    Trước
                </button>
            @else
                <a href="{{ $favorites->previousPageUrl() }}&brand={{ request()->brand ?? 'all' }}&category={{ request()->category ?? 'all' }}&sort={{ request()->sort ?? 'default' }}"
                    class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600">
                    Trước
                </a>
            @endif
            @foreach ($favorites->getUrlRange(1, $favorites->lastPage()) as $page => $url)
                <a href="{{ $url }}&brand={{ request()->brand ?? 'all' }}&category={{ request()->category ?? 'all' }}&sort={{ request()->sort ?? 'default' }}"
                    class="px-4 py-2 text-sm font-medium {{ $favorites->currentPage() == $page ? 'bg-orange-500 text-white' : 'bg-white text-gray-800 hover:bg-gray-100' }} rounded-lg">
                    {{ $page }}
                </a>
            @endforeach
            @if ($favorites->hasMorePages())
                <a href="{{ $favorites->nextPageUrl() }}&brand={{ request()->brand ?? 'all' }}&category={{ request()->category ?? 'all' }}&sort={{ request()->sort ?? 'default' }}"
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
</x-layout-frontend>
