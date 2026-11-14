<x-layout-frontend>
    <main class="bg-gradient-to-b from-[#FAF8F5] via-stone-50 to-stone-100 min-h-screen pb-16">
        <x-page-hero 
            title="Hóa Đơn Bàn Ăn Điện Tử" 
            subtitle="Theo dõi chi tiết các món ăn đã gọi, trạng thái chuẩn bị của nhà bếp và thanh toán trực tiếp qua mã VietQR." 
            badge="CIVILIZE E-RECEIPT"
            :breadcrumbs="[
                ['name' => 'Bàn ăn', 'url' => route('site.table.scan', $table->id)],
                ['name' => $table->name, 'url' => route('site.table.scan', $table->id)],
                ['name' => 'Hóa đơn điện tử', 'url' => '']
            ]" 
        />

        <div class="container mx-auto px-4 max-w-xl pt-6 sm:pt-10">

            <!-- THẺ HÓA ĐƠN ĐIỆN TỬ (Giao diện biên lai tối giản, trang nhã) -->
            <div class="bg-white rounded-xl sm:rounded-2xl border border-stone-200 shadow-md overflow-hidden mb-6">
                
                <!-- Header Bill -->
                <div class="p-6 bg-white text-stone-900 text-center border-b border-dashed border-stone-300 relative">
                    <h2 class="text-xs uppercase tracking-[0.2em] text-stone-500 font-bold">NHÀ HÀNG CIVILIZE PHÚC THỌ</h2>
                    <h1 class="text-2xl font-bold text-stone-900 mt-1">{{ $table->name }}</h1>
                    <p class="text-xs text-stone-500 mt-1">
                        {{ $table->floor ? $table->floor->name : 'Sảnh' }} • Hóa đơn #{{ $order->id }} • {{ $order->created_at ? $order->created_at->format('H:i - d/m/Y') : date('H:i - d/m/Y') }}
                    </p>

                    <!-- Dấu Mộc Đã Thanh Toán (nếu status = 1 hoặc 2) -->
                    @if ($order->status == 1 || $order->status == 2)
                        <div class="mt-3.5 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-stone-100 text-stone-700 border border-stone-200 text-xs font-semibold">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span>ĐÃ THANH TOÁN ({{ $order->payment_method ?: 'VietQR' }})</span>
                        </div>
                    @else
                        <div class="mt-3.5 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-stone-100 text-stone-700 border border-stone-200 text-xs font-semibold">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            <span>CHƯA THANH TOÁN</span>
                        </div>
                    @endif
                </div>

                <!-- Danh Sách Chi Tiết Món Ăn -->
                <div class="p-5 sm:p-6 divide-y divide-stone-100 text-xs">
                    <div class="pb-2.5 flex items-center justify-between text-stone-400 font-bold uppercase tracking-wider text-[11px]">
                        <span>Món ăn / Đồ uống</span>
                        <div class="flex items-center gap-6">
                            <span>Trạng thái</span>
                            <span class="w-20 text-right">Thành tiền</span>
                        </div>
                    </div>

                    @foreach ($orderDetails as $item)
                        <div class="py-3 flex items-center justify-between gap-2">
                            <div class="min-w-0 flex-1 pr-2">
                                <span class="font-bold text-stone-900 block truncate text-xs sm:text-sm">
                                    {{ $item->product->name ?? 'Món đã chọn' }}
                                </span>
                                <span class="text-[11px] text-stone-500">
                                    {{ number_format($item->price, 0, ',', '.') }}₫ × {{ $item->qty }}
                                </span>
                            </div>

                            <div class="flex items-center gap-3 shrink-0">
                                <!-- Badge Trạng thái món ăn: Tối giản, không sặc sỡ -->
                                <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-stone-100 text-stone-700 border border-stone-200">
                                    {{ $item->status == 'completed' ? 'Đã lên bàn' : ($item->status == 'cooking' ? 'Đang nấu' : 'Chờ bếp') }}
                                </span>

                                <span class="font-bold text-stone-900 w-20 text-right text-xs sm:text-sm">
                                    {{ number_format($item->amount, 0, ',', '.') }}₫
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Bảng Tính Tiền -->
                <div class="p-5 sm:p-6 bg-stone-50/70 border-t border-stone-200 space-y-2 text-xs">
                    <div class="flex items-center justify-between text-stone-600">
                        <span>Tạm tính các món:</span>
                        <span class="font-bold text-stone-800">{{ number_format($subtotal, 0, ',', '.') }}₫</span>
                    </div>

                    <div class="flex items-center justify-between text-stone-600">
                        <span>Ưu đãi quét QR tại bàn (-10%):</span>
                        <span class="font-bold text-stone-800">-{{ number_format($discountAmount, 0, ',', '.') }}₫</span>
                    </div>

                    <div class="pt-3 border-t border-stone-200 flex items-center justify-between text-sm sm:text-base">
                        <span class="font-bold text-stone-900">Tổng thanh toán:</span>
                        <span class="text-xl sm:text-2xl font-bold text-stone-900">
                            {{ number_format($finalAmount, 0, ',', '.') }}₫
                        </span>
                    </div>
                </div>

                <!-- NẾU CHƯA THANH TOÁN: HIỂN THỊ KHUNG QUÉT MÃ VIETQR NHANH -->
                @if ($order->status == 0)
                    <div class="p-6 bg-stone-50 border-t border-stone-200 text-center">
                        <span class="text-xs font-bold text-stone-800 block mb-3">
                            Quét mã VietQR chuyển khoản thanh toán hóa đơn:
                        </span>
                        
                        <div class="inline-block p-3 bg-white rounded-xl border border-stone-200 shadow-xs mb-3">
                            <img src="{{ $qrUrl }}" alt="VietQR" class="w-48 h-48 mx-auto object-contain">
                        </div>

                        <div class="text-[11px] text-stone-600 mb-4 font-mono">
                            STK: <strong>{{ $bankInfo['account_no'] }}</strong> ({{ $bankInfo['bank_id'] }}) • ND: <strong>{{ $memo }}</strong>
                        </div>

                        <form action="{{ route('site.table.payment.confirm', $table->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="order_id" value="{{ $order->id }}">
                            <input type="hidden" name="payment_method" value="VietQR">
                            <button type="submit" class="w-full py-3 px-4 bg-stone-900 hover:bg-stone-800 text-white font-bold text-xs uppercase tracking-wider rounded-lg transition-colors flex items-center justify-center cursor-pointer">
                                <span>Xác nhận đã chuyển khoản thành công</span>
                            </button>
                        </form>
                    </div>
                @endif

                <!-- Footer Nút Bấm -->
                <div class="p-4 bg-stone-50 border-t border-stone-200 flex items-center justify-between gap-3 text-xs flex-wrap">
                    <div class="flex items-center gap-2">
                        <a href="{{ route('site.product.gridview') }}" class="py-2 px-3.5 rounded bg-white hover:bg-stone-100 border border-stone-300 font-semibold text-stone-800 transition-colors">
                            Gọi thêm món
                        </a>
                        <a href="{{ route('site.table.scan', $table->id) }}" class="py-2 px-3.5 rounded bg-stone-100 hover:bg-stone-200 border border-stone-200 font-semibold text-stone-700 transition-colors">
                            Quay lại bàn ăn
                        </a>
                    </div>

                    @if ($order->status == 1 || $order->status == 2)
                        <a href="{{ route('site.table.end') }}" class="py-2 px-3.5 rounded bg-stone-900 hover:bg-stone-800 text-white font-bold uppercase tracking-wider text-[11px] transition-colors">
                            Hoàn tất & Rời bàn
                        </a>
                    @endif
                </div>

            </div>

        </div>
    </main>
</x-layout-frontend>
