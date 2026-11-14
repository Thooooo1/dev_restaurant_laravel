<!-- Facebook / Messenger Style Notification Bell Component for Frontend Header -->
<div x-data="{ open: false }" class="relative inline-block text-left front-noti-root">
    <!-- Bell Trigger Button (Facebook / Messenger Circle Style) -->
    <button type="button" 
            @click="open = !open; if(open) { window.frontNotiOnOpen && window.frontNotiOnOpen(); }"
            class="group relative flex items-center justify-center w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-stone-100 hover:bg-stone-200/80 text-stone-700 hover:text-stone-900 transition-all cursor-pointer focus:outline-none"
            title="Thông báo của bạn">
        <i class="fa-regular fa-bell text-base group-hover:scale-110 transition-transform"></i>
        <!-- Red Notification Badge / Dot Indicator -->
        <span class="frontNotiBadge hidden absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center ring-2 ring-white shadow-xs leading-none">
            0
        </span>
    </button>

    <!-- Facebook / Messenger Style Dropdown Panel -->
    <div x-cloak 
         x-show="open" 
         @click.outside="open = false" 
         @keydown.escape.window="open = false" 
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         style="display: none;"
         class="absolute right-0 mt-2.5 w-96 max-w-[94vw] bg-white rounded-2xl shadow-2xl border border-stone-200/90 z-50 overflow-hidden transform origin-top-right text-stone-800">
        
        <!-- Header -->
        <div class="p-4 pb-2.5 border-b border-stone-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <h3 class="text-base font-bold text-stone-900 tracking-tight">Thông báo</h3>
                <span class="frontNotiHeaderCount text-xs px-2.5 py-0.5 rounded-full bg-red-100 text-red-600 font-bold">
                    0 mới
                </span>
            </div>
            <button type="button" 
                    onclick="window.frontNotiMarkAllRead(event)"
                    class="text-xs font-semibold text-stone-600 hover:text-stone-900 hover:underline cursor-pointer transition-colors">
                Đánh dấu đã đọc tất cả
            </button>
        </div>

        <!-- Filter Tabs: Tất cả / Chưa đọc -->
        <div class="flex items-center gap-2 px-4 py-2 border-b border-stone-100 bg-stone-50/50 text-xs">
            <button type="button" 
                    onclick="window.frontNotiSetFilter('all')"
                    class="frontTabAll px-3 py-1 rounded-full font-bold bg-stone-900 text-white transition-all cursor-pointer">
                Tất cả
            </button>
            <button type="button" 
                    onclick="window.frontNotiSetFilter('unread')"
                    class="frontTabUnread px-3 py-1 rounded-full font-medium text-stone-600 hover:bg-stone-200/60 transition-all cursor-pointer">
                Chưa đọc
            </button>
        </div>

        <!-- Notification List -->
        <div class="frontNotiList max-h-[380px] overflow-y-auto divide-y divide-stone-100 text-xs">
            <div class="p-8 text-center text-stone-400">
                <p class="font-medium text-xs">Đang tải thông báo...</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="p-2.5 bg-stone-50 border-t border-stone-100 text-center text-xs text-stone-500 flex items-center justify-between px-4">
            <span class="text-[11px] text-stone-400">Cập nhật tự động thời gian thực</span>
            @if(Auth::check() && (Auth::user()->isAdmin() || Auth::user()->isEmployee()))
                <a href="{{ route('admin.dashboard') }}" class="font-semibold text-stone-800 hover:text-black hover:underline text-xs">
                    Vào trang quản trị »
                </a>
            @else
                <a href="{{ route('site.orders') }}" class="font-semibold text-stone-800 hover:text-black hover:underline text-xs">
                    Xem đơn hàng của tôi »
                </a>
            @endif
        </div>
    </div>
</div>

@once
<!-- Floating Toast Popup for Realtime Alerts -->
<div id="frontNotiToastContainer" class="fixed top-20 right-4 z-50 space-y-2 pointer-events-none max-w-sm w-full"></div>

<script>
(function() {
    if (window.__FRONT_NOTI_SCRIPT_LOADED__) return;
    window.__FRONT_NOTI_SCRIPT_LOADED__ = true;

    let currentFilter = 'all';
    let currentUnreadCount = 0;
    let isFirstLoad = true;

    function getTypeLabel(type) {
        switch(type) {
            case 'booking':
                return 'Đặt bàn';
            case 'service_call':
                return 'Phục vụ';
            case 'order':
                return 'Đơn hàng';
            case 'payment':
                return 'Thanh toán';
            default:
                return 'Hệ thống';
        }
    }

    // Âm thanh chuông báo Facebook Ding-Dong
    function playNotiSound() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, ctx.currentTime);
            osc.frequency.setValueAtTime(1174.66, ctx.currentTime + 0.12);
            gain.gain.setValueAtTime(0.2, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.5);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.5);
        } catch(e) {}
    }

    window.frontNotiOnOpen = function() {
        window.frontNotiFetch();
    };

    window.frontNotiSetFilter = function(filter) {
        currentFilter = filter;
        document.querySelectorAll('.frontTabAll').forEach(el => {
            el.className = filter === 'all' 
                ? 'frontTabAll px-3 py-1 rounded-full font-bold bg-stone-900 text-white transition-all cursor-pointer'
                : 'frontTabAll px-3 py-1 rounded-full font-medium text-stone-600 hover:bg-stone-200/60 transition-all cursor-pointer';
        });
        document.querySelectorAll('.frontTabUnread').forEach(el => {
            el.className = filter === 'unread'
                ? 'frontTabUnread px-3 py-1 rounded-full font-bold bg-stone-900 text-white transition-all cursor-pointer'
                : 'frontTabUnread px-3 py-1 rounded-full font-medium text-stone-600 hover:bg-stone-200/60 transition-all cursor-pointer';
        });
        window.frontNotiFetch();
    };

    window.frontNotiMarkAllRead = function(event) {
        if (event) event.preventDefault();
        currentUnreadCount = 0;
        document.querySelectorAll('.frontNotiBadge').forEach(b => {
            b.classList.add('hidden');
            b.textContent = '0';
        });
        document.querySelectorAll('.frontNotiHeaderCount').forEach(h => {
            h.textContent = '0 mới';
        });
        document.querySelectorAll('.frontNotiList [id^="noti-item-"]').forEach(itemEl => {
            itemEl.classList.remove('bg-stone-50', 'hover:bg-stone-100/70', 'font-medium');
            itemEl.classList.add('bg-white', 'hover:bg-stone-50');
            const dot = itemEl.querySelector('.bg-red-500');
            if (dot) dot.remove();
        });

        fetch('{{ route("site.notifications.markAllRead") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(() => {
            window.frontNotiFetch();
        }).catch(() => {});
    };

    window.frontNotiFetch = function() {
        fetch(`{{ route("site.notifications.feed") }}?filter=${currentFilter}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) return;

            const unread = data.unread_count || 0;
            currentUnreadCount = unread;

            document.querySelectorAll('.frontNotiBadge').forEach(b => {
                if (unread > 0) {
                    b.textContent = unread > 99 ? '99+' : unread;
                    b.classList.remove('hidden');
                } else {
                    b.classList.add('hidden');
                    b.textContent = '0';
                }
            });

            document.querySelectorAll('.frontNotiHeaderCount').forEach(h => {
                h.textContent = `${unread} mới`;
            });

            if (!isFirstLoad && unread > currentUnreadCount) {
                playNotiSound();
            }
            isFirstLoad = false;

            renderNotificationsList(data.notifications);
        })
        .catch(err => {
            console.error('Lỗi tải thông báo:', err);
        });
    };

    function renderNotificationsList(items) {
        let html = '';
        if (!items || items.length === 0) {
            html = `
                <div class="py-12 px-6 text-center text-stone-400">
                    <p class="font-bold text-stone-700 text-xs uppercase tracking-wider">Không có thông báo mới</p>
                    <p class="text-[11px] text-stone-400 mt-1">Các thông báo về đặt bàn và đơn hàng sẽ xuất hiện ở đây.</p>
                </div>
            `;
        } else {
            items.forEach(item => {
                const isUnread = !item.is_read;
                const bgClass = isUnread ? 'bg-stone-50 hover:bg-stone-100/70 font-medium' : 'bg-white hover:bg-stone-50';
                
                html += `
                    <div class="p-3.5 flex items-start gap-3 transition-colors cursor-pointer ${bgClass} relative group border-b border-stone-100 last:border-b-0" 
                         id="noti-item-${item.id}"
                         onclick="window.handleNotificationClick(${item.id}, '${item.url}', event)">
                        
                        <div class="flex-1 min-w-0 pr-2">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <span class="text-[11px] font-bold text-stone-900 uppercase tracking-wide">
                                    ${getTypeLabel(item.type)}
                                </span>
                                <span class="text-[11px] text-stone-400 shrink-0 font-normal">
                                    ${item.time_ago}
                                </span>
                            </div>
                            <h4 class="text-xs ${isUnread ? 'font-bold text-stone-900' : 'font-semibold text-stone-700'} leading-snug">
                                ${item.title}
                            </h4>
                            <p class="text-[11.5px] text-stone-600 line-clamp-2 leading-relaxed mt-1">
                                ${item.message}
                            </p>
                        </div>

                        ${isUnread ? '<span class="w-2 h-2 rounded-full bg-red-500 shrink-0 mt-2" title="Chưa đọc"></span>' : ''}
                    </div>
                `;
            });
        }

        document.querySelectorAll('.frontNotiList').forEach(container => {
            container.innerHTML = html;
        });
    }

    window.handleNotificationClick = function(id, targetUrl, event) {
        if (event) event.preventDefault();

        if (currentUnreadCount > 0) {
            currentUnreadCount--;
            document.querySelectorAll('.frontNotiBadge').forEach(b => {
                if (currentUnreadCount === 0) {
                    b.classList.add('hidden');
                    b.textContent = '0';
                } else {
                    b.textContent = currentUnreadCount > 99 ? '99+' : currentUnreadCount;
                }
            });
            document.querySelectorAll('.frontNotiHeaderCount').forEach(h => {
                h.textContent = `${currentUnreadCount} mới`;
            });
        }

        const items = document.querySelectorAll(`[id="noti-item-${id}"]`);
        items.forEach(itemEl => {
            itemEl.classList.remove('bg-stone-50', 'hover:bg-stone-100/70', 'font-medium');
            itemEl.classList.add('bg-white', 'hover:bg-stone-50');
            const dot = itemEl.querySelector('.bg-red-500');
            if (dot) dot.remove();
        });

        fetch(`/notifications/${id}/read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            keepalive: true
        }).catch(() => {});

        setTimeout(() => {
            if (targetUrl && targetUrl !== 'javascript:void(0)') {
                window.location.href = targetUrl;
            } else {
                window.frontNotiFetch();
            }
        }, 80);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            window.frontNotiFetch();
            setInterval(window.frontNotiFetch, 15000);
        });
    } else {
        window.frontNotiFetch();
        setInterval(window.frontNotiFetch, 15000);
    }
})();
</script>
@endonce
