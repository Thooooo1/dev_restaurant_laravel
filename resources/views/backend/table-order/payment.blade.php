<x-layout-backend>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-12 col-md">
                    <strong class="fw-bold h4 text-danger">{{ $table->floor->name ?? '' }} - {{ $table->name }} | THANH TOÁN</strong>
                </div>
                <div class="col-12 col-md-auto mt-3 mt-md-0">
                    <div class="d-flex flex-wrap justify-content-md-end">
                        <a href="{{ route('admin.table-order.index') }}" class="btn btn-secondary btn-sm mb-2 mr-2"><i class="fas fa-arrow-left"></i> Quay lại</a>
                        <button class="btn btn-warning btn-sm mb-2 mr-2" id="btnEditMode" onclick="toggleEditMode()"><i class="fas fa-edit"></i> Sửa</button>
                        <button class="btn btn-success btn-sm mb-2 mr-2" data-toggle="modal" data-target="#modalAddProduct"><i class="fas fa-plus"></i> Thêm sản phẩm</button>
                        <button class="btn btn-primary btn-sm mb-2" onclick="handlePrintInvoice()"><i class="fas fa-print"></i> In hoá đơn</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header p-2 p-md-3">
                            <strong>Khách:</strong> {{ $order->user->fullname ?? $order->name ?? 'Khách lẻ' }}
                            <span class="ml-2 ml-md-3"><strong>ĐT:</strong> {{ $order->user->phone ?? $order->phone ?? '---' }}</span>
                        </div>
                        <div class="card-body p-2 p-md-3">
                            <!-- Payment Info Row -->
                            <div class="row mb-4 align-items-center">
                                <div class="col-12 col-md-6 d-flex justify-content-between justify-content-md-start align-items-center">
                                    <label class="mb-0 mr-2">Phương thức:</label>
                                    <select class="form-control form-control-sm w-auto" name="payment_method" id="paymentMethodSelect">
                                        <option value="1" selected>Tiền mặt</option>
                                        <option value="2">Chuyển khoản NH</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6 d-flex justify-content-between justify-content-md-end align-items-center text-left text-md-right mt-2 mt-md-0">
                                    <label class="mb-0 mr-2">Tổng tiền:</label>
                                    <span class="h5 text-danger font-weight-bold mb-0" id="total-amount-header">{{ number_format($totalAmount, 0, ',', '.') }} ₫</span>
                                </div>
                            </div>

                            <table class="table table-sm" id="paymentTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Tên món</th>
                                        <th class="text-right">SL</th>
                                        <th class="text-right">Đơn giá</th>
                                        <th class="text-right">Thành tiền</th>
                                        <th class="text-center action-col"></th>
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
             const paymentMethodValue = document.getElementById('paymentMethodSelect').value;
             const paymentMethodText = paymentMethodValue === '2' ? 'Chuyển khoản NH' : 'Tiền mặt';

             const items = [];
             let total = 0;

             document.querySelectorAll('#paymentTable tbody tr').forEach((tr, idx) => {
                 const name = tr.dataset.name || 'N/A';
                 const price = parseInt(tr.dataset.price);
                 const qtyInput = tr.querySelector('.qty-input');
                 const qty = qtyInput ? parseInt(qtyInput.value) : 0;
                 if (isNaN(price) || isNaN(qty) || qty < 1) return;
                 const amount = price * qty;
                 total += amount;
                 items.push({ stt: idx + 1, name, price, qty, amount });
             });

             const customerName = @json($order->user->fullname ?? $order->name ?? 'Khách lẻ');
             const customerPhone = @json($order->user->phone ?? $order->phone ?? '---');
             const floorName = @json($table->floor->name ?? '');
             const tableName = @json($table->name ?? '');
             const orderId = @json($order->id);
             const createdAt = @json(optional($order->created_at)->format('H:i d/m/Y'));

             const html = generateInvoiceHTML({
                 floorName,
                 tableName,
                 orderId,
                 createdAt,
                 customerName,
                 customerPhone,
                 paymentMethodText,
                 items,
                 total
             });

             const w = window.open('', 'PRINT', 'width=400,height=650');
             if (!w) {
                 toastr.error('Trình duyệt đang chặn cửa sổ in');
                 return;
             }
             w.document.write(html);
             w.document.close();
             w.focus();
             setTimeout(() => {
                 w.print();
                 setTimeout(() => w.close(), 200);
             }, 250);
        }

        function generateInvoiceHTML(data) {
            const {
                floorName,
                tableName,
                orderId,
                createdAt,
                customerName,
                customerPhone,
                paymentMethodText,
                items,
                total
            } = data;

            const nf = new Intl.NumberFormat('vi-VN');
            const rows = items.map(i => {
                return `
                    <tr>
                        <td class="c">${i.stt}</td>
                        <td class="l">${escapeHtml(i.name)}</td>
                        <td class="q">${i.qty}</td>
                        <td class="p">${nf.format(i.price)} đ</td>
                        <td class="a">${nf.format(i.amount)} đ</td>
                    </tr>
                `;
            }).join('');

            const totalText = `${nf.format(total)} đ`;
            const title = 'HÓA ĐƠN';
            const metaFloorTable = `${floorName ? `Tầng: <strong>${escapeHtml(floorName)}</strong> | ` : ''}Bàn: <strong>${escapeHtml(tableName)}</strong>`;
            const metaInvoiceId = `Mã HĐ: <strong>#${escapeHtml(String(orderId))}</strong>`;
            const metaCustomer = `Khách: <strong>${escapeHtml(customerName)}</strong> | SĐT: <strong>${escapeHtml(customerPhone)}</strong>`;
            const metaPayment = `Phương thức thanh toán: <strong>${escapeHtml(paymentMethodText)}</strong>`;
            const metaTime = `Thời gian: <strong>${escapeHtml(createdAt || '')}</strong>`;

            return `<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>In hóa đơn</title>
  <style>
    @media print { @page { size: 80mm auto; margin: 3mm; } }
    html, body { padding: 0; margin: 0; }
    body { font-family: Arial, sans-serif; font-size: 12px; width: 80mm; }
    .bill { width: 74mm; margin: 0 auto; }
    .title { text-align: center; font-size: 16px; font-weight: 700; margin: 4px 0 6px; }
    .meta { font-size: 11px; margin-bottom: 4px; }
    .line { border-top: 1px dashed #000; margin: 6px 0; }
    table { width: 100%; border-collapse: collapse; font-size: 11px; }
    th, td { padding: 4px 0; vertical-align: top; }
    thead th { border-top: 1px dashed #000; border-bottom: 1px dashed #000; font-weight: 700; }
    tbody tr td { border-bottom: 1px dashed #000; }
    .c { text-align: center; width: 24px; }
    .l { text-align: left; }
    .q { text-align: right; white-space: nowrap; width: 32px; }
    .p { text-align: right; white-space: nowrap; width: 62px; }
    .a { text-align: right; white-space: nowrap; width: 64px; }
    .sum { font-weight: 700; }
    .footer { text-align: center; font-size: 11px; margin-top: 8px; }
  </style>
</head>
<body>
  <div class="bill">
    <div class="title">${title}</div>
    <div class="meta">${metaFloorTable}</div>
    <div class="meta">${metaInvoiceId}</div>
    <div class="meta">${metaCustomer}</div>
    <div class="meta">${metaPayment}</div>
    <div class="meta">${metaTime}</div>
    <div class="line"></div>
    <table>
      <thead>
        <tr>
          <th class="c">#</th>
          <th class="l">Tên món ăn</th>
          <th class="q">SL</th>
          <th class="p">Đơn giá</th>
          <th class="a">Thành tiền</th>
        </tr>
      </thead>
      <tbody>
        ${rows}
      </tbody>
      <tfoot>
        <tr>
          <td colspan="4" class="l sum">Tổng cộng</td>
          <td class="a sum">${totalText}</td>
        </tr>
      </tfoot>
    </table>
    <div class="footer">Cảm ơn quý khách!</div>
  </div>
</body>
</html>`;
        }

        function escapeHtml(str) {
            return String(str)
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
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
