<x-layout-backend>
    <!-- Page header -->
    <div class="mb-4">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-800 flex items-center gap-3">
                    <span class="w-8 h-8 bg-gradient-to-br from-orange-400 to-orange-600 rounded-lg flex items-center justify-center text-white">
                        <i class="fa-solid fa-cash-register text-sm"></i>
                    </span>
                    Quản lý Order
                </h1>
                <p class="text-slate-500 mt-1 text-sm">Chọn bàn để tạo order hoặc quản lý order hiện tại</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 text-sm">
                    <span class="w-3 h-3 rounded-full bg-slate-500"></span>
                    <span class="text-slate-600">Trống</span>
                </div>
                <div class="flex items-center gap-2 text-sm">
                    <span class="w-3 h-3 rounded-full bg-amber-400"></span>
                    <span class="text-slate-600">Có khách</span>
                </div>
                <div class="flex items-center gap-2 text-sm">
                    <span class="w-3 h-3 rounded-full bg-green-500"></span>
                    <span class="text-slate-600">Đã ghép</span>
                </div>
                <div class="flex items-center gap-2 text-sm">
                    <span class="w-3 h-3 rounded-full bg-red-500 animate-pulse"></span>
                    <span class="text-slate-600">Có món mới</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Floors & Tables -->
    <div class="space-y-4">
        @foreach($floors as $floor)
            <div class="card-hover">
                <!-- Floor header -->
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-8 h-8 bg-slate-100 rounded-lg flex items-center justify-center text-slate-600">
                        <i class="fa-solid fa-layer-group text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-slate-800">{{ $floor->name }}</h2>
                        <p class="text-slate-500 text-xs">{{ $floor->tables->count() }} bàn</p>
                    </div>
                </div>
                
                <!-- Tables grid -->
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
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
                            
                            $statusClass = 'bg-slate-500 hover:bg-slate-600'; // available
                            $statusText = 'Bàn Trống';
                            if ($isOccupied) {
                                if ($hasNewItems) {
                                    $statusClass = 'bg-red-500 hover:bg-red-600 animate-pulse'; // has new items
                                } else {
                                    $statusClass = 'bg-amber-400 hover:bg-amber-500'; // occupied
                                }
                            }
                            if ($isLocked) {
                                $statusClass = 'bg-green-500 hover:bg-green-600'; // locked/merged
                            }
                        @endphp
                        
                        <div class="table-card" 
                             data-table-id="{{ $table->id }}"
                             data-table-name="{{ $table->name }}"
                             data-floor-name="{{ $floor->name }}"
                             data-is-locked="{{ $isLocked ? 'true' : 'false' }}"
                             onclick="handleTableClick({{ $table->id }}, '{{ $table->name }}', {{ $isOccupied ? 'true' : 'false' }}, '{{ $floor->name }}', {{ $isLocked ? 'true' : 'false' }})">
                            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden cursor-pointer transition-all hover:shadow-lg hover:-translate-y-1 @if($hasNewItems) ring-2 ring-red-500 @endif">
                                <!-- Table status header -->
                                <div class="{{ $statusClass }} px-3 py-2 text-white relative">
                                    <div class="font-bold text-base">{{ $table->name }}</div>
                                    @if($isLocked)
                                        <button onclick="event.stopPropagation(); confirmUnmerge({{ $table->id }})" class="absolute top-2 right-2 w-6 h-6 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center transition-colors" title="Hủy ghép bàn">
                                            <i class="fa-solid fa-unlock text-xs"></i>
                                        </button>
                                    @endif
                                    @if(!$isOccupied && !$isLocked)
                                        <button onclick="event.stopPropagation(); showMergeModal({{ $table->id }}, '{{ $table->name }}', '{{ $floor->name }}')" class="absolute top-2 right-2 w-6 h-6 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center transition-colors" title="Ghép bàn">
                                            <i class="fa-solid fa-link text-xs"></i>
                                        </button>
                                    @endif
                                </div>
                                
                                <!-- Table info -->
                                <div class="p-3">
                                    @if($isOccupied)
                                        @php
                                           $totalSeats = $table->seats + ($lockedSeats[$order->id] ?? 0);
                                        @endphp
                                        <div class="flex items-center gap-2 text-slate-600 mb-2">
                                            <i class="fa-solid fa-users w-3.5 text-center text-xs"></i>
                                            <span class="font-medium text-sm">{{ $order->number_of_guests }}/{{ $totalSeats }} khách</span>
                                        </div>
                                        @if(isset($order) && $order->created_at)
                                            <div class="flex items-center gap-2 text-slate-500 text-xs">
                                                <i class="fa-solid fa-clock w-3.5 text-center text-xs"></i>
                                                <span>{{ $order->created_at->timezone('Asia/Ho_Chi_Minh')->format('H:i d/m/y') }}</span>
                                            </div>
                                        @endif
                                        <div class="mt-2 pt-2 border-t border-slate-100">
                                            @if($hasNewItems)
                                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-red-50 text-red-600 rounded-full text-xs font-medium">
                                                    <i class="fa-solid fa-bell text-xs"></i>
                                                    Có món mới
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-amber-50 text-amber-600 rounded-full text-xs font-medium">
                                                    <i class="fa-solid fa-utensils text-xs"></i>
                                                    Đang phục vụ
                                                </span>
                                            @endif
                                        </div>
                                    @elseif($isLocked)
                                        <div class="text-slate-600 mb-2 text-sm">
                                            <span class="font-medium">Ghép với {{ $mergedOrder->table->name ?? '...' }}</span>
                                        </div>
                                        <div class="mt-2 pt-2 border-t border-slate-100">
                                            <span class="inline-flex items-center gap-1 px-2 py-1 bg-green-50 text-green-600 rounded-full text-xs font-medium">
                                                <i class="fa-solid fa-link text-xs"></i>
                                                Đã ghép
                                            </span>
                                        </div>
                                    @else
                                        <div class="text-slate-500 mb-2 text-sm">
                                            <span class="font-medium">0/{{ $table->seats }} khách</span>
                                        </div>
                                        <div class="mt-2 pt-2 border-t border-slate-100">
                                            <span class="inline-flex items-center gap-1 px-2 py-1 bg-slate-100 text-slate-600 rounded-full text-xs font-medium">
                                                <i class="fa-solid fa-check text-xs"></i>
                                                Sẵn sàng
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <!-- Modal Đăng ký bàn -->
    <div class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4" id="registerTableModal">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full max-h-[90vh] overflow-auto">
            <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
                <h3 class="text-base font-semibold text-slate-800" id="modalTableName">Đăng ký bàn</h3>
                <button onclick="closeModal('registerTableModal')" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-slate-100 transition-colors">
                    <i class="fa-solid fa-times text-slate-400 text-sm"></i>
                </button>
            </div>
            
            <form id="registerTableForm" action="{{ route('admin.table-order.registerTable') }}" method="POST">
                @csrf
                <div class="p-4 space-y-4">
                    <input type="hidden" id="selectedTableId" name="table_id" value="{{ old('table_id') }}">
                    
                    <div>
                        <label for="customerPhone" class="block text-sm font-medium text-slate-700 mb-1">
                            Số điện thoại <span class="text-red-500">*</span>
                        </label>
                        <input type="text" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-400 focus:ring-2 focus:ring-orange-400/20 transition-all outline-none @error('phone') border-red-400 @enderror text-sm" id="customerPhone" name="phone" value="{{ old('phone') }}" placeholder="Nhập số điện thoại">
                        @error('phone')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-slate-500 text-xs mt-1">Nhập số điện thoại để tự động lấy thông tin khách hàng</p>
                    </div>

                    <div>
                        <label for="customerName" class="block text-sm font-medium text-slate-700 mb-1">
                            Tên khách hàng <span class="text-red-500">*</span>
                        </label>
                        <input type="text" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-400 focus:ring-2 focus:ring-orange-400/20 transition-all outline-none @error('customer_name') border-red-400 @enderror text-sm" id="customerName" name="customer_name" value="{{ old('customer_name') }}" placeholder="Nhập tên khách hàng">
                        @error('customer_name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="customerGender" class="block text-sm font-medium text-slate-700 mb-1">
                            Giới tính
                        </label>
                        <select class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-400 focus:ring-2 focus:ring-orange-400/20 transition-all outline-none bg-white text-sm" id="customerGender" name="gender">
                            <option value="1" {{ old('gender') == '1' ? 'selected' : '' }}>Nam</option>
                            <option value="0" {{ old('gender') == '0' ? 'selected' : '' }}>Nữ</option>
                        </select>
                    </div>

                    <div>
                        <label for="numberOfGuests" class="block text-sm font-medium text-slate-700 mb-1">
                            Số lượng khách <span class="text-red-500">*</span>
                        </label>
                        <input type="number" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-400 focus:ring-2 focus:ring-orange-400/20 transition-all outline-none @error('number_of_guests') border-red-400 @enderror text-sm" id="numberOfGuests" name="number_of_guests" min="1" value="{{ old('number_of_guests', 1) }}" placeholder="Nhập số lượng khách">
                        @error('number_of_guests')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <div class="px-4 py-3 border-t border-slate-200 flex gap-3">
                    <button type="button" onclick="closeModal('registerTableModal')" class="flex-1 px-3 py-2 rounded-lg border border-slate-300 text-slate-700 font-medium hover:bg-slate-50 transition-colors text-sm">
                        Hủy
                    </button>
                    <button type="submit" class="flex-1 px-3 py-2 rounded-lg bg-gradient-to-r from-orange-400 to-orange-600 text-white font-medium hover:from-orange-500 hover:to-orange-700 transition-all shadow-lg shadow-orange-500/25 text-sm">
                        Đăng ký
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Ghép bàn -->
    <div class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4" id="mergeTableModal">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full max-h-[90vh] overflow-auto">
            <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
                <h3 class="text-base font-semibold text-slate-800" id="mergeSourceTableName">Ghép bàn</h3>
                <button onclick="closeModal('mergeTableModal')" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-slate-100 transition-colors">
                    <i class="fa-solid fa-times text-slate-400 text-sm"></i>
                </button>
            </div>
            
            <div class="p-4 space-y-4">
                <input type="hidden" id="mergeSourceTableId">
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        Chọn bàn muốn ghép vào (Bàn đang có khách)
                    </label>
                    <select class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-400 focus:ring-2 focus:ring-orange-400/20 transition-all outline-none bg-white text-sm" id="mergeTargetTableId">
                        <option value="">-- Chọn bàn --</option>
                    </select>
                </div>
            </div>
            
            <div class="px-4 py-3 border-t border-slate-200 flex gap-3">
                <button type="button" onclick="closeModal('mergeTableModal')" class="flex-1 px-3 py-2 rounded-lg border border-slate-300 text-slate-700 font-medium hover:bg-slate-50 transition-colors text-sm">
                    Hủy
                </button>
                <button type="button" onclick="submitMergeTable()" class="flex-1 px-3 py-2 rounded-lg bg-gradient-to-r from-orange-400 to-orange-600 text-white font-medium hover:from-orange-500 hover:to-orange-700 transition-all shadow-lg shadow-orange-500/25 text-sm">
                    Xác nhận ghép
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Thông báo Đơn Mới -->
    <div class="fixed top-4 right-4 z-[60] hidden" id="newOrderModal">
        <div class="bg-white rounded-xl shadow-2xl w-80 border-l-4 border-red-500">
            <div class="p-4">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-orange-500 rounded-full flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-bell text-white"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-base font-bold text-slate-800">Đơn Mới!</h3>
                        <p class="text-xs text-slate-600" id="newOrderTable"></p>
                    </div>
                </div>
                <div class="text-xs text-slate-700 mb-3" id="newOrderContent"></div>
                <div class="flex gap-2">
                    <button type="button" onclick="closeNewOrderModal()" class="flex-1 px-3 py-2 rounded-lg border border-slate-300 text-slate-700 font-medium text-xs hover:bg-slate-50 transition-colors">
                        Đóng
                    </button>
                    <button type="button" id="viewNewOrderBtn" class="flex-1 px-3 py-2 rounded-lg bg-gradient-to-r from-orange-500 to-red-500 text-white font-medium text-xs hover:from-orange-600 hover:to-red-600 transition-all">
                        Xem & In
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentTableId = null;
        let processedOrderIds = new Set(); // Store order IDs we've already notified about
        let currentNewOrderTableId = null;
        
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        
        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function openNewOrderModal(order) {
            $('#newOrderTable').text('Bàn: ' + order.table.name);
            let total = order.order_details.reduce((sum, item) => sum + item.amount, 0);
            let contentHtml = `<div class="mt-1"><strong>Tổng:</strong> ${total.toLocaleString('vi-VN')}đ</div>`;
            $('#newOrderContent').html(contentHtml);
            currentNewOrderTableId = order.table.id;
            $('#newOrderModal').removeClass('hidden');
        }

        function closeNewOrderModal() {
            $('#newOrderModal').addClass('hidden');
        }

        $(document).ready(function() {
            // When user clicks "Xem Đơn & In"
            $('#viewNewOrderBtn').click(function() {
                if (currentNewOrderTableId) {
                    window.location.href = '{{ route("admin.table-order.order", ":id") }}'.replace(':id', currentNewOrderTableId);
                }
            });

            // Poll for new orders every 3 seconds
            setInterval(function() {
                $.ajax({
                    url: '{{ route("admin.table-order.checkNewOrders") }}',
                    method: 'GET',
                    success: function(response) {
                        if (response.success && response.new_orders.length > 0) {
                            response.new_orders.forEach(order => {
                                if (!processedOrderIds.has(order.id)) {
                                    processedOrderIds.add(order.id);
                                    openNewOrderModal(order);
                                }
                            });
                        }
                    },
                    error: function() {
                        console.log('Error checking new orders');
                    }
                });
            }, 3000);
            @if($errors->has('phone') || $errors->has('customer_name') || $errors->has('number_of_guests') || $errors->has('table_id'))
                // Khôi phục tiêu đề modal khi có lỗi validation
                const tableId = $('#selectedTableId').val();
                if (tableId) {
                    const tableCard = $(`.table-card[data-table-id="${tableId}"]`);
                    if (tableCard.length) {
                        const tableName = tableCard.data('table-name');
                        const floorName = tableCard.data('floor-name');
                        $('#modalTableName').text((floorName ? (floorName + ' - ') : '') + tableName);
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
                            toastr.success('Đã tìm thấy khách hàng: ' + response.user.fullname);
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
                toastr.info('Bàn này đang được ghép với bàn khác');
                return;
            }
            
            if (isOccupied) {
                // Redirect to full page order
                window.location.href = '{{ route("admin.table-order.order", ":id") }}'.replace(':id', tableId);
            } else {
                $('#modalTableName').text((floorName ? (floorName + ' - ') : '') + tableName);
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
            $('#mergeSourceTableName').text((floorName ? (floorName + ' - ') : '') + tableName);
            
            // Populate active tables
            let html = '<option value="">-- Chọn bàn --</option>';
            $('.table-card').each(function() {
                 let id = $(this).data('table-id');
                 let name = $(this).data('table-name');
                 let fName = $(this).data('floor-name');
                 let isLocked = $(this).data('is-locked') === true || $(this).data('is-locked') === 'true';
                 
                 // Find if this table is occupied
                 let hasOrder = false;
                 @foreach($activeOrders as $orderTableId => $order)
                    if (id == {{ $orderTableId }}) hasOrder = true;
                 @endforeach
                 
                 // Exclude itself and locked tables, only include occupied tables
                 if (id != tableId && !isLocked && hasOrder) {
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
                toastr.error('Vui lòng chọn bàn để ghép');
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
                        toastr.success(response.message);
                        closeModal('mergeTableModal');
                        setTimeout(function() {
                            location.reload();
                        }, 500);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('Có lỗi xảy ra');
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
                            toastr.success(response.message);
                            setTimeout(function() {
                                location.reload();
                            }, 500);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function() {
                        toastr.error('Có lỗi xảy ra');
                    }
                });
            }
        }
    </script>
</x-layout-backend>
