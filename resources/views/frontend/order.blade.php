<x-layout-frontend>
    <main class="bg-[#FAF8F5] min-h-screen pb-16">
        <!-- Page Breadcrumb & Header -->
        <x-page-hero 
            title="Lịch Sử & Theo Dõi Đơn Hàng" 
            subtitle="Kiểm tra tiến độ chế biến và trạng thái giao nhận các món ăn bạn đã đặt tại nhà hàng." 
            :breadcrumbs="[
                ['name' => 'Đơn hàng của tôi', 'url' => '']
            ]" 
        />

        <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-5xl pt-8 sm:pt-10">
            <!-- Filter Tabs -->
            <div class="flex items-center gap-2 overflow-x-auto pb-4 mb-6 border-b border-stone-200 scrollbar-none">
                <button type="button" 
                        onclick="showTab('all')" 
                        class="tab-button active-tab px-4 py-2 rounded-none sm:rounded-sm text-xs sm:text-sm font-semibold transition-colors cursor-pointer bg-[#C25E00] text-white shadow-2xs whitespace-nowrap">
                    Tất cả đơn
                </button>
                <button type="button" 
                        onclick="showTab('pending')" 
                        class="tab-button px-4 py-2 rounded-none sm:rounded-sm text-xs sm:text-sm font-semibold transition-colors cursor-pointer bg-white hover:bg-stone-100 text-stone-700 border border-stone-200/80 whitespace-nowrap">
                    <span>Đang chờ xử lý</span>
                    <span class="ml-1 text-xs text-amber-600 font-bold">({{ $statusCounts['pending'] ?? 0 }})</span>
                </button>
                <button type="button" 
                        onclick="showTab('confirmed')" 
                        class="tab-button px-4 py-2 rounded-none sm:rounded-sm text-xs sm:text-sm font-semibold transition-colors cursor-pointer bg-white hover:bg-stone-100 text-stone-700 border border-stone-200/80 whitespace-nowrap">
                    <span>Đã xác nhận & Nấu</span>
                    <span class="ml-1 text-xs text-orange-600 font-bold">({{ $statusCounts['confirmed'] ?? 0 }})</span>
                </button>
                <button type="button" 
                        onclick="showTab('shipped')" 
                        class="tab-button px-4 py-2 rounded-none sm:rounded-sm text-xs sm:text-sm font-semibold transition-colors cursor-pointer bg-white hover:bg-stone-100 text-stone-700 border border-stone-200/80 whitespace-nowrap">
                    <span>Đã giao thành công</span>
                    <span class="ml-1 text-xs text-emerald-600 font-bold">({{ $statusCounts['shipped'] ?? 0 }})</span>
                </button>
                <button type="button" 
                        onclick="showTab('cancelled')" 
                        class="tab-button px-4 py-2 rounded-none sm:rounded-sm text-xs sm:text-sm font-semibold transition-colors cursor-pointer bg-white hover:bg-stone-100 text-stone-700 border border-stone-200/80 whitespace-nowrap">
                    Đã hủy
                </button>
            </div>

            <!-- Orders Content Area -->
            <div class="space-y-6">
                @foreach ($ordersByStatus as $status => $orders)
                    <div class="tab-content {{ $loop->first ? '' : 'hidden' }}" id="tab-{{ $status }}">
                        @if ($orders->isEmpty())
                            <div class="bg-white rounded-none sm:rounded-sm border border-stone-200/90 p-12 text-center shadow-xs">
                                <div class="w-16 h-16 rounded-full bg-stone-50 border border-stone-200 text-stone-300 flex items-center justify-center mx-auto mb-3 text-2xl">
                                    <i class="fa-solid fa-receipt text-stone-400"></i>
                                </div>
                                <h3 class="font-serif text-lg font-bold text-stone-900 mb-1">Chưa có đơn hàng nào</h3>
                                <p class="text-stone-500 text-xs mb-5">Bạn chưa có đơn hàng nào trong mục này.</p>
                                <a href="{{ route('site.product.gridview') }}" 
                                   class="inline-flex items-center justify-center px-6 py-2.5 rounded-none sm:rounded-sm bg-stone-900 hover:bg-[#C25E00] text-white font-bold text-xs uppercase tracking-[0.15em] transition-colors shadow-xs">
                                    <span>Khám phá món ngon</span>
                                </a>
                            </div>
                        @else
                            <div class="space-y-5">
                                @foreach ($orders as $order)
                                    @php
                                        $statusConfig = match((int)$order->status) {
                                            0 => ['label' => 'Đang chờ xử lý', 'bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-200', 'step' => 1, 'desc' => 'Nhà hàng đã tiếp nhận đơn và đang chuẩn bị nguyên liệu.'],
                                            1 => ['label' => 'Đã xác nhận & Chế biến', 'bg' => 'bg-orange-50', 'text' => 'text-orange-700', 'border' => 'border-orange-200', 'step' => 2, 'desc' => 'Đầu bếp đang nấu món nóng hổi để phục vụ.'],
                                            2 => ['label' => 'Đã giao thành công', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'step' => 3, 'desc' => 'Đơn hàng đã hoàn tất. Chúc bạn ngon miệng!'],
                                            default => ['label' => 'Đã hủy', 'bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-200', 'step' => 0, 'desc' => 'Đơn hàng này đã được hủy theo yêu cầu.'],
                                        };
                                        $orderTotal = $order->orderDetails ? $order->orderDetails->sum('amount') : 0;
                                    @endphp

                                    <div class="bg-white rounded-none sm:rounded-sm border border-stone-200/90 shadow-xs overflow-hidden">
                                        <!-- Order Top Bar -->
                                        <div class="p-5 sm:p-6 border-b border-stone-100 flex flex-wrap items-center justify-between gap-3">
                                            <div class="flex items-center gap-3">
                                                <span class="font-mono font-bold text-stone-900 text-sm">#ORD-{{ $order->id }}</span>
                                                <span class="text-stone-300">•</span>
                                                <span class="text-xs text-stone-500">{{ $order->created_at ? $order->created_at->format('H:i - d/m/Y') : '' }}</span>
                                            </div>

                                            <div class="flex items-center gap-2">
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }} {{ $statusConfig['border'] }}">
                                                    <span class="w-1.5 h-1.5 rounded-full {{ (int)$order->status === 1 ? 'bg-orange-500 animate-pulse' : ((int)$order->status === 2 ? 'bg-emerald-500' : 'bg-amber-500') }}"></span>
                                                    <span>{{ $statusConfig['label'] }}</span>
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Visual Timeline Stepper (Chỉ hiển thị khi đơn không bị hủy) -->
                                        @if ((int)$order->status !== 3)
                                            <div class="px-5 sm:px-8 py-4 bg-[#FAF8F5] border-b border-stone-100">
                                                <div class="grid grid-cols-3 gap-2 text-center text-xs">
                                                    <!-- Step 1 -->
                                                    <div class="flex flex-col items-center">
                                                        <div class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-xs mb-1 {{ $statusConfig['step'] >= 1 ? 'bg-[#C25E00] text-white' : 'bg-stone-200 text-stone-500' }}">
                                                            1
                                                        </div>
                                                        <span class="font-medium {{ $statusConfig['step'] >= 1 ? 'text-stone-900 font-bold' : 'text-stone-400' }}">Tiếp nhận</span>
                                                    </div>
                                                    <!-- Step 2 -->
                                                    <div class="flex flex-col items-center">
                                                        <div class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-xs mb-1 {{ $statusConfig['step'] >= 2 ? 'bg-[#C25E00] text-white' : 'bg-stone-200 text-stone-500' }}">
                                                            2
                                                        </div>
                                                        <span class="font-medium {{ $statusConfig['step'] >= 2 ? 'text-stone-900 font-bold' : 'text-stone-400' }}">Đang nấu</span>
                                                    </div>
                                                    <!-- Step 3 -->
                                                    <div class="flex flex-col items-center">
                                                        <div class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-xs mb-1 {{ $statusConfig['step'] >= 3 ? 'bg-emerald-600 text-white' : 'bg-stone-200 text-stone-500' }}">
                                                            ✓
                                                        </div>
                                                        <span class="font-medium {{ $statusConfig['step'] >= 3 ? 'text-stone-900 font-bold' : 'text-stone-400' }}">Hoàn tất</span>
                                                    </div>
                                                </div>
                                                <p class="text-center text-[11px] text-stone-500 mt-2 italic">{{ $statusConfig['desc'] }}</p>
                                            </div>
                                        @endif

                                        <!-- Order Items Preview -->
                                        <div class="p-5 sm:p-6 divide-y divide-stone-100">
                                            @foreach ($order->orderDetails as $detail)
                                                <div class="py-3 first:pt-0 last:pb-0 flex items-center justify-between gap-4">
                                                    <div class="flex items-center gap-3.5 min-w-0">
                                                        <div class="w-12 h-12 rounded-none sm:rounded-sm bg-stone-100 border border-stone-200/80 overflow-hidden shrink-0">
                                                            <img src="{{ asset('images/product/' . ($detail->product?->image ?? 'default-product.png')) }}" 
                                                                 alt="{{ $detail->product?->name }}" 
                                                                 class="w-full h-full object-cover">
                                                        </div>
                                                        <div class="min-w-0">
                                                            <h4 class="font-bold text-stone-900 text-xs sm:text-sm truncate">
                                                                {{ $detail->product?->name ?? 'Món ăn' }}
                                                            </h4>
                                                            <span class="text-[11px] text-stone-500">
                                                                {{ number_format($detail->price, 0, ',', '.') }}₫ × {{ $detail->qty }}
                                                            </span>
                                                        </div>
                                                    </div>

                                                    <span class="font-bold text-stone-900 text-xs sm:text-sm shrink-0">
                                                        {{ number_format($detail->amount, 0, ',', '.') }}₫
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>

                                        <!-- Order Footer: Total & Actions -->
                                        <div class="p-5 sm:p-6 bg-[#FAF8F5] border-t border-stone-100 flex flex-wrap items-center justify-between gap-4">
                                            <div>
                                                <span class="text-xs text-stone-500">Tổng thanh toán:</span>
                                                <span class="font-serif text-lg sm:text-xl font-bold text-[#C25E00] ml-1.5">
                                                    {{ number_format($orderTotal, 0, ',', '.') }}₫
                                                </span>
                                            </div>

                                            <div class="flex items-center gap-2.5">
                                                @if ($order->status == 2 || $order->status == 3)
                                                    <a href="{{ route('site.orders.reorder', $order->id) }}" 
                                                       class="px-4 py-2 rounded-none sm:rounded-sm bg-stone-100 hover:bg-stone-200 text-stone-800 text-xs font-semibold transition-colors">
                                                        <i class="fa-solid fa-rotate-right mr-1 text-stone-500"></i>
                                                        <span>Đặt lại món</span>
                                                    </a>
                                                @endif
                                                <a href="{{ route('site.orders.detail', $order->id) }}" 
                                                   class="px-5 py-2.5 rounded-none sm:rounded-sm bg-stone-900 hover:bg-[#C25E00] text-white text-xs font-bold uppercase tracking-wider transition-colors shadow-2xs">
                                                    <span>Xem chi tiết</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </main>

    <script>
        function showTab(status) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
            const target = document.getElementById('tab-' + status);
            if (target) target.classList.remove('hidden');

            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('bg-[#C25E00]', 'text-white', 'shadow-2xs');
                btn.classList.add('bg-white', 'text-stone-700', 'border', 'border-stone-200/80');
            });
            event.target.closest('.tab-button').classList.remove('bg-white', 'text-stone-700', 'border', 'border-stone-200/80');
            event.target.closest('.tab-button').classList.add('bg-[#C25E00]', 'text-white', 'shadow-2xs');
        }
    </script>
</x-layout-frontend>
