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
     class="fixed z-40 print:hidden font-sans transition-all duration-200 bottom-20 md:bottom-6 left-4"
     style="left: calc(1rem + env(safe-area-inset-left, 0px));">
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
        
        <!-- Header: Nền trắng chữ đen trang nhã -->
        <div class="bg-white px-5 py-4 text-stone-900 relative border-b border-stone-200 shrink-0">
            <button type="button" id="closeServiceCallBtn" class="absolute top-3.5 right-3.5 text-stone-400 hover:text-stone-700 w-8 h-8 rounded-full flex items-center justify-center hover:bg-stone-100 transition-colors cursor-pointer" aria-label="Đóng">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
            <div>
                <h3 class="text-base font-bold tracking-tight text-stone-900 uppercase">Gọi Nhân Viên Phục Vụ</h3>
                <div class="flex items-center gap-2 mt-0.5">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[11px] font-semibold bg-stone-100 text-stone-700 border border-stone-200" id="modalTableBadge">
                        <span id="modalTableSubtitleText">{{ $currentTableName ? ('Vị trí: ' . $currentTableName) : 'Chọn số bàn của bạn' }}</span>
                    </span>
                    <span class="text-[11px] text-stone-500 hidden sm:inline">• Trực bàn sẵn sàng</span>
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
                    <span class="text-[11px] text-stone-400">Chạm để chọn (có thể chọn nhiều)</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2" id="serviceQuickChips">
                    <!-- Chip 1: Thêm đá -->
                    <button type="button" class="service-chip relative p-2.5 rounded-lg border border-stone-200 bg-stone-50/70 hover:bg-stone-100 text-center transition-all cursor-pointer group" data-type="water" data-label="Thêm đá">
                        <span class="text-xs font-semibold text-stone-800 leading-tight block">Thêm đá</span>
                        <svg class="chip-check hidden absolute top-1.5 right-1.5 w-3 h-3 text-[#C25E00]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                    </button>

                    <!-- Chip 2: Xin khăn lạnh / giấy -->
                    <button type="button" class="service-chip relative p-2.5 rounded-lg border border-stone-200 bg-stone-50/70 hover:bg-stone-100 text-center transition-all cursor-pointer group" data-type="cutlery" data-label="Xin khăn lạnh / giấy">
                        <span class="text-xs font-semibold text-stone-800 leading-tight block">Xin khăn lạnh / giấy</span>
                        <svg class="chip-check hidden absolute top-1.5 right-1.5 w-3 h-3 text-[#C25E00]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                    </button>

                    <!-- Chip 3: Xin thêm bát đĩa -->
                    <button type="button" class="service-chip relative p-2.5 rounded-lg border border-stone-200 bg-stone-50/70 hover:bg-stone-100 text-center transition-all cursor-pointer group" data-type="cutlery" data-label="Xin thêm bát đĩa">
                        <span class="text-xs font-semibold text-stone-800 leading-tight block">Xin thêm bát đĩa</span>
                        <svg class="chip-check hidden absolute top-1.5 right-1.5 w-3 h-3 text-[#C25E00]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                    </button>

                    <!-- Chip 4: Châm thêm nước -->
                    <button type="button" class="service-chip relative p-2.5 rounded-lg border border-stone-200 bg-stone-50/70 hover:bg-stone-100 text-center transition-all cursor-pointer group" data-type="water" data-label="Châm thêm nước">
                        <span class="text-xs font-semibold text-stone-800 leading-tight block">Châm thêm nước</span>
                        <svg class="chip-check hidden absolute top-1.5 right-1.5 w-3 h-3 text-[#C25E00]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                    </button>

                    <!-- Chip 5: Xin thêm gia vị -->
                    <button type="button" class="service-chip relative p-2.5 rounded-lg border border-stone-200 bg-stone-50/70 hover:bg-stone-100 text-center transition-all cursor-pointer group" data-type="cutlery" data-label="Xin thêm gia vị">
                        <span class="text-xs font-semibold text-stone-800 leading-tight block">Xin thêm gia vị</span>
                        <svg class="chip-check hidden absolute top-1.5 right-1.5 w-3 h-3 text-[#C25E00]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                    </button>

                    <!-- Chip 6: Yêu cầu thanh toán -->
                    <button type="button" class="service-chip relative p-2.5 rounded-lg border border-stone-200 bg-stone-50/70 hover:bg-stone-100 text-center transition-all cursor-pointer group" data-type="bill" data-label="Yêu cầu thanh toán">
                        <span class="text-xs font-semibold text-stone-800 leading-tight block">Yêu cầu thanh toán</span>
                        <svg class="chip-check hidden absolute top-1.5 right-1.5 w-3 h-3 text-[#C25E00]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
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

            <!-- Thông báo lỗi (nếu có) -->
            <div id="serviceCallStatusMsg" class="hidden text-xs p-3 rounded-md"></div>

            <!-- Footer: Nút bấm gửi yêu cầu -->
            <div class="pt-1">
                <div class="flex items-center gap-2.5">
                    <button type="button" id="cancelServiceCallBtn" class="px-4 py-2.5 text-xs font-semibold text-stone-600 hover:text-stone-900 bg-stone-100 hover:bg-stone-200 rounded-md transition-colors cursor-pointer">
                        Đóng
                    </button>
                    <button type="submit" id="submitServiceCallBtn" class="flex-1 py-2.5 px-4 text-xs font-bold text-white bg-[#C25E00] hover:bg-[#A34E00] rounded-md shadow-md transition-all flex items-center justify-center cursor-pointer uppercase tracking-wider">
                        <span id="submitServiceCallBtnText">Gửi Yêu Cầu Phục Vụ</span>
                    </button>
                </div>
                <p class="text-[11px] text-stone-500 text-center mt-2 leading-relaxed">
                    Nhân viên sẽ nhận được thông báo và hỗ trợ bạn sớm nhất.
                </p>
            </div>
        </form>

        <!-- Panel Trạng thái sau khi gửi (Xác nhận theo thời gian thực) -->
        <div id="serviceCallSuccessState" class="hidden p-5 text-center flex-1 overflow-y-auto space-y-4">
            <div class="w-12 h-12 bg-amber-50 text-[#C25E00] rounded-full flex items-center justify-center mx-auto border border-amber-200">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            </div>
            <div>
                <h4 class="text-base font-bold text-stone-900">Đã gửi yêu cầu thành công</h4>
                <p class="text-xs text-stone-500 mt-1" id="successStateSubtitle">
                    Nhân viên đã nhận được yêu cầu và đang đến hỗ trợ bàn của bạn.
                </p>
            </div>

            <!-- Box tóm tắt thông tin đã gửi -->
            <div class="bg-stone-50 rounded-lg p-3 text-left border border-stone-200 space-y-1.5 text-xs">
                <div class="flex justify-between text-stone-600">
                    <span>Số bàn:</span>
                    <strong class="text-stone-900 font-semibold" id="successStateTable">--</strong>
                </div>
                <div class="flex justify-between text-stone-600">
                    <span>Nội dung yêu cầu:</span>
                    <strong class="text-[#C25E00] font-semibold text-right max-w-[200px]" id="successStateRequests">--</strong>
                </div>
                <div id="successStateNoteRow" class="hidden flex justify-between text-stone-600">
                    <span>Ghi chú:</span>
                    <span class="text-stone-800 text-right italic max-w-[200px]" id="successStateNote"></span>
                </div>
            </div>

            <!-- Timeline trạng thái -->
            <div class="border-t border-stone-200 pt-3 text-left space-y-2">
                <div class="text-[11px] font-bold text-stone-500 uppercase tracking-wider">Tiến trình phục vụ</div>
                <div class="space-y-2 text-xs">
                    <div class="flex items-center gap-2 text-emerald-700 font-semibold">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>Đã tiếp nhận yêu cầu trên hệ thống</span>
                    </div>
                    <div class="flex items-center gap-2 text-amber-700 font-semibold">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                        <span id="callStatusStep2">Nhân viên đang trên đường đến bàn</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <button type="button" id="sendAnotherCallBtn" class="flex-1 py-2 text-xs font-semibold text-stone-700 bg-stone-100 hover:bg-stone-200 rounded-md transition-colors cursor-pointer">
                    Gửi thêm yêu cầu
                </button>
                <button type="button" id="closeSuccessModalBtn" class="flex-1 py-2 text-xs font-bold text-white bg-[#C25E00] hover:bg-[#A34E00] rounded-md transition-colors cursor-pointer">
                    Đóng cửa sổ
                </button>
            </div>
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

    // Xử lý chạm nhanh các thẻ nhu cầu (Service Chips) - Hỗ trợ chọn nhiều cùng lúc (Multi-select)
    chips.forEach(chip => {
        chip.addEventListener('click', function() {
            const isSelected = this.classList.contains('is-selected');
            const checkIcon = this.querySelector('.chip-check');

            if (!isSelected) {
                this.classList.add('is-selected', 'border-[#C25E00]', 'bg-amber-50', 'ring-1', 'ring-[#C25E00]');
                this.classList.remove('border-stone-200', 'bg-stone-50/70');
                if (checkIcon) checkIcon.classList.remove('hidden');
            } else {
                this.classList.remove('is-selected', 'border-[#C25E00]', 'bg-amber-50', 'ring-1', 'ring-[#C25E00]');
                this.classList.add('border-stone-200', 'bg-stone-50/70');
                if (checkIcon) checkIcon.classList.add('hidden');
            }

            // Đồng bộ call_type: nếu có chọn bill thì type là bill
            const selectedChips = document.querySelectorAll('.service-chip.is-selected');
            const hasBill = Array.from(selectedChips).some(c => c.getAttribute('data-type') === 'bill');
            typeInput.value = hasBill ? 'bill' : (selectedChips.length > 0 ? 'cutlery' : 'call_waiter');
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

            // Tập hợp các yêu cầu đã chọn
            const selectedChips = document.querySelectorAll('.service-chip.is-selected');
            const labels = Array.from(selectedChips).map(c => c.getAttribute('data-label'));
            const finalLabel = labels.length > 0 ? labels.join(', ') : 'Gọi nhân viên phục vụ';
            formData.set('call_type_label', finalLabel);

            submitBtn.disabled = true;
            submitBtnText.textContent = 'Đang gửi yêu cầu...';

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
                submitBtnText.textContent = 'Gửi Yêu Cầu Phục Vụ';

                if (data.success) {
                    playDing();

                    // Chuyển sang màn hình xác nhận phục vụ chi tiết
                    form.classList.add('hidden');
                    const successState = document.getElementById('serviceCallSuccessState');
                    if (successState) {
                        successState.classList.remove('hidden');

                        const selectedTableOption = tableSelect.options[tableSelect.selectedIndex];
                        const tableName = (selectedTableOption && selectedTableOption.value) ? selectedTableOption.text.split(' (')[0] : (modalTableSubtitleText.textContent.replace('Vị trí: ', '').replace('Bàn: ', '') || 'Bàn của bạn');
                        document.getElementById('successStateTable').textContent = tableName;
                        document.getElementById('successStateRequests').textContent = finalLabel;
                        
                        const customNote = noteInput.value.trim();
                        const noteRow = document.getElementById('successStateNoteRow');
                        if (customNote) {
                            document.getElementById('successStateNote').textContent = customNote;
                            noteRow.classList.remove('hidden');
                        } else {
                            noteRow.classList.add('hidden');
                        }
                    }

                    // Visual feedback trên nút chuông nổi
                    if (pingIndicator) pingIndicator.classList.remove('hidden');
                    if (bellIcon) bellIcon.classList.add('text-amber-300');
                    if (btnSub) btnSub.textContent = 'Đã gửi yêu cầu...';
                    if (btnAction) btnAction.textContent = 'Đang chờ';
                } else {
                    statusMsg.className = 'text-xs p-3 rounded-md bg-rose-50 text-rose-700 border border-rose-200 block';
                    statusMsg.textContent = data.message || 'Có lỗi xảy ra, vui lòng thử lại.';
                }
            })
            .catch(err => {
                submitBtn.disabled = false;
                submitBtnText.textContent = 'Gửi Yêu Cầu Phục Vụ';
                statusMsg.className = 'text-xs p-3 rounded-md bg-rose-50 text-rose-700 border border-rose-200 block';
                statusMsg.textContent = 'Không thể gửi yêu cầu, vui lòng vẫy tay gọi nhân viên trực tiếp.';
            });
        });
    }

    // Nút "Gửi thêm yêu cầu" và "Đóng cửa sổ" trong panel trạng thái
    const sendAnotherBtn = document.getElementById('sendAnotherCallBtn');
    const closeSuccessBtn = document.getElementById('closeSuccessModalBtn');

    if (sendAnotherBtn) {
        sendAnotherBtn.addEventListener('click', function() {
            const successState = document.getElementById('serviceCallSuccessState');
            if (successState) successState.classList.add('hidden');
            form.classList.remove('hidden');
            noteInput.value = '';
            chips.forEach(c => {
                c.classList.remove('is-selected', 'border-[#C25E00]', 'bg-amber-50', 'ring-1', 'ring-[#C25E00]');
                c.classList.add('border-stone-200', 'bg-stone-50/70');
                const checkEl = c.querySelector('.chip-check');
                if (checkEl) checkEl.classList.add('hidden');
            });
            typeInput.value = 'call_waiter';
        });
    }

    if (closeSuccessBtn) {
        closeSuccessBtn.addEventListener('click', function() {
            closeModal();
            setTimeout(() => {
                const successState = document.getElementById('serviceCallSuccessState');
                if (successState) successState.classList.add('hidden');
                form.classList.remove('hidden');
                noteInput.value = '';
                chips.forEach(c => {
                    c.classList.remove('is-selected', 'border-[#C25E00]', 'bg-amber-50', 'ring-1', 'ring-[#C25E00]');
                    c.classList.add('border-stone-200', 'bg-stone-50/70');
                    const checkEl = c.querySelector('.chip-check');
                    if (checkEl) checkEl.classList.add('hidden');
                });
                typeInput.value = 'call_waiter';
            }, 300);
        });
    }
});
</script>
