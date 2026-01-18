@foreach ($products as $product)
<div class="bg-white rounded-lg shadow mb-4 p-4 flex flex-col md:flex-row gap-4">
    <div class="w-full md:w-1/4">
        <img src="{{ asset('/images/product/' . $product->image) }}" alt="{{$product->name }}" class="rounded-lg w-full object-cover">
       
    </div>
    <div class="w-full md:w-2/4">
        <h3 class="text-lg font-bold text-orange-600">{{ $product->name }}  
        @if ($product->new_p == 1)
            <span class=" bg-red-500 text-white text-xs px-2 py-1 rounded-tl-md rounded-br-md ml-2">Mới</span>
        @endif
    </h3>
        <div class="flex flex-wrap items-center gap-2 mt-2 text-sm text-gray-500">
            <!-- <span class="px-2 py-1 border rounded"> Số lượng : {{ $product->qty }}</span> -->
            <span class="px-2 py-1 border rounded"> {{ $product->reviews_count }} Đánh giá</span>
            <span class="px-2 py-1 border rounded">{{ $product->brand->name }}</span>
            <span class="px-2 py-1 border rounded">{{ $product->category->name }}</span>
        </div>
        <p class="text-sm mt-2 text-gray-600">
            {{ $product->description }}
        </p>
    </div>
    <div class="w-full md:w-1/4 text-left md:text-right">
        <div class="text-orange-600 text-lg font-bold">
            <span class="line-through text-gray-400 ml-2">{{ number_format($product->price_buy, 0, ',', '.') . ' VND' }}</span>
            <span class="text-orange-500 font-semibold text-lg">{{ $product->price_sale ? number_format($product->price_sale, 0, ',', '.') . ' VND' : 'Liên hệ' }}</span>           
        </div>
        <!-- <div class="mt-2 text-gray-700">{{ $product->category->name }}</div> -->
        <div class="flex justify-end items-center gap-2 mt-4">
            <form action="{{ route('site.addcart', ['id' => $product->id]) }}" method="POST" class="flex items-center gap-2" data-save-scroll="true">
                @csrf
                <button
                    type="submit"
                    class="bg-orange-600 text-white py-2 px-4 rounded hover:bg-orange-500 whitespace-nowrap md:min-w-[170px] text-center">
                    Thêm vào giỏ hàng
                </button>
            </form>
            <div class="flex items-center gap-2">
                <a href="{{ route('site.product.detail', ['slug'=>$product->slug])}}" class="bg-gray-300 text-black py-2 px-4 rounded hover:bg-gray-400">
                    <i class="fas fa-eye"></i>
                </a>
                @if (auth()->check() && auth()->user()->favorites->contains($product->id))
                    <form action="{{ route('site.favorites.remove', $product->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-gray-300 text-black py-2 px-4 rounded hover:bg-gray-400">
                            <i class="fas fa-heart"></i> 
                        </button>
                    </form>
                @else
                    <form action="{{ route('site.favorites.add', $product->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-gray-300 text-black py-2 px-4 rounded hover:bg-gray-400">
                            <i class="fa-regular fa-heart"></i> 
                        </button>
                    </form>
                @endif
                
            </div>
        </div>
    </div>
</div>
@endforeach

<div class="flex justify-center items-center mt-8 mb-6">
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
