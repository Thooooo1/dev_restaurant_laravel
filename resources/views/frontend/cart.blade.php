<x-layout-frontend>
    @php
    $cart = session()->get('cart_' . Auth::id(), []);
    @endphp
  <section class="bg-gray-200 ml-5">
    <div class="breadcrumb flex items-center text-gray-600 text-sm">
        <span class="mr-4">Bạn đang ở đây:</span>
        <a href="{{url('/')}}" class="hover:text-orange-500"> Quay lại Trang chủ</a>
        <span class="mx-2">></span>
        <span class="font-semibold text-gray-800">Giỏ hàng</span>
    </div>
  </section>

  <section class="container mx-auto py-8">
    <div class="max-w-4xl mx-auto bg-white shadow-lg rounded-lg p-6">
        <h2 class="text-2xl font-bold mb-6">Giỏ Hàng</h2>

        @if(Auth::check()) 
            @if(count($cart) == 0) 
                <div class="text-red-500 font-semibold mb-4">
                    Giỏ hàng của bạn hiện tại đang trống.
                </div>
            @else
                <form action="{{ route('site.updatecart') }}" method="POST">
                    @csrf
                    @foreach ($cart as $id => $item)
                        <div class="flex justify-between border-b pb-4">
                            <div class="flex space-x-4">
                                <img src="{{ asset('images/product/' . $item['image']) }}" alt="{{ $item['name'] }}"
                                    class="w-24 h-24 object-cover rounded-md">
                                <div>
                                    <h3 class="text-lg font-semibold">{{ $item['name'] }}</h3>
                                    <p class="text-sm text-gray-500">Giá: {{ number_format($item['price'], 0, ',', '.') }} VND</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <input type="number" name="qty[{{ $id }}]" value="{{ $item['qty'] }}" class="w-16 text-center border rounded-md py-1" min="1">
                            </div>
                            <div class="text-lg font-semibold text-gray-800">
                                {{ number_format($item['price'] * $item['qty'], 0, ',', '.') }} VND
                            </div>
                            <a href="{{ route('site.product.detail', $item['slug']) }}" class="text-red-500 hover:text-red-700">Chi tiết</a>

                            <a href="{{ route('site.delcart', $id) }}" class="text-red-500 hover:text-red-700">Xóa</a>
                        </div>
                    @endforeach
                    <div class="mt-6">
                      <button type="submit" class="w-full bg-orange-500 text-white py-2 px-4 rounded-md hover:bg-orange-600 focus:outline-none">
                          Cập nhật giỏ hàng
                      </button>
                  </div>
                    <div class="flex justify-between border-t pt-4 mt-6">
                        <div class="font-semibold text-lg">Tổng cộng</div>
                        <div class="font-semibold text-lg text-gray-800">
                            {{ number_format(array_sum(array_map(fn($item) => $item['price'] * $item['qty'], $cart)), 0, ',', '.') }} VND
                        </div>
                    </div>
                    <div class="mb-6 py-2">
                      <label for="discount" class="block text-gray-700 font-medium mb-2">Mã giảm giá:</label>
                      <div class="flex">
                          <input type="text" id="discount" name="discount" placeholder="Nhập mã giảm giá"
                              class="flex-grow border border-gray-300 rounded-l-md p-2 focus:outline-none focus:ring focus:ring-blue-200">
                          <button type="button" id="apply_discount"
                              class="bg-blue-500 text-white px-4 rounded-r-md hover:bg-blue-600 focus:outline-none focus:ring focus:ring-blue-300">
                              Áp dụng
                          </button>
                      </div>
                      <p id="discount_message" class="text-sm text-green-600 mt-2 hidden"></p>
                  </div>
      
                    
                </form>

                <!-- Nút Thanh toán -->
                <div class="mt-6">
                    <a href="{{ route('site.checkoutForm') }}" class="w-full bg-green-500 text-white py-2 px-4 rounded-md hover:bg-green-600 text-center block">
                        Thanh toán
                    </a>
                </div>
            @endif
        @else 
            <div class="text-red-500 font-semibold mb-4">
                Bạn cần đăng nhập để xem giỏ hàng.
            </div>
            <a href="{{ route('site.login') }}" class="w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600 text-center block">
                Đăng nhập
            </a>
        @endif
    </div>
  </section>
</x-layout-frontend>
