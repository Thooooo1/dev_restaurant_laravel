<style>
    /* Ẩn hoàn toàn container của thư viện Toastr cũ để tránh xung đột */
    #toast-container {
        display: none !important;
    }

    /* KHỐI CHỨA THÔNG BÁO TỐI GIẢN DƯỚI THANH MENU (TOP-RIGHT) */
    #app-toast-container {
        position: fixed !important;
        top: 108px !important; /* Nằm dưới thanh Header 96px, hoàn toàn không chạm vào menu */
        right: 24px !important;
        z-index: 999999 !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: flex-end !important;
        gap: 8px !important;
        pointer-events: none !important;
        max-width: calc(100vw - 48px) !important;
    }

    /* THÔNG BÁO TINH TẾ - HOÀN TOÀN KHÔNG DÙNG ICON, CHỮ ĐEN/SÁNG RÕ RÀNG */
    .app-toast-item {
        pointer-events: auto !important;
        display: inline-block !important;
        background: #FFFFFF !important;
        color: #0F172A !important;
        padding: 9px 18px !important;
        border-radius: 8px !important;
        border: 1px solid #E2E8F0 !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.04) !important;
        font-family: 'Inter', system-ui, -apple-system, sans-serif !important;
        font-size: 13.5px !important;
        font-weight: 600 !important;
        line-height: 1.4 !important;
        cursor: pointer !important;
        user-select: none !important;
        opacity: 0;
        transform: translateY(-8px) scale(0.98);
        transition: opacity 0.2s cubic-bezier(0.16, 1, 0.3, 1), transform 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
        white-space: nowrap !important;
    }

    .app-toast-item.toast-success {
        border-color: #D1FAE5 !important;
        background: #FFFFFF !important;
        color: #065F46 !important;
    }

    .app-toast-item.toast-error {
        border-color: #FECACA !important;
        background: #FEF2F2 !important;
        color: #991B1B !important;
    }

    .app-toast-item.toast-warning {
        border-color: #FDE68A !important;
        background: #FFFBEB !important;
        color: #92400E !important;
    }

    .app-toast-item.show {
        opacity: 1 !important;
        transform: translateY(0) scale(1) !important;
    }

    .app-toast-item.hide {
        opacity: 0 !important;
        transform: translateY(-8px) scale(0.96) !important;
    }
</style>

<div id="app-toast-container"></div>

<script>
    (function() {
        window.showAppToast = function(message, type = 'success', duration = 1600) {
            if (!message) return;
            const container = document.getElementById('app-toast-container');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = 'app-toast-item toast-' + (type || 'success');
            // THUẦN CHỮ TỰ NHIÊN, KHÔNG CHÈN ICON HAY NÚT TẮT RƯỜM RÀ
            toast.textContent = message;

            // Bấm vào thông báo là đóng ngay lập tức
            toast.addEventListener('click', function() {
                dismissToast(toast);
            });

            container.appendChild(toast);

            // Kích hoạt animation hiện ra mượt mà
            requestAnimationFrame(() => {
                toast.classList.add('show');
            });

            // Tự động biến mất sau đúng duration (1.6 giây)
            let timer = setTimeout(() => {
                dismissToast(toast);
            }, duration);

            function dismissToast(el) {
                if (!el || el._isDismissing) return;
                el._isDismissing = true;
                clearTimeout(timer);
                el.classList.remove('show');
                el.classList.add('hide');
                setTimeout(() => {
                    if (el.parentNode) el.parentNode.removeChild(el);
                }, 200);
            }
        };

        // Ghi đè Toastr toàn cục để tương thích 100% với toàn bộ code cũ
        window.toastr = {
            success: (msg) => window.showAppToast(msg, 'success', 1600),
            error: (msg) => window.showAppToast(msg, 'error', 1800),
            warning: (msg) => window.showAppToast(msg, 'warning', 1600),
            info: (msg) => window.showAppToast(msg, 'info', 1600),
            clear: () => {
                const c = document.getElementById('app-toast-container');
                if (c) c.innerHTML = '';
            },
            remove: () => {
                const c = document.getElementById('app-toast-container');
                if (c) c.innerHTML = '';
            },
            options: {}
        };
    })();
</script>

@if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.showAppToast("{!! addslashes(session('success')) !!}", 'success', 1600);
        });
    </script>
@endif

@if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.showAppToast("{!! addslashes(session('error')) !!}", 'error', 1800);
        });
    </script>
@endif

@if(session('warning'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.showAppToast("{!! addslashes(session('warning')) !!}", 'warning', 1600);
        });
    </script>
@endif

@if(session('info'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.showAppToast("{!! addslashes(session('info')) !!}", 'info', 1600);
        });
    </script>
@endif
