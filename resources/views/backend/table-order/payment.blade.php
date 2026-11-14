<x-layout-backend>

    <!-- Top Action Bar (Shopify POS / Toast POS / Ant Design Pro Style) -->
    <div class="bg-white border border-[#F0F0F0] rounded p-4 mb-4 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <!-- Left: Table & Context (Thuần typography) -->
            <div class="flex items-center gap-3">
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-lg font-bold text-[rgba(0,0,0,0.85)] tracking-tight">Thanh toán: {{ $table->name }}</h1>
                        <span class="polaris-badge polaris-badge-default">{{ $table->floor->name ?? 'Tầng 1' }}</span>
                        <span class="polaris-badge polaris-badge-warning">Chờ thanh toán</span>
                    </div>
                    <p class="text-xs text-[rgba(0,0,0,0.45)] mt-0.5">
                        Mã đơn: <span class="font-mono font-medium text-[rgba(0,0,0,0.65)]">#{{ $order->id }}</span>
                        @php
                            $dispCustName = $order->customer_name;
                            $dispCustPhone = $order->customer_phone;
                            $hasCustomName = !empty($dispCustName) && $dispCustName !== 'Khách tại bàn' && !str_starts_with(mb_strtolower($dispCustName), 'bàn');
                            $hasCustomPhone = !empty($dispCustPhone) && $dispCustPhone !== '---' && $dispCustPhone !== '—' && $dispCustPhone !== '0900000000';
                        @endphp
                        @if($hasCustomName)
                            • Khách: <strong>{{ $dispCustName }}</strong>
                        @endif
                        @if($hasCustomPhone)
                            <span class="text-[rgba(0,0,0,0.65)] font-mono">({{ $dispCustPhone }})</span>
                        @endif
                        • Bắt đầu: <span class="text-[rgba(0,0,0,0.65)]">{{ $order->created_at ? $order->created_at->format('H:i - d/m/Y') : 'Vừa xong' }}</span>
                    </p>
                </div>
            </div>

            <!-- Right: Action Buttons -->
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('admin.table-order.index') }}" class="ant-btn ant-btn-sm">
                    <i class="fa-solid fa-arrow-left text-xs"></i>
                    <span>Quay lại</span>
                </a>
                <button type="button" class="ant-btn ant-btn-sm" id="btnEditMode" onclick="toggleEditMode()">
                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                    <span id="btnEditModeText">Sửa SL</span>
                </button>
                <button type="button" class="ant-btn ant-btn-sm" onclick="printProvisionalBill()">
                    <i class="fa-solid fa-print text-xs"></i>
                    <span>In hoá đơn</span>
                </button>
                <button type="button" class="ant-btn ant-btn-primary ant-btn-sm font-semibold" onclick="handleConfirmPayment()">
                    <span>{{ ($order->payment_status === \App\Models\Order::PAYMENT_PAID || $order->isPaid()) ? 'Hoàn tất & Giải phóng bàn' : 'Xác nhận thanh toán' }}</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Main Payment Canvas -->
    <div class="ant-card p-5 shadow-sm">
        @if($order->payment_status === \App\Models\Order::PAYMENT_PAID || $order->isPaid())
            <div class="p-4 mb-5 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm shrink-0">✓</span>
                    <div>
                        <div class="font-bold text-emerald-900 text-sm">
                            Khách hàng đã thanh toán thành công qua {{ $order->payment_method == 2 || $order->payment_method === 'VietQR' ? 'Chuyển khoản VietQR' : 'Ngân hàng' }}!
                        </div>
                        <div class="text-xs text-emerald-700 mt-0.5">
                            Thời gian ghi nhận: {{ $order->paid_at ? $order->paid_at->format('H:i:s d/m/Y') : date('H:i:s d/m/Y') }} • Số tiền: {{ number_format($order->total_price ?: $totalAmount, 0, ',', '.') }}₫
                        </div>
                    </div>
                </div>
                <button type="button" onclick="handleConfirmPayment()" class="py-2 px-4 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-lg text-xs uppercase tracking-wider transition-colors cursor-pointer shadow-2xs">
                    Chốt & Giải phóng bàn
                </button>
            </div>
        @endif
        <!-- Payment Options & Total Header -->
        <div class="p-5 bg-[#FAFAFA] border border-[#F0F0F0] rounded-xl mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3.5">
                <label for="paymentMethodSelect" class="text-sm font-bold text-gray-700 uppercase tracking-wide">Phương thức thanh toán:</label>
                <select class="polaris-select text-sm font-semibold py-2 px-3.5 rounded-lg border-gray-300 bg-white" name="payment_method" id="paymentMethodSelect">
                    <option value="1" {{ ($order->payment_method == 1 || !$order->payment_method) ? 'selected' : '' }}>Tiền mặt</option>
                    <option value="2" {{ $order->payment_method == 2 ? 'selected' : '' }}>Chuyển khoản VietQR</option>
                    <option value="3" {{ $order->payment_method == 3 ? 'selected' : '' }}>Quẹt thẻ POS (ATM / Visa / Master)</option>
                </select>
                <button type="button" 
                        id="openVietQrModalBtn" 
                        onclick="openVietQrModal()" 
                        class="px-3.5 py-2 border border-gray-300 bg-white hover:bg-gray-50 text-gray-800 rounded-lg text-xs font-bold shadow-2xs flex items-center justify-center cursor-pointer transition-colors">
                    <span>Mở mã QR</span>
                </button>
            </div>
            <div class="flex items-center gap-3 sm:justify-end">
                <span class="text-sm font-bold text-gray-500 uppercase tracking-wide">Tổng tiền thanh toán:</span>
                <span class="text-3xl font-black font-mono text-[#F47716]" id="total-amount-header">{{ number_format($totalAmount, 0, ',', '.') }} ₫</span>
            </div>
        </div>

        <!-- Table of Items (Polaris Table Style) -->
        <div class="overflow-x-auto border border-[#F0F0F0] rounded-xl shadow-2xs">
            <table class="w-full" id="paymentTable">
                <thead class="bg-gray-50/80 border-b border-[#F0F0F0]">
                    <tr class="text-xs uppercase text-gray-500 font-bold tracking-wider">
                        <th class="w-14 py-3.5 px-4 text-center">#</th>
                        <th class="py-3.5 px-4 text-left text-sm font-bold text-gray-700">Tên món ăn</th>
                        <th class="py-3.5 px-4 text-right text-sm font-bold text-gray-700">Số lượng</th>
                        <th class="py-3.5 px-4 text-right text-sm font-bold text-gray-700">Đơn giá</th>
                        <th class="py-3.5 px-4 text-right text-sm font-bold text-gray-700">Thành tiền</th>
                        <th class="py-3.5 px-4 text-center action-col w-12"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($orderDetails as $i => $item)
                        <tr data-id="{{ $item->id }}" data-price="{{ $item->price }}" data-name="{{ $item->product->name ?? 'N/A' }}" class="hover:bg-amber-50/20 transition-colors">
                            <td class="py-3.5 px-4 text-center font-mono text-sm text-gray-400">{{ $i + 1 }}</td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-base text-gray-900">{{ $item->product->name ?? 'N/A' }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <span class="qty-display font-mono font-bold text-base text-gray-900 bg-gray-100 px-3 py-1 rounded-md">{{ $item->qty }}</span>
                                <input type="number" class="polaris-input qty-input d-none text-right font-mono text-base font-bold" style="width: 80px; display: inline-block;" value="{{ $item->qty }}" min="1" oninput="updateRowTotal(this)">
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono text-base text-gray-600">{{ number_format($item->price, 0, ',', '.') }} ₫</td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-base text-gray-900 amount-display">{{ number_format($item->amount, 0, ',', '.') }} ₫</td>
                            <td class="py-3.5 px-4 text-center action-col">
                                <button class="text-gray-400 hover:text-red-500 transition-colors btn-delete-item disabled p-1" disabled onclick="confirmDeleteItem({{ $item->id }})">
                                    <i class="fa-solid fa-trash-can text-sm"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Bottom Action Toolbar -->
        <div class="mt-6 pt-5 border-t border-[#F0F0F0] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <a href="{{ route('admin.table-order.index') }}" class="ant-btn text-sm py-2 px-4 font-medium flex items-center gap-2">
                <i class="fa-solid fa-arrow-left text-xs"></i>
                <span>Quay lại sơ đồ bàn</span>
            </a>
            <div class="flex items-center gap-3">
                <button type="button" class="ant-btn text-sm py-2 px-4 font-medium flex items-center gap-2" onclick="printProvisionalBill()">
                    <i class="fa-solid fa-print text-xs"></i>
                    <span>In hoá đơn</span>
                </button>
                <button type="button" class="ant-btn ant-btn-primary px-6 py-2.5 text-base font-bold flex items-center gap-2 shadow-xs" onclick="handleConfirmPayment()">
                    <i class="fa-solid fa-check text-sm"></i>
                    <span>Xác nhận thu tiền</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Add Product -->
    <div class="modal fade" id="modalAddProduct" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm sản phẩm</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Danh mục</label>
                        <select class="form-control" id="categorySelect" onchange="loadProducts(this.value)">
                            <option value="">-- Chọn danh mục --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Sản phẩm</label>
                        <select class="form-control" id="productSelect">
                            <option value="">-- Chọn sản phẩm --</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Số lượng</label>
                        <input type="number" class="form-control" id="productQty" value="1" min="1">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-primary" id="btnAddProduct" onclick="addProductToOrder()">Thêm</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal VietQR Hiện Đại Chuẩn UI Kiểu Mới (Theo phong cách Checkout 2 cột) -->
    <div id="modalVietQr" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
        <div class="bg-white rounded-2xl max-w-3xl w-full shadow-2xl border border-gray-100 overflow-hidden transform transition-all duration-300">
            <!-- Modal Header -->
            <div class="px-6 py-4 bg-white text-gray-900 flex items-center justify-between border-b border-gray-100">
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-gray-900">Thanh Toán Chuyển Khoản VietQR</h3>
                    <p class="text-[11px] text-gray-500 mt-0.5">{{ $table->name }} • {{ $table->floor->name ?? 'Khu vực chính' }} • Đơn #{{ $order->id }}</p>
                </div>
                <button type="button" onclick="closeVietQrModal()" class="text-gray-400 hover:text-gray-700 text-sm w-8 h-8 rounded-lg flex items-center justify-center hover:bg-gray-100 transition-colors cursor-pointer border border-gray-200">
                    ✕
                </button>
            </div>

            <!-- Modal Body (2 Cột chuẩn như thiết kế Checkout) -->
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-stretch">
                    
                    <!-- Cột Trái (7 cols): Mã QR Code & Đếm ngược thời gian thực -->
                    <div class="md:col-span-7 flex flex-col items-center justify-between p-5 bg-[#FAF8F5] rounded-xl border border-stone-200/80 text-center">
                        <div class="w-full">
                            <p class="text-xs font-semibold text-stone-700 leading-relaxed mb-3">
                                Quét mã QR dưới đây bằng ứng dụng Ngân hàng hoặc Ví điện tử để hoàn tất thanh toán.
                            </p>
                            
                            <!-- Khung chứa mã QR trắng tinh tế -->
                            <div class="relative inline-block p-3 bg-white rounded-xl border border-stone-300/80 shadow-md group">
                                <img src="" id="posVietQrImage" alt="VietQR Bàn" class="w-60 h-60 sm:w-64 sm:h-64 object-contain rounded-lg mx-auto">
                                
                                <!-- Overlay khi nhận tiền thành công -->
                                <div id="posPaymentSuccessOverlay" class="hidden absolute inset-0 bg-white/95 rounded-xl flex flex-col items-center justify-center p-4 text-center z-10">
                                    <div class="w-14 h-14 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-3xl mb-2 animate-bounce">
                                        <i class="fa-solid fa-circle-check"></i>
                                    </div>
                                    <h4 class="text-base font-black text-gray-900">ĐÃ NHẬN TIỀN THÀNH CÔNG!</h4>
                                    <p class="text-xs text-gray-500 mt-0.5">Webhook ngân hàng đã tự động xác nhận.</p>
                                    <span class="text-xs text-emerald-600 font-bold mt-2">Đang hoàn tất hóa đơn...</span>
                                </div>
                            </div>
                        </div>

                        <!-- Countdown Timer & Lắng nghe thời gian thực -->
                        <div class="w-full mt-4 pt-3 border-t border-gray-100 flex items-center justify-center gap-2 text-xs">
                            <div class="flex items-center gap-1.5 text-gray-600 font-medium">
                                <i class="fa-regular fa-clock text-gray-500"></i>
                                <span>Mã QR có hiệu lực:</span>
                                <span id="posQrCountdown" class="font-mono font-bold text-gray-800 bg-gray-100 px-2 py-0.5 rounded border border-gray-200">03:00</span>
                            </div>
                        </div>
                    </div>

                    <!-- Cột Phải (5 cols): Tổng Tiền & Chi tiết chuyển khoản & Nút thao tác -->
                    <div class="md:col-span-5 flex flex-col justify-between space-y-4">
                        <div class="space-y-3">
                            <!-- Card Tổng tiền đơn giản, thanh lịch -->
                            <div class="p-4 bg-white rounded-xl border border-gray-200 shadow-2xs flex items-center justify-between">
                                <div>
                                    <span class="text-xs uppercase tracking-wider text-gray-900 font-bold block">Tổng thanh toán</span>
                                    <span class="text-xs text-gray-500 font-medium">VietQR (Napas 247)</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-2xl font-black font-mono tracking-tight text-gray-900" id="posQrAmount">0 ₫</span>
                                </div>
                            </div>

                            <!-- Thông tin chuyển khoản chi tiết -->
                            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200/80 text-xs space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-500">Phương thức:</span>
                                    <span class="font-bold text-gray-800">VietQR (Napas 247)</span>
                                </div>
                                <div class="flex items-center justify-between pt-1 border-t border-gray-100">
                                    <span class="text-gray-500">Nội dung CK:</span>
                                    <div class="flex items-center gap-1">
                                        <span class="font-mono font-bold text-gray-900 bg-gray-100 px-2 py-0.5 rounded border border-gray-200" id="posQrMemo">BAN{{ $table->id }}</span>
                                        <button type="button" onclick="copyPosMemo()" class="text-[11px] text-gray-500 hover:text-gray-800 cursor-pointer p-0.5" title="Sao chép nội dung">
                                            <i class="fa-regular fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between pt-1 border-t border-gray-100">
                                    <span class="text-gray-500">Ngân hàng:</span>
                                    <span class="font-bold text-gray-800">{{ config('vietqr.bank_name') }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-500">Số tài khoản:</span>
                                    <span class="font-mono font-bold text-gray-800">{{ config('vietqr.account_no') }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-500">Chủ tài khoản:</span>
                                    <span class="font-bold text-gray-800 uppercase">{{ config('vietqr.account_name') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Nút Thao Tác: Bấm thanh toán là thành công luôn không cần giả lập Webhook -->
                        <div class="pt-2">
                            <button type="button" 
                                    id="posConfirmPayBtn"
                                    onclick="confirmVietQrPaymentNow()" 
                                    class="w-full py-2.5 px-4 bg-gray-900 hover:bg-black text-white rounded-xl text-xs font-bold transition-all flex items-center justify-center cursor-pointer shadow-xs active:scale-[0.99]">
                                <span>Xác nhận thanh toán thành công</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let isEditMode = false;
        let originalQuantities = {}; // Lưu giá trị số lượng ban đầu
        let posPollInterval = null;

        function openVietQrModal() {
            // Tính tổng tiền hiện tại
            let currentTotal = 0;
            document.querySelectorAll('#paymentTable tbody tr').forEach(tr => {
                const price = parseFloat(tr.dataset.price) || 0;
                const qtyInput = tr.querySelector('.qty-input');
                const qty = qtyInput ? parseInt(qtyInput.value) : 1;
                currentTotal += price * qty;
            });

            if (currentTotal <= 0) {
                currentTotal = {{ (int)$totalAmount }};
            }

            const bankId = '{{ config("vietqr.bank_id") }}';
            const accountNo = '{{ config("vietqr.account_no") }}';
            const accountName = encodeURIComponent('{{ config("vietqr.account_name") }}');
            const memo = 'BAN{{ $table->id }}';

            const qrUrl = `https://img.vietqr.io/image/${bankId}-${accountNo}-compact2.png?amount=${currentTotal}&addInfo=${memo}&accountName=${accountName}`;

            document.getElementById('posVietQrImage').src = qrUrl;
            document.getElementById('posQrAmount').textContent = currentTotal.toLocaleString('vi-VN') + ' ₫';
            document.getElementById('posQrMemo').textContent = memo;

            // Đặt phương thức thanh toán là Chuyển khoản VietQR
            document.getElementById('paymentMethodSelect').value = '2';

            document.getElementById('modalVietQr').classList.remove('hidden');

            // Bắt đầu đếm ngược thời gian hiệu lực và polling Webhook
            startPosCountdown();
            startPosPaymentPolling();
        }

        function closeVietQrModal() {
            document.getElementById('modalVietQr').classList.add('hidden');
            stopPosCountdown();
            if (posPollInterval) {
                clearInterval(posPollInterval);
                posPollInterval = null;
            }
        }

        let posCountdownSeconds = 180; // 3 phút đếm ngược chuẩn như ảnh 1
        let posCountdownTimer = null;

        function startPosCountdown() {
            posCountdownSeconds = 180;
            updatePosCountdownDisplay();
            if (posCountdownTimer) clearInterval(posCountdownTimer);
            posCountdownTimer = setInterval(function() {
                posCountdownSeconds--;
                if (posCountdownSeconds <= 0) {
                    clearInterval(posCountdownTimer);
                    const el = document.getElementById('posQrCountdown');
                    if (el) el.textContent = 'Hết hạn';
                } else {
                    updatePosCountdownDisplay();
                }
            }, 1000);
        }

        function stopPosCountdown() {
            if (posCountdownTimer) {
                clearInterval(posCountdownTimer);
                posCountdownTimer = null;
            }
        }

        function updatePosCountdownDisplay() {
            const mins = Math.floor(posCountdownSeconds / 60).toString().padStart(2, '0');
            const secs = (posCountdownSeconds % 60).toString().padStart(2, '0');
            const el = document.getElementById('posQrCountdown');
            if (el) el.textContent = `${mins}:${secs}`;
        }

        function copyPosMemo() {
            const memo = document.getElementById('posQrMemo')?.textContent;
            if (memo && navigator.clipboard) {
                navigator.clipboard.writeText(memo).then(() => {
                    toastr.success('Đã sao chép nội dung chuyển khoản: ' + memo);
                });
            }
        }

        function startPosPaymentPolling() {
            if (posPollInterval) clearInterval(posPollInterval);

            posPollInterval = setInterval(function() {
                fetch('{{ route("api.payment.check-status", $order->id) }}')
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && data.is_paid) {
                            clearInterval(posPollInterval);
                            posPollInterval = null;

                            // Hiển thị overlay thành công
                            document.getElementById('posPaymentSuccessOverlay')?.classList.remove('hidden');
                            document.getElementById('posQrStatus')?.classList.add('hidden');

                            toastr.success('Hệ thống Webhook đã xác nhận thanh toán thành công!');
                            printProvisionalBill(true);

                            setTimeout(() => {
                                window.location.href = '{{ route("admin.table-order.index") }}';
                            }, 2000);
                        }
                    })
                    .catch(err => console.log('Checking POS payment status:', err));
            }, 3000);
        }

        // Bấm xác nhận thanh toán trực tiếp trong modal VietQR thành công luôn
        function confirmVietQrPaymentNow() {
            const btn = document.getElementById('posConfirmPayBtn');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang hoàn tất thanh toán...';
            }

            stopPosCountdown();
            if (posPollInterval) {
                clearInterval(posPollInterval);
                posPollInterval = null;
            }

            // Tính tổng tiền hiện tại
            let currentTotal = 0;
            const rows = document.querySelectorAll('#paymentTable tbody tr');
            const items = [];
            rows.forEach(tr => {
                const id = tr.dataset.id;
                const price = parseFloat(tr.dataset.price) || 0;
                const qtyInput = tr.querySelector('.qty-input');
                const qty = qtyInput ? parseInt(qtyInput.value) : 1;
                items.push({ id, qty });
                currentTotal += price * qty;
            });

            if (currentTotal <= 0) {
                currentTotal = {{ (int)$totalAmount }};
            }

            // Lưu số lượng trước, sau đó xác nhận thanh toán trực tiếp
            $.ajax({
                url: '{{ route("admin.table-order.updateTableOrderQuantities") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    order_id: {{ $order->id }},
                    items: items
                },
                success: function() {
                    $.ajax({
                        url: '{{ route("admin.table-order.processPayment") }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            order_id: {{ $order->id }},
                            payment_method: 2, // VietQR
                            total_price: currentTotal
                        },
                        success: function(payRes) {
                            if (payRes.success) {
                                // Hiển thị overlay thành công ngay lập tức!
                                document.getElementById('posPaymentSuccessOverlay')?.classList.remove('hidden');
                                document.getElementById('posQrStatus')?.classList.add('hidden');

                                toastr.success('Thanh toán Chuyển khoản VietQR thành công!');
                                printProvisionalBill(true);

                                setTimeout(() => {
                                    window.location.href = '{{ route("admin.table-order.index") }}';
                                }, 1800);
                            } else {
                                if (btn) {
                                    btn.disabled = false;
                                    btn.innerHTML = '<span>Xác nhận thanh toán thành công</span>';
                                }
                                toastr.error(payRes.message || 'Thanh toán thất bại');
                            }
                        },
                        error: function() {
                            if (btn) {
                                btn.disabled = false;
                                btn.innerHTML = '<span>Xác nhận thanh toán thành công</span>';
                            }
                            toastr.error('Lỗi khi gửi yêu cầu thanh toán');
                        }
                    });
                },
                error: function() {
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = '<span>Xác nhận thanh toán thành công</span>';
                    }
                    toastr.error('Lỗi cập nhật đơn hàng trước khi thanh toán');
                }
            });
        }

        function simulatePosWebhook() {
            const btn = document.getElementById('posSimulateBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang giả lập Webhook...';

            let currentTotal = 0;
            document.querySelectorAll('#paymentTable tbody tr').forEach(tr => {
                const price = parseFloat(tr.dataset.price) || 0;
                const qtyInput = tr.querySelector('.qty-input');
                const qty = qtyInput ? parseInt(qtyInput.value) : 1;
                currentTotal += price * qty;
            });

            fetch('{{ route("api.payment.simulate-webhook") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    order_id: {{ $order->id }},
                    amount: currentTotal > 0 ? currentTotal : {{ (int)$totalAmount }}
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    btn.innerHTML = '<i class="fa-solid fa-check text-emerald-600"></i> Đã gửi Webhook thành công!';
                } else {
                    btn.disabled = false;
                    btn.innerHTML = 'Thử lại';
                    toastr.error(data.message || 'Lỗi giả lập');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = 'Thử lại';
                toastr.error('Lỗi kết nối Webhook');
            });
        }

        // Lưu giá trị ban đầu khi trang load
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.qty-input');
            inputs.forEach(input => {
                const tr = input.closest('tr');
                const id = tr.dataset.id;
                originalQuantities[id] = parseInt(input.value);
            });

            // Tự động mở modal khi chọn VietQR
            const paymentSelect = document.getElementById('paymentMethodSelect');
            if (paymentSelect) {
                paymentSelect.addEventListener('change', function() {
                    if (this.value === '2') {
                        openVietQrModal();
                    }
                });
            }
        });

        function saveQuantities() {
            const inputs = document.querySelectorAll('.qty-input');
            const items = [];
            let isValid = true;
            let hasChanges = false; // Kiểm tra xem có thay đổi gì không

            inputs.forEach(input => {
                const qty = parseInt(input.value);
                const tr = input.closest('tr');
                const id = tr.dataset.id;
                
                if (qty < 1 || isNaN(qty)) {
                    toastr.error('Số lượng món không hợp lệ');
                    isValid = false;
                    return;
                }

                // Kiểm tra xem có thay đổi so với giá trị ban đầu không
                if (qty !== originalQuantities[id]) {
                    hasChanges = true;
                    items.push({
                        id: id,
                        qty: qty
                    });
                }
            });

            if (!isValid) return false;

            // Nếu không có thay đổi gì, chỉ cần thoát chế độ sửa không cần gọi API
            if (!hasChanges) {
                toastr.info('Không có thay đổi nào để cập nhật');
                return true;
            }

            const printBtn = document.querySelector('button[onclick="handlePrintInvoice()"]');
            printBtn.disabled = true;
            printBtn.classList.add('opacity-50', 'cursor-not-allowed');

            $.ajax({
                url: '{{ route("admin.table-order.updateTableOrderQuantities") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    order_id: {{ $order->id }},
                    items: items
                },
                success: function(res) {
                    if (res.success) {
                        toastr.success('Cập nhật số lượng thành công');
                        // Cập nhật lại giá trị ban đầu sau khi lưu thành công
                        items.forEach(item => {
                            originalQuantities[item.id] = item.qty;
                        });
                        // Enable nút in sau khi lưu xong
                        printBtn.disabled = false;
                        printBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                        location.reload();
                    } else {
                        toastr.error(res.message);
                        printBtn.disabled = false;
                        printBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    }
                },
                error: function(err) {
                    toastr.error('Lỗi cập nhật đơn hàng');
                    printBtn.disabled = false;
                    printBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            });

            return true;
        }

        // In trực tiếp thông qua iframe ẩn để không bị trình duyệt chặn popup
        function directPrintHTML(html) {
            let iframe = document.getElementById('payment_print_iframe');
            if (!iframe) {
                iframe = document.createElement('iframe');
                iframe.id = 'payment_print_iframe';
                iframe.style.position = 'fixed';
                iframe.style.right = '0';
                iframe.style.bottom = '0';
                iframe.style.width = '0';
                iframe.style.height = '0';
                iframe.style.border = '0';
                document.body.appendChild(iframe);
            }
            const doc = iframe.contentWindow.document;
            doc.open();
            doc.write(html);
            doc.close();
            iframe.contentWindow.focus();
            setTimeout(() => {
                try {
                    iframe.contentWindow.print();
                } catch(e) {
                    console.error('Lỗi khi in:', e);
                }
            }, 300);
        }

        function toggleEditMode() {
            if (isEditMode) {
                saveQuantities();
            }

            isEditMode = !isEditMode;
            const deleteBtns = document.querySelectorAll('.btn-delete-item');
            const qtyDisplays = document.querySelectorAll('.qty-display');
            const qtyInputs = document.querySelectorAll('.qty-input');
            const btn = document.getElementById('btnEditMode');

            if (isEditMode) {
                deleteBtns.forEach(el => el.removeAttribute('disabled'));
                qtyDisplays.forEach(el => el.classList.add('d-none'));
                qtyInputs.forEach(el => el.classList.remove('d-none'));
                btn.innerHTML = '<i class="fa-solid fa-check"></i> <span id="btnEditModeText">Xong</span>';
                btn.classList.remove('btn-warning');
                btn.classList.add('btn-info');
            } else {
                deleteBtns.forEach(el => el.setAttribute('disabled', 'disabled'));
                qtyDisplays.forEach(el => el.classList.remove('d-none'));
                qtyInputs.forEach(el => el.classList.add('d-none'));
                btn.innerHTML = '<i class="fa-solid fa-pen-to-square"></i> <span id="btnEditModeText">Sửa SL</span>';
                btn.classList.remove('btn-info');
                btn.classList.add('btn-warning');
            }
        }

        // In hoá đơn tạm tính hoặc hoá đơn thanh toán
        function printProvisionalBill(isFinal = false) {
            const paymentMethodValue = document.getElementById('paymentMethodSelect').value;
            const paymentMethodText = paymentMethodValue === '2' ? 'Chuyển khoản VietQR' : (paymentMethodValue === '3' ? 'Quẹt thẻ POS' : 'Tiền mặt');

            const items = [];
            let total = 0;

            document.querySelectorAll('#paymentTable tbody tr').forEach((tr, idx) => {
                const name = tr.dataset.name || 'Món ăn';
                const price = parseFloat(tr.dataset.price) || 0;
                const qtyInput = tr.querySelector('.qty-input');
                const qty = qtyInput ? parseInt(qtyInput.value) : 1;
                if (isNaN(price) || isNaN(qty) || qty < 1) return;
                const amount = price * qty;
                total += amount;
                items.push({ stt: idx + 1, name, price, qty, amount });
            });

            if (items.length === 0) {
                toastr.warning('Chưa có món nào để in hoá đơn');
                return;
            }

            const customerName = @json($order->customer_name);
            const customerPhone = @json($order->customer_phone);
            const floorName = @json($table->floor->name ?? '');
            const tableName = @json($table->name ?? '');
            const orderId = @json($order->id);
            const createdAt = @json(optional($order->created_at)->format('H:i d/m/Y') ?? now()->format('H:i d/m/Y'));

            const html = generateInvoiceHTML({
                floorName,
                tableName,
                orderId,
                createdAt,
                customerName,
                customerPhone,
                paymentMethodText,
                items,
                total,
                isFinal
            });

            directPrintHTML(html);
            toastr.success(isFinal ? 'Đang in hoá đơn thanh toán' : 'Đang in phiếu tạm tính');
        }

        // Xác nhận thanh toán & xuất hoá đơn
        function handleConfirmPayment() {
            const rows = document.querySelectorAll('#paymentTable tbody tr');
            if (rows.length === 0) {
                toastr.error('Bàn này chưa có món ăn để thanh toán!');
                return;
            }

            const paymentMethod = document.getElementById('paymentMethodSelect').value;
            const paymentMethodText = paymentMethod === '2' ? 'Chuyển khoản VietQR' : (paymentMethod === '3' ? 'Quẹt thẻ POS' : 'Tiền mặt');

            if (!confirm(`Xác nhận thanh toán cho bàn ${@json($table->name ?? '')} bằng hình thức ${paymentMethodText}?`)) {
                return;
            }

            // Tính tổng tiền hiện tại
            let currentTotal = 0;
            const items = [];
            rows.forEach(tr => {
                const id = tr.dataset.id;
                const price = parseFloat(tr.dataset.price) || 0;
                const qtyInput = tr.querySelector('.qty-input');
                const qty = qtyInput ? parseInt(qtyInput.value) : 1;
                items.push({ id, qty });
                currentTotal += price * qty;
            });

            // Lưu số lượng trước, sau đó thanh toán
            $.ajax({
                url: '{{ route("admin.table-order.updateTableOrderQuantities") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    order_id: {{ $order->id }},
                    items: items
                },
                success: function(res) {
                    // Tiến hành thanh toán
                    $.ajax({
                        url: '{{ route("admin.table-order.processPayment") }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            order_id: {{ $order->id }},
                            payment_method: paymentMethod,
                            total_price: currentTotal
                        },
                        success: function(payRes) {
                            if (payRes.success) {
                                toastr.success('Thanh toán thành công!');
                                printProvisionalBill(true);
                                setTimeout(() => {
                                    window.location.href = '{{ route("admin.table-order.index") }}';
                                }, 1800);
                            } else {
                                toastr.error(payRes.message || 'Thanh toán thất bại');
                            }
                        },
                        error: function(err) {
                            toastr.error('Lỗi khi gửi yêu cầu thanh toán');
                        }
                    });
                },
                error: function(err) {
                    toastr.error('Lỗi cập nhật đơn hàng trước khi thanh toán');
                }
            });
        }

        function generateInvoiceHTML(data) {
            const {
                floorName,
                tableName,
                orderId,
                createdAt,
                customerName,
                customerPhone,
                paymentMethodText,
                items,
                total,
                isFinal
            } = data;

            const nf = new Intl.NumberFormat('vi-VN');
            const rows = items.map(i => {
                return `
                    <tr>
                        <td class="c">${i.stt}</td>
                        <td class="l">${escapeHtml(i.name)}</td>
                        <td class="q">${i.qty}</td>
                        <td class="p">${nf.format(i.price)} đ</td>
                        <td class="a">${nf.format(i.amount)} đ</td>
                    </tr>
                `;
            }).join('');

            const totalText = `${nf.format(total)} đ`;
            const title = isFinal ? 'HÓA ĐƠN THANH TOÁN' : 'PHIẾU TẠM TÍNH';
            const metaFloorTable = `${floorName ? `Tầng: <strong>${escapeHtml(floorName)}</strong> | ` : ''}Bàn: <strong>${escapeHtml(tableName)}</strong>`;
            const metaInvoiceId = `Mã HĐ: <strong>#${escapeHtml(String(orderId))}</strong>`;
            const metaCustomer = `Khách: <strong>${escapeHtml(customerName)}</strong> | SĐT: <strong>${escapeHtml(customerPhone)}</strong>`;
            const metaPayment = `Phương thức thanh toán: <strong>${escapeHtml(paymentMethodText)}</strong>`;
            const metaTime = `Thời gian: <strong>${escapeHtml(createdAt || '')}</strong>`;

            const bankId = '{{ config("vietqr.bank_id") }}';
            const accountNo = '{{ config("vietqr.account_no") }}';
            const accountName = encodeURIComponent('{{ config("vietqr.account_name") }}');
            const qrImageHtml = (!isFinal || paymentMethodText.includes('Chuyển khoản')) ? `
                <div style="text-align: center; margin-top: 10px; border-top: 1px dashed #000; padding-top: 8px;">
                    <div style="font-weight: bold; font-size: 11px; margin-bottom: 4px;">QUÉT MÃ VIETQR ĐỂ THANH TOÁN</div>
                    <img src="https://img.vietqr.io/image/${bankId}-${accountNo}-compact2.png?amount=${total}&addInfo=BAN{{ $table->id }}&accountName=${accountName}" style="width: 48mm; height: 48mm; margin: 0 auto; display: block;" />
                    <div style="font-size: 10px; margin-top: 4px;">STK: ${accountNo} (${bankId})</div>
                    <div style="font-size: 10px; font-weight: bold;">Nội dung: BAN{{ $table->id }}</div>
                </div>
            ` : '';

            return `<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>In hóa đơn</title>
  <style>
    @media print { @page { size: 80mm auto; margin: 3mm; } }
    html, body { padding: 0; margin: 0; }
    body { font-family: Arial, sans-serif; font-size: 12px; width: 80mm; }
    .bill { width: 74mm; margin: 0 auto; }
    .title { text-align: center; font-size: 16px; font-weight: 700; margin: 4px 0 6px; }
    .meta { font-size: 11px; margin-bottom: 4px; }
    .line { border-top: 1px dashed #000; margin: 6px 0; }
    table { width: 100%; border-collapse: collapse; font-size: 11px; }
    th, td { padding: 4px 0; vertical-align: top; }
    thead th { border-top: 1px dashed #000; border-bottom: 1px dashed #000; font-weight: 700; }
    tbody tr td { border-bottom: 1px dashed #000; }
    .c { text-align: center; width: 24px; }
    .l { text-align: left; }
    .q { text-align: right; white-space: nowrap; width: 32px; }
    .p { text-align: right; white-space: nowrap; width: 62px; }
    .a { text-align: right; white-space: nowrap; width: 64px; }
    .sum { font-weight: 700; }
    .footer { text-align: center; font-size: 11px; margin-top: 8px; }
  </style>
</head>
<body>
  <div class="bill">
    <div class="title">${title}</div>
    <div class="meta">${metaFloorTable}</div>
    <div class="meta">${metaInvoiceId}</div>
    <div class="meta">${metaCustomer}</div>
    <div class="meta">${metaPayment}</div>
    <div class="meta">${metaTime}</div>
    <div class="line"></div>
    <div class="table-responsive"><table>
      <thead>
        <tr>
          <th class="c">#</th>
          <th class="l">Tên món ăn</th>
          <th class="q">SL</th>
          <th class="p">Đơn giá</th>
          <th class="a">Thành tiền</th>
        </tr>
      </thead>
      <tbody>
        ${rows}
      </tbody>
      <tfoot>
        <tr>
          <td colspan="4" class="l sum">Tổng cộng</td>
          <td class="a sum">${totalText}</td>
        </tr>
      </tfoot>
    </table></div>
    ${qrImageHtml}
    <div class="footer" style="margin-top: 8px;">Cảm ơn quý khách & Hẹn gặp lại!</div>
  </div>
</body>
</html>`;
        }

        function escapeHtml(str) {
            return String(str)
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        // Functions for Edit Mode (Delete, Add, Update Qty)
        function updateRowTotal(input) {
             const qty = parseInt(input.value);
             const tr = input.closest('tr');
             const price = parseInt(tr.dataset.price);
             const amountDisplay = tr.querySelector('.amount-display');
             
             if (!isNaN(qty) && qty > 0) {
                 const amount = price * qty;
                 amountDisplay.innerText = new Intl.NumberFormat('vi-VN').format(amount) + ' ₫';
                 updateTotal();
             }
        }

        function updateTotal() {
            let total = 0;
            document.querySelectorAll('#paymentTable tbody tr').forEach(tr => {
                const qtyInput = tr.querySelector('.qty-input');
                const qty = parseInt(qtyInput.value);
                const price = parseInt(tr.dataset.price);
                if (!isNaN(qty) && qty > 0) {
                    total += price * qty;
                }
            });
            const formattedTotal = new Intl.NumberFormat('vi-VN').format(total) + ' ₫';
            document.getElementById('total-amount-display').innerText = formattedTotal;
            document.getElementById('total-amount-header').innerText = formattedTotal;
        }

        function confirmDeleteItem(id) {
            if (confirm('Bạn có chắc chắn muốn xóa món này không?')) {
                $.ajax({
                    url: '{{ route("admin.table-order.removeProductFromOrder") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        order_detail_id: id
                    },
                    success: function(res) {
                        if (res.success) {
                            location.reload();
                        } else {
                            alert(res.message);
                        }
                    }
                });
            }
        }

        function loadProducts(categoryId) {
            if (!categoryId) return;
            $.ajax({
                url: '{{ route("admin.table-order.getProducts") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    category_id: categoryId
                },
                success: function(res) {
                    if (res.success) {
                        let html = '<option value="">-- Chọn sản phẩm --</option>';
                        res.products.forEach(p => {
                            html += `<option value="${p.id}">${p.name}</option>`;
                        });
                        $('#productSelect').html(html);
                    }
                }
            });
        }

        function addProductToOrder() {
             const productId = $('#productSelect').val();
             const qty = $('#productQty').val();
             
             if (!productId) {
                 toastr.error('Vui lòng chọn sản phẩm');
                 return;
             }

             $.ajax({
                 url: '{{ route("admin.table-order.addProductToOrder") }}',
                 type: 'POST',
                 data: {
                     _token: '{{ csrf_token() }}',
                     order_id: {{ $order->id }},
                     product_id: productId,
                     qty: qty
                 },
                 success: function(res) {
                     if (res.success) {
                         location.reload();
                     } else {
                         toastr.error(res.message);
                     }
                 }
             });
        }
    </script>
</x-layout-backend>
