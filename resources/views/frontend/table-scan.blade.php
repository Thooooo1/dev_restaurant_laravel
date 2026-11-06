<x-layout-frontend>
    <main class="bg-gradient-to-b from-[#FAF8F5] via-stone-50 to-stone-100 min-h-screen pb-16">
        <x-page-hero 
            title="Gọi Món & Thanh Toán Tại Bàn" 
            subtitle="Quý khách quét mã QR để chọn món trực tiếp, gửi vào bếp và quét mã VietQR thanh toán nhanh chóng." 
            badge="CIVILIZE SMART QR"
            :breadcrumbs="[
                ['name' => 'Bàn ăn', 'url' => ''],
                ['name' => $table->name, 'url' => '']
            ]" 
        />

        <div class="container mx-auto px-4 max-w-xl pt-6 sm:pt-10">
            
            <!-- THẺ THÔNG TIN BÀN PHỤC VỤ -->
            <div class="bg-white rounded-xl sm:rounded-2xl border border-stone-200/90 shadow-xl overflow-hidden mb-6 transition-all">
                
                <!-- Header Thẻ Bàn -->
                <div class="p-6 bg-gradient-to-r from-stone-900 via-stone-800 to-stone-900 text-white relative overflow-hidden">
                    <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-[#C25E00]/15 rounded-full blur-xl pointer-events-none"></div>
                    
                    <div class="flex items-center justify-between relative z-10">
                        <div>
                            <span class="text-[11px] uppercase tracking-widest text-orange-300 font-extrabold block">Vị Trí Của Bạn</span>
                            <h1 class="text-2xl sm:text-3xl font-serif font-black text-white tracking-tight">
                                {{ $table->name }}
                            </h1>
                            <p class="text-xs text-stone-300 mt-0.5">
                                <span>{{ $table->floor ? $table->floor->name : 'Khu vực sảnh' }}</span>
                                <span>•</span>
                                <span>{{ $table->seats ?? 4 }} chỗ ngồi</span>
                            </p>
                        </div>

                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 shrink-0">
                            Đã kết nối
                        </span>
                    </div>

                    <!-- Banner Ưu Đãi 10% -->
                    <div class="mt-4 pt-3.5 border-t border-stone-700/60 flex items-center justify-between text-xs">
                        <div class="text-amber-300 font-bold">
                            <span>Ưu đãi độc quyền: Tự động giảm 10% tại bàn</span>
                        </div>
                        <span class="px-2 py-0.5 bg-amber-400/20 text-amber-300 rounded text-[10px] font-extrabold uppercase">
                            VIP ORDER
                        </span>
                    </div>
                </div>

                <!-- NỘI DUNG CHÍNH: NẾU BÀN ĐÃ CÓ ĐƠN HÀNG ĐANG PHỤC VỤ -->
                @if (isset($activeOrder) && $activeOrder)
                    <div class="p-6 bg-amber-50/50 border-b border-amber-100">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-orange-500"></span>
                                <h3 class="font-bold text-stone-900 text-sm">Bàn đang có đơn phục vụ</h3>
                            </div>
                            <span class="text-xs font-mono font-bold text-stone-500">Mã đơn #{{ $activeOrder->id }}</span>
                        </div>

                        <div class="p-4 bg-white rounded-lg border border-amber-200/80 shadow-xs mb-4">
                            <div class="flex items-center justify-between text-xs text-stone-600 mb-2">
                                <span>Số món đã gọi:</span>
                                <strong class="text-stone-900 font-bold">{{ $activeOrder->orderDetails->sum('qty') }} món ăn & đồ uống</strong>
                            </div>
                            <div class="flex items-center justify-between text-xs text-stone-600 mb-2">
                                <span>Tạm tính bữa ăn:</span>
                                <span class="text-stone-800 line-through">{{ number_format($activeOrder->orderDetails->sum('amount'), 0, ',', '.') }}₫</span>
                            </div>
                            <div class="flex items-center justify-between text-xs text-emerald-700 mb-2">
                                <span>Giảm giá đặt bàn (10%):</span>
                                <strong class="font-bold">-{{ number_format(round($activeOrder->orderDetails->sum('amount') * 0.1), 0, ',', '.') }}₫</strong>
                            </div>
                            <div class="pt-2 border-t border-stone-100 flex items-center justify-between text-sm">
                                <span class="font-bold text-stone-900">Tổng thanh toán:</span>
                                <span class="text-lg font-black text-orange-600">
                                    {{ number_format(max(0, $activeOrder->orderDetails->sum('amount') - round($activeOrder->orderDetails->sum('amount') * 0.1)), 0, ',', '.') }}₫
                                </span>
                            </div>
                        </div>

                        <!-- 3 Nút Hành Động Cho Bàn Đang Dùng Bữa -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-2">
                            <!-- Gọi thêm món -->
                            <a href="{{ route('site.product.gridview') }}" 
                               class="py-3 px-4 rounded bg-[#C25E00] hover:bg-orange-700 text-white font-bold text-xs uppercase tracking-wider shadow-md transition-all flex items-center justify-center text-center">
                                Gọi Thêm Món Ngay
                            </a>

                            <!-- Quẹt mã VietQR thanh toán luôn -->
                            <a href="{{ route('site.table.payment.qr', $table->id) }}" 
                               class="py-3 px-4 rounded bg-stone-900 hover:bg-black text-white font-bold text-xs uppercase tracking-wider shadow-md transition-all flex items-center justify-center text-center border border-stone-800">
                                Quẹt VietQR Trả Tiền
                            </a>
                        </div>

                        <div class="text-center mt-3">
                            <a href="{{ route('site.table.bill', $table->id) }}" class="text-xs text-stone-500 hover:text-orange-600 font-semibold underline inline-flex items-center">
                                Xem chi tiết danh sách từng món đã gọi của bàn
                            </a>
                        </div>
                    </div>
                @endif

                <!-- BẮT ĐẦU GỌI MÓN MỚI -->
                <div class="p-6">
                    <form action="{{ route('site.table.start', $table->id) }}" method="POST" class="space-y-4">
                        @csrf
                        
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="phone" class="text-xs sm:text-sm font-bold text-stone-800">
                                    Số điện thoại của bạn
                                </label>
                                <span class="text-[11px] text-stone-400 font-normal">(Không bắt buộc)</span>
                            </div>
                            <input type="tel" 
                                   id="phone" 
                                   name="phone" 
                                   value="{{ Auth::check() ? Auth::user()->phone : session('customer_phone') }}"
                                   class="w-full px-4 py-3 bg-stone-50 border border-stone-300 rounded-lg text-stone-900 text-sm font-medium focus:outline-none focus:bg-white focus:border-[#C25E00] focus:ring-1 focus:ring-[#C25E00] transition-colors"
                                   placeholder="Ví dụ: 0988 123 456">
                        </div>

                        <div>
                            <label for="name" class="block text-xs sm:text-sm font-bold text-stone-800 mb-1.5">
                                Tên người đại diện bàn <span class="text-stone-400 font-normal text-xs">(hoặc biệt danh)</span>
                            </label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   value="{{ Auth::check() ? Auth::user()->fullname : session('customer_name') }}"
                                   class="w-full px-4 py-3 bg-stone-50 border border-stone-300 rounded-lg text-stone-900 text-sm font-medium focus:outline-none focus:bg-white focus:border-[#C25E00] focus:ring-1 focus:ring-[#C25E00] transition-colors"
                                   placeholder="Ví dụ: Anh Nam, Bàn gia đình...">
                        </div>

                        <!-- Nút Bắt Đầu Chọn Món -->
                        <div class="pt-2">
                            <button type="submit" 
                                    class="w-full py-4 px-6 rounded-lg bg-[#C25E00] hover:bg-[#A34E00] text-white font-bold text-sm uppercase tracking-wider shadow-md transition-all flex items-center justify-center cursor-pointer">
                                Mở Thực Đơn & Chọn Món Ăn
                            </button>
                        </div>
                    </form>

                    <!-- Phím Tắt Tiện Ích Cho Bàn -->
                    <div class="mt-6 pt-5 border-t border-stone-100">
                        <span class="block text-center text-[11px] uppercase tracking-wider text-stone-400 font-bold mb-3">
                            Phục Vụ Nhanh Tại Bàn
                        </span>

                        <div class="grid grid-cols-3 gap-2">
                            <button type="button" onclick="quickCallService({{ $table->id }}, 'water', 'Xin thêm đá / nước lọc')" 
                                    class="p-2.5 rounded-lg border border-stone-200 hover:border-orange-400 hover:bg-orange-50/50 transition-colors text-center cursor-pointer">
                                <span class="text-xs font-bold text-stone-700 block">Xin thêm đá</span>
                            </button>

                            <button type="button" onclick="quickCallService({{ $table->id }}, 'cutlery', 'Xin chén đĩa / gia vị / khăn lạnh')" 
                                    class="p-2.5 rounded-lg border border-stone-200 hover:border-orange-400 hover:bg-orange-50/50 transition-colors text-center cursor-pointer">
                                <span class="text-xs font-bold text-stone-700 block">Chén đĩa / Khăn</span>
                            </button>

                            <button type="button" onclick="quickCallService({{ $table->id }}, 'payment', 'Yêu cầu thanh toán tiền mặt tại quầy')" 
                                    class="p-2.5 rounded-lg border border-stone-200 hover:border-orange-400 hover:bg-orange-50/50 transition-colors text-center cursor-pointer">
                                <span class="text-xs font-bold text-stone-700 block">Gọi tính tiền</span>
                            </button>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Cam kết chất lượng dịch vụ -->
            <div class="text-center text-xs text-stone-400 flex items-center justify-center gap-4">
                <span>Thanh toán an toàn VietQR</span>
                <span>•</span>
                <span>Bếp nhận món tức thì</span>
            </div>

        </div>
    </main>

    <script>
        function quickCallService(tableId, type, note) {
            fetch('{{ route("site.service-call.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    table_id: tableId,
                    call_type: type,
                    note: note
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (typeof toastr !== 'undefined') {
                        toastr.success(data.message || 'Đã gửi yêu cầu đến nhân viên phục vụ!');
                    } else {
                        alert(data.message || 'Đã gửi yêu cầu đến nhân viên phục vụ!');
                    }
                    const pingIndicator = document.getElementById('serviceCallPing');
                    if (pingIndicator) pingIndicator.classList.remove('hidden');
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.error(data.message || 'Không thể gửi yêu cầu');
                    } else {
                        alert(data.message || 'Không thể gửi yêu cầu');
                    }
                }
            })
            .catch(err => {
                console.error(err);
                if (typeof toastr !== 'undefined') {
                    toastr.error('Lỗi kết nối, vui lòng thử lại');
                } else {
                    alert('Lỗi kết nối, vui lòng thử lại');
                }
            });
        }
    </script>
</x-layout-frontend>
