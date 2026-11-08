<x-layout-backend>

    <!-- Page Header (Ant Design Pro Style) -->
    <div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-xl font-bold text-[rgba(0,0,0,0.85)] tracking-tight">Quản lý bàn ăn</h1>
            </div>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('admin.table-order.index') }}" class="ant-btn ant-btn-sm bg-orange-500 text-white hover:bg-orange-600 border-none shadow-xs font-bold uppercase tracking-wider">
                <span>Mở POS Bán Hàng</span>
            </a>
            <a href="{{ route('admin.table.create') }}" class="ant-btn ant-btn-primary ant-btn-sm font-bold uppercase tracking-wider">
                <span>+ Thêm bàn mới</span>
            </a>
            <a href="{{ route('admin.table.trash') }}" class="ant-btn ant-btn-sm text-gray-500 hover:text-red-600 font-medium">
                <span>Thùng rác</span>
            </a>
        </div>
    </div>

    <!-- Stat Overview Cards (Shopify Polaris / Ant Design Metric Cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
        <!-- Tổng số bàn -->
        <div class="bg-white p-4 rounded-xl border border-gray-200/80 shadow-2xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider block">Tổng số bàn</span>
                <span class="text-2xl font-black text-gray-900 mt-0.5 block">{{ $stats['total'] ?? 0 }} <span class="text-xs font-normal text-gray-400">bàn</span></span>
            </div>
            <span class="px-2.5 py-1 rounded-md bg-gray-100 border border-gray-200 text-sm font-bold text-gray-900" style="color: #111827 !important;">
                {{ count($floors) }} tầng
            </span>
        </div>

        <!-- Bàn đang có khách -->
        <div class="bg-white p-4 rounded-xl border border-gray-200/80 shadow-2xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider block flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                    Đang có khách
                </span>
                <span class="text-2xl font-black text-gray-900 mt-0.5 block">{{ $stats['serving'] ?? 0 }} <span class="text-xs font-normal text-gray-400">bàn</span></span>
            </div>
            <span class="px-2.5 py-1 rounded-md bg-gray-100 border border-gray-200 text-sm font-bold text-gray-900" style="color: #111827 !important;">
                {{ $stats['total'] > 0 ? round(($stats['serving'] / $stats['total']) * 100) : 0 }}%
            </span>
        </div>

        <!-- Bàn còn trống -->
        <div class="bg-white p-4 rounded-xl border border-gray-200/80 shadow-2xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider block flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                    Bàn sẵn sàng
                </span>
                <span class="text-2xl font-black text-gray-900 mt-0.5 block">{{ $stats['empty'] ?? 0 }} <span class="text-xs font-normal text-gray-400">bàn</span></span>
            </div>
            <span class="px-2.5 py-1 rounded-md bg-gray-100 border border-gray-200 text-sm font-bold text-gray-900" style="color: #111827 !important;">
                Sẵn có
            </span>
        </div>

        <!-- Doanh thu tạm tính trên bàn -->
        <div class="bg-white p-4 rounded-xl border border-gray-200/80 shadow-2xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider block">Tạm tính trên bàn</span>
                <span class="text-2xl font-black text-[rgba(0,0,0,0.85)] mt-0.5 block tracking-tight">{{ number_format($stats['serving_revenue'] ?? 0) }}₫</span>
            </div>
            <span class="px-2.5 py-1 rounded-md bg-gray-100 border border-gray-200 text-sm font-bold text-gray-900" style="color: #111827 !important;">
                {{ $stats['serving'] }} đơn
            </span>
        </div>
    </div>

    <!-- Filter Bar (Shopify Polaris IndexFilters Style) -->
    <div class="ant-card p-3.5 mb-4 shadow-sm bg-white rounded-xl border border-gray-100">
        <form method="GET" action="{{ route('admin.table.index') }}" class="flex flex-wrap gap-3 items-center justify-between">
            <div class="flex flex-wrap items-center gap-2.5">
                <!-- Lọc tầng -->
                <div class="flex items-center gap-1.5">
                    <label class="text-xs font-semibold text-gray-600">Khu vực / Tầng:</label>
                    <select name="floor_id" class="polaris-select text-xs min-w-[150px]" onchange="this.form.submit()">
                        <option value="">-- Tất cả tầng --</option>
                        @foreach ($floors as $floor)
                            <option value="{{ $floor->id }}" {{ request('floor_id') == $floor->id ? 'selected' : '' }}>
                                {{ $floor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Lọc trạng thái phục vụ -->
                <div class="flex items-center gap-1.5">
                    <label class="text-xs font-semibold text-gray-600">Phục vụ:</label>
                    <select name="serving_status" class="polaris-select text-xs min-w-[160px]" onchange="this.form.submit()">
                        <option value="">-- Tất cả bàn --</option>
                        <option value="serving" {{ request('serving_status') == 'serving' ? 'selected' : '' }}>Đang có khách ({{ $stats['serving'] }})</option>
                        <option value="empty" {{ request('serving_status') == 'empty' ? 'selected' : '' }}>Bàn trống ({{ $stats['empty'] }})</option>
                    </select>
                </div>

                <!-- Tìm kiếm theo tên bàn -->
                <div class="relative">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Tìm tên bàn hoặc tầng..." 
                           class="polaris-select text-xs px-3 py-1.5 min-w-[200px]" />
                </div>

                <button type="submit" class="ant-btn ant-btn-sm ant-btn-primary font-bold">
                    <span>Lọc danh sách</span>
                </button>

                @if(request('floor_id') || request('serving_status') || request('q'))
                    <a href="{{ route('admin.table.index') }}" class="ant-btn ant-btn-sm text-gray-500 hover:text-red-500 font-medium">
                        <span>Xóa lọc</span>
                    </a>
                @endif
            </div>

            <!-- Phím tắt hướng dẫn -->
            <div class="text-xs text-gray-500 hidden xl:flex items-center gap-2">
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-stone-100 text-stone-700 font-medium text-[11px]">
                    Bấm "Xem chi tiết" để xem danh sách món ăn & thành tiền từng bàn
                </span>
            </div>
        </form>
    </div>

    <!-- Data Table (Shopify Polaris / Ant Design Pro Style) -->
    <div class="ant-card shadow-sm overflow-hidden bg-white rounded-xl border border-gray-100">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-200/80 text-[11px] font-bold text-gray-600 uppercase tracking-wider">
                        <th class="w-12 py-3 px-3 text-center">ID</th>
                        <th class="py-3 px-3">Tên bàn & Khu vực</th>
                        <th class="py-3 px-3">Trạng thái phục vụ</th>
                        <th class="py-3 px-3 min-w-[220px]">Món ăn đang dùng</th>
                        <th class="py-3 px-3 text-center min-w-[230px]">Thao tác phục vụ & Thanh toán</th>
                        <th class="py-3 px-3 text-center w-28">Cài đặt</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-xs">
                    @forelse ($tables as $table)
                        @php
                            $activeOrder = $activeOrders[$table->id] ?? null;
                            $hasActive = ($activeOrder !== null);
                            $orderDishes = $hasActive ? $activeOrder->orderDetails : collect();
                            $dishesCount = $orderDishes->count();
                            $subtotalPrice = $orderDishes->sum('amount');
                            $finalTableTotal = $hasActive && $activeOrder->total_price > 0 ? $activeOrder->total_price : $subtotalPrice;
                        @endphp
                        <tr class="hover:bg-orange-50/20 transition-colors {{ $hasActive ? 'bg-orange-50/10' : '' }}">
                            <!-- ID -->
                            <td class="py-3.5 px-3 text-center font-mono text-gray-400 font-semibold">
                                #{{ $table->id }}
                            </td>

                            <!-- Tên bàn & Khu vực -->
                            <td class="py-3.5 px-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-9 h-9 rounded-lg {{ $hasActive ? 'bg-orange-100 text-[#F47716]' : 'bg-gray-100 text-gray-600' }} flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
                                        {{ $table->name }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-sm text-gray-900">{{ $table->name }}</div>
                                        <div class="flex items-center gap-1.5 mt-0.5">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-600">
                                                {{ $table->floor->name ?? 'Không phân tầng' }}
                                            </span>
                                            <span class="text-[11px] text-gray-400 font-mono">
                                                {{ $table->seats }} ghế
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Trạng thái phục vụ (Realtime) -->
                            <td class="py-3.5 px-3">
                                @if ($hasActive)
                                    <div>
                                        <span class="px-2.5 py-1 rounded-md bg-gray-100 border border-gray-200 text-sm font-bold text-gray-900 inline-block" style="color: #111827 !important;">
                                            Đang có khách
                                        </span>
                                        <div class="mt-1.5 flex items-center gap-2 text-[11px] text-gray-500">
                                            <span class="font-mono font-semibold text-gray-700">Đơn #{{ $activeOrder->id }}</span>
                                            <span>•</span>
                                            <span class="text-gray-400">{{ $activeOrder->created_at ? $activeOrder->created_at->locale('vi')->diffForHumans() : '' }}</span>
                                        </div>
                                        <div class="mt-1 font-semibold text-[rgba(0,0,0,0.85)] text-xs">
                                            Tạm tính: {{ number_format($finalTableTotal) }}₫
                                        </div>
                                    </div>
                                @else
                                    <div>
                                        <span class="px-2.5 py-1 rounded-md bg-gray-100 border border-gray-200 text-sm font-bold text-gray-900 inline-block" style="color: #111827 !important;">
                                            Bàn trống
                                        </span>
                                        <p class="text-[11px] text-gray-400 mt-1">Sẵn sàng đón khách</p>
                                    </div>
                                @endif
                            </td>

                            <!-- Món ăn đang dùng -->
                            <td class="py-3.5 px-3">
                                @if ($hasActive && $dishesCount > 0)
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-1.5 text-[11px]">
                                            <span class="font-bold text-gray-700">
                                                {{ $dishesCount }} món đang dùng
                                            </span>
                                        </div>
                                        <!-- Danh sách tóm tắt 2-3 món đầu -->
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($orderDishes->take(3) as $od)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded bg-amber-50 text-amber-800 border border-amber-200/60 text-[11px]">
                                                    {{ Str::limit($od->product->name ?? ('Món #'.$od->product_id), 18) }}
                                                    <b class="ml-1 text-[#F47716]">x{{ $od->qty }}</b>
                                                </span>
                                            @endforeach
                                            @if ($dishesCount > 3)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-gray-100 text-gray-500 text-[10px]">
                                                    +{{ $dishesCount - 3 }} món nữa
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @elseif ($hasActive)
                                    <div class="text-[11px] text-amber-600 italic">
                                        Đã mở bàn, đang chọn món...
                                    </div>
                                @else
                                    <div class="text-[11px] text-gray-400 italic">
                                        Chưa có món nào
                                    </div>
                                @endif
                            </td>

                            <!-- Thao tác phục vụ & Thanh toán (Trọng tâm) -->
                            <td class="py-3.5 px-3 text-center">
                                @if ($hasActive)
                                    <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                        <!-- Nút Xem chi tiết (Modal popup) -->
                                        <button type="button" 
                                                onclick="openDishesModal({{ $table->id }})" 
                                                class="ant-btn ant-btn-sm bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 font-semibold cursor-pointer shadow-2xs"
                                                title="Xem danh sách chi tiết các món bàn đang gọi">
                                            <span>Xem chi tiết</span>
                                        </button>

                                        <!-- Nút Thanh toán (Chuyển sang trang thanh toán POS) -->
                                        <a href="{{ route('admin.table-order.payment', $table->id) }}" 
                                           class="ant-btn ant-btn-sm bg-emerald-600 text-white hover:bg-emerald-700 border-none font-bold shadow-xs uppercase tracking-wider"
                                           title="Thanh toán hóa đơn cho bàn này (VietQR / Tiền mặt / Quẹt thẻ)">
                                            <span>Thanh toán</span>
                                        </a>

                                        <!-- Nút Thêm món (Mở giao diện POS order của bàn) -->
                                        <a href="{{ route('admin.table-order.order', $table->id) }}" 
                                           class="ant-btn ant-btn-sm bg-orange-50 text-orange-700 hover:bg-orange-100 border border-orange-200 font-semibold shadow-2xs"
                                           title="Gọi thêm món vào bàn">
                                            <span>Thêm món</span>
                                        </a>
                                    </div>
                                @else
                                    <div class="flex items-center justify-center">
                                        <!-- Bàn trống: Nút Mở bàn / Gọi món -->
                                        <a href="{{ route('admin.table-order.order', $table->id) }}" 
                                           class="ant-btn ant-btn-sm bg-gray-50 text-gray-700 hover:bg-[#F47716] hover:text-white border border-gray-200 hover:border-[#F47716] font-semibold transition-all shadow-2xs">
                                            <span>Mở bàn & Gọi món</span>
                                        </a>
                                    </div>
                                @endif
                            </td>

                            <!-- Cài đặt bàn (Chữ rõ ràng, không dùng icon khó hiểu) -->
                            <td class="py-3.5 px-3 text-center">
                                <div class="flex items-center justify-center gap-2 text-xs">
                                    <a href="{{ route('admin.table.status', $table->id) }}" class="text-[11px] font-semibold {{ $table->status == 1 ? 'text-emerald-700 hover:underline' : 'text-gray-400 hover:underline' }}" title="{{ $table->status == 1 ? 'Khóa bàn' : 'Mở bàn' }}">
                                        {{ $table->status == 1 ? 'Bật' : 'Tắt' }}
                                    </a>
                                    <span class="text-gray-200">|</span>
                                    <a href="{{ route('admin.table.qr', $table->id) }}" class="text-[11px] font-semibold text-blue-600 hover:underline" title="In mã QR để bàn">
                                        QR
                                    </a>
                                    <span class="text-gray-200">|</span>
                                    <a href="{{ route('admin.table.edit', $table->id) }}" class="text-[11px] font-semibold text-amber-700 hover:underline" title="Chỉnh sửa thông tin">
                                        Sửa
                                    </a>
                                    <span class="text-gray-200">|</span>
                                    <a href="{{ route('admin.table.delete', $table->id) }}" class="text-[11px] font-semibold text-rose-600 hover:underline" title="Xóa vào thùng rác" onclick="return confirm('Bạn có chắc muốn xóa bàn này vào thùng rác?')">
                                        Xóa
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-xs text-[rgba(0,0,0,0.45)]">
                                <p class="font-bold text-gray-700 text-sm">Không có bàn ăn nào phù hợp với bộ lọc hiện tại</p>
                                <p class="text-gray-400 mt-1">Hãy thử xóa bộ lọc hoặc thêm bàn ăn mới.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-3 border-t border-[#F0F0F0] bg-[#FAFAFA] flex justify-between items-center text-xs text-gray-500">
            <div>
                Hiển thị <b>{{ $tables->count() }}</b> trên tổng số <b>{{ $tables->total() }}</b> bàn
            </div>
            <div>
                {{ $tables->links() }}
            </div>
        </div>
    </div>

    <!-- Interactive Ant Design Pro Modal: Xem chi tiết món ăn của bàn -->
    <div id="tableDishesModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop blur -->
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" onclick="closeDishesModal()"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-gray-100">
                
                <!-- Modal Header: Nền trắng chữ đen thanh lịch, hiện đại -->
                <div class="px-6 py-4 bg-white text-gray-900 flex items-center justify-between border-b border-gray-100">
                    <div>
                        <h3 class="text-base font-bold text-gray-900" id="modalTableTitle">Chi tiết món ăn bàn</h3>
                        <p class="text-xs text-gray-500 mt-0.5" id="modalTableSub">Đang tải thông tin bàn...</p>
                    </div>
                    <button type="button" onclick="closeDishesModal()" class="w-8 h-8 rounded-lg bg-gray-50 text-gray-400 hover:text-gray-800 hover:bg-gray-100 flex items-center justify-center transition-colors font-bold text-sm cursor-pointer border border-gray-200" aria-label="Đóng">
                        ✕
                    </button>
                </div>

                <!-- Modal Body Loading Skeleton -->
                <div id="modalLoading" class="p-8 text-center">
                    <div class="w-10 h-10 border-3 border-orange-500 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
                    <p class="text-xs text-gray-500 font-medium">Đang lấy dữ liệu món ăn thời gian thực...</p>
                </div>

                <!-- Modal Body Empty State -->
                <div id="modalEmptyState" class="p-8 text-center hidden">
                    <h4 class="text-sm font-bold text-gray-800" id="modalEmptyMessage">Bàn này hiện đang trống</h4>
                    <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">Chưa có lượt khách nào đăng ký gọi món tại bàn này.</p>
                    <div class="mt-4">
                        <a id="modalEmptyOrderBtn" href="#" class="ant-btn ant-btn-primary ant-btn-sm font-bold uppercase tracking-wider">
                            <span>Mở bàn & Gọi món ngay</span>
                        </a>
                    </div>
                </div>

                <!-- Modal Body Content -->
                <div id="modalContent" class="p-6 hidden">
                    <!-- Customer & Order Meta -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 p-3 bg-gray-50 rounded-xl mb-4 text-xs border border-gray-100">
                        <div>
                            <span class="text-gray-400 block text-[11px]">Mã đơn hàng:</span>
                            <span class="font-mono font-bold text-gray-800" id="modalOrderCode">#00</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-[11px]">Khách hàng:</span>
                            <span class="font-semibold text-gray-800" id="modalCustomerName">Khách tại bàn</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-[11px]">Bắt đầu ngồi:</span>
                            <span class="font-semibold text-gray-800" id="modalSeatedTime">Vừa xong</span>
                        </div>
                    </div>

                    <!-- Dishes List Table -->
                    <div class="border border-gray-200 rounded-xl overflow-hidden mb-4 shadow-2xs">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-50 border-b border-gray-200 text-gray-500 font-semibold uppercase text-[10px]">
                                <tr>
                                    <th class="py-2.5 px-3">Món ăn</th>
                                    <th class="py-2.5 px-3 text-center w-20">SL</th>
                                    <th class="py-2.5 px-3 text-right w-28">Đơn giá</th>
                                    <th class="py-2.5 px-3 text-right w-32">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody id="modalDishesTableBody" class="divide-y divide-gray-100">
                                <!-- Dynamic rows -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Summary & Totals -->
                    <div class="bg-gradient-to-br from-amber-50/60 to-orange-50/60 p-4 rounded-xl border border-orange-100/80 space-y-1.5 text-xs">
                        <div class="flex justify-between text-gray-600">
                            <span>Tạm tính (<span id="modalTotalItems">0</span> món):</span>
                            <span class="font-semibold text-gray-900" id="modalSubtotal">0₫</span>
                        </div>
                        <div class="flex justify-between text-emerald-700" id="modalDiscountRow">
                            <span>Chiết khấu / Ưu đãi:</span>
                            <span class="font-semibold" id="modalDiscount">-0₫</span>
                        </div>
                        <div class="pt-2 border-t border-orange-200/60 flex justify-between items-center text-sm">
                            <span class="font-bold text-gray-900 uppercase tracking-wide">Tổng tiền thanh toán:</span>
                            <span class="font-black text-xl text-[#F47716]" id="modalFinalTotal">0₫</span>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer Actions -->
                <div class="px-6 py-3.5 bg-gray-50 border-t border-gray-100 flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <a id="modalBillLink" href="#" target="_blank" class="ant-btn ant-btn-sm text-gray-700 hover:text-blue-600 border border-gray-200 font-medium">
                            <span>Hóa đơn & QR khách</span>
                        </a>
                        <a id="modalQrLink" href="#" class="ant-btn ant-btn-sm text-gray-700 hover:text-gray-900 border border-gray-200 font-medium">
                            <span>Mã QR để bàn</span>
                        </a>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="closeDishesModal()" class="ant-btn ant-btn-sm text-gray-600 hover:text-gray-800 font-medium cursor-pointer">
                            Đóng
                        </button>
                        <a id="modalOrderMoreBtn" href="#" class="ant-btn ant-btn-sm bg-orange-50 text-orange-700 hover:bg-orange-100 border border-orange-200 font-semibold">
                            <span>Gọi thêm món</span>
                        </a>
                        <a id="modalPaymentBtn" href="#" class="ant-btn ant-btn-sm bg-emerald-600 text-white hover:bg-emerald-700 font-bold border-none shadow-xs uppercase tracking-wider">
                            <span>Thanh toán ngay</span>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- JavaScript to Handle Modal AJAX -->
    <script>
        function openDishesModal(tableId) {
            const modal = document.getElementById('tableDishesModal');
            const loading = document.getElementById('modalLoading');
            const emptyState = document.getElementById('modalEmptyState');
            const content = document.getElementById('modalContent');
            
            // Show modal and loading state
            modal.classList.remove('hidden');
            loading.classList.remove('hidden');
            emptyState.classList.add('hidden');
            content.classList.add('hidden');

            document.getElementById('modalTableTitle').innerText = 'Đang tải thông tin bàn #' + tableId + '...';
            document.getElementById('modalTableSub').innerText = 'Đang kết nối hệ thống gọi món...';

            // Fetch Table Dishes via AJAX
            fetch(`/admin/table/${tableId}/dishes`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                loading.classList.add('hidden');

                if (!data.success || data.is_empty) {
                    emptyState.classList.remove('hidden');
                    document.getElementById('modalTableTitle').innerText = data.table ? `${data.table.name} (${data.table.floor_name})` : 'Bàn #' + tableId;
                    document.getElementById('modalTableSub').innerText = 'Trạng thái: Bàn trống (Sẵn sàng phục vụ)';
                    document.getElementById('modalEmptyMessage').innerText = data.message || 'Bàn này hiện đang trống';
                    
                    const emptyBtn = document.getElementById('modalEmptyOrderBtn');
                    emptyBtn.href = data.order_url || `/admin/table-order/${tableId}/order`;

                    // Disable or hide footer checkout actions
                    document.getElementById('modalPaymentBtn').classList.add('hidden');
                    document.getElementById('modalOrderMoreBtn').classList.add('hidden');
                    document.getElementById('modalBillLink').classList.add('hidden');
                    document.getElementById('modalQrLink').href = `/admin/table/${tableId}/qr`;
                    return;
                }

                // Show Content
                content.classList.remove('hidden');
                document.getElementById('modalPaymentBtn').classList.remove('hidden');
                document.getElementById('modalOrderMoreBtn').classList.remove('hidden');
                document.getElementById('modalBillLink').classList.remove('hidden');

                // Header data
                document.getElementById('modalTableTitle').innerText = `${data.table.name} — ${data.table.floor_name}`;
                document.getElementById('modalTableSub').innerText = `Bàn ${data.table.seats} chỗ ngồi • Trạng thái: ${data.order.status_text}`;

                // Meta data
                document.getElementById('modalOrderCode').innerText = '#' + data.order.id;
                document.getElementById('modalCustomerName').innerText = data.order.customer_name + (data.order.phone !== '—' ? ` (${data.order.phone})` : '');
                document.getElementById('modalSeatedTime').innerText = data.order.time_seated;

                // Render dishes rows
                const tbody = document.getElementById('modalDishesTableBody');
                tbody.innerHTML = '';

                data.dishes.forEach((dish, idx) => {
                    const row = document.createElement('tr');
                    row.className = 'hover:bg-amber-50/20 transition-colors';
                    row.innerHTML = `
                        <td class="py-2.5 px-3">
                            <div class="flex items-center gap-2.5">
                                <img src="${dish.thumbnail}" alt="${dish.name}" class="w-10 h-10 rounded-lg object-cover shrink-0 border border-gray-200 shadow-2xs" onerror="this.src='{{ asset('/images/default-product.png') }}'"/>
                                <div>
                                    <div class="font-semibold text-gray-900">${dish.name}</div>
                                    <div class="text-[10px] text-gray-400">Lượt gọi #${dish.order_turn}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-2.5 px-3 text-center font-bold text-gray-800">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-100 text-gray-800 text-[11px]">
                                x${dish.qty}
                            </span>
                        </td>
                        <td class="py-2.5 px-3 text-right font-mono text-gray-600">
                            ${dish.price_formatted}
                        </td>
                        <td class="py-2.5 px-3 text-right font-mono font-bold text-gray-900">
                            ${dish.amount_formatted}
                        </td>
                    `;
                    tbody.appendChild(row);
                });

                // Summary numbers
                document.getElementById('modalTotalItems').innerText = data.summary.total_qty;
                document.getElementById('modalSubtotal').innerText = data.summary.subtotal_formatted;
                
                const discountRow = document.getElementById('modalDiscountRow');
                if (data.summary.discount > 0) {
                    discountRow.classList.remove('hidden');
                    document.getElementById('modalDiscount').innerText = '-' + data.summary.discount_formatted;
                } else {
                    discountRow.classList.add('hidden');
                }

                document.getElementById('modalFinalTotal').innerText = data.summary.final_total_formatted;

                // Action links
                document.getElementById('modalPaymentBtn').href = data.urls.payment_url;
                document.getElementById('modalOrderMoreBtn').href = data.urls.order_url;
                document.getElementById('modalBillLink').href = data.urls.bill_url;
                document.getElementById('modalQrLink').href = data.urls.qr_url;
            })
            .catch(err => {
                loading.classList.add('hidden');
                emptyState.classList.remove('hidden');
                document.getElementById('modalEmptyMessage').innerText = 'Không thể tải chi tiết bàn. Vui lòng thử lại!';
                console.error(err);
            });
        }

        function closeDishesModal() {
            const modal = document.getElementById('tableDishesModal');
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDishesModal();
            }
        });
    </script>
</x-layout-backend>
