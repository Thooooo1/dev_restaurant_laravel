<!-- Facebook / Messenger Style Notification Bell Component for Frontend Header -->
<div class="relative inline-block text-left" id="frontNotiRoot">
    <!-- Bell Trigger Button (Facebook / Messenger Circle Style) -->
    <button type="button" 
            id="frontNotiBtn" 
            class="group relative flex items-center justify-center w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-stone-100 hover:bg-stone-200/80 text-stone-700 hover:text-[#C25E00] transition-all cursor-pointer focus:outline-none"
            title="Thông báo của bạn">
        <i class="fa-regular fa-bell text-base group-hover:scale-110 transition-transform" id="frontBellIcon"></i>
        <!-- Facebook Style Red Badge -->
        <span id="frontNotiBadge" 
              class="hidden absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 bg-[#E41E3F] text-white text-[10px] font-black rounded-full flex items-center justify-center shadow-xs border-2 border-white leading-none">
            0
        </span>
    </button>

    <!-- Facebook / Messenger Style Dropdown Panel -->
    <div id="frontNotiDropdown" 
         class="hidden absolute right-0 mt-2.5 w-96 max-w-[94vw] bg-white rounded-2xl shadow-2xl border border-stone-200/90 z-50 overflow-hidden transform origin-top-right transition-all duration-200 text-stone-800">
        
        <!-- Header -->
        <div class="p-4 pb-2.5 border-b border-stone-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <h3 class="text-base font-bold text-stone-900 tracking-tight">Thông báo</h3>
                <span id="frontNotiHeaderCount" class="text-xs px-2 py-0.5 rounded-full bg-orange-100 text-[#C25E00] font-bold">
                    0 mới
                </span>
            </div>
            <button type="button" 
                    id="frontMarkAllReadBtn" 
                    class="text-xs font-semibold text-[#C25E00] hover:text-[#9A3412] hover:underline cursor-pointer transition-colors">
                Đánh dấu đã đọc tất cả
            </button>
        </div>

        <!-- Filter Tabs: Tất cả / Chưa đọc (Facebook style) -->
        <div class="flex items-center gap-2 px-4 py-2 border-b border-stone-100 bg-stone-50/50 text-xs">
            <button type="button" 
                    id="frontTabAll" 
                    class="px-3 py-1 rounded-full font-bold bg-[#C25E00] text-white transition-all cursor-pointer">
                Tất cả
            </button>
            <button type="button" 
                    id="frontTabUnread" 
                    class="px-3 py-1 rounded-full font-medium text-stone-600 hover:bg-stone-200/60 transition-all cursor-pointer">
                Chưa đọc
            </button>
        </div>

        <!-- Notification List -->
        <div class="max-h-[380px] overflow-y-auto divide-y divide-stone-100 text-xs" id="frontNotiList">
            <div class="p-8 text-center text-stone-400">
                <p class="font-medium text-xs">Đang tải thông báo...</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="p-2.5 bg-stone-50 border-t border-stone-100 text-center text-xs text-stone-500 flex items-center justify-between px-4">
            <span class="text-[11px] text-stone-400">Cập nhật tự động thời gian thực</span>
            @if(Auth::check() && (Auth::user()->isAdmin() || Auth::user()->isEmployee()))
                <a href="{{ route('admin.dashboard') }}" class="font-semibold text-[#C25E00] hover:underline text-xs">
                    Vào trang quản trị »
                </a>
            @else
                <a href="{{ route('site.orders') }}" class="font-semibold text-[#C25E00] hover:underline text-xs">
                    Xem đơn hàng của tôi »
                </a>
            @endif
        </div>
    </div>
</div>

<!-- Facebook Style Floating Toast Popup for Realtime Alerts -->
<div id="frontNotiToastContainer" class="fixed top-20 right-4 z-50 space-y-2 pointer-events-none max-w-sm w-full"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const root = document.getElementById('frontNotiRoot');
    const btn = document.getElementById('frontNotiBtn');
    const dropdown = document.getElementById('frontNotiDropdown');
    const badge = document.getElementById('frontNotiBadge');
    const headerCount = document.getElementById('frontNotiHeaderCount');
    const list = document.getElementById('frontNotiList');
    const markAllBtn = document.getElementById('frontMarkAllReadBtn');
    const tabAll = document.getElementById('frontTabAll');
    const tabUnread = document.getElementById('frontTabUnread');
    const toastContainer = document.getElementById('frontNotiToastContainer');
    const bellIcon = document.getElementById('frontBellIcon');

    if (!btn || !dropdown) return;

    let currentFilter = 'all';
    let currentUnreadCount = 0;
    let isFirstLoad = true;

    // Helper: phân loại nhãn badge không icon
    function getTypeBadgeClass(type) {
        switch(type) {
            case 'booking':
                return 'bg-purple-100 text-purple-800';
            case 'service_call':
                return 'bg-amber-100 text-amber-800';
            case 'order':
                return 'bg-orange-100 text-[#C25E00]';
            case 'payment':
                return 'bg-emerald-100 text-emerald-800';
            default:
                return 'bg-stone-100 text-stone-700';
        }
    }

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
            osc.frequency.setValueAtTime(880, ctx.currentTime); // A5
            osc.frequency.setValueAtTime(1174.66, ctx.currentTime + 0.12); // D6
            gain.gain.setValueAtTime(0.2, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.5);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.5);
        } catch(e) {}
    }

    // Toggle Dropdown & Tự động giảm/hết số thông báo khi người dùng mở xem
    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        const willOpen = dropdown.classList.contains('hidden');
        dropdown.classList.toggle('hidden');

        if (willOpen) {
            // Khi người dùng bấm mở xem thông báo:
            // Tức thì làm trống hoặc giảm số badge về 0 ("xem rồi thì hết luôn")
            if (currentUnreadCount > 0) {
                currentUnreadCount = 0;
                badge.classList.add('hidden');
                badge.textContent = '0';
                headerCount.textContent = '0 mới';

                // Gửi request mark-all-read lên server chạy ngầm
                fetch('{{ route("site.notifications.markAllRead") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    keepalive: true
                }).catch(() => {});
            }

            fetchNotifications();
        }
    });

    // Close when clicking outside
    document.addEventListener('click', function(e) {
        if (!root.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });

    // Filter Tabs
    tabAll.addEventListener('click', function() {
        currentFilter = 'all';
        tabAll.className = 'px-3 py-1 rounded-full font-bold bg-[#C25E00] text-white transition-all cursor-pointer';
        tabUnread.className = 'px-3 py-1 rounded-full font-medium text-stone-600 hover:bg-stone-200/60 transition-all cursor-pointer';
        fetchNotifications();
    });

    tabUnread.addEventListener('click', function() {
        currentFilter = 'unread';
        tabUnread.className = 'px-3 py-1 rounded-full font-bold bg-[#C25E00] text-white transition-all cursor-pointer';
        tabAll.className = 'px-3 py-1 rounded-full font-medium text-stone-600 hover:bg-stone-200/60 transition-all cursor-pointer';
        fetchNotifications();
    });

    // Mark All As Read thủ công
    markAllBtn.addEventListener('click', function(e) {
        e.preventDefault();
        currentUnreadCount = 0;
        badge.classList.add('hidden');
        badge.textContent = '0';
        headerCount.textContent = '0 mới';

        fetch('{{ route("site.notifications.markAllRead") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            fetchNotifications();
        });
    });

    // Fetch Notifications from Feed
    function fetchNotifications() {
        fetch(`{{ route("site.notifications.feed") }}?filter=${currentFilter}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) return;

            // Nếu dropdown đang đóng thì mới cập nhật số unread từ server,
            // còn nếu dropdown đang mở thì người dùng đang xem nên giữ số 0
            const isDropdownOpen = !dropdown.classList.contains('hidden');
            const unread = data.unread_count || 0;

            if (!isDropdownOpen) {
                currentUnreadCount = unread;
                if (unread > 0) {
                    badge.textContent = unread > 99 ? '99+' : unread;
                    badge.classList.remove('hidden');
                    headerCount.textContent = `${unread} mới`;

                    // Báo chuông nếu có thông báo mới sau lần tải đầu
                    if (!isFirstLoad && unread > currentUnreadCount) {
                        playNotiSound();
                    }
                } else {
                    badge.classList.add('hidden');
                    headerCount.textContent = '0 mới';
                }
            } else {
                headerCount.textContent = '0 mới';
                badge.classList.add('hidden');
            }

            isFirstLoad = false;

            // Render Danh sách
            renderNotificationsList(data.notifications);
        })
        .catch(err => {
            console.error('Lỗi tải thông báo:', err);
        });
    }

    // Render HTML danh sách thông báo: Thuần typography cao cấp, KHÔNG icon thừa, cực kỳ sạch sẽ
    function renderNotificationsList(items) {
        if (!items || items.length === 0) {
            list.innerHTML = `
                <div class="py-12 px-6 text-center text-stone-400">
                    <p class="font-bold text-stone-700 text-xs uppercase tracking-wider">Không có thông báo mới</p>
                    <p class="text-[11px] text-stone-400 mt-1">Các thông báo về đặt bàn và đơn hàng sẽ xuất hiện ở đây.</p>
                </div>
            `;
            return;
        }

        let html = '';
        items.forEach(item => {
            const isUnread = !item.is_read;
            const bgClass = isUnread ? 'bg-amber-50/50 hover:bg-amber-50/80 font-medium' : 'bg-white hover:bg-stone-50';
            
            html += `
                <div class="p-3.5 flex items-start gap-3 transition-colors cursor-pointer ${bgClass} relative group border-b border-stone-100 last:border-b-0" 
                     id="noti-item-${item.id}"
                     onclick="handleNotificationClick(${item.id}, '${item.url}', event)">
                    
                    <!-- Nội dung thông báo thuần typography sạch sẽ -->
                    <div class="flex-1 min-w-0 pr-2">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded tracking-wide uppercase ${getTypeBadgeClass(item.type)}">
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

                    <!-- Chấm chỉ báo trạng thái chưa đọc -->
                    ${isUnread ? '<span class="w-2 h-2 rounded-full bg-[#C25E00] shrink-0 mt-2" title="Chưa đọc"></span>' : ''}
                </div>
            `;
        });

        list.innerHTML = html;
    }

    // Xử lý khi click vào 1 thông báo: Giảm/hết số lập tức và chuyển trang
    window.handleNotificationClick = function(id, targetUrl, event) {
        if (event) event.preventDefault();

        // 1. Giảm số lượng badge lập tức trên giao diện
        if (currentUnreadCount > 0) {
            currentUnreadCount--;
            if (currentUnreadCount === 0) {
                badge.classList.add('hidden');
                badge.textContent = '0';
                headerCount.textContent = '0 mới';
            } else {
                badge.textContent = currentUnreadCount > 99 ? '99+' : currentUnreadCount;
                headerCount.textContent = `${currentUnreadCount} mới`;
            }
        }

        // 2. Chuyển item sang trạng thái đã đọc
        const itemEl = document.getElementById(`noti-item-${id}`);
        if (itemEl) {
            itemEl.classList.remove('bg-amber-50/50', 'hover:bg-amber-50/80', 'font-medium');
            itemEl.classList.add('bg-white', 'hover:bg-stone-50');
            const dot = itemEl.querySelector('.bg-\\[\\#C25E00\\]');
            if (dot) dot.remove();
        }

        // 3. Gửi beacon/fetch keepalive lên server
        fetch(`/notifications/${id}/read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            keepalive: true
        }).catch(() => {});

        // 4. Chuyển hướng trang
        setTimeout(() => {
            if (targetUrl && targetUrl !== 'javascript:void(0)') {
                window.location.href = targetUrl;
            } else {
                fetchNotifications();
            }
        }, 80);
    };

    // Toast thông báo nổi khi có tin mới (Không dùng icon theo yêu cầu)
    function showFacebookToast(noti) {
        const toast = document.createElement('div');
        toast.className = 'pointer-events-auto bg-white rounded-xl shadow-2xl border-l-4 border-[#C25E00] border-y border-r border-stone-200 p-3.5 transform transition-all duration-300 translate-x-full opacity-0 flex items-start gap-3';
        toast.innerHTML = `
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-1">
                    <span class="text-[10px] font-bold text-[#C25E00] uppercase tracking-wider">${getTypeLabel(noti.type)}</span>
                    <span class="text-[10px] text-stone-400">Vừa xong</span>
                </div>
                <h5 class="text-xs font-bold text-stone-900 mt-1 truncate">${noti.title}</h5>
                <p class="text-[11px] text-stone-600 line-clamp-2 mt-0.5 leading-relaxed">${noti.message}</p>
                <div class="mt-2 flex items-center gap-2">
                    <a href="${noti.url}" class="text-[11px] font-bold text-white bg-[#C25E00] hover:bg-[#A34E00] px-3 py-1 rounded transition-colors inline-block">
                        Xem chi tiết
                    </a>
                </div>
            </div>
            <button type="button" class="text-stone-400 hover:text-stone-700 text-sm font-bold p-1 cursor-pointer" onclick="this.closest('div').remove()">
                ×
            </button>
        `;

        toastContainer.appendChild(toast);

        setTimeout(() => {
            toast.classList.remove('translate-x-full', 'opacity-0');
        }, 50);

        setTimeout(() => {
            toast.classList.add('translate-x-full', 'opacity-0');
            setTimeout(() => toast.remove(), 350);
        }, 6000);
    }

    // Tải thông báo ngay khi tải trang
    fetchNotifications();

    // Tự động polling thời gian thực mỗi 15 giây
    setInterval(fetchNotifications, 15000);
});
</script>
