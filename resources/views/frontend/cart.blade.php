<x-layout-frontend>
    @php
    $cart = session()->get('cart_' . Auth::id(), []);
    @endphp
  <section class="bg-gray-200 py-2 px-4">
    <div class="breadcrumb flex items-center text-gray-600 text-sm container mx-auto">
      <span class="mr-4">Bạn đang ở đây:</span>
      <a href="{{url('/')}}" class="hover:text-orange-500"> Quay lại Trang chủ</a>
      <span class="mx-2">></span>
      <span class="font-semibold text-gray-800">
        @if(session('is_table_order'))
          Gọi món - {{ session('table_name') }}
        @else
          Giỏ hàng
        @endif
      </span>
      
      @if(session('is_table_order'))
        <a href="{{ route('site.table.end') }}" class="ml-4 bg-red-500 text-white px-4 py-1 rounded hover:bg-red-600">Kết thúc gọi món</a>
      @endif
    </div>
  </section>

  <section class="container mx-auto pb-0 px-2">
    <div class="max-w-5xl mx-auto bg-white shadow-lg rounded-t-lg p-6">
        <h2 class="text-2xl font-bold mb-6">
          @if(session('is_table_order'))
            Gọi Món - {{ session('table_name') }}
          @else
            Giỏ Hàng
          @endif
        </h2>

        @if(Auth::check()) 
            @if(count($cart) == 0) 
                <div class="text-red-500 font-semibold mb-4">
                    Giỏ hàng của bạn hiện tại đang trống.
                </div>
            @else
                <form action="{{ route('site.updatecart') }}" method="POST">
                    @csrf
                    @foreach ($cart as $id => $item)
                        <div class="flex flex-col md:flex-row justify-between items-center border-b pb-2 gap-4"
                            data-cart-row="true"
                            data-id="{{ $id }}"
                            data-price="{{ $item['price'] }}">
                            <div class="flex space-x-4 w-full md:w-5/12 mt-2">
                                <img src="{{ asset('images/product/' . $item['image']) }}" alt="{{ $item['name'] }}"
                                    class="w-28 h-28 object-cover rounded-md flex-shrink-0">
                                <div>
                                    <h3 class="text-lg font-semibold">{{ $item['name'] }}</h3>
                                    <p class="text-sm text-gray-500">Giá: {{ number_format($item['price'], 0, ',', '.') }} VND</p>
                                </div>
                            </div>
                            <div class="flex justify-between md:justify-center items-center w-full md:w-4/12 gap-4">
                                <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden">
                                    <button type="button" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 btn-qty-minus" data-id="{{ $id }}">-</button>
                                    <input type="number" name="qty[{{ $id }}]" value="{{ $item['qty'] }}" class="w-16 text-center border-0 qty-input" min="1" data-id="{{ $id }}">
                                    <button type="button" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 btn-qty-plus" data-id="{{ $id }}">+</button>
                                </div>
                                <div class="text-lg font-semibold text-gray-800 item-amount">
                                    {{ number_format($item['price'] * $item['qty'], 0, ',', '.') }} VND
                                </div>
                            </div>
                            <div class="flex justify-end w-full md:w-3/12 gap-4">
                                <a href="{{ route('site.product.detail', $item['slug']) }}" class="text-blue-500 hover:text-blue-700">Chi tiết</a>
                                <a href="{{ route('site.delcart', $id) }}" class="text-red-500 hover:text-red-700">Xóa</a>
                            </div>
                        </div>
                    @endforeach
                    
                    <div class="flex justify-between pt-4 mt-4">
                        <div class="font-semibold text-lg">Tổng cộng</div>
                        <div id="cart-total-amount" class="font-semibold text-lg text-gray-800">
                            {{ number_format(array_sum(array_map(fn($item) => $item['price'] * $item['qty'], $cart)), 0, ',', '.') }} VND
                        </div>
                    </div>
                    @if (session('is_table_order') && session('table_name'))
                        <div class="mt-6">
                            <button type="submit" onclick="return confirm('Gửi thực đơn cho nhân viên để xác nhận?')" class="w-full bg-orange-500 text-white py-2 px-4 rounded-md hover:bg-orange-600 focus:outline-none">
                                Gửi thực đơn
                            </button>
                        </div>
                    @else
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
                        <!-- Nút Thanh toán -->
                        <div class="mt-6">
                            <a href="{{ route('site.checkoutForm') }}" class="w-full bg-green-500 text-white py-2 px-4 rounded-md hover:bg-green-600 text-center block">
                                Thanh toán
                            </a>
                        </div>
                    @endif
                </form>
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
  {{-- Phần trạng thái món ăn --}}
  @if(isset($tableOrder) && $tableOrder && isset($tableOrderDetails) && $tableOrderDetails->count() > 0)
    @php
        $orderTotal = 0;
        foreach($tableOrderDetails as $detail) {
            $price = $detail->price ?? ($detail->product->price_sale ?? 0);
            $orderTotal += $price * $detail->qty;
        }
    @endphp
    <section class="container mx-auto pb-16 px-2">
        <div class="max-w-5xl mx-auto">
            <div class="bg-white shadow-md rounded-b-lg rounded-t-none overflow-hidden">
                <div class="w-full flex items-center justify-between px-4 py-3 bg-yellow-500 text-white">
                    <span class="font-semibold text-lg">Trạng Thái Món Ăn</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full whitespace-nowrap">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-3 text-left text-gray-700 font-semibold">Món Ăn</th>
                                <th class="px-4 py-3 text-center text-gray-700 font-semibold">Số Lượng</th>
                                <th class="px-4 py-3 text-right text-gray-700 font-semibold">Đơn Giá</th>
                                <th class="px-4 py-3 text-right text-gray-700 font-semibold">Thành Tiền</th>
                                <th class="px-4 py-3 text-center text-gray-700 font-semibold">Trạng Thái</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($tableOrderDetails as $detail)
                                @php
                                    $price = $detail->price ?? ($detail->product->price_sale ?? 0);
                                    $total = $price * $detail->qty;
                                    $statusText = '';
                                    $statusClass = '';
                                    switch($detail->status) {
                                        case 'pending':
                                            $statusText = 'Chờ xác nhận';
                                            $statusClass = 'bg-yellow-100 text-yellow-800';
                                            break;
                                        case 'confirmed':
                                            $statusText = 'Đã được xác nhận';
                                            $statusClass = 'bg-blue-100 text-blue-800';
                                            break;
                                        case 'cooking':
                                            $statusText = 'Đầu bếp đang chế biến';
                                            $statusClass = 'bg-orange-100 text-orange-800';
                                            break;
                                        case 'served':
                                            $statusText = 'Đã lên món';
                                            $statusClass = 'bg-green-100 text-green-800';
                                            break;
                                        default:
                                            $statusText = 'Chờ xác nhận';
                                            $statusClass = 'bg-yellow-100 text-yellow-800';
                                    }
                                @endphp
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-gray-800">{{ $detail->product->name ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-center font-medium text-gray-800">{{ $detail->qty }}</td>
                                    <td class="px-4 py-3 text-right font-medium text-gray-800">{{ number_format($price, 0, ',', '.') }} VND</td>
                                    <td class="px-4 py-3 text-right font-medium text-gray-800">{{ number_format($total, 0, ',', '.') }} VND</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $statusClass }}">
                                            {{ $statusText }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-50">
                                <td colspan="3" class="px-4 py-3 text-right font-bold text-gray-800 text-lg">Tổng Cộng:</td>
                                <td class="px-4 py-3 text-right font-bold text-gray-800 text-lg">{{ number_format($orderTotal, 0, ',', '.') }} VND</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </section>
  @endif

  <script>
    document.addEventListener('DOMContentLoaded', function () {
        var container = document.querySelector('.max-w-5xl');
        if (!container) {
            return;
        }

        container.addEventListener('click', function (event) {
            var minusButton = event.target.closest('.btn-qty-minus');
            var plusButton = event.target.closest('.btn-qty-plus');

            if (!minusButton && !plusButton) {
                return;
            }

            var button = minusButton || plusButton;
            var wrapper = button.parentElement;
            var input = wrapper.querySelector('.qty-input');

            if (!input) {
                return;
            }

            var current = parseInt(input.value, 10);
            if (isNaN(current) || current < 1) {
                current = 1;
            }

            if (plusButton) {
                current += 1;
            } else if (minusButton) {
                current = Math.max(current - 1, 1);
            }

            input.value = current;

            var row = button.closest('[data-cart-row="true"]');
            if (row) {
                var price = parseInt(row.getAttribute('data-price'), 10) || 0;
                var amountElement = row.querySelector('.item-amount');
                if (amountElement) {
                    var lineTotal = price * current;
                    amountElement.textContent = formatCurrency(lineTotal) + ' VND';
                }
            }

            updateCartTotal();
        });

        function formatCurrency(value) {
            if (typeof Intl !== 'undefined' && Intl.NumberFormat) {
                return new Intl.NumberFormat('vi-VN').format(value);
            }
            return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        function updateCartTotal() {
            var rows = document.querySelectorAll('[data-cart-row="true"]');
            var total = 0;

            rows.forEach(function (row) {
                var price = parseInt(row.getAttribute('data-price'), 10) || 0;
                var input = row.querySelector('.qty-input');
                if (!input) {
                    return;
                }
                var qty = parseInt(input.value, 10);
                if (isNaN(qty) || qty < 1) {
                    qty = 1;
                }
                total += price * qty;
            });

            var totalElement = document.getElementById('cart-total-amount');
            if (totalElement) {
                totalElement.textContent = formatCurrency(total) + ' VND';
            }
        }


        // Polling để kiểm tra trạng thái order (tự động kết thúc khi thanh toán xong)
        @if(session('is_table_order'))
        setInterval(function() {
            // Gọi một request đơn giản để kiểm tra trạng thái (có thể dùng AJAX hoặc reload trang)
            // Ở đây dùng cách đơn giản: reload trang mỗi 5 giây để CartController kiểm tra lại trạng thái
            window.location.reload();
        }, 5000); // Kiểm tra mỗi 5 giây
        @endif
    });
</script>
</x-layout-frontend>
