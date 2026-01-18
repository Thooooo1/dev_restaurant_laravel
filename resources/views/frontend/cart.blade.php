<x-layout-frontend>
    @php
    $cart = session()->get('cart_' . Auth::id(), []);
    @endphp
  <section class="bg-gray-200 py-2 px-4">
    <div class="breadcrumb flex items-center text-gray-600 text-sm container mx-auto">
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
                        <div class="flex flex-col md:flex-row justify-between items-center border-b pb-4 gap-4">
                            <div class="flex space-x-4 w-full md:w-5/12">
                                <img src="{{ asset('images/product/' . $item['image']) }}" alt="{{ $item['name'] }}"
                                    class="w-24 h-24 object-cover rounded-md flex-shrink-0">
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
                                <div class="text-lg font-semibold text-gray-800">
                                    {{ number_format($item['price'] * $item['qty'], 0, ',', '.') }} VND
                                </div>
                            </div>
                            <div class="flex justify-end w-full md:w-3/12 gap-4">
                                <a href="{{ route('site.product.detail', $item['slug']) }}" class="text-blue-500 hover:text-blue-700">Chi tiết</a>
                                <a href="{{ route('site.delcart', $id) }}" class="text-red-500 hover:text-red-700">Xóa</a>
                            </div>
                        </div>
                    @endforeach
                    <div class="mt-6">
                      <button type="submit" class="w-full bg-orange-500 text-white py-2 px-4 rounded-md hover:bg-orange-600 focus:outline-none">
                          Xác nhận
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
  @if(isset($tableOrder) && $tableOrder && isset($tableOrderDetails) && $tableOrderDetails->count() > 0)
    <section class="container mx-auto pb-8">
        <div class="max-w-4xl mx-auto mt-4">
            <div id="historyAccordion" class="">
                <div class="card">
                    <div class="card-header p-2 bg-yellow-500">
                        <button class="btn w-100 btn-link p-0 text-white" data-toggle="collapse" data-target="#history-turn-all">
                            Món ăn đã gọi
                        </button>
                    </div>
                    <div id="history-turn-all" class="collapse">
                        <div class="card-body p-2">
                            @foreach($tableOrderDetails as $detail)
                                @php
                                    $price = $detail->price ?? ($detail->product->price_sale ?? 0);
                                    $amount = $price * ($detail->qty ?? 0);
                                @endphp
                                <div class="order-item bg-light border-b" id="history-item-{{ $detail->id }}" data-price="{{ $price }}">
                                    <div class="row align-items-center">
                                        <div class="col-6">
                                            <div class="font-weight-bold">{{ $detail->product->name ?? 'N/A' }}</div>
                                            <div class="text-muted small">{{ number_format($price, 0, ',', '.') }} ₫ x {{ $detail->qty }}</div>
                                        </div>
                                        <div class="col-6 text-right">
                                            <div class="font-weight-bold" id="history-amount-{{ $detail->id }}">{{ number_format($amount, 0, ',', '.') }} ₫</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
  @endif

  <script>
    document.addEventListener('DOMContentLoaded', function () {
        var container = document.querySelector('.max-w-4xl');
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
        });
    });

    @if(session('kitchen_print_order_id') && isset($tableOrder) && $tableOrder && isset($kitchenItems) && count($kitchenItems) > 0 && $tableOrder->id === session('kitchen_print_order_id'))
        window.addEventListener('load', function () {
            var data = {
                floor: {!! json_encode(optional(optional($tableOrder->table)->floor)->name ?? '') !!},
                table: {!! json_encode(optional($tableOrder->table)->name ?? '') !!},
                customer_name: {!! json_encode(optional($tableOrder->user)->fullname ?? ($tableOrder->name ?? 'Khách lẻ')) !!},
                customer_phone: {!! json_encode(optional($tableOrder->user)->phone ?? ($tableOrder->phone ?? '---')) !!},
                order_turn: {{ $tableOrder->order_turn ?? 1 }},
                note: {!! json_encode($tableOrder->note ?? '') !!},
                items: {!! json_encode($kitchenItems) !!}
            };

            var html = generateKitchenTicketHTML(data);
            var w = window.open('', 'PRINT', 'width=400,height=600');
            if (w) {
                w.document.write(html);
                w.document.close();
                w.focus();
                w.print();
                w.close();
            }
        });

        function generateKitchenTicketHTML(data) {
            var floor = data.floor;
            var table = data.table;
            var customer_name = data.customer_name;
            var customer_phone = data.customer_phone;
            var order_turn = data.order_turn;
            var note = data.note;
            var items = data.items || [];
            var now = new Date();
            var pad = function (n) {
                return String(n).padStart(2, '0');
            };
            var dateStr = pad(now.getDate()) + '/' + pad(now.getMonth() + 1) + '/' + now.getFullYear() + ' ' + pad(now.getHours()) + ':' + pad(now.getMinutes());
            var rows = '';

            items.forEach(function (i) {
                rows += '<tr><td style="text-align:center;width:30px;">' + i.stt + '</td><td>' + i.name + '</td><td style="text-align:right;width:50px;">' + i.qty + '</td></tr>';
            });

            var noteHtml = '';
            if (note) {
                noteHtml = '<div class="meta" style="border:1px dashed #000; padding:5px; margin:5px 0;">Ghi chú: <strong>' + note + '</strong></div>';
            }

            return '<!doctype html><html><head><meta charset="utf-8"><title>Phiếu gọi món</title><style>' +
                '@media print { @page { size: 80mm auto; margin: 5mm; } }' +
                'body { font-family: Arial, sans-serif; width: 80mm; }' +
                '.title { text-align:center; font-size:16px; font-weight:bold; margin-bottom:6px; }' +
                '.meta { font-size:12px; margin-bottom:4px; }' +
                'table { width:100%; border-collapse: collapse; font-size:12px; }' +
                'th, td { padding:4px 0; border-bottom:1px dashed #000; }' +
                'th { text-align:left; border-top:1px dashed #000; }' +
                '.footer { margin-top:8px; font-size:11px; text-align:center; }' +
                '</style></head><body>' +
                '<div class="title">PHIẾU GỌI MÓN</div>' +
                '<div class="meta">Tầng: <strong>' + floor + '</strong> | Bàn: <strong>' + table + '</strong></div>' +
                '<div class="meta">Lần order: <strong>' + order_turn + '</strong> | Thời gian: ' + dateStr + '</div>' +
                '<div class="meta">Khách: <strong>' + customer_name + '</strong> | ĐT: ' + customer_phone + '</div>' +
                '<div class="meta">Phương thức: <strong>Tự order</strong></div>' +
                noteHtml +
                '<table><thead><tr><th style="width:30px; text-align:center;">STT</th><th>Món</th><th style="width:50px; text-align:right;">SL</th></tr></thead><tbody>' + rows + '</tbody></table>' +
                '<div class="footer">In từ hệ thống nhà hàng</div>' +
                '</body></html>';
        }
    @endif
  </script>
</x-layout-frontend>
