<x-layout-backend>
    <section class="content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <div>
                <h1>{{ $table->floor->name ?? '' }} - {{ $table->name }} | Thanh toán</h1>
            </div>
            <div>
                <a href="{{ route('admin.table-order.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Quay lại</a>
                <button class="btn btn-primary btn-sm ml-2" onclick="printInvoice()"><i class="fas fa-print"></i> In hoá đơn</button>
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
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Tên món</th>
                                <th class="text-right">SL</th>
                                <th class="text-right">Đơn giá</th>
                                <th class="text-right">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orderDetails as $i => $item)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $item->product->name ?? 'N/A' }}</td>
                                    <td class="text-right">{{ $item->qty }}</td>
                                    <td class="text-right">{{ number_format($item->price, 0, ',', '.') }} ₫</td>
                                    <td class="text-right">{{ number_format($item->amount, 0, ',', '.') }} ₫</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4" class="text-right">Tổng cộng</th>
                                <th class="text-right text-danger">{{ number_format($totalAmount, 0, ',', '.') }} ₫</th>
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

    <script>
        function formatMoney(amount) {
            return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
        }
        function printInvoice() {
            const items = [
                @foreach($orderDetails as $i => $item)
                    {
                        stt: {{ $i + 1 }},
                        name: "{{ $item->product->name ?? 'N/A' }}",
                        qty: {{ $item->qty }},
                        price: {{ $item->price }}
                    },
                @endforeach
            ];
            let rows = '';
            items.forEach(i => {
                const amount = i.price * i.qty;
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
                <div class="meta" style="text-align:right;"><strong>Tổng cộng: {{ number_format($totalAmount, 0, ',', '.') }} ₫</strong></div>
                <div class="footer">Cảm ơn quý khách!</div>
            </body></html>`;
            const w = window.open('', 'PRINT', 'width=400,height=600');
            w.document.write(html); w.document.close(); w.focus(); w.print(); w.close();
        }
    </script>
</x-layout-backend>
