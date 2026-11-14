<x-layout-backend>

    <!-- Page Header (Ant Design Pro Style) -->
    <div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-xl font-bold text-[rgba(0,0,0,0.85)] tracking-tight">Quản lý bàn ăn</h1>
            </div>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('admin.table-order.index') }}" class="ant-btn ant-btn-primary ant-btn-sm font-bold uppercase tracking-wider">
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
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-4 mb-5">
        <!-- Tổng số bàn -->
        <div class="bg-white p-3 sm:p-4 rounded-lg border border-gray-200/80 shadow-2xs flex items-center justify-between">
            <div class="min-w-0">
                <span class="text-[10px] sm:text-[11px] font-semibold text-gray-400 uppercase tracking-wider block truncate">Tổng số bàn</span>
                <span class="text-xl sm:text-2xl font-black text-gray-900 mt-0.5 block">{{ $stats['total'] ?? 0 }} <span class="text-xs font-normal text-gray-400">bàn</span></span>
            </div>
            <span class="text-xs font-semibold text-gray-500 shrink-0 ml-1">
                {{ count($floors) }} tầng
            </span>
        </div>

        <!-- Bàn đang có khách -->
        <div class="bg-white p-3 sm:p-4 rounded-lg border border-gray-200/80 shadow-2xs flex items-center justify-between">
            <div class="min-w-0">
                <span class="text-[10px] sm:text-[11px] font-semibold text-gray-400 uppercase tracking-wider block truncate flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-gray-400 shrink-0"></span>
                    Đang có khách
                </span>
                <span class="text-xl sm:text-2xl font-black text-gray-900 mt-0.5 block">{{ $stats['serving'] ?? 0 }} <span class="text-xs font-normal text-gray-400">bàn</span></span>
            </div>
            <span class="text-xs font-semibold text-gray-500 shrink-0 ml-1">
                {{ $stats['total'] > 0 ? round(($stats['serving'] / $stats['total']) * 100) : 0 }}%
            </span>
        </div>

        <!-- Bàn còn trống -->
        <div class="bg-white p-3 sm:p-4 rounded-lg border border-gray-200/80 shadow-2xs flex items-center justify-between">
            <div class="min-w-0">
                <span class="text-[10px] sm:text-[11px] font-semibold text-gray-400 uppercase tracking-wider block truncate flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-gray-400 shrink-0"></span>
                    Bàn sẵn sàng
                </span>
                <span class="text-xl sm:text-2xl font-black text-gray-900 mt-0.5 block">{{ $stats['empty'] ?? 0 }} <span class="text-xs font-normal text-gray-400">bàn</span></span>
            </div>
            <span class="text-xs font-semibold text-gray-500 shrink-0 ml-1">
                Sẵn có
            </span>
        </div>

        <!-- Doanh thu tạm tính trên bàn -->
        <div class="bg-white p-3 sm:p-4 rounded-lg border border-gray-200/80 shadow-2xs flex items-center justify-between">
            <div class="min-w-0">
                <span class="text-[10px] sm:text-[11px] font-semibold text-gray-400 uppercase tracking-wider block truncate">Tạm tính trên bàn</span>
                <span class="text-xl sm:text-2xl font-black text-[rgba(0,0,0,0.85)] mt-0.5 block tracking-tight truncate">{{ number_format($stats['serving_revenue'] ?? 0) }}₫</span>
            </div>
            <span class="text-xs font-semibold text-gray-500 shrink-0 ml-1">
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
                                        <span class="text-xs font-semibold text-gray-800 block">
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
                                        <span class="text-xs font-medium text-gray-500 block">
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
                                           class="ant-btn ant-btn-sm ant-btn-success !bg-emerald-600 !text-white hover:!bg-emerald-700 !border-emerald-600 font-bold shadow-xs uppercase tracking-wider"
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

        <div class="flex min-h-full items-center justify-center p-3 text-center sm:p-4">
            <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-2xl transition-all sm:my-6 sm:w-full sm:max-w-xl border border-gray-150">
                
                <!-- Modal Header: Tinh gọn, thanh lịch -->
                <div class="px-5 py-3.5 bg-white text-gray-900 flex items-center justify-between border-b border-gray-100">
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-gray-900" id="modalTableTitle">Chi tiết món ăn bàn</h3>
                        <p class="text-[11px] text-gray-500 mt-0.5" id="modalTableSub">Đang tải thông tin bàn...</p>
                    </div>
                    <button type="button" onclick="closeDishesModal()" class="w-7 h-7 rounded-md bg-gray-50 text-gray-400 hover:text-gray-800 hover:bg-gray-100 flex items-center justify-center transition-colors font-bold text-xs cursor-pointer border border-gray-200" aria-label="Đóng">
                        ✕
                    </button>
                </div>

                <!-- Modal Body Loading Skeleton -->
                <div id="modalLoading" class="p-8 text-center">
                    <div class="w-8 h-8 border-2 border-orange-500 border-t-transparent rounded-full animate-spin mx-auto mb-2.5"></div>
                    <p class="text-xs text-gray-500 font-medium">Đang tải dữ liệu gọi món...</p>
                </div>

                <!-- Modal Body Empty State -->
                <div id="modalEmptyState" class="p-8 text-center hidden">
                    <h4 class="text-sm font-bold text-gray-800" id="modalEmptyMessage">Bàn này hiện đang trống</h4>
                    <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">Chưa có lượt khách nào đăng ký gọi món tại bàn này.</p>
                    <div class="mt-3.5">
                        <a id="modalEmptyOrderBtn" href="#" class="ant-btn ant-btn-primary ant-btn-sm font-bold uppercase tracking-wider text-xs">
                            <span>Mở bàn & Gọi món</span>
                        </a>
                    </div>
                </div>

                <!-- Modal Body Content: Tinh gọn & có thanh cuộn mượt cho danh sách món -->
                <div id="modalContent" class="p-4 sm:p-5 hidden">
                    <!-- Customer & Order Meta: Thanh 1 dòng tinh giản, loại bỏ chiếm chỗ dọc -->
                    <div class="flex flex-wrap items-center justify-between gap-2 px-3 py-2 bg-gray-50 rounded-lg text-xs border border-gray-100 mb-3">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-gray-400 text-[11px]">Đơn:</span>
                            <span class="font-mono font-bold text-gray-800" id="modalOrderCode">#00</span>
                            <span class="text-gray-300">•</span>
                            <span class="font-medium text-gray-700" id="modalCustomerName">Khách tại bàn</span>
                        </div>
                        <div class="text-gray-500 text-[11px]">
                            <span class="text-gray-400">Ngồi: </span><span class="font-medium text-gray-700" id="modalSeatedTime">Vừa xong</span>
                        </div>
                    </div>

                    <!-- Dishes List Table: Cuộn tối đa khi nhiều món, không làm tràn màn hình -->
                    <div class="border border-gray-200 rounded-lg overflow-hidden mb-3 shadow-2xs max-h-56 sm:max-h-64 overflow-y-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-50 border-b border-gray-200 text-gray-500 font-semibold uppercase text-[10px] sticky top-0 z-10">
                                <tr>
                                    <th class="py-2 px-2.5">Món ăn</th>
                                    <th class="py-2 px-2 text-center w-14">SL</th>
                                    <th class="py-2 px-2 text-right w-24">Đơn giá</th>
                                    <th class="py-2 px-2.5 text-right w-24">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody id="modalDishesTableBody" class="divide-y divide-gray-100">
                                <!-- Dynamic rows -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Summary & Totals: Thiết kế tinh gọn, số liệu rõ ràng -->
                    <div class="bg-amber-50/50 p-3 rounded-lg border border-amber-200/60 space-y-1 text-xs">
                        <div class="flex justify-between items-center text-gray-600">
                            <span>Tạm tính (<span id="modalTotalItems">0</span> món):</span>
                            <span class="font-mono font-semibold text-gray-900" id="modalSubtotal">0₫</span>
                        </div>
                        <div class="flex justify-between items-center text-emerald-700 hidden" id="modalDiscountRow">
                            <span>Ưu đãi / Giảm giá:</span>
                            <span class="font-mono font-semibold" id="modalDiscount">-0₫</span>
                        </div>
                        <div class="pt-1.5 border-t border-amber-200/60 flex justify-between items-center">
                            <span class="font-bold text-gray-900 uppercase text-xs">Tổng thanh toán:</span>
                            <span class="font-black text-base sm:text-lg text-[#F47716] font-mono" id="modalFinalTotal">0₫</span>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer Actions: Tinh gọn các nút điều hướng -->
                <div class="px-4 sm:px-5 py-3 bg-gray-50 border-t border-gray-100 flex flex-wrap items-center justify-between gap-2 text-xs">
                    <div>
                        <a id="modalBillLink" href="#" target="_blank" class="ant-btn ant-btn-sm text-gray-700 hover:text-blue-600 border border-gray-200 font-medium text-xs">
                            <span>Hóa đơn khách</span>
                        </a>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <button type="button" onclick="closeDishesModal()" class="ant-btn ant-btn-sm text-gray-600 hover:text-gray-800 font-medium cursor-pointer text-xs">
                            Đóng
                        </button>
                        <a id="modalOrderMoreBtn" href="#" class="ant-btn ant-btn-sm bg-orange-50 text-orange-700 hover:bg-orange-100 border border-orange-200 font-semibold text-xs">
                            <span>Gọi thêm món</span>
                        </a>
                        <a id="modalPaymentBtn" href="#" class="ant-btn ant-btn-sm ant-btn-success !bg-emerald-600 !text-white hover:!bg-emerald-700 !border-emerald-600 font-bold shadow-xs uppercase tracking-wider text-xs">
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
                    const qrLink = document.getElementById('modalQrLink');
                    if (qrLink) qrLink.href = `/admin/table/${tableId}/qr`;
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
                    const turnBadge = dish.order_turn > 1 
                        ? `<span class="inline-block text-[9px] text-amber-700 bg-amber-50 px-1 py-0.2 rounded font-medium border border-amber-200 mt-0.5">Đợt #${dish.order_turn}</span>` 
                        : '';
                    row.innerHTML = `
                        <td class="py-1.5 px-2.5">
                            <div class="flex items-center gap-2">
                                <img src="${dish.thumbnail}" alt="${dish.name}" class="w-7 h-7 sm:w-8 sm:h-8 rounded-md object-cover shrink-0 border border-gray-200 shadow-2xs" onerror="this.src='{{ asset('/images/default-product.png') }}'"/>
                                <div class="min-w-0">
                                    <div class="font-semibold text-gray-900 text-xs truncate max-w-[200px] sm:max-w-[240px]" title="${dish.name}">${dish.name}</div>
                                    ${turnBadge}
                                </div>
                            </div>
                        </td>
                        <td class="py-1.5 px-2 text-center font-bold text-gray-800 text-xs">
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-gray-100 text-gray-800 text-[10px]">
                                x${dish.qty}
                            </span>
                        </td>
                        <td class="py-1.5 px-2 text-right font-mono text-[11px] text-gray-500">
                            ${dish.price_formatted}
                        </td>
                        <td class="py-1.5 px-2.5 text-right font-mono font-bold text-xs text-gray-900">
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
                const qrLink = document.getElementById('modalQrLink');
                if (qrLink) qrLink.href = data.urls.qr_url;
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
