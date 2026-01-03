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
                                                 data-floor-name="{{ $floor->name }}"
                                                 style="cursor: pointer;"
                                                 onclick="handleTableClick({{ $table->id }}, '{{ $table->name }}', {{ $isOccupied ? 'true' : 'false' }}, '{{ $floor->name }}')">
                                                <div class="p-3 border rounded text-white" 
                                                     style="background-color: {{ $isOccupied ? '#ffc107' : '#343a40' }}; min-height: 120px; display: flex; flex-direction: column; justify-content: flex-start;">
                                                    <div class="font-weight-bold">{{ $table->name }}</div>
                                                    <hr style="border-color: rgba(255,255,255,0.3); margin: 8px 0;">
                                                    <div>
                                                        @if($isOccupied)
                                                            <div>Trạng thái: Có khách</div>
                                                            @if(isset($order) && $order->created_at)
                                                                <small>Bắt đầu: {{ $order->created_at->format('H:i d/m/Y') }}</small>
                                                            @endif
                                                        @else
                                                            <div>Trạng thái: Trống</div>
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

    <!-- Modal Order món -->
    <div class="modal fade" id="orderModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><span id="orderModalTableName"></span></h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="orderTableId" name="table_id">
                    <input type="hidden" id="currentOrderId" name="order_id">
                    
                    <!-- Thông tin khách hàng -->
                    <div class="mb-3 p-2 bg-light rounded">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Khách hàng:</strong> <span id="orderCustomerName">---</span>
                            </div>
                            <div class="col-md-6">
                                <strong>SĐT:</strong> <span id="orderCustomerPhone">---</span>
                            </div>
                        </div>
                    </div>

                    <!-- Danh sách món đã order -->
                    <div id="orderItemsList" class="mb-3">
                        <div id="orderItemsContent"></div>
                    </div>

                    <!-- Danh sách món để chọn -->
                    <div class="mb-3">
                        <label>Chọn danh mục:</label>
                        <div class="category-menu" id="categoryMenu">
                            <div class="category-item active" data-id="">Tất cả</div>
                            @php
                                $categories = \App\Models\Category::whereNull('deleted_at')->orderBy('name')->get();
                            @endphp
                            @foreach($categories as $category)
                                <div class="category-item" data-id="{{ $category->id }}">{{ $category->name }}</div>
                            @endforeach
                        </div>
                        <input type="hidden" id="categoryFilter" value="">
                    </div>

                    <div id="productsList" class="row" style="max-height: 400px; overflow-y: auto;">
                        <!-- Products will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="saveOrder()">Lưu lại</button>
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
            margin-right: 10px;
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
    </style>

    <script>
        let currentTableId = null;
        let currentOrderId = null;
        let currentOrderDetails = [];
        let originalOrderDetails = [];
        let allProducts = [];
        let isOrderSaved = true;
        let shouldReload = false;

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

            // Lọc theo danh mục (backup event)
            $('#categoryFilter').on('change', function() {
                loadProducts();
            });

            // Intercept modal close
            $('#orderModal').on('hide.bs.modal', function(e) {
                if (!isOrderSaved) {
                    if (!confirm('Bạn có thay đổi chưa lưu. Nếu đóng, mọi thay đổi sẽ bị mất. Bạn có chắc chắn muốn đóng không?')) {
                        e.preventDefault();
                        return;
                    }
                }
            });
        });

        // Xử lý click vào bàn
        function handleTableClick(tableId, tableName, isOccupied, floorName) {
            currentTableId = tableId;
            
            if (isOccupied) {
                shouldReload = false;
                $('#orderModalTableName').text((floorName ? (floorName + ' - ') : '') + tableName);
                $('#orderTableId').val(tableId);
                
                // Reset UI before loading
                $('#orderCustomerName').text('---');
                $('#orderCustomerPhone').text('---');
                $('#orderItemsContent').html('<p class="text-muted">Đang tải...</p>');

                loadTableOrder(tableId);
                
                $('#orderModal').modal('show');
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

        // Load order của bàn
        function loadTableOrder(tableId) {
            $.ajax({
                url: '{{ route("admin.table-order.getOrderDetails", ":id") }}'.replace(':id', tableId),
                type: 'GET',
                success: function(response) {
                    if (response.success && response.order) {
                        currentOrderId = response.order.id;
                        $('#currentOrderId').val(response.order.id);
                        
                        if (response.order.user) {
                            $('#orderCustomerName').text(response.order.user.fullname);
                            $('#orderCustomerPhone').text(response.order.user.phone || '---');
                        } else {
                            $('#orderCustomerName').text(response.order.customer_name || 'Khách lẻ');
                            $('#orderCustomerPhone').text(response.order.customer_phone || '---');
                        }

                        // Initialize local state
                        // Cần chuẩn hóa dữ liệu để thống nhất cấu trúc
                        currentOrderDetails = response.order_details.map(item => ({
                            product_id: item.product_id,
                            qty: item.qty,
                            price: item.price,
                            product_name: item.product ? item.product.name : 'N/A'
                        }));
                        
                        originalOrderDetails = JSON.parse(JSON.stringify(currentOrderDetails));
                        isOrderSaved = true;

                        displayOrderItems();
                        loadProducts();
                    }
                },
                error: function() {
                    toastr.error('Không thể tải thông tin order');
                }
            });
        }

        // Hiển thị danh sách món đã order (từ local state)
        function displayOrderItems() {
            let html = '';
            let total = 0;

            if (currentOrderDetails && currentOrderDetails.length > 0) {
                currentOrderDetails.forEach(function(item) {
                    const itemTotal = item.price * item.qty;
                    total += itemTotal;
                    html += `
                        <div class="order-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>${item.product_name}</strong>
                                    <br>
                                    <small>${item.qty} x ${formatMoney(item.price)}</small>
                                </div>
                                <div class="text-right">
                                    <div class="font-weight-bold">${formatMoney(itemTotal)}</div>
                                    <div class="btn-group btn-group-sm mt-1">
                                        <button class="btn btn-sm btn-secondary" onclick="updateQty(${item.product_id}, ${item.qty - 1})">-</button>
                                        <span class="btn btn-sm">${item.qty}</span>
                                        <button class="btn btn-sm btn-secondary" onclick="updateQty(${item.product_id}, ${item.qty + 1})">+</button>
                                        <button class="btn btn-sm btn-danger" onclick="removeItem(${item.product_id})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
                html += `<div class="mt-3 pt-3">
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

        // Load danh sách sản phẩm và lưu vào allProducts
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
                        // Update allProducts cache (merge or replace? For simplicity, we just use what's loaded currently for adding)
                        // Note: If we change category, allProducts might miss products from other categories. 
                        // But we only add products visible on screen.
                        // Better: Store loaded products in a map.
                        
                        response.products.forEach(function(product) {
                            // Update global cache
                            const existingIndex = allProducts.findIndex(p => p.id === product.id);
                            if (existingIndex >= 0) {
                                allProducts[existingIndex] = product;
                            } else {
                                allProducts.push(product);
                            }

                            let imagePath = product.image ? '{{ asset("/images/product") }}/' + product.image : '{{ asset("/images/default-product.png") }}';
                            html += `
                                <div class="col-md-4 mb-3">
                                    <div class="product-card" onclick="addProductToOrder(${product.id})">
                                        <img src="${imagePath}" class="product-img" alt="${product.name}" onerror="this.src='{{ asset("/images/default-product.png") }}'">
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

        // Thêm món vào order (Local)
        function addProductToOrder(productId) {
            if (!currentOrderId) {
                toastr.error('Vui lòng đăng ký bàn trước');
                return;
            }

            const product = allProducts.find(p => p.id === productId);
            if (!product) {
                toastr.error('Không tìm thấy thông tin sản phẩm');
                return;
            }

            const existingItem = currentOrderDetails.find(item => item.product_id === productId);
            if (existingItem) {
                existingItem.qty += 1;
            } else {
                currentOrderDetails.push({
                    product_id: product.id,
                    qty: 1,
                    price: product.price_sale || 0,
                    product_name: product.name
                });
            }

            isOrderSaved = false;
            displayOrderItems();
        }

        // Cập nhật số lượng (Local)
        function updateQty(productId, newQty) {
            if (newQty < 1) {
                removeItem(productId);
                return;
            }

            const item = currentOrderDetails.find(item => item.product_id === productId);
            if (item) {
                item.qty = newQty;
                isOrderSaved = false;
                displayOrderItems();
            }
        }

        // Xóa món (Local)
        function removeItem(productId) {
            if (!confirm('Bạn có chắc muốn xóa món này khỏi danh sách?')) {
                return;
            }

            currentOrderDetails = currentOrderDetails.filter(item => item.product_id !== productId);
            isOrderSaved = false;
            displayOrderItems();
        }

        // Lưu order lên server
        function saveOrder() {
            if (!currentOrderId) return;

            $.ajax({
                url: '{{ route("admin.table-order.saveOrder") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    order_id: currentOrderId,
                    items: currentOrderDetails
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        // Update original state to match current
                        originalOrderDetails = JSON.parse(JSON.stringify(currentOrderDetails));
                        isOrderSaved = true;
                        shouldReload = true;
                    } else {
                        toastr.error(response.message || 'Lưu thất bại');
                    }
                },
                error: function(xhr) {
                    let msg = 'Có lỗi xảy ra';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    toastr.error(msg);
                }
            });
        }

        // Format tiền
        function formatMoney(amount) {
            return new Intl.NumberFormat('vi-VN', {
                style: 'currency',
                currency: 'VND'
            }).format(amount);
        }
    </script>
</x-layout-backend>
