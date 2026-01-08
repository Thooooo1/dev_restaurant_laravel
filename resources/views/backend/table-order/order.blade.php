<x-layout-backend>
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ $table->floor->name ?? '' }} - {{ $table->name }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.table-order.index') }}">Bàn</a></li>
                        <li class="breadcrumb-item active">Đặt món</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                <div class="mb-2 mb-md-0">
                    <a href="{{ route('admin.table-order.index') }}" class="btn btn-secondary btn-sm mb-1" id="backBtn"><i class="fas fa-arrow-left"></i> Quay lại</a>
                    <button type="button" class="btn btn-primary btn-sm ml-2 mb-1" id="printKitchenBtn" onclick="printKitchen()" disabled><i class="fas fa-print"></i> In thực đơn</button>
                    <a href="{{ route('admin.table-order.payment', $table->id) }}" class="btn btn-danger btn-sm ml-2 mb-1 {{ $hasAnyItems ? '' : 'disabled' }}" id="payBtn"><i class="fas fa-money-bill-wave"></i> Thanh toán</a>
                </div>
                <div>
                    <span class="font-weight-bold">Tổng tiền:</span>
                    <span class="text-danger font-weight-bold" id="totalAmountDisplay">0 ₫</span>
                </div>
            </div>
            <div class="row">
                <!-- Left Column: Order Info & Items -->
                <div class="col-md-5">
                    <div class="card h-100">
                        <div class="card-header bg-secondary text-white">
                            <h3 class="card-title">{{ $order->user->fullname ?? $order->customer_name ?? 'Khách lẻ' }} - {{ $order->user->phone ?? $order->customer_phone ?? '---' }}</h3>
                        </div>
                        <div class="p-2 d-flex flex-column">
                            <!-- Note Input -->
                            <div class="form-group mb-2">
                                <label for="orderNote" class="sr-only">Ghi chú</label>
                                <textarea id="orderNote" class="form-control" rows="2" placeholder="Ghi chú món ăn (VD: Không cay, ít đường...)" style="resize: none;">{{ $order->note }}</textarea>
                            </div>
                            @if(isset($historyByTurn) && $historyByTurn->count() > 0)
                                <div class="" id="historyAccordion">
                                    @foreach($historyByTurn as $turn => $items)
                                        <div class="card">
                                            <div class="card-header p-2 bg-warning ">
                                                <button class="btn w-100 btn-link p-0 text-white" data-toggle="collapse" data-target="#history-turn-{{ $turn }}">
                                                        Gọi món lần: {{ $turn }}
                                                </button>
                                            </div>
                                            <div id="history-turn-{{ $turn }}" class="collapse">
                                                <div class="card-body p-2">
                                                    @foreach($items as $it)
                                                        @php
                                                            $price = $it->price ?? ($it->product->price_sale ?? 0);
                                                            $amount = $price * ($it->qty ?? 0);
                                                        @endphp
                                                        <div class="order-item bg-light" id="history-item-{{ $it->id }}" data-price="{{ $price }}">
                                                            <div class="row align-items-center">
                                                                <div class="col-6">
                                                                    <div class="font-weight-bold">{{ $it->product->name ?? 'N/A' }}</div>
                                                                    <div class="text-muted small">{{ number_format($price, 0, ',', '.') }} ₫ x {{ $it->qty }}</div>
                                                                </div>
                                                                <div class="col-6 text-right">
                                                                    <div class="font-weight-bold" id="history-amount-{{ $it->id }}">{{ number_format($amount, 0, ',', '.') }} ₫</div>
                                                                    <div class="d-flex justify-content-end align-items-end h-100">
                                                                        <div class="btn-group btn-group-sm">
                                                                            <button class="btn btn-secondary" onclick="decrementHistory({{ $it->id }})">-</button>
                                                                            <span class="btn btn-light border" id="history-qty-{{ $it->id }}" style="min-width: 30px;">{{ $it->qty }}</span>
                                                                            <button class="btn btn-secondary" disabled>+</button>
                                                                        </div>
                                                                        <button class="btn btn-link text-danger p-0 ml-2" onclick="deleteHistory({{ $it->id }})"><i class="fas fa-trash fa-lg"></i></button>
                                                                    </div>
                                                                </div>
                                                                
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            <!-- Order Items -->
                            <div class="flex-grow-1" style="max-height: calc(100vh - 300px); overflow-y: auto; overflow-x: hidden;">
                                <div id="orderItemsContent">
                                    <!-- Items will be rendered here via JS -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Menu -->
                <div class="col-md-7 order-menu-container">
                    <div class="card h-100">
                        <div class="card-body">
                             <!-- Categories -->
                            <div class="mb-3 product-category-container">
                                <div class="category-menu" id="categoryMenu">
                                    @foreach($categories as $category)
                                        <div class="category-item {{ $loop->first ? 'active' : '' }}" data-id="{{ $category->id }}">{{ $category->name }}</div>
                                    @endforeach
                                </div>
                                <input type="hidden" id="categoryFilter" value="{{ $categories->first()->id ?? '' }}">
                            </div>

                            <!-- Products Grid -->
                            <div id="productsList" class="row overflow-auto" style="height: 600px;">
                                <!-- Products will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
        @media (max-width: 768px) {
            .order-menu-container {
                margin-top: 10px;
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
                margin-right: 5px;
            }
        }
        @media (min-width: 992px) {
            .category-item {
                display: inline-block;
                padding: 4px 10px;
                border: 1px solid #ddd;
                border-radius: 20px;
                cursor: pointer;
                transition: all 0.2s;
                margin-bottom: 2px;
            }
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
            height: 100%;
        }
        .product-card:hover {
            background-color: #f8f9fa;
            border-color: #007bff;
        }
        .category-item:hover {
            background-color: #e9ecef;
        }
        .category-item.active {
            background-color: #6c757d;
            color: white;
            border-color: #6c757d;
        }
        .product-img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 5px 5px 0 0;
            margin-bottom: 10px;
        }
        .product-category-container {
            border-bottom: 1px solid #eee;
        }
    </style>

    <script>
        let currentTableId = {{ $table->id }};
        let currentOrderId = {{ $order->id }};
        let orderTurn = {{ $order->order_turn ?? 1 }};
        let currentOrderDetails = [];
        let existingOrderDetails = @json($orderDetails); // Load existing items
        let originalOrderDetails = [];
        let allProducts = [];
        let isOrderSaved = true;
        let hasExistingItems = {{ $hasAnyItems ? 'true' : 'false' }};

        $(document).ready(function() {
            // Initial render
            displayOrderItems();
            loadProducts();

            // Category click
            $(document).on('click', '.category-item', function() {
                $('.category-item').removeClass('active');
                $(this).addClass('active');
                const categoryId = $(this).data('id');
                $('#categoryFilter').val(categoryId);
                loadProducts();
            });

            // Monitor Note change
            $('#orderNote').on('input', function() {
                isOrderSaved = false;
            });

            // Warn on leave if unsaved
            window.onbeforeunload = function() {
                if (!isOrderSaved && currentOrderDetails.length > 0) {
                    return "Bạn có thay đổi chưa lưu. Bạn có chắc chắn muốn rời đi?";
                }
            };
        });

        // Load products
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
                            // Update cache
                            const existingIndex = allProducts.findIndex(p => p.id === product.id);
                            if (existingIndex >= 0) {
                                allProducts[existingIndex] = product;
                            } else {
                                allProducts.push(product);
                            }

                            let imagePath = product.image ? '{{ asset("/images/product") }}/' + product.image : '{{ asset("/images/default-product.png") }}';
                            html += `
                                <div class="col-md-3 col-6 mb-3">
                                    <div class="product-card" onclick="addProductToOrder(${product.id})">
                                        <img src="${imagePath}" class="product-img" alt="${product.name}" onerror="this.src='{{ asset("/images/default-product.png") }}'">
                                        <div class="text-center">
                                            <div class="text-danger font-weight-bold mt-1">${formatMoney(product.price_sale || 0)}</div>
                                            <div style="font-weight: bold; overflow: hidden;">${product.name}</div>
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

        // Display Order Items
        function displayOrderItems() {
            let html = '';
            let total = 0;

            // 1. Render Existing Items (Read-only)
            if (existingOrderDetails && existingOrderDetails.length > 0) {
                html += '<div class="text-muted small mb-2 font-weight-bold text-uppercase border-bottom pb-1">Đã gọi (Lưu bếp)</div>';
                existingOrderDetails.forEach(function(item) {
                    // DB items have 'product' relation, or we fallback
                    const productName = item.product ? item.product.name : (item.product_name || 'N/A');
                    const price = item.price || 0;
                    const qty = item.qty || 0;
                    const itemTotal = price * qty;
                    total += itemTotal;

                    html += `
                        <div class="order-item bg-light text-muted">
                            <div class="row align-items-center">
                                <div class="col-6">
                                    <div class="font-weight-bold">${productName}</div>
                                    <div class="text-muted small">${formatMoney(price)} x ${qty}</div>
                                </div>
                                <div class="col-3 text-center">
                                    <span class="font-weight-bold">x${qty}</span>
                                </div>
                                <div class="col-3 text-right">
                                    <div class="font-weight-bold">${formatMoney(itemTotal)}</div>
                                </div>
                            </div>
                        </div>
                    `;
                });
            }

            // 2. Render Current Items (Editable)
            if (currentOrderDetails && currentOrderDetails.length > 0) {
                if (existingOrderDetails && existingOrderDetails.length > 0) {
                     html += '<div class="text-primary small mb-2 mt-3 font-weight-bold text-uppercase border-bottom pb-1">Đang chọn (Chưa lưu)</div>';
                }
                
                currentOrderDetails.forEach(function(item) {
                    const itemTotal = item.price * item.qty;
                    total += itemTotal;
                    html += `
                        <div class="order-item">
                            <div class="row align-items-center">
                                <div class="col-6">
                                    <div class="font-weight-bold">${item.product_name}</div>
                                    <div class="text-muted small">${formatMoney(item.price)} x ${item.qty}</div>
                                </div>
                                <div class="col-6 text-right">
                                    <div class="font-weight-bold">${formatMoney(itemTotal)}</div>
                                    <div class="d-flex justify-content-end align-items-end h-100">
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-secondary" onclick="updateQty(${item.product_id}, ${item.qty - 1})">-</button>
                                            <span class="btn btn-light border" style="min-width: 30px;">${item.qty}</span>
                                            <button class="btn btn-secondary" onclick="updateQty(${item.product_id}, ${item.qty + 1})">+</button>
                                        </div>
                                        <button class="btn btn-link text-danger p-0 ml-2" onclick="removeItem(${item.product_id})"><i class="fas fa-trash fa-lg"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
            } 
            
            $('#orderItemsContent').html(html);
            $('#totalAmountDisplay').text(formatMoney(total));
            
            // Disable Print Kitchen if no NEW items
            $('#printKitchenBtn').prop('disabled', !(currentOrderDetails && currentOrderDetails.length > 0));

            // Update Payment Button State: ENABLED only if hasExistingItems is true
            if (hasExistingItems) {
                $('#payBtn').removeClass('disabled');
            } else {
                $('#payBtn').addClass('disabled');
            }
        }

        function addProductToOrder(productId) {
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

        function removeItem(productId) {
            if (!confirm('Bạn có chắc muốn xóa món này?')) {
                return;
            }

            currentOrderDetails = currentOrderDetails.filter(item => item.product_id !== productId);
            isOrderSaved = false;
            displayOrderItems();
        }

        function decrementHistory(detailId) {
            const qtyEl = document.getElementById('history-qty-' + detailId);
            if (!qtyEl) return;
            const qty = parseInt(qtyEl.textContent, 10) || 0;
            if (qty <= 0) return;
            if (!confirm('Bạn có chắc muốn trừ 1 món này?')) return;
            const newQty = qty - 1;
            if (newQty < 1) {
                deleteHistory(detailId);
                return;
            }
            $.ajax({
                url: '{{ route("admin.table-order.updateProductQty") }}',
                method: 'POST',
                data: { _token: '{{ csrf_token() }}', order_detail_id: detailId, qty: newQty },
                success: function(resp) {
                    if (resp.success) {
                        toastr.success('Đã cập nhật số lượng');
                        const amountEl = document.getElementById('history-amount-' + detailId);
                        const itemEl = document.getElementById('history-item-' + detailId);
                        qtyEl.textContent = String(newQty);
                        const price = parseFloat(itemEl?.getAttribute('data-price') || '0') || 0;
                        const newAmount = price * newQty;
                        if (amountEl) amountEl.textContent = formatMoney(newAmount);
                    } else {
                        toastr.error(resp.message || 'Cập nhật thất bại');
                    }
                },
                error: function(xhr) {
                    let msg = 'Có lỗi xảy ra';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    toastr.error(msg);
                }
            });
        }

        function deleteHistory(detailId) {
            if (!confirm('Bạn có chắc muốn xoá món này?')) return;
            $.ajax({
                url: '{{ route("admin.table-order.removeProductFromOrder") }}',
                method: 'POST',
                data: { _token: '{{ csrf_token() }}', order_detail_id: detailId },
                success: function(resp) {
                    if (resp.success) {
                        toastr.success('Đã xoá món');
                        const itemEl = document.getElementById('history-item-' + detailId);
                        if (itemEl) itemEl.remove();
                    } else {
                        toastr.error(resp.message || 'Xoá thất bại');
                    }
                },
                error: function(xhr) {
                    let msg = 'Có lỗi xảy ra';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    toastr.error(msg);
                }
            });
        }

        function printKitchen() {
            if (!currentOrderId || !currentOrderDetails || currentOrderDetails.length === 0) return;
            const note = $('#orderNote').val();
            saveOrder(function() {
                const items = currentOrderDetails.map((item, idx) => ({stt: idx+1, name: item.product_name, qty: item.qty}));
                const html = generateKitchenTicketHTML({
                    floor: '{{ $table->floor->name ?? '' }}',
                    table: '{{ $table->name }}',
                    customer_name: '{{ $order->user->fullname ?? $order->customer_name ?? 'Khách lẻ' }}',
                    customer_phone: '{{ $order->user->phone ?? $order->customer_phone ?? '---' }}',
                    order_turn: orderTurn,
                    note: note,
                    items
                });
                const w = window.open('', 'PRINT', 'width=400,height=600');
                w.document.write(html);
                w.document.close();
                w.focus();
                w.print();
                w.close();
                $.post('{{ route('admin.table-order.incrementOrderTurn') }}', { _token: '{{ csrf_token() }}', order_id: currentOrderId })
                    .always(function() { 
                        // Reload to sync state
                        window.location.reload(); 
                    });
            });
        }

        function saveOrder(callback) {
            if (!currentOrderId) return;
            const note = $('#orderNote').val();

            $.ajax({
                url: '{{ route("admin.table-order.saveOrder") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    order_id: currentOrderId,
                    items: currentOrderDetails,
                    note: note
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        
                        // Optimistically update state
                        isOrderSaved = true;
                        hasExistingItems = true;
                        $('#payBtn').removeClass('disabled');

                        if (typeof callback === 'function') callback();
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

        // Format Money
        function formatMoney(amount) {
            return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
        }

        function generateKitchenTicketHTML(data) {
            const { floor, table, customer_name, customer_phone, order_turn, note, items } = data;
            const now = new Date();
            const pad = n => String(n).padStart(2, '0');
            const dateStr = `${pad(now.getDate())}/${pad(now.getMonth()+1)}/${now.getFullYear()} ${pad(now.getHours())}:${pad(now.getMinutes())}`;
            let rows = '';
            items.forEach(i => {
                rows += `<tr><td style="text-align:center;width:30px;">${i.stt}</td><td>${i.name}</td><td style="text-align:right;width:50px;">${i.qty}</td></tr>`;
            });
            let noteHtml = note ? `<div class="meta" style="border:1px dashed #000; padding:5px; margin:5px 0;">Ghi chú: <strong>${note}</strong></div>` : '';
            return `<!doctype html><html><head><meta charset="utf-8"><title>Phiếu gọi món</title><style>
                @media print { @page { size: 80mm auto; margin: 5mm; } }
                body { font-family: Arial, sans-serif; width: 80mm; }
                .title { text-align:center; font-size:16px; font-weight:bold; margin-bottom:6px; }
                .meta { font-size:12px; margin-bottom:6px; }
                table { width:100%; border-collapse: collapse; font-size:12px; }
                th, td { padding:4px 0; border-bottom:1px dashed #000; }
                th { text-align:left; border-top:1px dashed #000; }
                .footer { margin-top:8px; font-size:11px; text-align:center; }
            </style></head><body>
                <div class="title">PHIẾU GỌI MÓN</div>
                <div class="meta">Tầng: <strong>${floor}</strong> | Bàn: <strong>${table}</strong></div>
                <div class="meta">Lần order: <strong>${order_turn}</strong> | Thời gian: ${dateStr}</div>
                <div class="meta">Khách: <strong>${customer_name}</strong> | ĐT: ${customer_phone}</div>
                ${noteHtml}
                <table><thead><tr><th style="width:30px; text-align:center;">STT</th><th>Món</th><th style="width:50px; text-align:right;">SL</th></tr></thead><tbody>${rows}</tbody></table>
                <div class="footer">In từ hệ thống nhà hàng</div>
            </body></html>`;
        }
    </script>
</x-layout-backend>
