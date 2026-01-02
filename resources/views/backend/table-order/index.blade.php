<x-layout-backend>
    <!-- CONTENT -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Quản lý Order tại bàn</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('admin/') }}">Trang chủ</a></li>
                        <li class="breadcrumb-item active">Order tại bàn</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="row">
            <!-- Cột hiển thị bàn -->
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Danh sách bàn</h3>
                    </div>
                    <div class="card-body">
                        @foreach($floors as $floor)
                            <div class="mb-4">
                                <h4 class="mb-3">
                                    <i class="fas fa-layer-group"></i> {{ $floor->name }}
                                </h4>
                                <div class="row">
                                    @foreach($floor->tables as $table)
                                        @php
                                            $isOccupied = isset($activeOrders[$table->id]);
                                            $order = $isOccupied ? $activeOrders[$table->id] : null;
                                        @endphp
                                        <div class="col-md-2 col-sm-3 col-4 mb-3">
                                            <div class="table-card {{ $isOccupied ? 'occupied' : 'available' }}" 
                                                 data-table-id="{{ $table->id }}"
                                                 data-table-name="{{ $table->name }}"
                                                 style="cursor: pointer;"
                                                 onclick="handleTableClick({{ $table->id }}, '{{ $table->name }}', {{ $isOccupied ? 'true' : 'false' }})">
                                                <div class="text-center p-3 border rounded" 
                                                     style="background-color: {{ $isOccupied ? '#ffc107' : '#28a745' }}; color: white; min-height: 100px; display: flex; flex-direction: column; justify-content: center;">
                                                    <i class="fas fa-chair fa-2x mb-2"></i>
                                                    <div class="font-weight-bold">{{ $table->name }}</div>
                                                    <small>
                                                        @if($isOccupied)
                                                            <i class="fas fa-users"></i> Có khách
                                                        @else
                                                            <i class="fas fa-check-circle"></i> Trống
                                                        @endif
                                                    </small>
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

            <!-- Sidebar Order -->
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Order món</h3>
                    </div>
                    <div class="card-body" id="orderSidebar">
                        <p class="text-muted">Vui lòng chọn bàn để bắt đầu order</p>
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
                    <h5 class="modal-title">Đăng ký bàn: <span id="modalTableName"></span></h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="registerTableForm">
                    <div class="modal-body">
                        <input type="hidden" id="selectedTableId" name="table_id">
                        
                        <div class="form-group">
                            <label for="customerPhone">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="customerPhone" name="phone" required>
                            <small class="form-text text-muted">Nhập số điện thoại để tự động lấy thông tin khách hàng</small>
                        </div>

                        <div class="form-group">
                            <label for="customerName">Tên khách hàng <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="customerName" name="customer_name" required>
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

    <!-- Modal Order món -->
    <div class="modal fade" id="orderModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Order món - Bàn: <span id="orderTableName"></span></h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="orderTableId" name="table_id">
                    <input type="hidden" id="currentOrderId" name="order_id">
                    
                    <!-- Danh sách món đã order -->
                    <div id="orderItemsList" class="mb-3">
                        <h6>Món đã order:</h6>
                        <div id="orderItemsContent"></div>
                    </div>

                    <!-- Danh sách món để chọn -->
                    <div class="mb-3">
                        <label>Chọn danh mục:</label>
                        <select class="form-control" id="categoryFilter">
                            <option value="">Tất cả</option>
                            @php
                                $categories = \App\Models\Category::whereNull('deleted_at')->orderBy('name')->get();
                            @endphp
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="productsList" class="row" style="max-height: 400px; overflow-y: auto;">
                        <!-- Products will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .table-card {
            transition: transform 0.2s;
        }
        .table-card:hover {
            transform: scale(1.05);
        }
        .table-card.occupied {
            opacity: 0.9;
        }
        .order-item {
            border-bottom: 1px solid #eee;
            padding: 10px 0;
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
    </style>

    <script>
        let currentTableId = null;
        let currentOrderId = null;

        // Xử lý click vào bàn
        function handleTableClick(tableId, tableName, isOccupied) {
            currentTableId = tableId;
            
            if (isOccupied) {
                // Bàn đã có khách, hiển thị order
                loadTableOrder(tableId);
                $('#orderTableName').text(tableName);
                $('#orderTableId').val(tableId);
                $('#orderModal').modal('show');
            } else {
                // Bàn trống, hiển thị form đăng ký
                $('#modalTableName').text(tableName);
                $('#selectedTableId').val(tableId);
                $('#customerPhone').val('');
                $('#customerName').val('');
                $('#customerName').prop('disabled', false);
                $('#registerTableModal').modal('show');
            }
        }

        // Check user theo số điện thoại
        $('#customerPhone').on('blur', function() {
            const phone = $(this).val();
            if (phone.length >= 10) {
                $.ajax({
                    url: '{{ route("admin.table-order.checkUserByPhone") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        phone: phone
                    },
                    success: function(response) {
                        if (response.success && response.user) {
                            $('#customerName').val(response.user.fullname);
                            $('#customerName').prop('disabled', true);
                            toastr.success('Đã tìm thấy khách hàng: ' + response.user.fullname);
                        } else {
                            $('#customerName').val('');
                            $('#customerName').prop('disabled', false);
                        }
                    },
                    error: function() {
                        $('#customerName').val('');
                        $('#customerName').prop('disabled', false);
                    }
                });
            }
        });

        // Đăng ký bàn
        $('#registerTableForm').on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                url: '{{ route("admin.table-order.registerTable") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    table_id: $('#selectedTableId').val(),
                    phone: $('#customerPhone').val(),
                    customer_name: $('#customerName').val()
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('#registerTableModal').modal('hide');
                        location.reload(); // Reload để cập nhật trạng thái bàn
                    }
                },
                error: function(xhr) {
                    const error = xhr.responseJSON?.message || 'Có lỗi xảy ra';
                    toastr.error(error);
                }
            });
        });

        // Load order của bàn
        function loadTableOrder(tableId) {
            $.ajax({
                url: '{{ route("admin.table-order.getTableOrder") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    table_id: tableId
                },
                success: function(response) {
                    if (response.success) {
                        currentOrderId = response.order.id;
                        $('#currentOrderId').val(response.order.id);
                        displayOrderItems(response.order_details);
                        loadProducts();
                    }
                },
                error: function() {
                    toastr.error('Không thể tải thông tin order');
                }
            });
        }

        // Hiển thị danh sách món đã order
        function displayOrderItems(orderDetails) {
            let html = '';
            if (orderDetails && orderDetails.length > 0) {
                let total = 0;
                orderDetails.forEach(function(item) {
                    const itemTotal = item.amount || (item.price * item.qty);
                    total += itemTotal;
                    html += `
                        <div class="order-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>${item.product?.name || 'N/A'}</strong>
                                    <br>
                                    <small>${item.qty} x ${formatMoney(item.price)}</small>
                                </div>
                                <div class="text-right">
                                    <div class="font-weight-bold">${formatMoney(itemTotal)}</div>
                                    <div class="btn-group btn-group-sm mt-1">
                                        <button class="btn btn-sm btn-secondary" onclick="updateQty(${item.id}, ${item.qty - 1})">-</button>
                                        <span class="btn btn-sm">${item.qty}</span>
                                        <button class="btn btn-sm btn-secondary" onclick="updateQty(${item.id}, ${item.qty + 1})">+</button>
                                        <button class="btn btn-sm btn-danger" onclick="removeItem(${item.id})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
                html += `<div class="mt-3 pt-3 border-top">
                    <div class="d-flex justify-content-between">
                        <strong>Tổng tiền:</strong>
                        <strong class="text-danger">${formatMoney(total)}</strong>
                    </div>
                </div>`;
            } else {
                html = '<p class="text-muted">Chưa có món nào</p>';
            }
            $('#orderItemsContent').html(html);
        }

        // Load danh sách sản phẩm
        function loadProducts() {
            const categoryId = $('#categoryFilter').val();
            
            $.ajax({
                url: '{{ route("admin.table-order.getProducts") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    category_id: categoryId
                },
                success: function(response) {
                    if (response.success) {
                        let html = '';
                        response.products.forEach(function(product) {
                            html += `
                                <div class="col-md-4 mb-3">
                                    <div class="product-card" onclick="addProductToOrder(${product.id})">
                                        <div class="text-center">
                                            <strong>${product.name}</strong>
                                            <br>
                                            <span class="text-danger">${formatMoney(product.price_sale || 0)}</span>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                        $('#productsList').html(html);
                    }
                },
                error: function() {
                    toastr.error('Không thể tải danh sách sản phẩm');
                }
            });
        }

        // Thêm món vào order
        function addProductToOrder(productId) {
            if (!currentOrderId) {
                toastr.error('Vui lòng đăng ký bàn trước');
                return;
            }

            $.ajax({
                url: '{{ route("admin.table-order.addProductToOrder") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    order_id: currentOrderId,
                    product_id: productId,
                    qty: 1
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        loadTableOrder(currentTableId);
                    }
                },
                error: function(xhr) {
                    const error = xhr.responseJSON?.message || 'Có lỗi xảy ra';
                    toastr.error(error);
                }
            });
        }

        // Cập nhật số lượng
        function updateQty(orderDetailId, newQty) {
            if (newQty < 1) {
                removeItem(orderDetailId);
                return;
            }

            $.ajax({
                url: '{{ route("admin.table-order.updateProductQty") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    order_detail_id: orderDetailId,
                    qty: newQty
                },
                success: function(response) {
                    if (response.success) {
                        loadTableOrder(currentTableId);
                    }
                },
                error: function() {
                    toastr.error('Có lỗi xảy ra');
                }
            });
        }

        // Xóa món
        function removeItem(orderDetailId) {
            if (!confirm('Bạn có chắc muốn xóa món này?')) {
                return;
            }

            $.ajax({
                url: '{{ route("admin.table-order.removeProductFromOrder") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    order_detail_id: orderDetailId
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        loadTableOrder(currentTableId);
                    }
                },
                error: function() {
                    toastr.error('Có lỗi xảy ra');
                }
            });
        }

        // Lọc theo danh mục
        $('#categoryFilter').on('change', function() {
            loadProducts();
        });

        // Format tiền
        function formatMoney(amount) {
            return new Intl.NumberFormat('vi-VN', {
                style: 'currency',
                currency: 'VND'
            }).format(amount);
        }

        // Khi modal order đóng, reload trang để cập nhật trạng thái bàn
        $('#orderModal').on('hidden.bs.modal', function() {
            location.reload();
        });
    </script>
</x-layout-backend>

