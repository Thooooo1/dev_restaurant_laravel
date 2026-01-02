<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In đơn hàng</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .container { width: 100%; max-width: 600px; margin: auto; }
        .header { text-align: center; margin-bottom: 15px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 12px; }
        .table th, .table td { border: 1px solid #000; padding: 5px; text-align: center; }
        .table th { background: #f2f2f2; }
        .footer { margin-top: 15px; text-align: center; font-style: italic; }
    </style>
    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</head>
<body>
    <div class="container">
        <div class="header">
            <h3>HÓA ĐƠN ĐƠN HÀNG</h3>
            <p>Ngày đặt hàng: {{ date('H:i d/m/Y', strtotime($order->created_at)) }}</p>
        </div>
        <p><strong>Họ tên:</strong> {{ $order->name }}</p>
        <p><strong>Điện thoại:</strong> {{ $order->phone }}</p>
        <p><strong>Địa chỉ:</strong> {{ $order->address }}</p>
        <p><strong>Ghi chú:</strong> {{ $order->note }}</p>
        <p><strong>Thanh toán:</strong> {{ $order->payment_method }}</p>
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Món ăn</th>
                    <th>Giá</th>
                    <th>SL</th>
                    <th>Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->orderDetails as $index => $detail)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $detail->product->name }}</td>
                    <td>{{ number_format($detail->price, 0) }} VNĐ</td>
                    <td>{{ $detail->qty }}</td>
                    <td>{{ number_format($detail->amount, 0) }} VNĐ</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="footer">
            <p>Cảm ơn quý khách đã đặt món tại VanHaiRestaurant!</p>
        </div>
    </div>
</body>
</html>
