<x-layout-backend>
    <!-- CONTENT -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Quản lý Order</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('admin/') }}">Trang chủ</a></li>
                        <li class="breadcrumb-item active">Order</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="row">
            <!-- Cột hiển thị bàn -->
            <div class="col-md-12">
                <div class="card">
                    <!-- <div class="card-header">
                        <h3 class="card-title">Danh sách bàn</h3>
                    </div> -->
                    <div class="card-body">
                        @foreach($floors as $floor)
                            @if(!$loop->first)
                                <div class="floor-separator"></div>
                            @endif
                            <div class="mb-4">
                                <h4 class="mb-3">
                                    <i class="fas fa-layer-group"></i> {{ $floor->name }}
                                </h4>
                                <div class="row">
                                    @foreach($floor->tables as $table)
                                        @php
                                            $isOccupied = isset($activeOrders[$table->id]);
                                            $order = $isOccupied ? $activeOrders[$table->id] : null;
                                            
                                            $isLocked = false;
                                            $mergedOrder = null;
                                            if (!$isOccupied && $table->locked_order_id && isset($ordersById[$table->locked_order_id])) {
                                                 $isLocked = true;
                                                 $mergedOrder = $ordersById[$table->locked_order_id];
                                            }
                                            
                                            $bgColor = '#424c54'; // available
                                            if ($isOccupied) $bgColor = '#ffc107'; // occupied
                                            if ($isLocked) $bgColor = '#42892f'; // locked/merged
                                        @endphp
                                        <div class="col-md-2 col-sm-3 col-4 mb-3">
                                            <div class="table-card {{ $isOccupied || $isLocked ? 'occupied' : 'available' }}" 
                                                 data-table-id="{{ $table->id }}"
                                                 data-table-name="{{ $table->name }}"
                                                 data-floor-name="{{ $floor->name }}"
                                                 style="cursor: pointer;"
                                                 onclick="handleTableClick({{ $table->id }}, '{{ $table->name }}', {{ $isOccupied ? 'true' : 'false' }}, '{{ $floor->name }}', {{ $isLocked ? 'true' : 'false' }})">
                                                <div class="p-2 border rounded text-white table-card-inner" 
                                                     style="background-color: {{ $bgColor }};">
                                                    <div class="font-weight-bold">{{ $table->name }}</div>
                                                    <hr style="border-color: rgba(255,255,255,0.3); margin: 8px 0;">
                                                    <div>
                                                        @if($isOccupied)
                                                            @php
                                                               $totalSeats = $table->seats + ($lockedSeats[$order->id] ?? 0);
                                                            @endphp
                                                            <div class=""><i class="fas fa-users"></i> {{ $order->number_of_guests }}/{{ $totalSeats }}</div>

                                                            @if(isset($order) && $order->created_at)
                                                                <div class="mt-1"><i class="fas fa-clock"></i> {{ $order->created_at->timezone('Asia/Ho_Chi_Minh')->format('H:i d/m/y') }}</div>
                                                            @endif

                                                        @elseif($isLocked)
                                                            <span>Ghép với {{ $mergedOrder->table->name ?? '...' }}</span>
                                                            <i class="fas fa-unlock text-white lock-format" onclick="event.stopPropagation(); confirmUnmerge({{ $table->id }})"></i>
                                                        @else
                                                            <div>Bàn Trống</div>
                                                            <div>0/{{ $table->seats }}</div>
                                                            <i class="fas fa-lock text-muted lock-format" onclick="event.stopPropagation(); showMergeModal({{ $table->id }}, '{{ $table->name }}', '{{ $floor->name }}')"></i>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Đăng ký bàn -->
    <div class="modal fade" id="registerTableModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><span id="modalTableName"></span></h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="registerTableForm" action="{{ route('admin.table-order.registerTable') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="selectedTableId" name="table_id" value="{{ old('table_id') }}">
                        @error('table_id')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                        
                        <div class="form-group">
                            <label for="customerPhone">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="customerPhone" name="phone" value="{{ old('phone') }}">
                            @error('phone')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Nhập số điện thoại để tự động lấy thông tin khách hàng</small>
                        </div>

                        <div class="form-group">
                            <label for="customerName">Tên khách hàng <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('customer_name') is-invalid @enderror" id="customerName" name="customer_name" value="{{ old('customer_name') }}">
                            @error('customer_name')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="customerGender">Giới tính</label>
                            <select class="form-control" id="customerGender" name="gender">
                                <option value="1" {{ old('gender') == '1' ? 'selected' : '' }}>Nam</option>
                                <option value="0" {{ old('gender') == '0' ? 'selected' : '' }}>Nữ</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="numberOfGuests">Số lượng khách <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('number_of_guests') is-invalid @enderror" id="numberOfGuests" name="number_of_guests" min="1" value="{{ old('number_of_guests', 1) }}">
                            @error('number_of_guests')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">Đăng ký</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Ghép bàn -->
    <div class="modal fade" id="mergeTableModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><span id="mergeSourceTableName"></span></h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="mergeSourceTableId">
                    <div class="form-group">
                        <label>Chọn bàn muốn ghép vào (Bàn đang có khách)</label>
                        <select class="form-control" id="mergeTargetTableId">
                            <option value="">-- Chọn bàn --</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" onclick="submitMergeTable()">Xác nhận ghép</button>
                </div>
            </div>
        </div>
    </div>



    <style>
        .table-card {
            transition: transform 0.2s;
        }
        .table-card-inner {
            min-height: 135px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            position: relative;
        }
        .floor-separator {
            border-top: 2px solid #e9ecef;
            margin: 20px 0;
        }
        /* PC: 2 columns for order items */
        /* Removed grid layout to fix layout issues as requested */
        .table-card.occupied {
            opacity: 0.9;
        }
        .order-item {
            border-bottom: 1px solid #eee;
            padding: 7px 0;
        }
        .product-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .product-card:hover {
            background-color: #f8f9fa;
        }
        .category-menu {
            overflow-x: auto;
            white-space: nowrap;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .category-item {
            display: inline-block;
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .category-item:hover {
            background-color: #e9ecef;
        }
        .category-item.active {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }
        .product-img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 5px 5px 0 0;
            margin-bottom: 10px;
        }
        .product-category-container {
            border-top: 2px solid #c4c4c4;
        }
        .lock-format { 
            cursor: pointer; 
            position: absolute; 
            bottom: 10px; 
            right: 10px; 
            font-size: 2.0rem; 
        }
        @media (min-width: 992px) {
            .order-item-container {
                padding: 0 20px;
            }
        }
    </style>

    <script>
        let currentTableId = null;


        $(document).ready(function() {
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
                $('#registerTableModal').modal('show');
            @endif

            // Xử lý click danh mục
            $(document).on('click', '.category-item', function() {
                $('.category-item').removeClass('active');
                $(this).addClass('active');
                const categoryId = $(this).data('id');
                $('#categoryFilter').val(categoryId);
                loadProducts();
            });

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
                $('#registerTableModal').modal('show');
            }
        }



        // Xử lý ghép bàn
        function showMergeModal(tableId, tableName, floorName) {
            $('#mergeSourceTableId').val(tableId);
            $('#mergeSourceTableName').text((floorName ? (floorName + ' - ') : '') + tableName);
            
            // Populate active tables
            let html = '<option value="">-- Chọn bàn --</option>';
            $('.table-card.occupied').each(function() {
                 let id = $(this).data('table-id');
                 let name = $(this).data('table-name');
                 let fName = $(this).data('floor-name');
                 // Exclude itself
                 if (id != tableId) {
                     html += `<option value="${id}">${(fName ? fName + ' - ' : '') + name}</option>`;
                 }
            });
            $('#mergeTargetTableId').html(html);
            $('#mergeTableModal').modal('show');
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
                        $('#mergeTableModal').modal('hide');
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
