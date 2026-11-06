<x-layout-frontend>
    <main class="bg-gradient-to-b from-[#FAF8F5] via-stone-50 to-stone-100 min-h-screen pb-16">
        <x-page-hero 
            title="Thanh Toán VietQR Tại Bàn" 
            subtitle="Quý khách mở ứng dụng ngân hàng bất kỳ để quét mã QR thanh toán tức thì mà không cần chờ thu ngân in hóa đơn giấy." 
            badge="VIETQR 24/7"
            :breadcrumbs="[
                ['name' => 'Bàn ăn', 'url' => route('site.table.scan', $table->id)],
                ['name' => $table->name, 'url' => route('site.table.scan', $table->id)],
                ['name' => 'Thanh toán QR', 'url' => '']
            ]" 
        />

        <div class="container mx-auto px-4 max-w-xl pt-6 sm:pt-10">
            
            <!-- THẺ CHÍNH THANH TOÁN VIETQR -->
            <div class="bg-white rounded-xl sm:rounded-2xl border border-stone-200/90 shadow-xl overflow-hidden mb-6">
                
                <!-- Header Đơn Hàng & Bàn -->
                <div class="p-5 sm:p-6 bg-gradient-to-r from-stone-900 to-stone-800 text-white flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-extrabold text-orange-400 uppercase tracking-widest block">Thanh Toán Bàn Ăn</span>
                        <h1 class="text-xl sm:text-2xl font-serif font-black text-white">
                            {{ $table->name }} <span class="text-stone-400 font-sans text-sm font-normal">({{ $table->floor ? $table->floor->name : 'Sảnh' }})</span>
                        </h1>
                        <p class="text-xs text-stone-300 mt-0.5 font-mono">
                            Mã đơn hàng: #{{ $order->id }}
                        </p>
                    </div>

                    <div class="text-right">
                        <span class="px-2.5 py-1 rounded bg-amber-400/20 text-amber-300 border border-amber-400/30 text-[11px] font-bold block mb-1">
                            Giảm 10% tại bàn
                        </span>
                        <span class="text-[11px] text-stone-400">Napas 247</span>
                    </div>
                </div>

                <!-- KHU VỰC QUÉT MÃ VIETQR TRỌNG TÂM -->
                <div class="p-6 sm:p-8 text-center bg-stone-50/50 border-b border-stone-200/80">
                    <!-- Trạng thái chờ Webhook thời gian thực -->
                    <div id="qrLoadingStatusBadge" class="mb-3">
                        <div class="inline-flex items-center gap-2 px-3 py-1 bg-amber-50 text-amber-800 text-xs font-semibold rounded-full border border-amber-200 animate-pulse">
                            <span class="w-2 h-2 rounded-full bg-[#F47716]"></span>
                            <span>Hệ thống đang tự động lắng nghe giao dịch chuyển khoản...</span>
                        </div>
                    </div>

                    <div class="inline-block p-4 sm:p-5 bg-white rounded-2xl border-2 border-orange-500/80 shadow-lg relative group overflow-hidden">
                        <!-- Ảnh mã QR động từ VietQR -->
                        <img src="{{ $qrUrl }}" 
                             alt="Mã VietQR thanh toán" 
                             class="w-64 h-64 sm:w-72 sm:h-72 object-contain mx-auto rounded-lg">
                        
                        <div class="mt-2 text-[11px] font-bold text-stone-500 text-center">
                            <span>Quét bằng app ngân hàng bất kỳ (Napas 247)</span>
                        </div>

                        <!-- Overlay hiển thị khi Webhook nhận được tiền thành công -->
                        <div id="tablePaymentSuccessOverlay" class="hidden absolute inset-0 bg-white/95 rounded-2xl flex flex-col items-center justify-center p-6 text-center z-20">
                            <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-3xl mb-3 animate-bounce">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>
                            <h3 class="text-lg font-black text-stone-900">Thanh Toán Thành Công!</h3>
                            <p class="text-xs text-stone-600 mt-1">Đã nhận được tiền chuyển khoản từ ngân hàng.</p>
                            <span class="text-xs text-emerald-600 font-bold mt-2">Đang chuyển sang hóa đơn sau 1.5 giây...</span>
                        </div>
                    </div>

                    <!-- Hiển thị Tổng Tiền Cần Thanh Toán Nổi Bật -->
                    <div class="mt-5">
                        <span class="text-xs uppercase font-extrabold tracking-wider text-stone-500 block mb-1">Số Tiền Cần Chuyển</span>
                        <div class="text-3xl sm:text-4xl font-serif font-black text-[#C25E00] tracking-tight">
                            {{ number_format($finalAmount, 0, ',', '.') }}₫
                        </div>
                        @if ($discountAmount > 0)
                            <p class="text-xs text-emerald-700 font-semibold mt-1">
                                (Đã tiết kiệm {{ number_format($discountAmount, 0, ',', '.') }}₫ nhờ ưu đãi gọi tại bàn)
                            </p>
                        @endif
                    </div>
                </div>

                <!-- THÔNG TIN CHUYỂN KHOẢN ĐỐI SOÁT (Có nút Copy tiện lợi) -->
                <div class="p-5 sm:p-6 space-y-3 text-xs bg-white">
                    <h3 class="font-bold text-stone-900 uppercase tracking-wider text-[11px] mb-2">
                        Thông tin tài khoản thụ hưởng
                    </h3>

                    <!-- Tên Ngân Hàng -->
                    <div class="p-2.5 rounded-lg bg-stone-50 border border-stone-200 flex items-center justify-between">
                        <div>
                            <span class="text-stone-400 block text-[10px]">Ngân hàng:</span>
                            <span class="font-bold text-stone-900 text-xs sm:text-sm">{{ $bankInfo['bank_name'] }}</span>
                        </div>
                        <span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded font-bold text-[10px]">{{ $bankInfo['bank_id'] }}</span>
                    </div>

                    <!-- Số Tài Khoản -->
                    <div class="p-2.5 rounded-lg bg-stone-50 border border-stone-200 flex items-center justify-between">
                        <div>
                            <span class="text-stone-400 block text-[10px]">Số tài khoản:</span>
                            <span class="font-mono font-black text-stone-900 text-sm sm:text-base tracking-wider" id="bankAccountNo">{{ $bankInfo['account_no'] }}</span>
                        </div>
                        <button type="button" onclick="copyToClipboard('{{ $bankInfo['account_no'] }}', 'Đã chép số tài khoản!')" class="px-3 py-1 bg-stone-200 hover:bg-stone-300 text-stone-800 rounded text-xs font-bold transition-colors">
                            Chép
                        </button>
                    </div>

                    <!-- Tên Chủ Tài Khoản -->
                    <div class="p-2.5 rounded-lg bg-stone-50 border border-stone-200 flex items-center justify-between">
                        <div>
                            <span class="text-stone-400 block text-[10px]">Chủ tài khoản:</span>
                            <span class="font-bold text-stone-900 text-xs sm:text-sm uppercase">{{ $bankInfo['account_name'] }}</span>
                        </div>
                    </div>

                    <!-- Nội Dung Chuyển Khoản -->
                    <div class="p-2.5 rounded-lg bg-amber-50 border border-amber-200 flex items-center justify-between">
                        <div>
                            <span class="text-amber-800 block text-[10px] font-bold">Nội dung chuyển khoản (bắt buộc):</span>
                            <span class="font-mono font-black text-amber-900 text-sm tracking-widest" id="bankMemo">{{ $memo }}</span>
                        </div>
                        <button type="button" onclick="copyToClipboard('{{ $memo }}', 'Đã chép nội dung chuyển khoản!')" class="px-3 py-1 bg-amber-200 hover:bg-amber-300 text-amber-900 rounded text-xs font-bold transition-colors">
                            Chép
                        </button>
                    </div>
                </div>

                <!-- NÚT XÁC NHẬN THANH TOÁN CHÍNH -->
                <div class="p-5 sm:p-6 bg-stone-50 border-t border-stone-200 space-y-3">
                    <form action="{{ route('site.table.payment.confirm', $table->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="order_id" value="{{ $order->id }}">
                        <input type="hidden" name="payment_method" value="VietQR">
                        
                        <button type="submit" 
                                class="w-full py-4 px-6 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white font-bold text-sm uppercase tracking-wider rounded-lg shadow-lg shadow-emerald-600/30 transition-all flex items-center justify-center cursor-pointer">
                            <span>Tôi Đã Chuyển Khoản Xong</span>
                        </button>
                    </form>

                    <div class="flex items-center justify-between pt-1">
                        <a href="{{ route('site.product.gridview') }}" class="text-xs text-stone-600 hover:text-orange-600 font-semibold">
                            Quay lại thực đơn
                        </a>

                        <button type="button" onclick="callCashier({{ $table->id }})" class="text-xs text-stone-600 hover:text-orange-600 font-semibold cursor-pointer">
                            Gọi nhân viên thu tiền mặt
                        </button>
                    </div>
                </div>

            </div>

            <!-- Tóm Tắt Món Ăn Trong Hóa Đơn -->
            <div class="bg-white rounded-xl border border-stone-200/90 shadow-sm p-5 text-xs">
                <h4 class="font-bold text-stone-800 uppercase tracking-wider text-xs mb-3 pb-2 border-b border-stone-100 flex items-center justify-between">
                    <span>Món ăn trong hóa đơn ({{ $orderDetails->count() }} món)</span>
                    <span class="text-stone-400 font-mono">Đợt phục vụ #{{ $order->order_turn ?? 1 }}</span>
                </h4>

                <div class="divide-y divide-stone-100 max-h-56 overflow-y-auto pr-1">
                    @foreach ($orderDetails as $item)
                        <div class="py-2.5 flex items-center justify-between">
                            <div class="flex-1 min-w-0 pr-3">
                                <span class="font-bold text-stone-900 block truncate">{{ $item->product->name ?? 'Món ăn' }}</span>
                                <span class="text-[11px] text-stone-400">{{ number_format($item->price, 0, ',', '.') }}₫ × {{ $item->qty }}</span>
                            </div>
                            <span class="font-extrabold text-stone-900 shrink-0">
                                {{ number_format($item->amount, 0, ',', '.') }}₫
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </main>

    <script>
        function copyToClipboard(text, successMsg) {
            navigator.clipboard.writeText(text).then(function() {
                alert(successMsg || 'Đã sao chép vào bộ nhớ tạm!');
            }).catch(function(err) {
                console.error('Lỗi chép:', err);
            });
        }

        function callCashier(tableId) {
            fetch('{{ route("site.service-call.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    table_id: tableId,
                    call_type: 'payment',
                    note: 'Khách yêu cầu thanh toán tiền mặt tại bàn'
                })
            })
            .then(res => res.json())
            .then(data => {
                alert('Đã báo nhân viên thu ngân! Nhân viên sẽ mang hóa đơn và tiền thối lại bàn cho quý khách.');
            });
        }

        // Lắng nghe trạng thái chuyển khoản thời gian thực (Polling Webhook Sepay / Casso / Ngân hàng)
        document.addEventListener('DOMContentLoaded', function() {
            const orderId = {{ $order->id }};
            const checkStatusUrl = '{{ route("api.payment.check-status", $order->id) }}';
            const billUrl = '{{ route("site.table.bill", $table->id) }}';
            let isPaidCompleted = false;

            const checkInterval = setInterval(function() {
                if (isPaidCompleted) return;

                fetch(checkStatusUrl, {
                    headers: { 'Accept': 'application/json' }
                })
                .then(res => res.json())
                .then(data => {
                    if (data && data.success && data.is_paid) {
                        isPaidCompleted = true;
                        clearInterval(checkInterval);

                        const overlay = document.getElementById('tablePaymentSuccessOverlay');
                        const statusBadge = document.getElementById('qrLoadingStatusBadge');
                        if (overlay) overlay.classList.remove('hidden');
                        if (statusBadge) statusBadge.classList.add('hidden');

                        setTimeout(function() {
                            window.location.href = billUrl;
                        }, 1500);
                    }
                })
                .catch(err => console.log('Checking table payment status:', err));
            }, 3000);
        });
    </script>
</x-layout-frontend>
