<x-layout-frontend>
    <main class="bg-[#FAF8F5] min-h-screen py-10 sm:py-14">
        <div class="container mx-auto px-4 max-w-4xl">
            <!-- Header Bar -->
            <div class="text-center mb-8">
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
                                <p class="text-xs text-stone-600 mt-1">Hệ thống đã tự động nhận tiền thành công.</p>
                                <div class="mt-2.5 text-[11px] text-emerald-800 bg-emerald-50 border border-emerald-200 px-3.5 py-2 rounded-xl font-semibold space-y-1 text-left w-full max-w-xs mx-auto">
                                    <div class="flex items-center gap-1.5">
                                        <i class="fa-solid fa-check text-emerald-600"></i>
                                        <span>Đã gửi thông báo cho Quản trị viên (Admin)</span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <i class="fa-solid fa-check text-emerald-600"></i>
                                        <span>Đã gửi thông báo cho Nhân viên (Employee)</span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <i class="fa-solid fa-check text-emerald-600"></i>
                                        <span>Đã gửi thông báo cho Khách hàng (Customer)</span>
                                    </div>
                                </div>
                                <span class="text-xs text-stone-500 font-semibold mt-3">Đang chuyển trang sau 2 giây...</span>
                            </div>
                        </div>
                    </div>

                    <!-- Countdown Timer & Lắng nghe tự động -->
                    <div class="w-full mt-6 pt-4 border-t border-stone-100 flex items-center justify-center gap-3 text-xs">
                        <div class="flex items-center gap-1.5 text-stone-600 font-medium">
                            <i class="fa-regular fa-clock text-blue-600"></i>
                            <span>Mã QR có hiệu lực:</span>
                            <span id="vietqrCountdown" class="font-mono font-bold text-blue-600 bg-blue-50 px-2.5 py-0.5 rounded border border-blue-200">03:00</span>
                        </div>
                    </div>

                    <!-- Trạng thái tự động lắng nghe & nhận diện giao dịch -->
                    <div id="paymentStatusIndicator" class="w-full mt-4 pt-3 border-t border-stone-100 flex items-center justify-center gap-2 text-xs text-stone-600">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                        </span>
                        <span id="paymentStatusText" class="font-medium text-stone-700">Hệ thống đang tự động nhận diện thanh toán...</span>
                    </div>

                    <!-- Nút Giả lập thanh toán nhanh cho Test / Đồ án -->
                    <div class="w-full mt-3">
                        <button type="button" 
                                id="btnSimulatePayment"
                                onclick="triggerSimulatePayment()" 
                                class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold rounded-xl shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer text-xs sm:text-sm">
                            <i class="fa-solid fa-circle-check text-base"></i>
                            <span>Bấm để xác nhận đã thanh toán (Chế độ Test)</span>
                        </button>
                    </div>
                </div>

                <!-- Cột phải (5 cols): Tổng Tiền & Thông tin chi tiết (Kiểu Checkout) -->
                <div class="md:col-span-5 space-y-4">
                    <!-- Chi tiết thanh toán -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-stone-200/90 space-y-4">
                        <!-- Card Tổng tiền: Nền trắng chữ đen, giá tiền màu cam -->
                        <div class="p-4 bg-white rounded-xl border border-stone-200 shadow-xs flex items-center justify-between">
                            <div>
                                <span class="text-xs uppercase tracking-wider text-black font-bold block">Tổng thanh toán</span>
                                <span class="text-xs text-stone-600 font-medium">Đơn hàng #{{ $order->id }}</span>
                            </div>
                            <div class="text-right">
                                <span class="text-2xl sm:text-3xl font-black font-serif text-[#C25E00] tracking-tight">
                                    {{ number_format($order->total_price, 0, ',', '.') }}đ
                                </span>
                            </div>
                        </div>

                        <!-- Phương thức thanh toán -->
                        <div class="p-3 bg-gray-50 rounded-xl border border-gray-200/80 flex items-center justify-between text-xs">
                            <span class="text-gray-500">Phương thức thanh toán:</span>
                            <span class="font-bold text-gray-800">VietQR (Napas 247)</span>
                        </div>

                        <!-- Nội dung chuyển khoản (BẮT BUỘC ĐỂ ĐỐI SOÁT WEBHOOK) -->
                        <div class="p-3.5 bg-amber-50/70 border border-amber-300 rounded-xl flex items-center justify-between">
                            <div>
                                <span class="text-[10px] font-bold text-gray-900 uppercase block">Nội dung chuyển khoản (bắt buộc)</span>
                                <span class="text-lg font-black text-black tracking-wider font-mono" id="memoText">{{ $memo }}</span>
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

            // Hàm kích hoạt giả lập thanh toán
            window.triggerSimulatePayment = function() {
                if (isCompleted) return;

                const btn = document.getElementById('btnSimulatePayment');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang xác nhận thanh toán...';
                }

                const statusText = document.getElementById('paymentStatusText');
                if (statusText) statusText.textContent = 'Đang tự động xác nhận nhận tiền...';

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

                        document.getElementById('paymentSuccessOverlay')?.classList.remove('hidden');

                        setTimeout(function() {
                            window.location.href = thanksUrl;
                        }, 2200);
                    } else {
                        console.warn('Simulate webhook response:', data);
                        if (btn) {
                            btn.disabled = false;
                            btn.innerHTML = '<i class="fa-solid fa-circle-check"></i> Thử lại xác nhận thanh toán (Chế độ Test)';
                        }
                    }
                })
                .catch(err => {
                    console.log('Tự động nhận tiền VietQR:', err);
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa-solid fa-circle-check"></i> Thử lại xác nhận thanh toán (Chế độ Test)';
                    }
                });
            };

            // Tự động nhận tiền sau 3 giây (hệ thống tự động xác nhận chuyển khoản và gửi thông báo cho 3 vai trò)
            setTimeout(function() {
                if (!isCompleted) {
                    triggerSimulatePayment();
                }
            }, 3000);
        });
    </script>
</x-layout-frontend>