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
                <div class="col-md-8">
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
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h3 class="card-title">Thanh toán</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Tổng tiền cần thanh toán:</label>
                                <h2 class="text-danger font-weight-bold">{{ number_format($totalAmount, 0, ',', '.') }} ₫</h2>
                            </div>
                            <hr>
                            <div class="form-group">
                                <label>Phương thức thanh toán:</label>
                                <select class="form-control" name="payment_method" id="paymentMethodSelect">
                                    <option value="1" selected>Tiền mặt</option>
                                    <option value="2">Chuyển khoản NH</option>
                                </select>
                            </div>
                            <hr>
                            <button class="btn btn-success btn-lg btn-block" onclick="handlePayment()">
                                <i class="fas fa-check-circle"></i> THANH TOÁN & KẾT THÚC
                            </button>
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

        function handlePayment() {
            const paymentMethod = document.getElementById('paymentMethodSelect').value;
            const totalPrice = {{ $totalAmount }};
            
            if(!confirm('Xác nhận thanh toán đơn hàng này?')) return;

            fetch('{{ route("admin.table-order.processPayment") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    order_id: {{ $order->id }},
                    payment_method: paymentMethod,
                    total_price: totalPrice
                })
            })
            .then(response => response.json())
            .then(res => {
                if (res.success) {
                    toastr.success(res.message);
                    setTimeout(() => {
                        window.location.href = '{{ route("admin.table-order.index") }}';
                    }, 1000);
                } else {
                    toastr.error(res.message);
                }
            })
            .catch(err => {
                toastr.error('Lỗi thanh toán');
            });
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
            document.getElementById('total-amount-display').innerText = new Intl.NumberFormat('vi-VN').format(total) + ' ₫';
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
