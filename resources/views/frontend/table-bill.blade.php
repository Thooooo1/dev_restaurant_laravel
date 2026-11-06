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
            
            @if(session('success'))
                <div class="mb-5 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs shadow-sm">
                    <div class="font-bold leading-relaxed">{{ session('success') }}</div>
                </div>
            @endif

            <!-- THẺ HÓA ĐƠN ĐIỆN TỬ (Mô phỏng tờ bill thanh lịch) -->
            <div class="bg-white rounded-xl sm:rounded-2xl border border-stone-200/90 shadow-xl overflow-hidden mb-6">
                
                <!-- Header Bill -->
                <div class="p-6 bg-stone-900 text-white text-center border-b border-stone-800 relative">
                    <h2 class="text-xs uppercase tracking-[0.25em] text-orange-400 font-extrabold">NHÀ HÀNG CIVILIZE PHÚC THỌ</h2>
                    <h1 class="text-2xl font-serif font-black text-white mt-1">{{ $table->name }}</h1>
                    <p class="text-xs text-stone-400 mt-0.5">
                        {{ $table->floor ? $table->floor->name : 'Sảnh' }} • Hóa đơn #{{ $order->id }} • {{ $order->created_at ? $order->created_at->format('H:i - d/m/Y') : date('H:i - d/m/Y') }}
                    </p>

                    <!-- Dấu Mộc Đã Thanh Toán (nếu status = 1 hoặc 2) -->
                    @if ($order->status == 1 || $order->status == 2)
                        <div class="mt-4 inline-flex items-center px-4 py-1.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 text-xs font-black uppercase tracking-wider">
                            <span>ĐÃ THANH TOÁN ({{ $order->payment_method ?: 'VietQR' }})</span>
                        </div>
                    @else
                        <div class="mt-4 inline-flex items-center px-4 py-1.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/40 text-xs font-black uppercase tracking-wider">
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
                                <!-- Badge Trạng thái món ăn -->
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $item->status == 'completed' ? 'bg-emerald-100 text-emerald-700' : ($item->status == 'cooking' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700') }}">
                                    {{ $item->status == 'completed' ? 'Đã lên bàn' : ($item->status == 'cooking' ? 'Đang nấu' : 'Chờ bếp') }}
                                </span>

                                <span class="font-extrabold text-stone-900 w-20 text-right text-xs sm:text-sm">
                                    {{ number_format($item->amount, 0, ',', '.') }}₫
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Bảng Tính Tiền -->
                <div class="p-5 sm:p-6 bg-stone-50/80 border-t border-stone-200 space-y-2 text-xs">
                    <div class="flex items-center justify-between text-stone-600">
                        <span>Tạm tính các món:</span>
                        <span class="font-bold text-stone-800">{{ number_format($subtotal, 0, ',', '.') }}₫</span>
                    </div>

                    <div class="flex items-center justify-between text-emerald-700">
                        <span>Ưu đãi quét QR tại bàn (-10%):</span>
                        <span class="font-bold">-{{ number_format($discountAmount, 0, ',', '.') }}₫</span>
                    </div>

                    <div class="pt-3 border-t border-stone-200 flex items-center justify-between text-sm sm:text-base">
                        <span class="font-bold text-stone-900">Tổng thanh toán:</span>
                        <span class="text-xl sm:text-2xl font-black text-[#C25E00]">
                            {{ number_format($finalAmount, 0, ',', '.') }}₫
                        </span>
                    </div>
                </div>

                <!-- NẾU CHƯA THANH TOÁN: HIỂN THỊ KHUNG QUÉT MÃ VIETQR NHANH -->
                @if ($order->status == 0)
                    <div class="p-6 bg-amber-50/50 border-t border-amber-200/80 text-center">
                        <span class="text-xs font-bold text-stone-800 block mb-3">
                            Quét mã VietQR chuyển khoản thanh toán hóa đơn:
                        </span>
                        
                        <div class="inline-block p-3 bg-white rounded-xl border border-orange-300 shadow-md mb-3">
                            <img src="{{ $qrUrl }}" alt="VietQR" class="w-48 h-48 mx-auto object-contain">
                        </div>

                        <div class="text-[11px] text-stone-600 mb-4 font-mono">
                            STK: <strong>{{ $bankInfo['account_no'] }}</strong> ({{ $bankInfo['bank_id'] }}) • ND: <strong>{{ $memo }}</strong>
                        </div>

                        <form action="{{ route('site.table.payment.confirm', $table->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="order_id" value="{{ $order->id }}">
                            <input type="hidden" name="payment_method" value="VietQR">
                            <button type="submit" class="w-full py-3.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs uppercase tracking-wider rounded-lg shadow-md transition-colors flex items-center justify-center cursor-pointer">
                                <span>Xác nhận đã chuyển khoản thành công</span>
                            </button>
                        </form>
                    </div>
                @endif

                <!-- Footer Nút Bấm -->
                <div class="p-4 bg-stone-100/70 border-t border-stone-200 flex items-center justify-between gap-3 text-xs flex-wrap">
                    <div class="flex items-center gap-2">
                        <a href="{{ route('site.product.gridview') }}" class="py-2 px-3 rounded bg-white hover:bg-stone-200 border border-stone-300 font-bold text-stone-800 transition-colors">
                            Gọi thêm món
                        </a>
                        <a href="{{ route('site.table.scan', $table->id) }}" class="text-stone-500 hover:text-stone-800 font-semibold underline">
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
