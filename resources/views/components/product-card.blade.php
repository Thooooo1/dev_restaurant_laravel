<div class="gallery-box relative bg-white rounded-lg shadow-md overflow-hidden group w-full">
    <div class="relative">
        <img src="{{ asset('/images/product/' . $product->image) }}" alt="{{$product->name }}"
            class="w-full h-64 object-cover transition-all duration-300 group-hover:opacity-60">
            <div class="absolute inset-0 flex justify-center items-center bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-all duration-300">
                <div class="flex space-x-4">
                    <a href="{{ route('site.product.detail', ['slug'=>$product->slug])}}" 
                       class="flex justify-center items-center w-12 h-12 bg-white bg-opacity-20 text-white rounded-full hover:bg-opacity-40 transition transform hover:scale-110">
                        <i class="fas fa-eye"></i>
                    </a>
            
                    <form action="{{ route('site.addcart', ['id' => $product->id]) }}" method="POST">
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
        <span class="text-orange-500 font-semibold text-lg">{{ $product->price_sale ? number_format($product->price_sale, 0, ',', '.') . ' VND' : 'Liên hệ' }}</span>
        <span class="line-through text-gray-400 ml-2">{{ number_format($product->price_buy, 0, ',', '.') . ' VND' }}</span>
        <h4 class="font-bold text-lg mt-2 mb-3">{{$product->name}}</h4>
        <a href="{{ route('site.product.detail', ['slug'=>$product->slug])}}"
            class="btn mt-2 bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600">Xem
            chi tiết</a>
    </div>
</div>