<div class="row mb-3">
    <div class="col-md-6">
        <p><strong>Mã hoá đơn:</strong> #{{ $order->id }}</p>
        <p><strong>Ngày tạo:</strong> {{ $order->created_at->format('d/m/Y H:i:s') }}</p>
        <p><strong>Phương thức TT:</strong> 
            @if($order->payment_method == 1) Tiền mặt @elseif($order->payment_method == 2) Chuyển khoản @else Khác @endif
        </p>
    </div>
    <div class="col-md-6">
        <p><strong>Khách hàng:</strong> {{ $order->user->fullname ?? $order->name ?? 'Khách lẻ' }}</p>
        <p><strong>SĐT:</strong> {{ $order->user->phone ?? $order->phone }}</p>
        <p><strong>Tổng tiền:</strong> <span class="text-danger font-weight-bold">{{ number_format($order->orderDetails->sum('amount'), 0, ',', '.') }} VNĐ</span></p>
    </div>
</div>

<table class="table table-bordered table-sm">
    <thead>
        <tr>
            <th>STT</th>
            <th>Tên món ăn</th>
            <th>Số lượng</th>
            <th>Đơn giá</th>
            <th>Thành tiền</th>
        </tr>
    </thead>
    <tbody>
        @foreach($order->orderDetails as $index => $detail)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $detail->product->name ?? 'Sản phẩm đã xóa' }}</td>
            <td>{{ $detail->qty }}</td>
            <td>{{ number_format($detail->price, 0, ',', '.') }}</td>
            <td>{{ number_format($detail->amount, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
