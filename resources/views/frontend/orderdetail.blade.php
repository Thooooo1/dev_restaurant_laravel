<x-layout-frontend>
    <section class="bg-gray-200 ml-5">
        <div class="breadcrumb flex items-center text-gray-600 text-sm  ">
            <span class="mr-4">Bạn đang ở đây:</span>
            <a href="{{ url('/') }}" class="hover:text-orange-500"> Quay lại Trang chủ</a>
            <span class="mx-2">></span>
            <a href="{{ url('/don-hang') }}" class="hover:text-orange-500"> Đơn hàng</a>
            <span class="mx-2">></span>
            <span class="font-semibold text-gray-800">Chi tiết đơn hàng</span>
            <span class="mx-2">></span>
            <span class="font-semibold text-gray-800">{{ $order->user->fullname }}</span>
        </div>
    </section>
    <section class="container mx-auto py-8">
        <div class="max-w-4xl mx-auto bg-white shadow-lg rounded-lg">
            <!-- Thông tin đơn hàng -->
            <div class="p-6 border-b">
                <h2 class="text-2xl font-bold mb-4 text-center">Thông tin đơn hàng</h2>
                <p><strong>Họ tên:</strong> {{ $order->user->fullname }}</p>
                <p><strong>Điện thoại:</strong> {{ $order->phone }}</p>
                <p><strong>Địa chỉ:</strong> {{ $order->address }}</p>
                <p><strong>Tổng tiền:</strong>
                    {{ number_format($order->orderDetails ? $order->orderDetails->sum('amount') : 0, 0, ',', '.') }} VNĐ
                </p>
                <p><strong>Trạng thái:</strong>
                    @switch($order->status)
                        @case(0)
                            Đang chờ xử lý
                        @break

                        @case(1)
                            Đang vận chuyển
                        @break

                        @case(2)
                            Đã giao hàng
                        @break

                        @case(3)
                            Đã hủy
                        @break
                    @endswitch
                </p>
                <p><strong>Ngày tạo đơn:</strong> {{ $order->created_at->format('H:i d/m/Y') }}</p>
                {{-- <a href="{{ route('orders.print', $order->id) }}" class="btn btn-success mt-2 inline-block">
                    <i class="fas fa-print"></i> In đơn hàng
                </a> --}}
            </div>

            <!-- Chi tiết sản phẩm -->
            <div class="p-6">
                <h2 class="text-2xl font-bold mb-4">Chi tiết món ăn</h2>
                <table class="table-auto w-full border border-gray-300">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border p-2">#</th>
                            <th class="border p-2">Hình ảnh</th>
                            <th class="border p-2">Tên món ăn</th>
                            <th class="border p-2">Giá</th>
                            <th class="border p-2">Số lượng</th>
                            <th class="border p-2">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orderDetails as $index => $detail)
                            <tr>
                                <td class="border p-2 text-center">{{ $index + 1 }}</td>
                                <td class="border p-2 text-center">
                                    <img src="{{ asset('images/product/' . $detail->product?->image) }}"
                                        alt="{{ $detail->product?->name }}" class="max-w-[50px] mx-auto">
                                </td>
                                <td class="border p-2">{{ $detail->product?->name ?? 'Sản phẩm không tồn tại' }}</td>
                                <td class="border p-2 text-right">{{ number_format($detail->price, 0, ',', '.') }} VNĐ
                                </td>
                                <td class="border p-2 text-center">{{ $detail->qty }}</td>
                                <td class="border p-2 text-right">{{ number_format($detail->amount, 0, ',', '.') }} VNĐ
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <p class="text-center font-bold my-4">Cảm ơn bạn đã đặt món tại VanHaiRestaurant !</p>
            </div>

            <!-- Tổng tiền -->
            <div class="p-6 border-t text-right">
                <strong class="text-lg">Tổng tiền: {{ number_format($orderDetails->sum('amount'), 0, ',', '.') }}
                    VNĐ</strong>
            </div>
            @if ($order->status != 3)
                <button type="button" onclick="toggleCancelModal()"
                    class="bg-red-500 text-white py-2 px-4 rounded-md ml-4 mb-4">
                    Hủy đơn hàng
                </button>

                <div id="cancelModal"
                    class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex justify-center items-center z-50">
                    <div class="bg-white p-6 rounded-lg shadow-lg w-1/3">
                        <h3 class="text-lg font-bold mb-4">Chọn lý do hủy đơn hàng</h3>
                        <form action="{{ route('site.orders.cancel', $order->id) }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Lý do hủy</label>
                                <div>
                                    <label class="block">
                                        <input type="checkbox" name="reasons[]" value="Không cần nữa" class="mr-2">
                                        Không cần nữa
                                    </label>
                                    <label class="block">
                                        <input type="checkbox" name="reasons[]" value="Đặt nhầm sản phẩm"
                                            class="mr-2">
                                        Đặt nhầm sản phẩm
                                    </label>
                                    <label class="block">
                                        <input type="checkbox" name="reasons[]" value="Thời gian giao hàng quá lâu"
                                            class="mr-2">
                                        Thời gian giao hàng quá lâu
                                    </label>
                                    <label class="block">
                                        <input type="checkbox" id="otherReasonCheckbox" name="reasons[]"
                                            value="Lý do khác" class="mr-2" onchange="toggleOtherReasonInput()">
                                        Lý do khác
                                    </label>
                                    <input type="text" id="otherReasonInput" name="other_reason"
                                        class="mt-2 block w-full border-gray-300 rounded-md shadow-sm hidden"
                                        placeholder="Nhập lý do khác...">
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <button type="button" onclick="toggleCancelModal()"
                                    class="bg-gray-300 text-gray-800 py-2 px-4 rounded-md mr-2">
                                    Đóng
                                </button>
                                <button type="submit" class="bg-red-500 text-white py-2 px-4 rounded-md">
                                    Xác nhận hủy
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

        </div>
    </section>
    <script>
        function toggleCancelModal() {
            const modal = document.getElementById('cancelModal');
            modal.classList.toggle('hidden');
        }

        function toggleOtherReasonInput() {
            const otherReasonCheckbox = document.getElementById('otherReasonCheckbox');
            const otherReasonInput = document.getElementById('otherReasonInput');
            if (otherReasonCheckbox.checked) {
                otherReasonInput.classList.remove('hidden');
            } else {
                otherReasonInput.classList.add('hidden');
            }
        }
    </script>


</x-layout-frontend>
