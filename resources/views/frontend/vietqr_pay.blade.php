<x-layout-frontend>
    <main class="bg-[#FAF8F5] min-h-screen py-10 sm:py-14">
        <div class="container mx-auto px-4 max-w-4xl">
            <!-- Header Bar -->
            <div class="text-center mb-8">
                <span class="inline-flex items-center px-3 py-1 bg-orange-100 text-[#C25E00] text-xs font-bold uppercase tracking-widest rounded-full mb-2">
                    Thanh toán chuyển khoản tự động
                </span>
                <h1 class="text-2xl sm:text-3xl font-black font-serif text-stone-900 tracking-tight">
                    Quét Mã VietQR Thanh Toán
                </h1>
                <p class="text-xs sm:text-sm text-stone-600 mt-1 max-w-md mx-auto">
                    Mở ứng dụng ngân hàng bất kỳ (MB, VCB, Techcom, VietinBank...) hoặc ví điện tử để quét mã thanh toán.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-start">
                <!-- Cột trái (7 cols): Mã VietQR động & Countdown Timer -->
                <div class="md:col-span-7 bg-white p-6 sm:p-8 rounded-2xl shadow-lg border border-stone-200/90 text-center relative overflow-hidden flex flex-col items-center justify-between">
                    <div class="w-full">
                        <p class="text-xs font-semibold text-stone-700 leading-relaxed mb-4">
                            Quét mã QR dưới đây bằng ứng dụng ngân hàng hoặc ví điện tử để hoàn tất thanh toán.
                        </p>

                        <!-- Khung ảnh QR trắng tinh gọn hiện đại -->
                        <div class="relative inline-block p-3 bg-white rounded-2xl border border-stone-200 shadow-md group">
                            <img src="{{ $qrUrl }}" 
                                 alt="Mã VietQR thanh toán" 
                                 id="vietqrImage"
                                 class="w-64 h-64 sm:w-72 sm:h-72 object-contain rounded-xl mx-auto">
                            
                            <!-- Overlay khi thành công -->
                            <div id="paymentSuccessOverlay" class="hidden absolute inset-0 bg-white/95 rounded-2xl flex flex-col items-center justify-center p-6 text-center z-10">
                                <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-3xl mb-3 animate-bounce">
                                    <i class="fa-solid fa-circle-check"></i>
                                </div>
                                <h3 class="text-lg font-black text-stone-900">Thanh Toán Thành Công!</h3>
                                <p class="text-xs text-stone-600 mt-1">Hệ thống đã tự động nhận tiền qua Webhook.</p>
                                <span class="text-xs text-emerald-600 font-bold mt-2">Đang chuyển trang sau 2 giây...</span>
                            </div>
                        </div>
                    </div>

                    <!-- Countdown Timer & Lắng nghe tự động -->
                    <div class="w-full mt-6 pt-4 border-t border-stone-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                        <div class="flex items-center gap-1.5 text-stone-600 font-medium">
                            <i class="fa-regular fa-clock text-blue-600"></i>
                            <span>Mã QR có hiệu lực:</span>
                            <span id="vietqrCountdown" class="font-mono font-bold text-blue-600 bg-blue-50 px-2.5 py-0.5 rounded border border-blue-200">03:00</span>
                        </div>

                        <div id="qrLoadingState" class="inline-flex items-center gap-2 px-3 py-1 bg-amber-50 text-amber-700 text-xs font-semibold rounded-full border border-amber-200 animate-pulse">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            <span>Đang chờ chuyển khoản...</span>
                        </div>
                    </div>

                    <!-- Nút giả lập Webhook cho dev test -->
                    @if(config('payment.mode') === 'demo')
                        <div class="w-full mt-4 pt-3 border-t border-stone-100">
                            <button type="button" 
                                    id="simulateWebhookBtn"
                                    class="w-full py-2.5 px-4 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 text-xs font-bold rounded-xl transition-colors flex items-center justify-center gap-1.5 cursor-pointer">
                                <i class="fa-solid fa-bolt text-amber-600"></i>
                                <span>[Demo] Giả lập Webhook đã nhận tiền</span>
                            </button>
                        </div>
                    @endif
                </div>

                <!-- Cột phải (5 cols): Tổng Tiền & Thông tin chi tiết (Kiểu Checkout) -->
                <div class="md:col-span-5 space-y-4">
                    <!-- Chi tiết thanh toán -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-stone-200/90 space-y-4">
                        <!-- Card Tổng tiền nổi bật (như ảnh thiết kế) -->
                        <div class="p-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl shadow-sm flex items-center justify-between">
                            <div>
                                <span class="text-[11px] uppercase tracking-wider text-blue-100 font-medium block">Tổng thanh toán</span>
                                <span class="text-xs text-blue-200">Đơn hàng #{{ $order->id }}</span>
                            </div>
                            <div class="text-right">
                                <span class="text-2xl font-black font-mono tracking-tight">
                                    {{ number_format($order->total_price, 0, ',', '.') }}đ
                                </span>
                            </div>
                        </div>

                        <!-- Phương thức thanh toán -->
                        <div class="p-3 bg-gray-50 rounded-xl border border-gray-200/80 flex items-center justify-between text-xs">
                            <span class="text-gray-500">Phương thức thanh toán:</span>
                            <span class="font-bold text-gray-800 flex items-center gap-1.5">
                                <i class="fa-solid fa-qrcode text-blue-600"></i> VietQR (Napas 247)
                            </span>
                        </div>

                        <!-- Nội dung chuyển khoản (BẮT BUỘC ĐỂ ĐỐI SOÁT WEBHOOK) -->
                        <div class="p-3.5 bg-amber-50/70 border border-amber-300 rounded-xl flex items-center justify-between">
                            <div>
                                <span class="text-[10px] font-bold text-amber-800 uppercase block">Nội dung chuyển khoản (bắt buộc)</span>
                                <span class="text-base font-black text-[#C25E00] tracking-wider font-mono" id="memoText">{{ $memo }}</span>
                            </div>
                            <button type="button" onclick="copyText('{{ $memo }}', 'Đã sao chép nội dung chuyển khoản!')" class="px-3 py-1.5 bg-[#C25E00] hover:bg-[#A34E00] text-white rounded-lg text-xs font-bold transition-colors shadow-xs cursor-pointer">
                                Sao chép
                            </button>
                        </div>

                        <!-- Thông tin ngân hàng -->
                        <div class="space-y-2.5 text-xs text-stone-700 pt-1 border-t border-stone-100">
                            <div class="flex items-center justify-between py-1.5 border-b border-stone-100">
                                <span class="text-stone-500">Ngân hàng thụ hưởng:</span>
                                <span class="font-bold text-stone-900">{{ $bankInfo['bank_name'] }}</span>
                            </div>
                            <div class="flex items-center justify-between py-1.5 border-b border-stone-100">
                                <span class="text-stone-500">Số tài khoản:</span>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-bold text-stone-900 text-sm">{{ $bankInfo['account_no'] }}</span>
                                    <button type="button" onclick="copyText('{{ $bankInfo['account_no'] }}', 'Đã sao chép số tài khoản!')" class="text-[#C25E00] hover:underline font-bold text-[11px] cursor-pointer">
                                        Sao chép
                                    </button>
                                </div>
                            </div>
                            <div class="flex items-center justify-between py-1.5">
                                <span class="text-stone-500">Chủ tài khoản:</span>
                                <span class="font-bold text-stone-900 uppercase">{{ $bankInfo['account_name'] }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Nút quay lại hoặc hỗ trợ -->
                    <div class="flex items-center justify-between text-xs pt-1 px-1">
                        <a href="{{ route('site.cart') }}" class="text-stone-500 hover:text-stone-900 font-medium">
                            <i class="fa-solid fa-arrow-left mr-1"></i> Quay lại giỏ hàng
                        </a>
                        <span class="text-stone-400">Hotline hỗ trợ: <strong class="text-[#C25E00]">0338 990 538</strong></span>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Toast copy -->
    <div id="copyToast" class="fixed bottom-6 left-1/2 -translate-x-1/2 bg-stone-900 text-white px-4 py-2 rounded-full text-xs font-semibold shadow-xl hidden z-50 transition-opacity">
        Đã sao chép!
    </div>

    <script>
        function copyText(text, message) {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(() => {
                    const toast = document.getElementById('copyToast');
                    toast.textContent = message || 'Đã sao chép!';
                    toast.classList.remove('hidden');
                    setTimeout(() => toast.classList.add('hidden'), 2000);
                });
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const orderId = {{ $order->id }};
            const checkStatusUrl = '{{ route("api.payment.check-status", $order->id) }}';
            const thanksUrl = '{{ route("site.thanks") }}';
            let isCompleted = false;

            // Countdown timer đếm ngược 3 phút (180s)
            let countdownSeconds = 180;
            const countdownEl = document.getElementById('vietqrCountdown');
            const countdownTimer = setInterval(function() {
                countdownSeconds--;
                if (countdownSeconds <= 0) {
                    clearInterval(countdownTimer);
                    if (countdownEl) countdownEl.textContent = 'Hết hạn';
                } else {
                    const mins = Math.floor(countdownSeconds / 60).toString().padStart(2, '0');
                    const secs = (countdownSeconds % 60).toString().padStart(2, '0');
                    if (countdownEl) countdownEl.textContent = `${mins}:${secs}`;
                }
            }, 1000);

            // Polling kiểm tra trạng thái thanh toán tự động mỗi 3 giây
            const pollInterval = setInterval(function() {
                if (isCompleted) return;

                fetch(checkStatusUrl)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && data.is_paid) {
                            isCompleted = true;
                            clearInterval(pollInterval);
                            clearInterval(countdownTimer);

                            // Hiển thị overlay thành công
                            document.getElementById('paymentSuccessOverlay')?.classList.remove('hidden');
                            document.getElementById('qrLoadingState')?.classList.add('hidden');

                            setTimeout(function() {
                                window.location.href = thanksUrl;
                            }, 2000);
                        }
                    })
                    .catch(err => console.log('Checking payment status:', err));
            }, 3000);

            // Nút bấm thanh toán trực tiếp thành công luôn
            const simBtn = document.getElementById('simulateWebhookBtn');
            if (simBtn) {
                simBtn.addEventListener('click', function() {
                    simBtn.disabled = true;
                    simBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang xác nhận thanh toán...';

                    fetch('{{ route("api.payment.simulate-webhook") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            order_id: orderId,
                            amount: {{ (int)$order->total_price }}
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            isCompleted = true;
                            clearInterval(pollInterval);
                            clearInterval(countdownTimer);
                            simBtn.innerHTML = '<i class="fa-solid fa-check"></i> Đã thanh toán thành công!';
                            document.getElementById('paymentSuccessOverlay')?.classList.remove('hidden');
                            document.getElementById('qrLoadingState')?.classList.add('hidden');
                            setTimeout(function() {
                                window.location.href = thanksUrl;
                            }, 1500);
                        } else {
                            simBtn.disabled = false;
                            simBtn.innerHTML = '<i class="fa-solid fa-circle-check text-sm"></i> <span>Tôi đã hoàn tất chuyển khoản</span>';
                            alert(data.message || 'Lỗi xác nhận thanh toán');
                        }
                    })
                    .catch(err => {
                        simBtn.disabled = false;
                        simBtn.innerHTML = '<i class="fa-solid fa-circle-check text-sm"></i> <span>Tôi đã hoàn tất chuyển khoản</span>';
                        console.error('Lỗi xác nhận:', err);
                    });
                });
            }
        });
    </script>
</x-layout-frontend>