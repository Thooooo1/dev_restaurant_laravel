<x-layout-backend>
    <section class="content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <div>
                <h1>{{ $table->floor->name ?? '' }} - {{ $table->name }} | Thanh toán</h1>
            </div>
            <div>
                <a href="{{ route('admin.table-order.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Quay lại</a>
                <button class="btn btn-warning btn-sm ml-2" id="btnEditMode" onclick="toggleEditMode()"><i class="fas fa-edit"></i> Sửa</button>
                <button class="btn btn-success btn-sm ml-2" data-toggle="modal" data-target="#modalAddProduct"><i class="fas fa-plus"></i> Thêm sản phẩm</button>
                <button class="btn btn-primary btn-sm ml-2" onclick="handlePrintInvoice()"><i class="fas fa-print"></i> In hoá đơn</button>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <strong>Khách:</strong> {{ $order->user->fullname ?? $order->name ?? 'Khách lẻ' }}
                    <span class="ml-3"><strong>ĐT:</strong> {{ $order->user->phone ?? $order->phone ?? '---' }}</span>
                </div>
                <div class="card-body">
                    <table class="table table-sm" id="paymentTable">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Tên món</th>
                                <th class="text-right">SL</th>
                                <th class="text-right">Đơn giá</th>
                                <th class="text-right">Thành tiền</th>
                                <th class="text-center action-col">#</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orderDetails as $i => $item)
                                <tr data-id="{{ $item->id }}" data-price="{{ $item->price }}" data-name="{{ $item->product->name ?? 'N/A' }}">
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $item->product->name ?? 'N/A' }}</td>
                                    <td class="text-right">
                                        <span class="qty-display">{{ $item->qty }}</span>
                                        <input type="number" class="form-control form-control-sm qty-input d-none" style="width: 70px; display: inline-block; text-align: right;" value="{{ $item->qty }}" min="1" oninput="updateRowTotal(this)">
                                    </td>
                                    <td class="text-right">{{ number_format($item->price, 0, ',', '.') }} ₫</td>
                                    <td class="text-right amount-display">{{ number_format($item->amount, 0, ',', '.') }} ₫</td>
                                    <td class="text-center action-col">
                                        <button class="btn btn-danger btn-sm btn-delete-item" disabled onclick="confirmDeleteItem({{ $item->id }})"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4" class="text-right">Tổng cộng</th>
                                <th class="text-right text-danger" id="total-amount-display">{{ number_format($totalAmount, 0, ',', '.') }} ₫</th>
                                <th class="action-col"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="card-footer text-right">
                    <a href="{{ route('admin.table-order.index') }}" class="btn btn-secondary">Quay lại</a>
                </div>
            </div>
        </div>
    </section>

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

    <script>
        let isEditMode = false;

        function toggleEditMode() {
            isEditMode = !isEditMode;
            const deleteBtns = document.querySelectorAll('.btn-delete-item');
            const qtyDisplays = document.querySelectorAll('.qty-display');
            const qtyInputs = document.querySelectorAll('.qty-input');
            const btn = document.getElementById('btnEditMode');

            if (isEditMode) {
                deleteBtns.forEach(el => el.removeAttribute('disabled'));
                qtyDisplays.forEach(el => el.classList.add('d-none'));
                qtyInputs.forEach(el => el.classList.remove('d-none'));
                btn.innerHTML = '<i class="fas fa-check"></i> Xong';
                btn.classList.remove('btn-warning');
                btn.classList.add('btn-info');
            } else {
                deleteBtns.forEach(el => el.setAttribute('disabled', 'disabled'));
                qtyDisplays.forEach(el => el.classList.remove('d-none'));
                qtyInputs.forEach(el => el.classList.add('d-none'));
                btn.innerHTML = '<i class="fas fa-edit"></i> Sửa';
                btn.classList.remove('btn-info');
                btn.classList.add('btn-warning');
            }
        }

        function handlePrintInvoice() {
            // Collect all items
            const inputs = document.querySelectorAll('.qty-input');
            const items = [];
            let isValid = true;

            inputs.forEach(input => {
                const qty = parseInt(input.value);
                const tr = input.closest('tr');
                const id = tr.dataset.id;
                
                if (qty < 1 || isNaN(qty)) {
                    toastr.error('Số lượng món không hợp lệ');
                    isValid = false;
                    return;
                }

                items.push({
                    id: id,
                    qty: qty
                });
            });

            if (!isValid) return;

            // Save order first
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
                        printInvoice();
                        // Optional: Reload page after print to reflect saved data in case print is cancelled but data saved
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        toastr.error(res.message);
                    }
                },
                error: function(err) {
                    toastr.error('Lỗi cập nhật đơn hàng');
                }
            });
        }

        function updateItemQty(id, el) {
            // Function no longer used for direct AJAX, but kept if needed for simple validation
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
                    },
                    error: function(err) {
                        alert('Lỗi xóa món');
                    }
                });
            }
        }

        function loadProducts(categoryId) {
            const productSelect = $('#productSelect');
            
            if (!categoryId) {
                productSelect.html('<option value="">-- Chọn sản phẩm --</option>');
                return;
            }

            // Show loading state
            productSelect.html('<option value="">Đang tải...</option>');
            productSelect.prop('disabled', true);

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
                            html += `<option value="${p.id}">${p.name} - ${new Intl.NumberFormat('vi-VN').format(p.price_sale)} đ</option>`;
                        });
                        productSelect.html(html);
                    } else {
                        productSelect.html('<option value="">Không có sản phẩm</option>');
                    }
                },
                error: function() {
                    productSelect.html('<option value="">Lỗi tải dữ liệu</option>');
                },
                complete: function() {
                    productSelect.prop('disabled', false);
                }
            });
        }

        function addProductToOrder() {
            const btn = $('#btnAddProduct');
            const productId = $('#productSelect').val();
            const qty = $('#productQty').val();
            
            if (!productId) {
                toastr.warning('Vui lòng chọn sản phẩm');
                return;
            }
            if (qty < 1) {
                toastr.warning('Số lượng phải lớn hơn 0');
                return;
            }

            // Disable button
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Đang thêm...');

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
                        toastr.success('Thêm món thành công');
                        setTimeout(() => {
                            location.reload();
                        }, 500);
                    } else {
                        toastr.error(res.message);
                        btn.prop('disabled', false).text('Thêm');
                    }
                },
                error: function(err) {
                    toastr.error('Lỗi thêm món');
                    btn.prop('disabled', false).text('Thêm');
                }
            });
        }

        function updateRowTotal(el) {
            const tr = el.closest('tr');
            const price = parseFloat(tr.dataset.price);
            const qty = parseInt(el.value);
            
            if (qty < 1 || isNaN(qty)) return;

            const amount = price * qty;
            tr.querySelector('.amount-display').textContent = formatMoney(amount);
            
            updateOrderTotal();
        }

        function updateOrderTotal() {
            let total = 0;
            document.querySelectorAll('#paymentTable tbody tr').forEach(tr => {
                const price = parseFloat(tr.dataset.price);
                const input = tr.querySelector('.qty-input');
                const qty = parseInt(input.value);
                
                if (qty >= 1 && !isNaN(qty)) {
                    total += price * qty;
                }
            });
            document.getElementById('total-amount-display').textContent = formatMoney(total);
        }

        function formatMoney(amount) {
            return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
        }

        function printInvoice() {
            const items = [];
            document.querySelectorAll('#paymentTable tbody tr').forEach((tr, index) => {
                const name = tr.dataset.name;
                const price = parseFloat(tr.dataset.price);
                const input = tr.querySelector('.qty-input');
                const qty = parseInt(input.value);
                
                if (qty >= 1 && !isNaN(qty)) {
                    items.push({
                        stt: index + 1,
                        name: name,
                        qty: qty,
                        price: price
                    });
                }
            });

            let rows = '';
            let totalAmount = 0;
            items.forEach(i => {
                const amount = i.price * i.qty;
                totalAmount += amount;
                rows += `<tr>
                    <td style="text-align:center;width:30px;">${i.stt}</td>
                    <td>${i.name}</td>
                    <td style="text-align:right;width:50px;">${i.qty}</td>
                    <td style="text-align:right;width:70px;">${formatMoney(i.price)}</td>
                    <td style="text-align:right;width:90px;">${formatMoney(amount)}</td>
                </tr>`;
            });
            const now = new Date();
            const pad = n => String(n).padStart(2,'0');
            const dateStr = `${pad(now.getDate())}/${pad(now.getMonth()+1)}/${now.getFullYear()} ${pad(now.getHours())}:${pad(now.getMinutes())}`;
            const html = `<!doctype html><html><head><meta charset="utf-8"><title>Hóa đơn thanh toán</title><style>
                @media print { @page { size: 80mm auto; margin: 5mm; } }
                body { font-family: Arial, sans-serif; width: 80mm; }
                .title { text-align:center; font-size:16px; font-weight:bold; margin-bottom:6px; }
                .meta { font-size:12px; margin-bottom:6px; }
                table { width:100%; border-collapse: collapse; font-size:12px; }
                th, td { padding:4px 0; border-bottom:1px dashed #000; }
                th { text-align:left; border-top:1px dashed #000; }
                .footer { margin-top:8px; font-size:11px; text-align:center; }
            </style></head><body>
                <div class="title">HÓA ĐƠN THANH TOÁN</div>
                <div class="meta">Tầng: <strong>{{ $table->floor->name ?? '' }}</strong> | Bàn: <strong>{{ $table->name }}</strong></div>
                <div class="meta">Khách: <strong>{{ $order->user->fullname ?? $order->name ?? 'Khách lẻ' }}</strong> | ĐT: {{ $order->user->phone ?? $order->phone ?? '---' }}</div>
                <div class="meta">Thời gian: ${dateStr}</div>
                <table><thead><tr><th style="width:30px; text-align:center;">STT</th><th>Món</th><th style="width:50px; text-align:right;">SL</th><th style="width:70px; text-align:right;">Đơn giá</th><th style="width:90px; text-align:right;">Thành tiền</th></tr></thead><tbody>${rows}</tbody></table>
                <div class="meta" style="text-align:right;"><strong>Tổng cộng: ${formatMoney(totalAmount)}</strong></div>
                <div class="footer">Cảm ơn quý khách!</div>
            </body></html>`;
            const w = window.open('', 'PRINT', 'width=400,height=600');
            w.document.write(html); w.document.close(); w.focus(); w.print(); w.close();
        }
    </script>
</x-layout-backend>
