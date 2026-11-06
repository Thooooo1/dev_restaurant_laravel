<x-layout-backend>
    <!-- Page Header (Chuẩn phong cách Dashboard POS cao cấp, thoáng đãng, dễ nhìn) -->
    <div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-xl font-bold text-[rgba(0,0,0,0.85)] tracking-tight">Quản Lý Bán Hàng (POS)</h1>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span> Trực tiếp
                </span>
            </div>
        </div>

        <!-- Quick Top Links (Tích hợp quản lý bàn và bán hàng POS) -->
        <div class="flex items-center gap-2 flex-wrap">
            @if(Auth::user()->isAdmin())
                <a href="{{ route('admin.table.create') }}" class="ant-btn ant-btn-primary ant-btn-sm font-bold uppercase tracking-wider shadow-2xs">
                    <span>+ Thêm bàn mới</span>
                </a>
                <a href="{{ route('admin.table.trash') }}" class="ant-btn ant-btn-sm text-gray-500 hover:text-red-600 font-medium bg-white border border-gray-200">
                    <span>Thùng rác bàn</span>
                </a>
            @endif
            <a href="{{ route('admin.order.index') }}" class="ant-btn ant-btn-sm font-medium bg-white border border-gray-200 text-gray-700">
                <span>Đơn hàng giao</span>
            </a>
            @if(Auth::user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="ant-btn ant-btn-sm font-medium bg-white border border-gray-200 text-gray-700">
                    <span>Báo cáo doanh thu</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Stat Overview Mini Cards (Shopify Polaris / Ant Design Style) -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5 mb-5">
        <!-- Tổng số bàn -->
        <div class="bg-white p-3.5 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider block">Tổng số bàn</span>
                <span class="text-xl font-black text-gray-900 mt-0.5 block">{{ $posStats['total'] ?? 0 }} <span class="text-xs font-normal text-gray-400">bàn</span></span>
            </div>
            <span class="text-xs font-semibold px-2 py-1 rounded bg-blue-50 text-blue-700">
                {{ count($floors) }} tầng
            </span>
        </div>

        <!-- Đang có khách -->
        <div class="bg-white p-3.5 rounded-xl border border-amber-200/80 shadow-sm flex items-center justify-between bg-amber-50/20">
            <div>
                <span class="text-[11px] font-semibold text-amber-800 uppercase tracking-wider block flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                    Đang có khách
                </span>
                <span class="text-xl font-black text-amber-900 mt-0.5 block">{{ $posStats['occupied'] ?? 0 }} <span class="text-xs font-normal text-amber-700">bàn</span></span>
            </div>
            <span class="text-xs font-bold px-2 py-1 rounded bg-amber-100 text-amber-800">
                {{ $posStats['total'] > 0 ? round(($posStats['occupied'] / $posStats['total']) * 100) : 0 }}%
            </span>
        </div>

        <!-- Bàn còn trống -->
        <div class="bg-white p-3.5 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider block">Bàn sẵn sàng</span>
                <span class="text-xl font-black text-gray-800 mt-0.5 block">{{ $posStats['empty'] ?? 0 }} <span class="text-xs font-normal text-gray-400">bàn</span></span>
            </div>
            <span class="text-xs font-semibold px-2 py-1 rounded bg-emerald-50 text-emerald-700">
                Sẵn có
            </span>
        </div>

        <!-- Doanh thu tạm tính trên bàn -->
        <div class="bg-white p-3.5 rounded-xl border border-orange-200/80 shadow-sm flex items-center justify-between bg-orange-50/20">
            <div>
                <span class="text-[11px] font-semibold text-orange-800 uppercase tracking-wider block">Tạm tính trên bàn</span>
                <span class="text-xl font-black text-[#C25E00] mt-0.5 block tracking-tight">{{ number_format($posStats['serving_revenue'] ?? 0) }}₫</span>
            </div>
            <span class="text-[11px] font-bold px-2 py-0.5 rounded bg-orange-100 text-orange-800">
                {{ $posStats['occupied'] }} đơn
            </span>
        </div>
    </div>

    <!-- Filter & Floor Control Bar (Sạch sẽ, trực quan, lọc tức thì không load lại trang) -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-3 mb-5 flex flex-wrap items-center justify-between gap-3 text-xs">
        <!-- Floor Tabs -->
        <div class="flex items-center gap-1.5 flex-wrap" id="floorTabGroup">
            <button type="button" 
                    onclick="filterFloor('all')" 
                    class="floor-filter-btn px-3.5 py-1.5 rounded-lg font-bold bg-stone-900 text-white transition-all cursor-pointer shadow-2xs" 
                    data-floor="all">
                Tất cả các tầng ({{ $posStats['total'] }})
            </button>
            @foreach($floors as $floor)
                <button type="button" 
                        onclick="filterFloor({{ $floor->id }})" 
                        class="floor-filter-btn px-3.5 py-1.5 rounded-lg font-medium text-gray-600 hover:text-stone-900 hover:bg-gray-100 bg-white border border-gray-200 transition-all cursor-pointer" 
                        data-floor="{{ $floor->id }}">
                    {{ $floor->name }} ({{ $floor->tables->count() }})
                </button>
            @endforeach
        </div>

        <!-- Status Filter Buttons & Search -->
        <div class="flex items-center gap-2 flex-wrap">
            <div class="flex items-center bg-gray-100 p-0.5 rounded-lg">
                <button type="button" onclick="filterStatus('all')" class="status-filter-btn px-2.5 py-1 rounded-md font-bold bg-white text-gray-900 shadow-2xs transition-all cursor-pointer" data-status="all">
                    Tất cả
                </button>
                <button type="button" onclick="filterStatus('occupied')" class="status-filter-btn px-2.5 py-1 rounded-md font-medium text-gray-600 hover:text-gray-900 transition-all cursor-pointer" data-status="occupied">
                    Có khách ({{ $posStats['occupied'] }})
                </button>
                <button type="button" onclick="filterStatus('empty')" class="status-filter-btn px-2.5 py-1 rounded-md font-medium text-gray-600 hover:text-gray-900 transition-all cursor-pointer" data-status="empty">
                    Bàn trống ({{ $posStats['empty'] }})
                </button>
            </div>

            <!-- Quick Table Search -->
            <input type="text" id="tableSearchInput" oninput="searchTables(this.value)" placeholder="Tìm tên bàn..." 
                   class="px-3 py-1.5 rounded-lg border border-gray-200 focus:outline-none focus:border-[#F47716] text-xs bg-gray-50 focus:bg-white transition-colors w-36 sm:w-44">
        </div>
    </div>

    <!-- Floors & Tables Grid -->
    <div class="space-y-6" id="tablesMainContainer">
        @foreach($floors as $floor)
            <div class="floor-section" data-floor-id="{{ $floor->id }}">
                <!-- Floor Title Bar -->
                <div class="flex items-center justify-between pb-2.5 mb-4 border-b border-gray-200">
                    <div class="flex items-center gap-2.5">
                        <h2 class="text-base font-black text-stone-900 uppercase tracking-wider">{{ $floor->name }}</h2>
                        <span class="text-xs text-stone-500 font-bold bg-stone-100 px-2.5 py-0.5 rounded-full border border-stone-200">({{ $floor->tables->count() }} bàn)</span>
                    </div>

                    <div class="text-xs text-stone-500 font-medium">
                        @php
                            $floorOccupied = $floor->tables->filter(fn($t) => isset($activeOrders[$t->id]))->count();
                        @endphp
                        <span>Đang dùng: <strong class="text-amber-600 font-bold text-sm">{{ $floorOccupied }}</strong> / {{ $floor->tables->count() }} bàn</span>
                    </div>
                </div>
                
                <!-- Table Cards Grid (Rộng rãi, to rõ, chuẩn POS chuyên nghiệp) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-4 gap-4 sm:gap-5">
                    @foreach($floor->tables as $table)
                        @php
                            $isOccupied = isset($activeOrders[$table->id]);
                            $order = $isOccupied ? $activeOrders[$table->id] : null;
                            $hasNewItems = $isOccupied ? ($order->has_new_items ?? false) : false;
                            
                            $isLocked = false;
                            $mergedOrder = null;
                            if (!$isOccupied && $table->locked_order_id && isset($ordersById[$table->locked_order_id])) {
                                 $isLocked = true;
                                 $mergedOrder = $ordersById[$table->locked_order_id];
                            }

                            // Tính toán món & doanh thu của bàn
                            $dishesCount = 0;
                            $tableAmount = 0;
                            if ($isOccupied && $order) {
                                $dishesCount = $order->orderDetails->sum('qty');
                                $sub = $order->orderDetails->sum('amount');
                                $tableAmount = $order->total_price > 0 ? $order->total_price : $sub;
                            }

                            $tableServiceCall = $activeServiceCalls[$table->id] ?? null;
                            $totalSeats = $table->seats + ($lockedSeats[$order->id ?? 0] ?? 0);
                        @endphp
                        
                        <div class="table-card group" 
                             data-table-id="{{ $table->id }}"
                             data-table-name="{{ $table->name }}"
                             data-floor-name="{{ $floor->name }}"
                             data-status="{{ $isOccupied ? 'occupied' : 'empty' }}"
                             data-is-locked="{{ $isLocked ? 'true' : 'false' }}">
                             
                            <!-- THẺ BÀN ĂN: Thiết kế chuẩn POS hiện đại, rộng rãi, lớn, dễ thao tác -->
                            <div class="bg-white rounded-2xl border transition-all duration-200 flex flex-col justify-between min-h-[220px] relative overflow-hidden shadow-xs hover:shadow-lg hover:-translate-y-0.5
                                @if($hasNewItems) border-rose-400 ring-2 ring-rose-300
                                @elseif($isOccupied) border-amber-300 bg-amber-50/15
                                @elseif($isLocked) border-indigo-200 bg-indigo-50/15
                                @else border-gray-200 hover:border-gray-300 @endif">

                                <!-- Top Color Accent Strip -->
                                <div class="h-2 w-full 
                                    @if($hasNewItems) bg-rose-500 animate-pulse
                                    @elseif($isOccupied) bg-gradient-to-r from-amber-500 to-orange-500
                                    @elseif($isLocked) bg-indigo-500
                                    @else bg-emerald-500 @endif">
                                </div>

                                <!-- Header Thẻ Bàn: Tên bàn to rõ, số ghế và nhãn trạng thái -->
                                <div class="p-4 pb-3 border-b border-gray-100 flex items-start justify-between">
                                    <div>
                                        <h3 class="text-xl sm:text-2xl font-black text-gray-900 tracking-tight leading-tight">
                                            {{ $table->name }}
                                        </h3>
                                        <span class="text-xs text-gray-500 font-medium block mt-1 whitespace-nowrap">
                                            {{ $totalSeats }} chỗ ngồi
                                        </span>
                                    </div>

                                    <!-- Status Pill Badge & Actions -->
                                    <div class="flex items-center gap-1.5">
                                        @if($hasNewItems)
                                            <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-rose-100 text-rose-700 border border-rose-200 animate-pulse">
                                                Món mới
                                            </span>
                                        @elseif($isOccupied)
                                            <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                Có khách
                                            </span>
                                        @elseif($isLocked)
                                            <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-indigo-100 text-indigo-700 border border-indigo-200">
                                                Đã ghép
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                Trống
                                            </span>
                                        @endif

                                        <!-- In mã QR để bàn -->
                                        <a href="{{ route('admin.table.qr', $table->id) }}" class="w-7 h-7 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-blue-600 flex items-center justify-center text-xs transition-colors" title="In mã QR để bàn">
                                            <i class="fa-solid fa-qrcode"></i>
                                        </a>

                                        @if($isLocked)
                                            <button type="button" onclick="event.stopPropagation(); confirmUnmerge({{ $table->id }})" class="w-7 h-7 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-rose-600 flex items-center justify-center text-xs transition-colors" title="Hủy ghép bàn">
                                                ✕
                                            </button>
                                        @elseif(!$isOccupied)
                                            <button type="button" onclick="event.stopPropagation(); showMergeModal({{ $table->id }}, '{{ $table->name }}', '{{ $floor->name }}')" class="w-7 h-7 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-blue-600 flex items-center justify-center text-xs transition-colors" title="Ghép bàn với bàn khác">
                                                <i class="fa-solid fa-link"></i>
                                            </button>
                                            @if(Auth::user()->isAdmin())
                                                <a href="{{ route('admin.table.edit', $table->id) }}" class="w-7 h-7 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-amber-600 flex items-center justify-center text-xs transition-colors" title="Sửa tên bàn / số ghế">
                                                    <i class="fa-solid fa-pen"></i>
                                                </a>
                                            @endif
                                        @else
                                            @if(Auth::user()->isAdmin())
                                                <a href="{{ route('admin.table.edit', $table->id) }}" class="w-7 h-7 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-amber-600 flex items-center justify-center text-xs transition-colors" title="Sửa tên bàn / số ghế">
                                                    <i class="fa-solid fa-pen"></i>
                                                </a>
                                            @endif
                                        @endif
                                    </div>
                                </div>

                                <!-- Body Thẻ Bàn: Hiển thị đầy đủ thông tin ăn uống & số tiền tạm tính -->
                                <div class="p-4 py-3 flex-1 flex flex-col justify-center">
                                    @if($isOccupied)
                                        <div class="space-y-2">
                                            <div class="flex items-center justify-between text-stone-500 text-xs font-medium">
                                                <span>{{ $order->number_of_guests ?? 1 }} khách</span>
                                                <span>{{ $order->created_at ? $order->created_at->format('H:i') : '' }}</span>
                                            </div>

                                            <div class="flex items-center justify-between py-1.5 border-y border-stone-100">
                                                <span class="font-bold text-stone-800 text-xs sm:text-sm">
                                                    {{ $dishesCount }} món đang dùng
                                                </span>
                                                <button type="button" onclick="event.stopPropagation(); openDishesModal({{ $table->id }})" 
                                                        class="text-blue-600 hover:text-blue-800 font-bold cursor-pointer text-xs hover:underline"
                                                        title="Xem danh sách chi tiết các món bàn đang gọi">
                                                    Xem chi tiết »
                                                </button>
                                            </div>

                                            <!-- Tổng tiền tạm tính: Chữ đậm, to, rõ ràng, không font-mono -->
                                            <div class="text-xl font-black text-[#C25E00] tracking-tight pt-1">
                                                {{ number_format($tableAmount, 0, ',', '.') }}₫
                                            </div>
                                        </div>
                                    @elseif($isLocked)
                                        <div class="text-xs text-indigo-700 py-3">
                                            <span class="block text-xs text-gray-400 mb-1">Ghép vào bàn chính:</span>
                                            <strong class="font-black text-base text-indigo-900">{{ $mergedOrder->table->name ?? '...' }}</strong>
                                        </div>
                                    @else
                                        <!-- Bàn trống -->
                                        <div class="text-center py-3 text-xs">
                                            <span class="block text-xs text-gray-400">Sẵn sàng đón khách</span>
                                            <span class="text-xs text-emerald-600 font-bold block mt-1">Bàn sạch</span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Service Call Banner (Nếu khách đang rung chuông) -->
                                @if($tableServiceCall)
                                    <div class="px-3 py-1.5 bg-amber-500 text-white text-xs font-bold flex items-center justify-between" id="serviceCallBar-{{ $table->id }}">
                                        <span class="truncate mr-1.5" title="{{ $tableServiceCall->note ?? $tableServiceCall->type_info['label'] }}">
                                            {{ $tableServiceCall->type_info['label'] }}
                                        </span>
                                        <button type="button" 
                                                onclick="event.stopPropagation(); resolveTableServiceCall({{ $tableServiceCall->id }}, {{ $table->id }})" 
                                                class="bg-white text-amber-900 hover:bg-amber-50 px-2 py-0.5 rounded text-[11px] font-bold whitespace-nowrap cursor-pointer shadow-2xs">
                                            Đã xong
                                        </button>
                                    </div>
                                @endif

                                <!-- Footer Nút Bấm: Thao tác to rõ, chuẩn POS cảm ứng & chuột -->
                                <div class="p-3 bg-gray-50 border-t border-gray-100 flex items-center gap-2">
                                    @if($isOccupied)
                                        <a href="{{ route('admin.table-order.order', $table->id) }}" 
                                           class="flex-1 py-2.5 px-3 bg-white hover:bg-amber-50 border border-gray-200 hover:border-amber-400 text-gray-800 hover:text-amber-800 font-bold rounded-xl text-xs sm:text-sm text-center transition-colors shadow-2xs">
                                            Gọi món
                                        </a>
                                        <a href="{{ route('admin.table-order.payment', $table->id) }}" 
                                           class="flex-1 py-2.5 px-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs sm:text-sm text-center transition-colors shadow-2xs">
                                            Thanh toán
                                        </a>
                                    @elseif($isLocked)
                                        <span class="w-full text-center text-xs text-gray-400 py-2">
                                            Đã ghép đơn chung
                                        </span>
                                    @else
                                        <!-- Bàn trống: Nút Mở bàn -->
                                        <button type="button" 
                                                onclick="handleTableClick({{ $table->id }}, '{{ $table->name }}', false, '{{ $floor->name }}', false)" 
                                                class="w-full py-2.5 px-3 bg-stone-900 hover:bg-[#C25E00] text-white font-bold rounded-xl text-xs sm:text-sm text-center transition-all cursor-pointer shadow-2xs">
                                            Mở bàn & Gọi món
                                        </button>
                                    @endif
                                </div>

                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <!-- Modal Đăng ký bàn khi bấm mở bàn trống -->
    <div class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4 backdrop-blur-2xs" id="registerTableModal">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full max-h-[90vh] overflow-auto border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-white text-gray-900 rounded-t-xl">
                <h3 class="text-base font-bold text-gray-900 tracking-tight" id="modalTableName">Mở Bàn Mới</h3>
                <button onclick="closeModal('registerTableModal')" class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-700 transition-colors cursor-pointer text-sm">
                    ✕
                </button>
            </div>
            
            <form id="registerTableForm" action="{{ route('admin.table-order.registerTable') }}" method="POST">
                @csrf
                <div class="p-5 space-y-4 text-xs">
                    <input type="hidden" id="selectedTableId" name="table_id" value="{{ old('table_id') }}">
                    
                    <div>
                        <label for="customerPhone" class="block font-bold text-gray-700 mb-1">
                            Số điện thoại khách hàng <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 focus:border-[#F47716] focus:outline-none transition-all text-xs bg-gray-50 focus:bg-white" id="customerPhone" name="phone" value="{{ old('phone') }}" placeholder="Nhập số điện thoại (ví dụ: 0988 123 456)">
                        @error('phone')
                            <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="customerName" class="block font-bold text-gray-700 mb-1">
                            Tên khách hàng đại diện <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 focus:border-[#F47716] focus:outline-none transition-all text-xs bg-gray-50 focus:bg-white" id="customerName" name="customer_name" value="{{ old('customer_name') }}" placeholder="Nhập tên khách hàng">
                        @error('customer_name')
                            <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="customerGender" class="block font-bold text-gray-700 mb-1">
                                Giới tính
                            </label>
                            <select class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 focus:border-[#F47716] focus:outline-none transition-all text-xs bg-white" id="customerGender" name="gender">
                                <option value="1" {{ old('gender') == '1' ? 'selected' : '' }}>Anh / Nam</option>
                                <option value="0" {{ old('gender') == '0' ? 'selected' : '' }}>Chị / Nữ</option>
                            </select>
                        </div>

                        <div>
                            <label for="numberOfGuests" class="block font-bold text-gray-700 mb-1">
                                Số lượng khách <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 focus:border-[#F47716] focus:outline-none transition-all text-xs bg-gray-50 focus:bg-white font-bold" id="numberOfGuests" name="number_of_guests" min="1" value="{{ old('number_of_guests', 1) }}">
                            @error('number_of_guests')
                                <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="px-5 py-3.5 bg-gray-50 border-t border-gray-100 flex gap-3 text-xs">
                    <button type="button" onclick="closeModal('registerTableModal')" class="flex-1 px-4 py-2.5 rounded-lg border border-gray-300 text-gray-700 font-semibold hover:bg-gray-100 transition-colors cursor-pointer">
                        Đóng
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2.5 rounded-lg bg-[#F47716] hover:bg-[#EA580C] text-white font-bold transition-all shadow-xs cursor-pointer uppercase tracking-wider">
                        Xác nhận mở bàn
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Ghép bàn -->
    <div class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4 backdrop-blur-2xs" id="mergeTableModal">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full max-h-[90vh] overflow-auto border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-white text-gray-900 rounded-t-xl">
                <h3 class="text-base font-bold text-gray-900 tracking-tight" id="mergeSourceTableName">Ghép Bàn Ăn</h3>
                <button onclick="closeModal('mergeTableModal')" class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-700 transition-colors cursor-pointer text-sm">
                    ✕
                </button>
            </div>
            
            <div class="p-5 space-y-4 text-xs">
                <input type="hidden" id="mergeSourceTableId">
                
                <div>
                    <label class="block font-bold text-gray-700 mb-1.5">
                        Chọn bàn muốn ghép vào (Bàn đang phục vụ khách):
                    </label>
                    <select class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 focus:border-[#F47716] focus:outline-none transition-all text-xs bg-white" id="mergeTargetTableId">
                        <option value="">-- Đang tải danh sách bàn có khách... --</option>
                    </select>
                </div>
            </div>
            
            <div class="px-5 py-3.5 bg-gray-50 border-t border-gray-100 flex gap-3 text-xs">
                <button type="button" onclick="closeModal('mergeTableModal')" class="flex-1 px-4 py-2.5 rounded-lg border border-gray-300 text-gray-700 font-semibold hover:bg-gray-100 transition-colors cursor-pointer">
                    Hủy
                </button>
                <button type="button" onclick="submitMergeTable()" class="flex-1 px-4 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-bold transition-all shadow-xs cursor-pointer uppercase tracking-wider">
                    Xác nhận ghép
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Thông báo Đơn Mới (Realtime Toast Popup) -->
    <div class="fixed top-4 right-4 z-[60] hidden" id="newOrderModal">
        <div class="bg-white rounded-xl shadow-2xl w-84 border-l-4 border-rose-500 overflow-hidden border border-gray-200">
            <div class="p-4">
                <div class="flex items-center gap-3 mb-2.5">
                    <div class="w-9 h-9 bg-rose-100 text-rose-600 rounded-full flex items-center justify-center font-bold text-sm shrink-0">
                        <i class="fa-solid fa-bell text-rose-600"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm font-bold text-gray-900">Khách Vừa Đặt Món!</h3>
                        <p class="text-xs text-gray-500 font-semibold truncate" id="newOrderTable"></p>
                    </div>
                </div>
                <div class="text-xs text-gray-700 mb-3 bg-gray-50 p-2.5 rounded-lg font-mono" id="newOrderContent"></div>
                <div class="flex gap-2 text-xs">
                    <button type="button" onclick="closeNewOrderModal()" class="flex-1 px-3 py-2 rounded-lg border border-gray-200 text-gray-700 font-medium hover:bg-gray-50 transition-colors">
                        Đóng
                    </button>
                    <button type="button" id="viewNewOrderBtn" class="flex-1 px-3 py-2 rounded-lg bg-[#F47716] hover:bg-[#EA580C] text-white font-bold transition-all text-center">
                        Xem & In
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentTableId = null;
        let processedOrderIds = new Set();
        let currentNewOrderTableId = null;
        let currentFloorFilter = 'all';
        let currentStatusFilter = 'all';
        let currentSearchQuery = '';
        
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        }
        
        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }

        // Filter Floors
        function filterFloor(floorId) {
            currentFloorFilter = String(floorId);
            document.querySelectorAll('.floor-filter-btn').forEach(btn => {
                const bf = btn.getAttribute('data-floor');
                if (bf === currentFloorFilter) {
                    btn.className = 'floor-filter-btn px-3.5 py-1.5 rounded-lg font-bold bg-stone-900 text-white transition-all cursor-pointer shadow-2xs';
                } else {
                    btn.className = 'floor-filter-btn px-3.5 py-1.5 rounded-lg font-medium text-gray-600 hover:text-stone-900 hover:bg-gray-100 bg-white border border-gray-200 transition-all cursor-pointer';
                }
            });
            applyAllFilters();
        }

        // Filter Status (All, Occupied, Empty)
        function filterStatus(status) {
            currentStatusFilter = status;
            document.querySelectorAll('.status-filter-btn').forEach(btn => {
                const bs = btn.getAttribute('data-status');
                if (bs === currentStatusFilter) {
                    btn.className = 'status-filter-btn px-2.5 py-1 rounded-md font-bold bg-white text-gray-900 shadow-2xs transition-all cursor-pointer';
                } else {
                    btn.className = 'status-filter-btn px-2.5 py-1 rounded-md font-medium text-gray-600 hover:text-gray-900 transition-all cursor-pointer';
                }
            });
            applyAllFilters();
        }

        // Search Tables
        function searchTables(kw) {
            currentSearchQuery = kw.trim().toLowerCase();
            applyAllFilters();
        }

        // Combined Filter Engine
        function applyAllFilters() {
            document.querySelectorAll('.floor-section').forEach(sec => {
                const fId = sec.getAttribute('data-floor-id');
                const floorMatches = (currentFloorFilter === 'all' || currentFloorFilter === fId);
                
                let visibleCardsInFloor = 0;
                sec.querySelectorAll('.table-card').forEach(card => {
                    const status = card.getAttribute('data-status');
                    const name = card.getAttribute('data-table-name').toLowerCase();

                    const statusMatches = (currentStatusFilter === 'all' || currentStatusFilter === status);
                    const nameMatches = (!currentSearchQuery || name.includes(currentSearchQuery));

                    if (floorMatches && statusMatches && nameMatches) {
                        card.style.display = '';
                        visibleCardsInFloor++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                sec.style.display = (visibleCardsInFloor > 0 && floorMatches) ? '' : 'none';
            });
        }

        function openNewOrderModal(order) {
            $('#newOrderTable').text('Bàn: ' + order.table.name);
            let total = order.order_details.reduce((sum, item) => sum + item.amount, 0);
            let contentHtml = `<div class="mt-0.5">Tổng tiền: <strong>${total.toLocaleString('vi-VN')}₫</strong></div>`;
            $('#newOrderContent').html(contentHtml);
            currentNewOrderTableId = order.table.id;
            $('#newOrderModal').removeClass('hidden');
        }

        function closeNewOrderModal() {
            $('#newOrderModal').addClass('hidden');
        }

        window.resolveTableServiceCall = function(callId, tableId) {
            $.ajax({
                url: '{{ url("admin/notifications/service-call") }}/' + callId + '/complete',
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    if (res.success) {
                        $('#serviceCallBar-' + tableId).slideUp(200, function() { $(this).remove(); });
                        if (typeof window.pollSystemNotifications === 'function') {
                            window.pollSystemNotifications();
                        }
                    }
                }
            });
        };

        $(document).ready(function() {
            $('#viewNewOrderBtn').click(function() {
                if (currentNewOrderTableId) {
                    window.location.href = '{{ route("admin.table-order.order", ":id") }}'.replace(':id', currentNewOrderTableId);
                }
            });

            // Poll for new orders every 4 seconds
            setInterval(function() {
                $.ajax({
                    url: '{{ route("admin.table-order.checkNewOrders") }}',
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            if (response.new_orders && response.new_orders.length > 0) {
                                response.new_orders.forEach(order => {
                                    if (!processedOrderIds.has(order.id)) {
                                        processedOrderIds.add(order.id);
                                        openNewOrderModal(order);
                                    }
                                });
                            }
                            if (response.service_calls && response.service_calls.length > 0) {
                                response.service_calls.forEach(call => {
                                    if (!$('#serviceCallBar-' + call.table_id).length) {
                                        const tableCard = $(`.table-card[data-table-id="${call.table_id}"]`);
                                        if (tableCard.length) {
                                            const callLabel = call.call_type_label || (call.call_type === 'bill' ? 'Yêu cầu thanh toán' : 'Gọi phục vụ');
                                            const barHtml = `
                                                <div class="px-3 py-1.5 bg-amber-500 text-white text-xs font-bold flex items-center justify-between" id="serviceCallBar-${call.table_id}">
                                                    <span class="truncate mr-1.5" title="${call.note || callLabel}">
                                                        ${callLabel}
                                                    </span>
                                                    <button type="button" 
                                                            onclick="event.stopPropagation(); resolveTableServiceCall(${call.id}, ${call.table_id})" 
                                                            class="bg-white text-amber-900 hover:bg-amber-50 px-2 py-0.5 rounded text-[11px] font-bold whitespace-nowrap cursor-pointer shadow-2xs">
                                                        Đã xong
                                                    </button>
                                                </div>
                                            `;
                                            tableCard.find('> div:last-child').before(barHtml);
                                        }
                                    }
                                });
                            }
                        }
                    },
                    error: function() {
                        console.log('Error checking new orders');
                    }
                });
            }, 4000);

            @if($errors->has('phone') || $errors->has('customer_name') || $errors->has('number_of_guests') || $errors->has('table_id'))
                const tableId = $('#selectedTableId').val();
                if (tableId) {
                    const tableCard = $(`.table-card[data-table-id="${tableId}"]`);
                    if (tableCard.length) {
                        const tableName = tableCard.data('table-name');
                        const floorName = tableCard.data('floor-name');
                        $('#modalTableName').text('Mở Bàn: ' + (floorName ? (floorName + ' - ') : '') + tableName);
                    }
                }
                openModal('registerTableModal');
            @endif

            // Check user theo số điện thoại (debounce)
            let checkUserTimeout;
            $('#customerPhone').on('input', function() {
                const phone = $(this).val();
                if (checkUserTimeout) clearTimeout(checkUserTimeout);
                checkUserTimeout = setTimeout(function() {
                    if (phone.length >= 10) checkUserByPhone(phone);
                }, 500);
            });

            function checkUserByPhone(phone) {
                $.ajax({
                    url: '{{ route("admin.table-order.checkUserByPhone") }}',
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}', phone: phone },
                    success: function(response) {
                        if (response.success && response.user) {
                            $('#customerName').val(response.user.fullname);
                            $('#customerName').prop('readonly', true);
                            if (response.user.gender !== null && response.user.gender !== undefined) {
                                $('#customerGender').val(String(response.user.gender));
                            } else {
                                $('#customerGender').val('1');
                            }
                            if (typeof toastr !== 'undefined') toastr.success('Đã tìm thấy khách hàng: ' + response.user.fullname);
                        } else {
                            if ($('#customerName').prop('readonly')) {
                                $('#customerName').val('');
                                $('#customerName').prop('readonly', false);
                            }
                        }
                    }
                });
            }
        });

        // Xử lý click vào bàn
        function handleTableClick(tableId, tableName, isOccupied, floorName, isLocked) {
            currentTableId = tableId;
            
            if (isLocked) {
                if (typeof toastr !== 'undefined') toastr.info('Bàn này đang được ghép với bàn khác');
                return;
            }
            
            if (isOccupied) {
                window.location.href = '{{ route("admin.table-order.order", ":id") }}'.replace(':id', tableId);
            } else {
                $('#modalTableName').text('Mở Bàn: ' + (floorName ? (floorName + ' - ') : '') + tableName);
                $('#selectedTableId').val(tableId);
                $('#customerPhone').val('');
                $('#customerName').val('');
                $('#customerName').prop('readonly', false);
                $('#customerGender').val('1');
                $('#numberOfGuests').val('1');
                openModal('registerTableModal');
            }
        }

        // Xử lý ghép bàn
        function showMergeModal(tableId, tableName, floorName) {
            $('#mergeSourceTableId').val(tableId);
            $('#mergeSourceTableName').text('Ghép: ' + (floorName ? (floorName + ' - ') : '') + tableName);
            
            let html = '<option value="">-- Chọn bàn đang phục vụ để ghép vào --</option>';
            $('.table-card').each(function() {
                 let id = $(this).data('table-id');
                 let name = $(this).data('table-name');
                 let fName = $(this).data('floor-name');
                 let isLocked = $(this).data('is-locked') === true || $(this).data('is-locked') === 'true';
                 let status = $(this).data('status');
                 
                 if (id != tableId && !isLocked && status === 'occupied') {
                     html += `<option value="${id}">${(fName ? fName + ' - ' : '') + name}</option>`;
                 }
            });
            $('#mergeTargetTableId').html(html);
            openModal('mergeTableModal');
        }

        function submitMergeTable() {
            let sourceId = $('#mergeSourceTableId').val();
            let targetId = $('#mergeTargetTableId').val();
            if (!targetId) {
                if (typeof toastr !== 'undefined') toastr.error('Vui lòng chọn bàn để ghép');
                return;
            }

            $.ajax({
                url: '{{ route("admin.table-order.mergeTable") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    source_table_id: sourceId,
                    target_table_id: targetId
                },
                success: function(response) {
                    if (response.success) {
                        if (typeof toastr !== 'undefined') toastr.success(response.message);
                        closeModal('mergeTableModal');
                        setTimeout(function() { location.reload(); }, 500);
                    } else {
                        if (typeof toastr !== 'undefined') toastr.error(response.message);
                    }
                },
                error: function() {
                    if (typeof toastr !== 'undefined') toastr.error('Có lỗi xảy ra');
                }
            });
        }

        function confirmUnmerge(tableId) {
            if (confirm('Bạn có chắc chắn muốn hủy ghép bàn này?')) {
                $.ajax({
                    url: '{{ route("admin.table-order.unmergeTable") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        table_id: tableId
                    },
                    success: function(response) {
                        if (response.success) {
                            if (typeof toastr !== 'undefined') toastr.success(response.message);
                            setTimeout(function() { location.reload(); }, 500);
                        } else {
                            if (typeof toastr !== 'undefined') toastr.error(response.message);
                        }
                    },
                    error: function() {
                        if (typeof toastr !== 'undefined') toastr.error('Có lỗi xảy ra');
                    }
                });
            }
        }

        // Modal xem chi tiết món ăn của bàn
        function openDishesModal(tableId) {
            const modal = document.getElementById('tableDishesModal');
            const loading = document.getElementById('modalLoading');
            const content = document.getElementById('modalContent');
            const emptyState = document.getElementById('modalEmptyState');

            if (!modal) return;

            // Reset UI state
            modal.classList.remove('hidden');
            loading.classList.remove('hidden');
            content.classList.add('hidden');
            emptyState.classList.add('hidden');

            // Fetch dishes data via AJAX
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

                data.dishes.forEach((dish) => {
                    const row = document.createElement('tr');
                    row.className = 'hover:bg-amber-50/20 transition-colors';
                    row.innerHTML = `
                        <td class="py-2.5 px-3">
                            <div class="flex items-center gap-2.5">
                                <img src="${dish.thumbnail}" alt="${dish.name}" class="w-9 h-9 rounded-lg object-cover shrink-0 border border-gray-200" onerror="this.src='/images/default-dish.jpg'"/>
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
                        <td class="py-2.5 px-3 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold border ${dish.status_badge}">
                                ${dish.status_text}
                            </span>
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

    <!-- Interactive Modal: Xem chi tiết món ăn của bàn -->
    <div id="tableDishesModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop blur -->
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" onclick="closeDishesModal()"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-gray-100">
                
                <!-- Modal Header: Nền trắng chữ đen thanh lịch, hiện đại -->
                <div class="px-6 py-4 bg-white text-gray-900 flex items-center justify-between border-b border-gray-100">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] uppercase font-bold tracking-wider text-[#F47716]">ĐƠN HÀNG ĐANG PHỤC VỤ</span>
                            <span id="modalTableBadge" class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Đang phục vụ
                            </span>
                        </div>
                        <h3 class="text-base font-bold text-gray-900 mt-0.5" id="modalTableTitle">Chi tiết món ăn bàn</h3>
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
                                    <th class="py-2.5 px-3 text-center w-16">SL</th>
                                    <th class="py-2.5 px-3 text-right w-24">Đơn giá</th>
                                    <th class="py-2.5 px-3 text-right w-28">Thành tiền</th>
                                    <th class="py-2.5 px-3 text-center w-28">Trạng thái</th>
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
    </script>
</x-layout-backend>
