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
                    Mở ứng dụng ngân hàng bất kỳ (MB, VCB, Techcom, Momo, VietQR...) và quét mã để thanh toán.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-start">
                <!-- Cột trái: Mã VietQR động -->
                <div class="md:col-span-6 bg-white p-6 sm:p-8 rounded-2xl shadow-lg border border-stone-200/90 text-center relative overflow-hidden">
                    <!-- Trạng thái chờ thanh toán -->
                    <div id="qrLoadingState" class="mb-4">
                        <div class="inline-flex items-center gap-2 px-3 py-1 bg-amber-50 text-amber-700 text-xs font-semibold rounded-full border border-amber-200 animate-pulse">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            <span>Đang chờ chuyển khoản...</span>
                        </div>
                    </div>

                    <!-- Khung ảnh QR -->
                    <div class="relative inline-block p-3 bg-gradient-to-br from-orange-50 to-amber-50 rounded-2xl border-2 border-[#C25E00]/30 shadow-inner group">
                        <img src="{{ $qrUrl }}" 
                             alt="Mã VietQR thanh toán" 
                             id="vietqrImage"
                             class="w-64 h-64 sm:w-72 sm:h-72 object-contain rounded-xl mx-auto shadow-sm">
                        
                        <!-- Overlay khi thành công -->
                        <div id="paymentSuccessOverlay" class="hidden absolute inset-0 bg-white/95 rounded-2xl flex flex-col items-center justify-center p-6 text-center">
                            <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-3xl mb-3 animate-bounce">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>
                            <h3 class="text-lg font-black text-stone-900">Thanh Toán Thành Công!</h3>
                            <p class="text-xs text-stone-600 mt-1">Hệ thống đã tự động nhận tiền qua Webhook.</p>
                            <span class="text-xs text-emerald-600 font-bold mt-2">Đang chuyển trang sau 2 giây...</span>
                        </div>
                    </div>

                    <p class="text-[11px] text-stone-500 mt-4 text-center">
                        <span>Hệ thống tự động duyệt ngay sau khi chuyển (không cần gửi ảnh bill)</span>
                    </p>

                    <!-- Nút giả lập Webhook cho dev test -->
                    <div class="mt-6 pt-4 border-t border-stone-100">
                        <button type="button" 
                                id="simulateWebhookBtn"
                                class="w-full py-2.5 px-4 bg-stone-100 hover:bg-stone-200 text-stone-700 text-xs font-bold rounded-xl transition-colors flex items-center justify-center cursor-pointer">
                            <span>[Thử nghiệm] Giả lập Webhook đã nhận tiền</span>
                        </button>
                    </div>
                </div>

                <!-- Cột phải: Thông tin chi tiết & Hướng dẫn -->
                <div class="md:col-span-6 space-y-4">
                    <!-- Chi tiết thanh toán -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-stone-200/90 space-y-4">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-stone-800 border-b border-stone-100 pb-3 flex items-center justify-between">
                            <span>Thông tin chuyển khoản</span>
                            <span class="text-xs font-normal text-stone-500">Đơn hàng #{{ $order->id }}</span>
                        </h2>

                        <!-- Số tiền -->
                        <div class="p-3.5 bg-gradient-to-r from-orange-500 to-amber-500 rounded-xl text-white flex items-center justify-between">
                            <div>
                                <span class="text-[11px] text-orange-100 font-medium block">Số tiền cần thanh toán</span>
                                <span class="text-xl sm:text-2xl font-black tracking-tight">
                                    {{ number_format($order->total_price, 0, ',', '.') }}đ
                                </span>
                            </div>
                            <button type="button" onclick="copyText('{{ (int)$order->total_price }}', 'Đã sao chép số tiền!')" class="px-2.5 py-1 bg-white/20 hover:bg-white/30 rounded text-xs font-semibold transition-colors">
                                Sao chép
                            </button>
                        </div>

                        <!-- Nội dung chuyển khoản (RẤT QUAN TRỌNG) -->
                        <div class="p-3.5 bg-amber-50/70 border border-amber-300 rounded-xl flex items-center justify-between">
                            <div>
                                <span class="text-[11px] font-bold text-amber-800 uppercase block">Nội dung chuyển khoản (bắt buộc)</span>
                                <span class="text-base font-black text-[#C25E00] tracking-wider" id="memoText">{{ $memo }}</span>
                            </div>
                            <button type="button" onclick="copyText('{{ $memo }}', 'Đã sao chép nội dung chuyển khoản!')" class="px-3 py-1.5 bg-[#C25E00] hover:bg-[#A34E00] text-white rounded-lg text-xs font-bold transition-colors shadow-xs">
                                Sao chép
                            </button>
                        </div>

                        <!-- Thông tin ngân hàng -->
                        <div class="space-y-2.5 text-xs text-stone-700 pt-1">
                            <div class="flex items-center justify-between py-1.5 border-b border-stone-100">
                                <span class="text-stone-500">Ngân hàng thụ hưởng:</span>
                                <span class="font-bold text-stone-900">{{ $bankInfo['bank_name'] }}</span>
                            </div>
                            <div class="flex items-center justify-between py-1.5 border-b border-stone-100">
                                <span class="text-stone-500">Số tài khoản:</span>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-bold text-stone-900 text-sm">{{ $bankInfo['account_no'] }}</span>
                                    <button type="button" onclick="copyText('{{ $bankInfo['account_no'] }}', 'Đã sao chép số tài khoản!')" class="text-[#C25E00] hover:underline font-bold text-[11px]">
                                        Copy
                                    </button>
                                </div>
                            </div>
                            <div class="flex items-center justify-between py-1.5 border-b border-stone-100">
                                <span class="text-stone-500">Chủ tài khoản:</span>
                                <span class="font-bold text-stone-900 uppercase">{{ $bankInfo['account_name'] }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Hướng dẫn các bước -->
                    <div class="bg-stone-50 p-5 rounded-2xl border border-stone-200/80 text-xs text-stone-600 space-y-2.5">
                        <h3 class="font-bold text-stone-800 flex items-center gap-2">
                            <i class="fa-solid fa-circle-info text-[#C25E00]"></i>
                            <span>Các bước thanh toán nhanh</span>
                        </h3>
                        <ol class="list-decimal list-inside space-y-1.5 text-[11px] leading-relaxed">
                            <li>Mở ứng dụng Ngân hàng hoặc Ví MoMo trên điện thoại.</li>
                            <li>Chọn tính năng <strong>Quét mã QR</strong> và hướng camera vào mã bên cạnh.</li>
                            <li>Kiểm tra đúng số tiền và bấm <strong>Xác nhận thanh toán</strong>.</li>
                            <li>Hệ thống Webhook sẽ tự động cập nhật đơn hàng của bạn ngay lập tức!</li>
                        </ol>
                    </div>

                    <!-- Nút quay lại hoặc hỗ trợ -->
                    <div class="flex items-center justify-between text-xs pt-1">
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
            navigator.clipboard.writeText(text).then(() => {
                const toast = document.getElementById('copyToast');
                toast.textContent = message || 'Đã sao chép!';
                toast.classList.remove('hidden');
                setTimeout(() => toast.classList.add('hidden'), 2000);
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const orderId = {{ $order->id }};
            const checkStatusUrl = '{{ route("api.payment.check-status", $order->id) }}';
            const thanksUrl = '{{ route("site.thanks") }}';
            let isCompleted = false;

            // Polling kiểm tra trạng thái thanh toán tự động mỗi 3 giây
            const pollInterval = setInterval(function() {
                if (isCompleted) return;

                fetch(checkStatusUrl)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && data.is_paid) {
                            isCompleted = true;
                            clearInterval(pollInterval);

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

            // Nút giả lập Webhook
            const simBtn = document.getElementById('simulateWebhookBtn');
            if (simBtn) {
                simBtn.addEventListener('click', function() {
                    simBtn.disabled = true;
                    simBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang giả lập chuyển tiền...';

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
                            simBtn.innerHTML = '<i class="fa-solid fa-check text-emerald-600"></i> Đã gửi Webhook thành công!';
                        } else {
                            simBtn.disabled = false;
                            simBtn.innerHTML = 'Thử lại';
                            alert(data.message || 'Lỗi giả lập Webhook');
                        }
                    })
                    .catch(err => {
                        simBtn.disabled = false;
                        simBtn.innerHTML = 'Thử lại';
                        console.error('Lỗi giả lập:', err);
                    });
                });
            }
        });
    </script>
</x-layout-frontend>
