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
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <strong>Khách:</strong> {{ $order->user->fullname ?? $order->name ?? 'Khách lẻ' }}
                            <span class="ml-3"><strong>ĐT:</strong> {{ $order->user->phone ?? $order->phone ?? '---' }}</span>
                        </div>
                        <div class="card-body">
                            <!-- Payment Info Row -->
                            <div class="row mb-4 align-items-end">
                                <div class="col-md-6">
                                    <div class="form-group mb-0" style="max-width: 300px;">
                                        <label>Phương thức thanh toán:</label>
                                        <select class="form-control" name="payment_method" id="paymentMethodSelect">
                                            <option value="1" selected>Tiền mặt</option>
                                            <option value="2">Chuyển khoản NH</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 text-right">
                                    <label>Tổng tiền cần thanh toán:</label>
                                    <h2 class="text-danger font-weight-bold mb-0" id="total-amount-header">{{ number_format($totalAmount, 0, ',', '.') }} ₫</h2>
                                </div>
                            </div>

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
                            </table>
                        </div>
                        <div class="card-footer text-right">
                            <a href="{{ route('admin.table-order.index') }}" class="btn btn-secondary">Quay lại</a>
                        </div>
                    </div>
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
            let currentTotal = 0;

            inputs.forEach(input => {
                const qty = parseInt(input.value);
                const tr = input.closest('tr');
                const id = tr.dataset.id;
                const price = parseInt(tr.dataset.price);
                
                if (qty < 1 || isNaN(qty)) {
                    toastr.error('Số lượng món không hợp lệ');
                    isValid = false;
                    return;
                }

                items.push({
                    id: id,
                    qty: qty
                });
                
                currentTotal += qty * price;
            });

            if (!isValid) return;

            const paymentMethod = document.getElementById('paymentMethodSelect').value;

            if(!confirm('Xác nhận thanh toán và in hoá đơn?')) return;

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
                        // After update success, process payment
                        processPaymentAndPrint(paymentMethod, currentTotal);
                    } else {
                        toastr.error(res.message);
                    }
                },
                error: function(err) {
                    toastr.error('Lỗi cập nhật đơn hàng');
                }
            });
        }

        function processPaymentAndPrint(paymentMethod, total) {
             $.ajax({
                url: '{{ route("admin.table-order.processPayment") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    order_id: {{ $order->id }},
                    payment_method: paymentMethod,
                    total_price: total
                },
                success: function(res) {
                    if (res.success) {
                        toastr.success('Thanh toán thành công');
                        printInvoice();
                        // Redirect to index after printing
                        setTimeout(() => {
                             window.location.href = '{{ route("admin.table-order.index") }}';
                        }, 2000);
                    } else {
                        toastr.error(res.message);
                    }
                },
                error: function(err) {
                     toastr.error('Lỗi thanh toán');
                }
            });
        }

        function handlePayment() {
            // Deprecated function, logic moved to handlePrintInvoice
        }

        function printInvoice() {
             // Open print window
             window.open('{{ route("admin.order.printorder", ["id" => $order->id]) }}', '_blank');
        }

        // Functions for Edit Mode (Delete, Add, Update Qty)
        function updateRowTotal(input) {
             const qty = parseInt(input.value);
             const tr = input.closest('tr');
             const price = parseInt(tr.dataset.price);
             const amountDisplay = tr.querySelector('.amount-display');
             
             if (!isNaN(qty) && qty > 0) {
                 const amount = price * qty;
                 amountDisplay.innerText = new Intl.NumberFormat('vi-VN').format(amount) + ' ₫';
                 updateTotal();
             }
        }

        function updateTotal() {
            let total = 0;
            document.querySelectorAll('#paymentTable tbody tr').forEach(tr => {
                const qtyInput = tr.querySelector('.qty-input');
                const qty = parseInt(qtyInput.value);
                const price = parseInt(tr.dataset.price);
                if (!isNaN(qty) && qty > 0) {
                    total += price * qty;
                }
            });
            const formattedTotal = new Intl.NumberFormat('vi-VN').format(total) + ' ₫';
            document.getElementById('total-amount-display').innerText = formattedTotal;
            document.getElementById('total-amount-header').innerText = formattedTotal;
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
                    }
                });
            }
        }

        function loadProducts(categoryId) {
            if (!categoryId) return;
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
                            html += `<option value="${p.id}">${p.name}</option>`;
                        });
                        $('#productSelect').html(html);
                    }
                }
            });
        }

        function addProductToOrder() {
             const productId = $('#productSelect').val();
             const qty = $('#productQty').val();
             
             if (!productId) {
                 toastr.error('Vui lòng chọn sản phẩm');
                 return;
             }

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
                         location.reload();
                     } else {
                         toastr.error(res.message);
                     }
                 }
             });
        }
    </script>
</x-layout-backend>
