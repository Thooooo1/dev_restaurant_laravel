<x-layout-frontend>
    @php
        $totalAmount = $orderDetails ? $orderDetails->sum('amount') : 0;
        $statusConfig = match((int)$order->status) {
            0 => ['label' => 'Đang chờ xử lý', 'bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-200', 'desc' => 'Đơn hàng đã tiếp nhận và đang chờ bếp xác nhận.'],
            1 => ['label' => 'Đã xác nhận & Đang chế biến', 'bg' => 'bg-orange-50', 'text' => 'text-orange-700', 'border' => 'border-orange-200', 'desc' => 'Đầu bếp đang nấu món và chuẩn bị phục vụ.'],
            2 => ['label' => 'Đã giao thành công', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'desc' => 'Đơn hàng đã được giao trọn vẹn đến bạn.'],
            default => ['label' => 'Đã hủy', 'bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-200', 'desc' => 'Đơn hàng đã hủy.'],
        };
    @endphp

    <main class="bg-[#FAF8F5] min-h-screen pb-16">
        <!-- Page Breadcrumb & Header -->
        <x-page-hero 
            :title="'Đơn Hàng #ORD-' . $order->id" 
            subtitle="Chi tiết thực đơn và trạng thái giao nhận món ăn." 
            :breadcrumbs="[
                ['name' => 'Đơn hàng của tôi', 'url' => route('site.orders')],
                ['name' => '#ORD-' . $order->id, 'url' => '']
            ]" 
        />

        <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-4xl pt-8 sm:pt-10">
            <div class="bg-white rounded-none sm:rounded-sm border border-stone-200/90 shadow-xs overflow-hidden">
                
                <!-- Header with Status Badge -->
                <div class="p-6 sm:p-8 border-b border-stone-100 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <span class="text-xs uppercase font-bold tracking-wider text-[#C25E00]">MÃ ĐƠN HÀNG</span>
                        <h2 class="font-serif text-2xl font-bold text-stone-900">#ORD-{{ $order->id }}</h2>
                        <p class="text-xs text-stone-500 mt-1">
                            Đặt lúc: <strong class="text-stone-700">{{ $order->created_at ? $order->created_at->format('H:i - d/m/Y') : '' }}</strong>
                        </p>
                    </div>

                    <div class="text-right">
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-semibold border {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }} {{ $statusConfig['border'] }}">
                            <span class="w-2 h-2 rounded-full {{ (int)$order->status === 1 ? 'bg-orange-500 animate-pulse' : ((int)$order->status === 2 ? 'bg-emerald-500' : 'bg-amber-500') }}"></span>
                            <span>{{ $statusConfig['label'] }}</span>
                        </span>
                        <p class="text-[11px] text-stone-400 mt-1">{{ $statusConfig['desc'] }}</p>
                    </div>
                </div>

                <!-- Customer & Payment Details Grid -->
                @php
                    $paymentInfo = $order->payment_method_info;
                @endphp
                <div class="p-6 sm:p-8 bg-[#FAF8F5] border-b border-stone-100 grid grid-cols-1 sm:grid-cols-3 gap-5 text-xs sm:text-sm">
                    <div>
                        <span class="block text-stone-400 text-xs font-medium mb-1">Người nhận / Khách hàng</span>
                        <strong class="text-stone-900 block font-semibold">{{ $order->user->fullname ?? $order->user->name ?? $order->name ?? 'Khách hàng' }}</strong>
                        <span class="text-stone-600 font-mono text-xs">{{ $order->phone }}</span>
                    </div>

                    <div>
                        <span class="block text-stone-400 text-xs font-medium mb-1">Địa chỉ giao / Bàn phục vụ</span>
                        <p class="text-stone-800 leading-relaxed font-medium">
                            {{ $order->address ?: ($order->table ? ($order->table->name . ' - ' . ($order->table->floor->name ?? 'Khu vực chính')) : 'Tại nhà hàng') }}
                        </p>
                        @if($order->note)
                            <span class="text-[11px] text-amber-700 italic block mt-1">Ghi chú: "{{ $order->note }}"</span>
                        @endif
                    </div>

                    <div>
                        <span class="block text-stone-400 text-xs font-medium mb-1">Phương thức thanh toán</span>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold {{ $paymentInfo['bg'] }} {{ $paymentInfo['text'] }} border {{ $paymentInfo['border'] }}">
                                <i class="fa-solid {{ $paymentInfo['icon'] }}"></i>
                                <span>{{ $paymentInfo['name'] }}</span>
                            </span>
                        </div>
                        <span class="text-[11px] block mt-1 font-semibold {{ (int)$order->status === 2 ? 'text-emerald-600' : 'text-amber-600' }}">
                            {{ (int)$order->status === 2 ? '✓ Đã thanh toán' : '⏳ Chưa thanh toán' }}
                        </span>
                    </div>
                </div>

                <!-- Dishes Table -->
                <div class="p-6 sm:p-8">
                    <h3 class="font-serif text-lg font-bold text-stone-900 mb-4">Danh Sách Món Ăn</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs sm:text-sm">
                            <thead>
                                <tr class="border-b border-stone-200 text-stone-400 uppercase text-[11px] tracking-wider text-left">
                                    <th class="py-3 font-semibold">Món ăn</th>
                                    <th class="py-3 text-right font-semibold">Đơn giá</th>
                                    <th class="py-3 text-center font-semibold">Số lượng</th>
                                    <th class="py-3 text-right font-semibold">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100">
                                @foreach ($orderDetails as $detail)
                                    <tr>
                                        <td class="py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-12 h-12 rounded-none sm:rounded-sm bg-stone-100 border border-stone-200/80 overflow-hidden shrink-0">
                                                    <img src="{{ asset('images/product/' . ($detail->product?->image ?? 'default-product.png')) }}" 
                                                         alt="{{ $detail->product?->name }}" 
                                                         class="w-full h-full object-cover">
                                                </div>
                                                <div>
                                                    <h4 class="font-bold text-stone-900 text-xs sm:text-sm">
                                                        {{ $detail->product?->name ?? 'Món ăn' }}
                                                    </h4>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-4 text-right text-stone-600">
                                            {{ number_format($detail->price, 0, ',', '.') }}₫
                                        </td>
                                        <td class="py-4 text-center font-bold text-stone-900">
                                            {{ $detail->qty }}
                                        </td>
                                        <td class="py-4 text-right font-bold text-stone-900">
                                            {{ number_format($detail->amount, 0, ',', '.') }}₫
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="border-t-2 border-stone-200">
                                    <td colspan="3" class="py-4 text-right font-bold text-stone-800 text-sm">Tổng thanh toán:</td>
                                    <td class="py-4 text-right font-serif font-bold text-xl text-[#C25E00]">
                                        {{ number_format($totalAmount, 0, ',', '.') }}₫
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="p-6 sm:p-8 bg-[#FAF8F5] border-t border-stone-100 flex flex-wrap items-center justify-between gap-4">
                    <a href="{{ route('site.orders') }}" 
                       class="text-xs font-semibold text-stone-600 hover:text-stone-900 flex items-center gap-1.5 transition-colors">
                        <i class="fa-solid fa-arrow-left text-[10px]"></i>
                        <span>Quay lại danh sách đơn</span>
                    </a>

                    <div class="flex items-center gap-3">
                        @if ((int)$order->status === 0)
                            <button type="button" 
                                    onclick="toggleCancelModal()" 
                                    class="px-4 py-2 rounded-none sm:rounded-sm bg-white hover:bg-rose-50 text-rose-700 font-semibold text-xs border border-rose-200 transition-colors cursor-pointer">
                                Hủy đơn hàng
                            </button>
                        @endif

                        @if ((int)$order->status === 2 || (int)$order->status === 3)
                            <a href="{{ route('site.orders.reorder', $order->id) }}" 
                               class="px-5 py-2.5 rounded-none sm:rounded-sm bg-[#C25E00] hover:bg-[#A34E00] text-white font-semibold text-xs shadow-xs transition-colors cursor-pointer">
                                <i class="fa-solid fa-rotate-right mr-1"></i>
                                <span>Đặt lại đơn này</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal Cancel Order -->
    <div id="cancelModal" class="fixed inset-0 bg-stone-950/60 backdrop-blur-xs hidden flex items-center justify-center z-[999999] p-4" style="z-index: 999999 !important;">
        <div class="bg-white rounded-none sm:rounded-sm max-w-md w-full p-6 sm:p-8 shadow-xl border border-stone-200">
            <h3 class="font-serif text-xl font-bold text-stone-900 mb-2">Hủy Đơn Hàng #ORD-{{ $order->id }}</h3>
            <p class="text-xs text-stone-600 mb-5 leading-relaxed">
                Vui lòng chọn lý do để nhà hàng hoàn thiện chất lượng dịch vụ:
            </p>

            <form action="{{ route('site.orders.cancel', $order->id) }}" method="POST" class="space-y-4">
                @csrf
                <div class="space-y-2.5 text-xs text-stone-700">
                    <label class="flex items-center gap-2 p-2.5 rounded-none sm:rounded-sm border border-stone-200 hover:bg-stone-50 cursor-pointer">
                        <input type="checkbox" name="reasons[]" value="Tôi bận việc đột xuất" class="accent-[#C25E00]">
                        <span>Tôi bận việc đột xuất</span>
                    </label>
                    <label class="flex items-center gap-2 p-2.5 rounded-none sm:rounded-sm border border-stone-200 hover:bg-stone-50 cursor-pointer">
                        <input type="checkbox" name="reasons[]" value="Đặt nhầm món ăn hoặc số lượng" class="accent-[#C25E00]">
                        <span>Đặt nhầm món ăn hoặc số lượng</span>
                    </label>
                    <label class="flex items-center gap-2 p-2.5 rounded-none sm:rounded-sm border border-stone-200 hover:bg-stone-50 cursor-pointer">
                        <input type="checkbox" name="reasons[]" value="Thời gian chờ đợi lâu hơn dự kiến" class="accent-[#C25E00]">
                        <span>Thời gian chờ đợi lâu hơn dự kiến</span>
                    </label>
                    <label class="flex items-center gap-2 p-2.5 rounded-none sm:rounded-sm border border-stone-200 hover:bg-stone-50 cursor-pointer">
                        <input type="checkbox" id="otherReasonCheckbox" name="reasons[]" value="Lý do khác" class="accent-[#C25E00]" onchange="toggleOtherReasonInput()">
                        <span>Lý do khác</span>
                    </label>
                    <input type="text" 
                           id="otherReasonInput" 
                           name="other_reason" 
                           class="w-full p-2.5 text-xs border border-stone-300 rounded-none sm:rounded-sm hidden focus:outline-none focus:border-[#C25E00]" 
                           placeholder="Nhập lý do khác...">
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-stone-100">
                    <button type="button" 
                            onclick="toggleCancelModal()" 
                            class="px-4 py-2 rounded-none sm:rounded-sm bg-stone-100 hover:bg-stone-200 text-stone-700 text-xs font-semibold transition-colors cursor-pointer">
                        Giữ lại đơn
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 rounded-none sm:rounded-sm bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-xs transition-colors cursor-pointer">
                        Xác nhận hủy
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleCancelModal() {
            const modal = document.getElementById('cancelModal');
            if (modal) modal.classList.toggle('hidden');
        }

        function toggleOtherReasonInput() {
            const cb = document.getElementById('otherReasonCheckbox');
            const inp = document.getElementById('otherReasonInput');
            if (cb && inp) {
                if (cb.checked) inp.classList.remove('hidden');
                else inp.classList.add('hidden');
            }
        }
    </script>
</x-layout-frontend>
