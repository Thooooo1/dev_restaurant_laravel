<x-layout-frontend>
    <section class="bg-gray-200 ml-5">
        <div class="breadcrumb flex items-center text-gray-600 text-sm">
            <span class="mr-4">Bạn đang ở đây:</span>
            <a href="{{ url('/') }}" class="hover:text-orange-500">Quay lại Trang chủ</a>
            <span class="mx-2">></span>
            <span class="font-semibold text-gray-800">Đơn hàng</span>
        </div>
    </section>

    <section class="container mx-auto py-8">
        <div class="max-w-5xl mx-auto bg-white shadow-lg rounded-lg">
            <div class="p-6 border-b">
                <h2 class="text-2xl font-bold mb-4 text-center">Danh sách đơn hàng</h2>
            </div>
            <div class="p-6">
                <!-- Tabs -->
                <div class="tabs mb-6 flex justify-center">
                    <button class="tab-button px-4 py-2 mx-2 border rounded bg-gray-100 hover:bg-gray-200"
                        onclick="showTab('all')">Tất cả </button>
                    <button class="tab-button px-4 py-2 mx-2 border rounded bg-gray-100 hover:bg-gray-200"
                        onclick="showTab('pending')">Đang chờ xử lý <span style="color: red">({{ $statusCounts['pending'] }})</span></button>
                    <button class="tab-button px-4 py-2 mx-2 border rounded bg-gray-100 hover:bg-gray-200"
                        onclick="showTab('confirmed')">Đã xác nhận <span style="color: red">({{ $statusCounts['confirmed'] }})</span></button>             
                    <button class="tab-button px-4 py-2 mx-2 border rounded bg-gray-100 hover:bg-gray-200"
                        onclick="showTab('shipped')">Đã giao <span style="color: red">({{ $statusCounts['shipped'] }})</span></button>              
                    <button class="tab-button px-4 py-2 mx-2 border rounded bg-gray-100 hover:bg-gray-200"
                        onclick="showTab('cancelled')">Đã hủy </button>
                </div>
                
                @foreach ($ordersByStatus as $status => $orders)
                    <div class="tab-content {{ $loop->first ? '' : 'hidden' }}" id="tab-{{ $status }}">
                        @if ($orders->isEmpty())
                            <div class="text-center text-red-500 font-semibold">
                                Chưa có đơn hàng.
                            </div>
                        @else
                            @foreach ($orders as $order)
                                <div class="border-b mb-4 pb-4">
                                    <h3 class="font-bold text-lg mb-2">Đơn hàng  - Ngày :
                                        {{ $order->created_at->format('d/m/Y') }}</h3>
                                        <div class="flex justify-between items-center">
                                            <p class="mb-2">
                                                Trạng thái:
                                                @if ($order->status == 0)
                                                    <span class="px-2 py-1 bg-yellow-200 text-yellow-800 rounded">Đang chờ xử lý</span>
                                                    <p> Chúng tôi đang xử lý đơn hàng, bạn đợi xíu nhé.</p>
                                                @elseif($order->status == 1)
                                                    <span class="px-2 py-1 bg-green-200 text-green-800 rounded">Đã xác nhận</span>
                                                    <p> Nhà hàng đang chuẩn bị món của bạn.</p>
                                                @elseif($order->status == 2)
                                                    <span class="px-2 py-1 bg-blue-200 text-blue-800 rounded">Đã giao</span>
                                                    <p> Chúc bạn dùng món ngon miệng.</p>
                                                @else
                                                    <span class="px-2 py-1 bg-red-200 text-red-800 rounded">Đã hủy</span>
                                                   
                                                @endif
                                            </p>
                                            <div class="flex items-center ">
                                                @if ($order->status == 2 || $order->status == 3)
                                                    <a href="{{ route('site.orders.reorder', $order->id) }}" class="bg-blue-500 text-white py-1 px-2 rounded hover:bg-blue-600 mb-2 ml-4">
                                                        Đặt lại
                                                    </a>
                                                @endif
                                                <a href="{{ route('site.orders.detail', $order->id) }}" class="bg-red-500 text-white py-1 px-2 rounded hover:bg-red-600 mb-2 ml-2">
                                                    Xem chi tiết
                                                </a>
                                            </div>
                                        </div>
                                        
                                   
                                    <table class="w-full border-collapse border border-gray-300 text-sm mt-4">
                                        <thead class="bg-gray-200 text-gray-700">
                                            <tr>
                                                <th class="border p-3">Hình ảnh</th>
                                                <th class="border p-3">Tên sản phẩm</th>
                                                <th class="border p-3 text-right">Giá</th>
                                                <th class="border p-3 text-center">Số lượng</th>
                                                <th class="border p-3 text-right">Thành tiền</th>
                                                <th class="border p-3 text-right">Đánh giá</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($order->orderDetails as $detail)
                                                <tr>
                                                    <td class="border p-3 text-center">
                                                        <img src="{{ asset('images/product/' . $detail->product?->image) }}"
                                                            alt="{{ $detail->product?->name }}"
                                                            class="max-w-[50px] mx-auto">
                                                    </td>
                                                    <td class="border p-3">
                                                        {{ $detail->product?->name ?? 'Sản phẩm không tồn tại' }}</td>
                                                    <td class="border p-3 text-right">
                                                        {{ number_format($detail->price, 0, ',', '.') }} VNĐ</td>
                                                    <td class="border p-3 text-center">{{ $detail->qty }}</td>
                                                    <td class="border p-3 text-right">
                                                        {{ number_format($detail->amount, 0, ',', '.') }} VNĐ</td>
                                                    <td class="border p-3 text-right">
                                                        <a href="{{ route('site.product.detail', ['slug' => $detail->product->slug])}}"
                                                            class="btn mt-2 bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600">Đánh giá</a>
                                                    </td>
                                                </tr>
                                                
                                            @endforeach
                                        </tbody>
                                    </table>
                                    @if ( $order->status == 2)
                                        <span class="text-center text-gray-700  ">Đánh giá ngay và nhận 300 Xu. </span>
                                    @endif
                                    @if ($order->status == 3)
                                    <p><strong>Lý do hủy:</strong> {{ $order->note ?? 'Không có lý do' }}</p>
                                @endif
                                </div>
                            @endforeach
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <script>
        function showTab(status) {
            const tabs = document.querySelectorAll('.tab-content');
            tabs.forEach(tab => tab.classList.add('hidden'));

            document.getElementById('tab-' + status).classList.remove('hidden');
        }
    </script>
</x-layout-frontend>
