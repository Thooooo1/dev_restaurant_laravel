<!-- ======================================================== -->
<!-- CIVILIZE PWA INSTALL PROMPT (TƯƠNG THÍCH MÁY TÍNH & MOBILE) -->
<!-- ======================================================== -->
<div id="pwaInstallBanner" 
     class="hidden fixed bottom-20 md:bottom-6 left-4 right-4 md:left-6 md:right-auto md:max-w-md z-50 bg-stone-900 text-white rounded-xl shadow-2xl border border-stone-800 p-4 transition-all duration-300 transform translate-y-4 opacity-0 print:hidden select-none">
    <div class="flex items-start gap-3.5">
        <div class="w-11 h-11 rounded-lg bg-stone-800 p-1 flex items-center justify-center shrink-0 border border-stone-700">
            <img src="{{ asset('images/logo/logo.png') }}" alt="CIVILIZE" class="w-full h-full object-contain">
        </div>
        <div class="flex-1 min-w-0">
            <h4 class="text-sm font-bold text-white tracking-tight flex items-center gap-1.5">
                <span>Cài đặt ứng dụng CIVILIZE</span>
                <span class="text-[10px] uppercase font-bold bg-[#C25E00] px-1.5 py-0.5 rounded text-white">App</span>
            </h4>
            <p id="pwaBannerDesc" class="text-xs text-stone-300 mt-1 leading-relaxed">
                Cài đặt ứng dụng lên thiết bị để đặt bàn, gọi món và nhận thông báo mượt mà như ứng dụng native.
            </p>
            <div class="flex items-center gap-2.5 mt-3">
                <button type="button" 
                        id="pwaInstallBtn"
                        class="px-3.5 py-1.5 rounded bg-[#C25E00] hover:bg-[#A34E00] text-white font-bold text-xs uppercase tracking-wider shadow-sm transition-colors cursor-pointer">
                    Cài đặt ngay
                </button>
                <button type="button" 
                        id="pwaDismissBtn"
                        class="px-2.5 py-1.5 text-xs text-stone-400 hover:text-white transition-colors cursor-pointer">
                    Để sau
                </button>
            </div>
        </div>
        <button type="button" 
                id="pwaCloseBtn"
                class="text-stone-400 hover:text-white p-1 transition-colors text-sm"
                title="Đóng">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
</div>

<script>
    (function() {
        let deferredPrompt = null;
        const banner = document.getElementById('pwaInstallBanner');
        const installBtn = document.getElementById('pwaInstallBtn');
        const dismissBtn = document.getElementById('pwaDismissBtn');
        const closeBtn = document.getElementById('pwaCloseBtn');
        const desc = document.getElementById('pwaBannerDesc');

        // Kiểm tra xem ứng dụng đã chạy ở chế độ standalone (đã cài đặt) chưa
        const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
        if (isStandalone) {
            return;
        }

        // Kiểm tra đã từ chối cài đặt trong vòng 5 ngày qua chưa
        const dismissedAt = localStorage.getItem('civilize_pwa_dismissed_at');
        if (dismissedAt && (Date.now() - parseInt(dismissedAt, 10)) < (5 * 24 * 60 * 60 * 1000)) {
            return;
        }

        function showBanner() {
            if (!banner) return;
            banner.classList.remove('hidden');
            setTimeout(() => {
                banner.classList.remove('translate-y-4', 'opacity-0');
            }, 50);
        }

        function hideBanner() {
            if (!banner) return;
            banner.classList.add('translate-y-4', 'opacity-0');
            setTimeout(() => {
                banner.classList.add('hidden');
            }, 300);
            localStorage.setItem('civilize_pwa_dismissed_at', Date.now().toString());
        }

        // Phát hiện Chrome / Edge / Android trước khi cài đặt
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            const isDesktop = window.innerWidth >= 768;
            if (desc) {
                desc.textContent = isDesktop 
                    ? 'Cài đặt CIVILIZE lên máy tính của bạn để mở cửa sổ riêng biệt và thao tác nhanh chóng hơn.'
                    : 'Thêm ứng dụng vào màn hình chính điện thoại để đặt bàn và gọi món thuận tiện.';
            }
            setTimeout(showBanner, 2000);
        });

        // Xử lý nút bấm cài đặt
        if (installBtn) {
            installBtn.addEventListener('click', async () => {
                if (deferredPrompt) {
                    deferredPrompt.prompt();
                    const { outcome } = await deferredPrompt.userChoice;
                    deferredPrompt = null;
                    hideBanner();
                } else {
                    // Dành cho iOS Safari hoặc trình duyệt không bắn beforeinstallprompt
                    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
                    if (isIOS) {
                        alert('Để cài đặt ứng dụng trên iPhone/iPad: Nhấn nút Chia sẻ (biểu tượng hình vuông có mũi tên lên) ở thanh dưới cùng Safari, sau đó chọn "Thêm vào Màn hình chính" (Add to Home Screen).');
                    } else {
                        alert('Để cài đặt ứng dụng: Mở menu trình duyệt (dấu 3 chấm ở góc trên) và chọn "Cài đặt ứng dụng" hoặc "Thêm vào Màn hình chính".');
                    }
                    hideBanner();
                }
            });
        }

        if (dismissBtn) dismissBtn.addEventListener('click', hideBanner);
        if (closeBtn) closeBtn.addEventListener('click', hideBanner);

        window.addEventListener('appinstalled', () => {
            hideBanner();
            deferredPrompt = null;
        });
    })();
</script>
