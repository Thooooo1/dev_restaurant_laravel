<!-- Chatbot Widget Component - CIVILIZE Restaurant Concierge -->
<div id="civilize-chatbot" class="fixed bottom-20 right-4 sm:bottom-6 sm:right-6 z-50 font-sans" style="font-family: inherit;">
    <!-- Floating Trigger Button (Chỉ hiển thị khi cửa sổ chat ĐANG ĐÓNG) -->
    <div id="chatbot-trigger-container" class="relative flex items-center transition-all duration-300">
        <button id="chatbot-toggle-btn" 
                class="relative bg-gradient-to-r from-[#A84E00] to-[#C25E00] hover:from-[#924200] hover:to-[#A34E00] text-white rounded-full w-14 h-14 flex items-center justify-center shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-105 active:scale-95 focus:outline-none ring-4 ring-orange-200/50 cursor-pointer"
                title="Tư vấn món & Đặt bàn">
            <span id="chatbot-icon-closed" class="flex items-center justify-center">
                <i class="fa-solid fa-comments text-2xl"></i>
            </span>
            <!-- Online Indicator Dot -->
            <span class="absolute top-1 right-1 w-3.5 h-3.5 bg-emerald-500 border-2 border-white rounded-full shadow-xs"></span>
        </button>
    </div>

    <!-- Chat Window Modal (Kích thước chuẩn Restaurant Concierge: rộng 410px, cao 580px, bo góc 16px) -->
    <div id="chatbot-window" 
         class="hidden fixed bottom-20 right-4 sm:bottom-6 sm:right-6 w-[94vw] sm:w-[410px] h-[540px] max-h-[78vh] bg-[#FAF8F5] rounded-2xl shadow-2xl flex flex-col overflow-hidden border border-stone-200/90 transition-all duration-300 transform scale-95 opacity-0 z-50">
        
        <!-- Header: Tông Cam Nâu Đẳng Cấp & Ấm Cúng Chuẩn Luxury Restaurant (Cao ~70px) -->
        <div class="bg-gradient-to-r from-[#A84E00] via-[#C25E00] to-[#D97706] text-white px-4 py-3 flex items-center justify-between shadow-sm shrink-0">
            <div class="flex items-center gap-3 min-w-0">
                <div class="relative shrink-0">
                    <img src="{{ asset('images/logo/logo.png') }}" 
                         alt="CIVILIZE" 
                         class="w-10 h-10 rounded-full bg-white p-1 border-2 border-white/40 shadow-sm object-contain">
                    <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-emerald-400 border-2 border-[#C25E00] rounded-full"></span>
                </div>
                <div class="min-w-0">
                    <h3 class="font-bold text-[15px] leading-tight text-white flex items-center gap-1.5 truncate">
                        <span>CIVILIZE Restaurant</span>
                    </h3>
                    <p class="text-[11.5px] text-orange-100/90 flex items-center gap-1.5 mt-0.5 whitespace-nowrap truncate font-medium">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-300 animate-pulse shrink-0"></span>
                        <span class="truncate">Trực tuyến • Tư vấn món & đặt bàn</span>
                    </p>
                </div>
            </div>

            <!-- Header Actions: Tinh tế, kích thước đồng nhất -->
            <div class="flex items-center gap-1.5 shrink-0 ml-2">
                <button id="chatbot-sound-toggle" 
                        class="w-7.5 h-7.5 rounded-full bg-black/15 hover:bg-black/30 text-white flex items-center justify-center transition text-xs cursor-pointer" 
                        title="Bật/Tắt âm thanh">
                    <i id="chatbot-sound-icon" class="fa-solid fa-volume-high"></i>
                </button>
                <button id="chatbot-clear-btn" 
                        class="w-7.5 h-7.5 rounded-full bg-black/15 hover:bg-black/30 text-white flex items-center justify-center transition text-xs cursor-pointer" 
                        title="Làm mới cuộc trò chuyện">
                    <i class="fa-solid fa-rotate-right"></i>
                </button>
                <button id="chatbot-close-btn" 
                        class="w-7.5 h-7.5 rounded-full bg-black/15 hover:bg-black/30 text-white flex items-center justify-center transition text-sm cursor-pointer" 
                        title="Thu nhỏ cửa sổ">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>

        <!-- Messages Area: Nền kem sáng ấm áp, tin nhắn và gợi ý gắn liền tự nhiên -->
        <div id="chatbot-messages" class="flex-1 p-4 overflow-y-auto space-y-3.5 bg-[#FAF8F5] scroll-smooth">
            <!-- Messages rendered dynamically via JS -->
        </div>

        <!-- Typing Indicator -->
        <div id="chatbot-typing" class="hidden px-4 py-2 bg-[#FAF8F5] flex items-center gap-2 text-xs text-stone-500 shrink-0">
            <img src="{{ asset('images/logo/logo.png') }}" alt="CIVILIZE" class="w-6 h-6 rounded-full bg-white p-0.5 border border-stone-200 object-contain shadow-2xs">
            <span class="font-medium text-stone-600">Nhân viên đang soạn tin...</span>
            <div class="flex space-x-1 items-center">
                <span class="w-1.5 h-1.5 bg-[#C25E00] rounded-full animate-bounce" style="animation-delay: 0ms"></span>
                <span class="w-1.5 h-1.5 bg-[#C25E00] rounded-full animate-bounce" style="animation-delay: 150ms"></span>
                <span class="w-1.5 h-1.5 bg-[#C25E00] rounded-full animate-bounce" style="animation-delay: 300ms"></span>
            </div>
        </div>

        <!-- Integrated Modern Input Capsule: Chuẩn Dribbble / iMessage -->
        <div class="p-3 bg-white border-t border-stone-200/70 shrink-0">
            <form id="chatbot-form" class="relative flex items-center bg-stone-100 rounded-full pl-4 pr-1.5 py-1 border border-stone-200 focus-within:border-[#C25E00] focus-within:bg-white focus-within:ring-2 focus-within:ring-orange-100 transition-all shadow-inner" onsubmit="return false;">
                <input type="text" 
                       id="chatbot-input" 
                       placeholder="Hỏi món ngon, đặt bàn, kiểm tra đơn..." 
                       autocomplete="off"
                       class="flex-1 bg-transparent text-stone-800 text-xs sm:text-[13.5px] py-1.5 focus:outline-none placeholder:text-stone-400" />
                <button type="submit" 
                        id="chatbot-send-btn" 
                        class="w-9 h-9 rounded-full bg-[#C25E00] hover:bg-[#A34E00] text-white flex items-center justify-center transition-all transform hover:scale-105 active:scale-95 disabled:opacity-50 cursor-pointer shadow-xs shrink-0"
                        title="Gửi tin nhắn">
                    <i class="fa-solid fa-paper-plane text-xs"></i>
                </button>
            </form>
            <div class="text-[10px] text-stone-400 text-center mt-1.5 font-medium">
                CIVILIZE Restaurant • Phục vụ chu đáo & tận tâm
            </div>
        </div>
    </div>
</div>

<!-- Audio Chime for Realistic Chat Notification -->
<audio id="chatbot-chime" preload="auto">
    <source src="https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3" type="audio/mpeg">
</audio>

<style>
    #chatbot-messages::-webkit-scrollbar {
        width: 5px;
    }
    #chatbot-messages::-webkit-scrollbar-track {
        background: transparent;
    }
    #chatbot-messages::-webkit-scrollbar-thumb {
        background: #fed7aa;
        border-radius: 9999px;
    }
    #chatbot-messages::-webkit-scrollbar-thumb:hover {
        background: #fb923c;
    }
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const chatContainer = document.getElementById('civilize-chatbot');
    if (!chatContainer) return;

    const toggleBtn = document.getElementById('chatbot-toggle-btn');
    const triggerContainer = document.getElementById('chatbot-trigger-container');
    const chatWindow = document.getElementById('chatbot-window');
    const closeBtn = document.getElementById('chatbot-close-btn');
    const clearBtn = document.getElementById('chatbot-clear-btn');
    const soundToggle = document.getElementById('chatbot-sound-toggle');
    const soundIcon = document.getElementById('chatbot-sound-icon');
    const chime = document.getElementById('chatbot-chime');
    const messagesEl = document.getElementById('chatbot-messages');
    const typingEl = document.getElementById('chatbot-typing');
    const form = document.getElementById('chatbot-form');
    const input = document.getElementById('chatbot-input');
    const sendBtn = document.getElementById('chatbot-send-btn');

    let isOpen = false;
    let isSoundOn = localStorage.getItem('civilize_chatbot_sound') !== 'false';
    updateSoundIcon();

    const STORAGE_KEY = 'civilize_chatbot_messages_v2';
    const SESSION_KEY = 'civilize_chatbot_session_id';
    let sessionId = localStorage.getItem(SESSION_KEY);
    if (!sessionId) {
        sessionId = 'sess_' + Date.now() + '_' + Math.random().toString(36).substring(2, 9);
        localStorage.setItem(SESSION_KEY, sessionId);
    }
    let messageList = [];

    // Helper to strip emojis if needed
    function cleanEmoji(text) {
        if (!text) return '';
        return text
            .replace(/[\u{1F000}-\u{1FAFF}\u{2600}-\u{26FF}\u{2700}-\u{27BF}\u{2300}-\u{23FF}\u{2B50}\u{200D}\u{FE0F}\u{20E3}\u{2190}-\u{21FF}\u{2900}-\u{297F}]/gu, '')
            .replace(/^[ \t]+/gm, '')
            .replace(/[ ]{2,}/g, ' ')
            .trim();
    }

    // Load saved messages from v2 storage
    try {
        const saved = localStorage.getItem(STORAGE_KEY);
        if (saved) {
            messageList = JSON.parse(saved);
        }
    } catch (e) {
        messageList = [];
    }

    function openChat() {
        isOpen = true;
        chatWindow.classList.remove('hidden');
        setTimeout(() => {
            chatWindow.classList.remove('scale-95', 'opacity-0');
            chatWindow.classList.add('scale-100', 'opacity-100');
        }, 10);

        // Ẩn nút tròn nổi bên ngoài khi mở cửa sổ chatbot để không bị thừa thãi đè lên nhau
        if (triggerContainer) {
            triggerContainer.classList.add('scale-0', 'opacity-0', 'pointer-events-none');
            setTimeout(() => {
                if (isOpen) triggerContainer.classList.add('hidden');
            }, 250);
        }

        renderMessages();
        scrollToBottom();
        setTimeout(() => input.focus(), 200);

        if (messageList.length === 0) {
            loadInitialSuggestions();
        }
    }

    function closeChat() {
        isOpen = false;
        chatWindow.classList.remove('scale-100', 'opacity-100');
        chatWindow.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            chatWindow.classList.add('hidden');
            // Hiện lại nút tròn nổi khi đóng cửa sổ chatbot
            if (triggerContainer) {
                triggerContainer.classList.remove('hidden');
                setTimeout(() => {
                    triggerContainer.classList.remove('scale-0', 'opacity-0', 'pointer-events-none');
                }, 10);
            }
        }, 250);
    }

    toggleBtn.addEventListener('click', () => {
        if (isOpen) closeChat();
        else openChat();
    });

    closeBtn.addEventListener('click', closeChat);

    function updateSoundIcon() {
        if (isSoundOn) {
            soundIcon.className = 'fa-solid fa-volume-high';
        } else {
            soundIcon.className = 'fa-solid fa-volume-xmark';
        }
        localStorage.setItem('civilize_chatbot_sound', isSoundOn);
    }

    soundToggle.addEventListener('click', () => {
        isSoundOn = !isSoundOn;
        updateSoundIcon();
    });

    function playChime() {
        if (isSoundOn && chime) {
            chime.currentTime = 0;
            chime.play().catch(() => {});
        }
    }

    clearBtn.addEventListener('click', () => {
        if (confirm('Bạn có muốn làm mới toàn bộ cuộc trò chuyện không?')) {
            messageList = [];
            localStorage.removeItem(STORAGE_KEY);
            localStorage.removeItem('civilize_chatbot_messages'); // Xóa cả key cũ
            
            fetch('{{ route("chatbot.clear", [], false) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ session_id: sessionId })
            }).catch(() => {});

            sessionId = 'sess_' + Date.now() + '_' + Math.random().toString(36).substring(2, 9);
            localStorage.setItem(SESSION_KEY, sessionId);

            loadInitialSuggestions();
        }
    });

    function loadInitialSuggestions() {
        showTyping(true);
        fetch('{{ route("chatbot.suggestions", [], false) }}?session_id=' + encodeURIComponent(sessionId), {
            headers: {
                'Accept': 'application/json'
            }
        })
            .then(res => res.json())
            .then(data => {
                showTyping(false);
                if (data.success) {
                    addBotMessage(data.greeting, [], data.quick_replies);
                }
            })
            .catch(() => {
                showTyping(false);
                addBotMessage("Dạ em chào anh/chị! Chào mừng anh/chị đến với CIVILIZE Restaurant.\nEm có thể hỗ trợ anh/chị xem thực đơn, đặt bàn hoặc kiểm tra đơn hàng ạ.", [], [
                    { title: 'Gợi ý món ngon hôm nay', payload: 'Gợi ý món ngon hôm nay' },
                    { title: 'Tôi muốn đặt bàn', payload: 'Tôi muốn đặt bàn' },
                    { title: 'Kiểm tra đơn hàng', payload: 'Tra cứu đơn hàng' },
                    { title: 'Địa chỉ & Giờ mở cửa', payload: 'Địa chỉ nhà hàng ở đâu?' },
                    { title: 'Ưu đãi và Freeship', payload: 'Nhà hàng có ưu đãi gì không?' }
                ]);
            });
    }

    function addUserMessage(text) {
        const msg = {
            id: 'u_' + Date.now(),
            sender: 'user',
            text: text,
            time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
        };
        messageList.push(msg);
        saveMessages();
        renderMessages();
        scrollToBottom();
    }

    function addBotMessage(text, products = [], quickReplies = []) {
        const cleanText = cleanEmoji(text);
        const msg = {
            id: 'b_' + Date.now(),
            sender: 'bot',
            text: cleanText,
            products: products || [],
            quickReplies: quickReplies || [],
            time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
        };
        messageList.push(msg);
        saveMessages();
        renderMessages();
        scrollToBottom();
        playChime();
    }

    function saveMessages() {
        try {
            if (messageList.length > 30) {
                messageList = messageList.slice(messageList.length - 30);
            }
            localStorage.setItem(STORAGE_KEY, JSON.stringify(messageList));
        } catch (e) {}
    }

    function scrollToBottom() {
        setTimeout(() => {
            messagesEl.scrollTop = messagesEl.scrollHeight;
        }, 60);
    }

    function showTyping(show) {
        if (show) {
            typingEl.classList.remove('hidden');
        } else {
            typingEl.classList.add('hidden');
        }
        scrollToBottom();
    }

    function renderMessages() {
        messagesEl.innerHTML = '';
        messageList.forEach((msg, idx) => {
            if (msg.sender === 'user') {
                const row = document.createElement('div');
                row.className = 'flex justify-end';
                row.innerHTML = `
                    <div class="max-w-[82%] bg-[#C25E00] text-white rounded-2xl rounded-tr-xs px-4 py-2.5 shadow-xs text-xs sm:text-[13.5px] leading-relaxed break-words">
                        <div>${escapeHtml(msg.text)}</div>
                        <div class="text-[10px] text-orange-200 text-right mt-1">${msg.time || ''}</div>
                    </div>
                `;
                messagesEl.appendChild(row);
            } else {
                const row = document.createElement('div');
                row.className = 'flex items-start gap-2.5';
                
                let productsHtml = '';
                if (msg.products && msg.products.length > 0) {
                    productsHtml = `
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-3 pt-2.5 border-t border-stone-100">
                            ${msg.products.map(p => `
                                <div class="bg-stone-50/80 rounded-xl border border-stone-200/90 overflow-hidden shadow-2xs hover:shadow-xs transition group flex flex-col justify-between">
                                    <div>
                                        <a href="${p.url}" target="_blank" class="block overflow-hidden h-24 bg-stone-100">
                                            <img src="${p.image}" alt="${escapeHtml(p.name)}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                        </a>
                                        <div class="p-2.5 pb-1">
                                            <a href="${p.url}" target="_blank" class="font-bold text-xs text-stone-800 hover:text-[#C25E00] line-clamp-1 block" title="${escapeHtml(p.name)}">
                                                ${escapeHtml(p.name)}
                                            </a>
                                            <div class="text-[#C25E00] font-extrabold text-xs mt-0.5">${p.price}</div>
                                        </div>
                                    </div>
                                    <div class="p-2.5 pt-1.5 flex gap-1.5">
                                        <a href="${p.url}" target="_blank" class="flex-1 bg-white hover:bg-stone-100 text-stone-700 text-center text-[11px] font-semibold py-1.5 rounded-full border border-stone-200 transition">
                                            Chi tiết
                                        </a>
                                        <button type="button" 
                                                onclick="chatbotAddToCart(${p.id}, this)"
                                                class="flex-1 bg-[#C25E00] hover:bg-[#A34E00] text-white text-center text-[11px] font-bold py-1.5 rounded-full transition flex items-center justify-center gap-1 cursor-pointer shadow-2xs">
                                            <i class="fas fa-plus text-[9px]"></i> Thêm
                                        </button>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    `;
                }

                // Gợi ý nhanh gắn liền ngay dưới tin nhắn của bot (Loại bỏ hoàn toàn khoảng trắng trống trải!)
                let quickRepliesHtml = '';
                if (msg.quickReplies && msg.quickReplies.length > 0) {
                    quickRepliesHtml = `
                        <div class="flex flex-wrap gap-1.5 mt-3 pt-2.5 border-t border-stone-100">
                            ${msg.quickReplies.map(chip => {
                                const titleEscaped = escapeHtml(chip.title || '');
                                const payloadEncoded = encodeURIComponent(chip.payload || chip.title || '');
                                const urlEncoded = chip.url ? encodeURIComponent(chip.url) : '';
                                return `
                                    <button type="button" 
                                            data-payload="${payloadEncoded}"
                                            data-url="${urlEncoded}"
                                            onclick="window.chatbotHandleChipClick(this)"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-stone-100/90 hover:bg-orange-50 text-stone-700 hover:text-[#C25E00] border border-stone-200 hover:border-orange-300 rounded-full text-xs font-medium transition active:scale-95 cursor-pointer shadow-2xs shrink-0">
                                        <span>${titleEscaped}</span>
                                    </button>
                                `;
                            }).join('')}
                        </div>
                    `;
                }

                row.innerHTML = `
                    <img src="{{ asset('images/logo/logo.png') }}" alt="CIVILIZE" class="w-8 h-8 rounded-full bg-white p-1 border border-stone-200 shadow-2xs object-contain shrink-0 mt-0.5">
                    <div class="max-w-[86%] bg-white text-stone-800 rounded-2xl rounded-tl-xs px-4 py-3 shadow-xs border border-stone-200/80 text-xs sm:text-[13.5px] leading-relaxed break-words">
                        <div class="whitespace-pre-line leading-relaxed">${formatBotReply(msg.text)}</div>
                        ${productsHtml}
                        ${quickRepliesHtml}
                        <div class="text-[10px] text-stone-400 mt-1.5 flex items-center justify-between">
                            <span>${msg.time || ''}</span>
                            <span class="text-[10px] text-stone-400 font-medium">Tư vấn viên</span>
                        </div>
                    </div>
                `;
                messagesEl.appendChild(row);
            }
        });
    }

    function formatBotReply(text) {
        if (!text) return '';
        let clean = cleanEmoji(text);
        clean = clean.replace(/[\*\_#]/g, '');
        let escaped = escapeHtml(clean);
        return escaped.replace(/\n/g, '<br>');
    }

    function escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.innerText = str;
        return div.innerHTML;
    }

    window.chatbotSendQuickReply = function(payload, url) {
        if (url) {
            window.location.href = url;
        } else {
            sendMessage(payload);
        }
    };

    window.chatbotHandleChipClick = function(btn) {
        if (!btn) return;
        const payload = decodeURIComponent(btn.getAttribute('data-payload') || '');
        const url = decodeURIComponent(btn.getAttribute('data-url') || '');
        window.chatbotSendQuickReply(payload, url);
    };

    function sendMessage(text) {
        const clean = text.trim();
        if (!clean) return;

        addUserMessage(clean);
        input.value = '';
        input.focus();
        showTyping(true);
        sendBtn.disabled = true;

        fetch('{{ route("chatbot.message", [], false) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ 
                message: clean,
                session_id: sessionId
            })
        })
        .then(async res => {
            const isJson = res.headers.get('content-type')?.includes('application/json');
            const data = isJson ? await res.json() : null;
            showTyping(false);
            sendBtn.disabled = false;
            if (res.ok && data && data.success) {
                addBotMessage(data.reply, data.products || [], data.quick_replies || []);
            } else {
                const fallbackReply = data?.reply || data?.error || 'Dạ em chào anh/chị ạ! Hôm nay nhà hàng CIVILIZE có các món đặc sản như Lẩu bắp bò nhúng dấm, Bò la hũ nướng tảng và Sườn nướng BBQ rất ngon miệng. Anh/chị có thể gọi Hotline 0338 990 538 để được phục vụ nhanh nhất nhé ạ!';
                addBotMessage(fallbackReply, data?.products || [], data?.quick_replies || []);
            }
        })
        .catch((err) => {
            console.error('Chatbot request error:', err);
            showTyping(false);
            sendBtn.disabled = false;
            addBotMessage('Dạ kết nối mạng đang chậm một chút, anh chị vui lòng nhắn lại hoặc gọi trực tiếp Hotline 0338 990 538 để được hỗ trợ ngay nhé ạ!');
        });
    }

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        sendMessage(input.value);
    });

    // Global Add to Cart function from Chatbot
    window.chatbotAddToCart = function(productId, btnEl) {
        if (!productId) return;
        const originalHtml = btnEl.innerHTML;
        btnEl.disabled = true;
        btnEl.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        fetch(`/addcart/${productId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(() => {
            btnEl.className = 'flex-1 bg-emerald-600 text-white text-center text-[11px] font-bold py-1.5 rounded-full transition';
            btnEl.innerHTML = '<i class="fas fa-check"></i> Đã thêm';
            if (typeof toastr !== 'undefined') {
                toastr.success('Đã thêm món vào giỏ hàng thành công!');
            }
            setTimeout(() => {
                btnEl.className = 'flex-1 bg-[#C25E00] hover:bg-[#A34E00] text-white text-center text-[11px] font-bold py-1.5 rounded-full transition flex items-center justify-center gap-1 cursor-pointer shadow-2xs';
                btnEl.innerHTML = originalHtml;
                btnEl.disabled = false;
            }, 2500);
        })
        .catch(() => {
            btnEl.disabled = false;
            btnEl.innerHTML = originalHtml;
            window.location.href = `/addcart/${productId}`;
        });
    };
});
</script>
