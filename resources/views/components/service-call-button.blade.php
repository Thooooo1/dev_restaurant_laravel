<!-- Floating Service Call Button (Chuông gọi nhân viên phục vụ tại bàn) -->
@php
    $currentTableId = session('table_id');
    $currentTableName = session('table_name');
    if ($currentTableId && !$currentTableName) {
        $foundTable = \App\Models\Table::find($currentTableId);
        $currentTableName = $foundTable ? $foundTable->name : null;
    }
@endphp

<div id="serviceCallFloatingWidget" 
     class="fixed z-40 print:hidden font-sans transition-all duration-200"
     style="bottom: calc(1.5rem + env(safe-area-inset-bottom, 0px)); left: calc(1rem + env(safe-area-inset-left, 0px));">
    <!-- Nút chuông gọi phục vụ: Thiết kế tinh tế, thuần typography cao cấp, tương thích Windows, Mac, iOS & Android -->
    <button type="button" 
            id="openServiceCallBtn"
            class="group flex items-center gap-2.5 bg-[#C25E00] hover:bg-[#A34E00] text-white px-4 py-2.5 rounded-full shadow-lg hover:shadow-xl transition-all duration-200 cursor-pointer select-none active:scale-95"
            title="Chuông gọi nhân viên phục vụ tại bàn">
        <div class="text-left">
            <span class="text-xs font-black tracking-wide uppercase block" id="serviceCallBtnTable">
                {{ $currentTableName ? ('Bàn ' . $currentTableName . ' • Gọi phục vụ') : 'Gọi phục vụ tại bàn' }}
            </span>
            <span class="block text-[10px] text-amber-100 font-normal leading-tight mt-0.5" id="serviceCallBtnSub">
                {{ $currentTableName ? 'Bấm để yêu cầu thêm đá, nước, gọi tính tiền' : 'Hỗ trợ tại bàn' }}
            </span>
        </div>
    </button>
</div>

<!-- Modal Chuông gọi nhân viên: Thiết kế ấm cúng, tương thích mọi kích thước màn hình điện thoại & máy tính -->
<div id="serviceCallModal" class="fixed inset-0 z-[999999] hidden bg-stone-950/70 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 transition-all duration-200" style="z-index: 999999 !important;">
    <div class="bg-white rounded-xl sm:rounded-2xl max-w-lg w-full max-h-[90dvh] flex flex-col shadow-2xl border border-stone-200/80 overflow-hidden transform scale-95 transition-all duration-200" id="serviceCallModalBox">
        
        <!-- Header: Tinh tế chuẩn ẩm thực CIVILIZE -->
        <div class="bg-stone-900 px-5 py-4 text-white relative border-b border-amber-600/30 shrink-0">
            <button type="button" id="closeServiceCallBtn" class="absolute top-3.5 right-3.5 text-stone-400 hover:text-white w-8 h-8 rounded-full flex items-center justify-center hover:bg-white/10 transition-colors cursor-pointer text-sm font-bold">
                ✕
            </button>
            <div>
                <h3 class="text-base font-bold tracking-tight text-white uppercase">Gọi Nhân Viên Phục Vụ</h3>
                <div class="flex items-center gap-2 mt-0.5">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-emerald-500/20 text-emerald-300 border border-emerald-500/30" id="modalTableBadge">
                        <span id="modalTableSubtitleText">{{ $currentTableName ? ('Vị trí: ' . $currentTableName) : 'Chọn số bàn của bạn' }}</span>
                    </span>
                    <span class="text-[11px] text-stone-400 hidden sm:inline">• Trực bàn sẵn sàng</span>
                </div>
            </div>
        </div>

        <!-- Body Form: Cuộn mượt trên thiết bị di động -->
        <form id="serviceCallForm" class="p-4 sm:p-5 space-y-4 overflow-y-auto flex-1">
            @csrf
            <input type="hidden" name="table_id" id="serviceCallTableId" value="{{ $currentTableId ?? '' }}">
            <input type="hidden" name="call_type" id="serviceCallTypeInput" value="call_waiter">

            <!-- Nếu chưa có bàn trong session -> Cho chọn số bàn -->
            <div id="tableSelectionBlock" class="{{ $currentTableId ? 'hidden' : '' }}">
                <label class="block text-xs font-bold text-stone-800 mb-1.5">
                    Quý khách đang ngồi tại bàn nào? <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <select id="serviceCallTableSelect" class="w-full text-xs px-3.5 py-2.5 bg-stone-50 border border-stone-300 rounded-md text-stone-900 focus:outline-none focus:border-[#C25E00] focus:bg-white transition-colors">
                        <option value="">-- Đang tải danh sách bàn ăn... --</option>
                    </select>
                </div>
            </div>

            <!-- Nhu cầu phục vụ thường gặp (Chọn nhanh một chạm thực tế của nhà hàng Việt Nam) -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-bold text-stone-800">
                        Quý khách cần hỗ trợ dịch vụ gì?
                    </label>
                    <span class="text-[11px] text-stone-400">Chạm để chọn nhanh</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2" id="serviceQuickChips">
                    <!-- Chip 1: Thêm đá lạnh -->
                    <button type="button" class="service-chip p-2.5 rounded-lg border border-stone-200 bg-stone-50/70 hover:bg-stone-100 text-center transition-all cursor-pointer group" data-type="water" data-note="Xin thêm đá lạnh">
                        <span class="text-xs font-semibold text-stone-800 leading-tight block">Thêm đá lạnh</span>
                    </button>

                    <!-- Chip 2: Khăn lạnh / Giấy ăn -->
                    <button type="button" class="service-chip p-2.5 rounded-lg border border-stone-200 bg-stone-50/70 hover:bg-stone-100 text-center transition-all cursor-pointer group" data-type="cutlery" data-note="Xin khăn lạnh / giấy ăn">
                        <span class="text-xs font-semibold text-stone-800 leading-tight block">Khăn lạnh / Giấy</span>
                    </button>

                    <!-- Chip 3: Bát đĩa / Đũa thìa -->
                    <button type="button" class="service-chip p-2.5 rounded-lg border border-stone-200 bg-stone-50/70 hover:bg-stone-100 text-center transition-all cursor-pointer group" data-type="cutlery" data-note="Xin thêm bát đĩa / đũa thìa">
                        <span class="text-xs font-semibold text-stone-800 leading-tight block">Bát đĩa / Đũa thìa</span>
                    </button>

                    <!-- Chip 4: Châm nước / Trà đá -->
                    <button type="button" class="service-chip p-2.5 rounded-lg border border-stone-200 bg-stone-50/70 hover:bg-stone-100 text-center transition-all cursor-pointer group" data-type="water" data-note="Châm thêm nước lọc / trà đá">
                        <span class="text-xs font-semibold text-stone-800 leading-tight block">Châm trà / Nước</span>
                    </button>

                    <!-- Chip 5: Gia vị (Ớt, chanh, mắm) -->
                    <button type="button" class="service-chip p-2.5 rounded-lg border border-stone-200 bg-stone-50/70 hover:bg-stone-100 text-center transition-all cursor-pointer group" data-type="cutlery" data-note="Xin thêm gia vị (ớt, chanh, nước mắm)">
                        <span class="text-xs font-semibold text-stone-800 leading-tight block">Gia vị / Ớt chanh</span>
                    </button>

                    <!-- Chip 6: Gọi tính tiền -->
                    <button type="button" class="service-chip p-2.5 rounded-lg border border-stone-200 bg-stone-50/70 hover:bg-stone-100 text-center transition-all cursor-pointer group" data-type="bill" data-note="Yêu cầu thanh toán hóa đơn tại bàn">
                        <span class="text-xs font-semibold text-stone-800 leading-tight block">Gọi tính tiền</span>
                    </button>
                </div>
            </div>

            <!-- Ghi chú thêm cho nhân viên -->
            <div>
                <label for="serviceCallNote" class="block text-xs font-bold text-stone-800 mb-1">
                    Ghi chú chi tiết cho nhân viên:
                </label>
                <textarea id="serviceCallNote" 
                          name="note" 
                          rows="2" 
                          placeholder="Ví dụ: Cần 2 ly đá, xin thêm chanh ớt tươi..."
                          class="w-full text-xs px-3.5 py-2.5 bg-stone-50 border border-stone-300 rounded-md focus:outline-none focus:border-[#C25E00] focus:bg-white resize-none text-stone-800 transition-colors"></textarea>
            </div>

            <!-- Thông báo trạng thái gửi -->
            <div id="serviceCallStatusMsg" class="hidden text-xs p-3 rounded-md"></div>

            <!-- Footer: Nút bấm chuẩn chuông nhà hàng -->
            <div class="flex items-center gap-2.5 pt-1">
                <button type="button" id="cancelServiceCallBtn" class="px-4 py-2.5 text-xs font-semibold text-stone-600 hover:text-stone-900 bg-stone-100 hover:bg-stone-200 rounded-md transition-colors cursor-pointer">
                    Đóng
                </button>
                <button type="submit" id="submitServiceCallBtn" class="flex-1 py-2.5 px-4 text-xs font-bold text-white bg-[#C25E00] hover:bg-[#A34E00] rounded-md shadow-md transition-all flex items-center justify-center cursor-pointer uppercase tracking-wider">
                    <span id="submitServiceCallBtnText">Rung Chuông Gọi Nhân Viên</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const openBtn = document.getElementById('openServiceCallBtn');
    const closeBtn = document.getElementById('closeServiceCallBtn');
    const cancelBtn = document.getElementById('cancelServiceCallBtn');
    const modal = document.getElementById('serviceCallModal');
    const modalBox = document.getElementById('serviceCallModalBox');
    const form = document.getElementById('serviceCallForm');
    const statusMsg = document.getElementById('serviceCallStatusMsg');
    const submitBtn = document.getElementById('submitServiceCallBtn');
    const submitBtnText = document.getElementById('submitServiceCallBtnText');
    const tableIdInput = document.getElementById('serviceCallTableId');
    const typeInput = document.getElementById('serviceCallTypeInput');
    const tableSelect = document.getElementById('serviceCallTableSelect');
    const noteInput = document.getElementById('serviceCallNote');
    const chips = document.querySelectorAll('.service-chip');
    const pingIndicator = document.getElementById('serviceCallPing');
    const bellIcon = document.getElementById('serviceBellIcon');
    const btnSub = document.getElementById('serviceCallBtnSub');
    const btnAction = document.getElementById('serviceCallBtnAction');
    const modalTableSubtitleText = document.getElementById('modalTableSubtitleText');

    // Âm thanh chuông bàn nhẹ nhàng (Web Audio API Ding!)
    function playDing() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(1318.5, ctx.currentTime); // Note E6
            osc.frequency.exponentialRampToValueAtTime(659.25, ctx.currentTime + 0.6); // Ramp down
            gain.gain.setValueAtTime(0.25, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.6);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.6);
        } catch (e) {
            // AudioContext not allowed or not supported, ignore silently
        }
    }

    // Xử lý chạm nhanh các thẻ nhu cầu (Service Chips)
    chips.forEach(chip => {
        chip.addEventListener('click', function() {
            const isSelected = this.classList.contains('border-[#C25E00]');
            
            // Bỏ chọn các chip khác
            chips.forEach(c => {
                c.classList.remove('border-[#C25E00]', 'bg-amber-500/10', 'ring-1', 'ring-[#C25E00]');
                c.classList.add('border-stone-200', 'bg-stone-50/70');
            });

            if (!isSelected) {
                this.classList.remove('border-stone-200', 'bg-stone-50/70');
                this.classList.add('border-[#C25E00]', 'bg-amber-500/10', 'ring-1', 'ring-[#C25E00]');
                
                const type = this.getAttribute('data-type') || 'call_waiter';
                const noteText = this.getAttribute('data-note') || '';
                typeInput.value = type;
                noteInput.value = noteText;
            } else {
                typeInput.value = 'call_waiter';
                noteInput.value = '';
            }
        });
    });

    // Mở modal
    function openModal() {
        if (modal && modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
        modal.classList.remove('hidden');
        setTimeout(() => {
            modalBox.classList.remove('scale-95');
            modalBox.classList.add('scale-100');
        }, 10);

        if (!tableIdInput.value) {
            loadTables();
        }
    }

    // Đóng modal
    function closeModal() {
        modalBox.classList.remove('scale-100');
        modalBox.classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 150);
    }

    if (openBtn) openBtn.addEventListener('click', openModal);
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) closeModal();
        });
    }

    // Tải danh sách bàn ăn (khi khách chưa quét QR tại bàn)
    let tablesLoaded = false;
    function loadTables() {
        if (tablesLoaded) return;
        fetch('{{ route("site.service-call.tables", [], false) }}', {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.floors) {
                tableSelect.innerHTML = '<option value="">-- Chọn số bàn của bạn --</option>';
                data.floors.forEach(floor => {
                    const optgroup = document.createElement('optgroup');
                    optgroup.label = floor.name;
                    if (floor.tables) {
                        floor.tables.forEach(tbl => {
                            const opt = document.createElement('option');
                            opt.value = tbl.id;
                            opt.textContent = tbl.name + ' (' + floor.name + ')';
                            if (data.current_table_id && data.current_table_id == tbl.id) {
                                opt.selected = true;
                                tableIdInput.value = tbl.id;
                            }
                            optgroup.appendChild(opt);
                        });
                    }
                    tableSelect.appendChild(optgroup);
                });
                tablesLoaded = true;
            }
        })
        .catch(err => console.error('Lỗi tải danh sách bàn:', err));
    }

    if (tableSelect) {
        tableSelect.addEventListener('change', function() {
            tableIdInput.value = this.value;
            const text = this.options[this.selectedIndex] ? this.options[this.selectedIndex].text : '';
            if (text) {
                modalTableSubtitleText.textContent = 'Bàn: ' + text;
                const btnTable = document.getElementById('serviceCallBtnTable');
                if (btnTable) btnTable.textContent = text.split(' (')[0];
            }
        });
    }

    // Submit form gọi phục vụ
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const tableId = tableIdInput.value;
            if (!tableId) {
                statusMsg.className = 'text-xs p-3 rounded-md bg-rose-50 text-rose-700 border border-rose-200 block';
                statusMsg.textContent = 'Vui lòng chọn số bàn để nhân viên đến phục vụ bạn!';
                return;
            }

            const formData = new FormData(form);
            formData.set('table_id', tableId);

            submitBtn.disabled = true;
            submitBtnText.textContent = 'Đang rung chuông...';

            fetch('{{ route("site.service-call.store", [], false) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                submitBtn.disabled = false;
                submitBtnText.textContent = 'Rung Chuông Gọi Nhân Viên';

                if (data.success) {
                    playDing();

                    statusMsg.className = 'text-xs p-3 rounded-md bg-emerald-50 text-emerald-800 border border-emerald-200 block';
                    statusMsg.textContent = data.message || 'Nhân viên đã nhận được chuông và đang đến ngay!';

                    // Visual feedback trên nút chuông nổi
                    if (pingIndicator) pingIndicator.classList.remove('hidden');
                    if (bellIcon) bellIcon.classList.add('text-amber-300');
                    if (btnSub) btnSub.textContent = 'Nhân viên đang đến...';
                    if (btnAction) btnAction.textContent = 'Đang gọi';

                    setTimeout(() => {
                        closeModal();
                        statusMsg.classList.add('hidden');
                        noteInput.value = '';
                        chips.forEach(c => {
                            c.classList.remove('border-[#C25E00]', 'bg-amber-500/10', 'ring-1', 'ring-[#C25E00]');
                            c.classList.add('border-stone-200', 'bg-stone-50/70');
                        });
                    }, 1800);
                } else {
                    statusMsg.className = 'text-xs p-3 rounded-md bg-rose-50 text-rose-700 border border-rose-200 block';
                    statusMsg.textContent = data.message || 'Có lỗi xảy ra, vui lòng thử lại.';
                }
            })
            .catch(err => {
                submitBtn.disabled = false;
                submitBtnText.textContent = 'Rung Chuông Gọi Nhân Viên';
                statusMsg.className = 'text-xs p-3 rounded-md bg-rose-50 text-rose-700 border border-rose-200 block';
                statusMsg.textContent = 'Không thể gửi yêu cầu, vui lòng vẫy tay gọi nhân viên trực tiếp.';
            });
        });
    }
});
</script>
