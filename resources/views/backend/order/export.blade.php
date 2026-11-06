<table>
    <thead>
        <tr>
            <th colspan="11" style="text-align: center; font-size: 16px; font-weight: bold;">DANH SÁCH ĐƠN HÀNG</th>
        </tr>
        <tr>
            <th colspan="11" style="text-align: center; font-weight: bold;">Nhà hàng: CIVILIZE</th>
        </tr>
        <tr>
            <th colspan="11" style="text-align: center; font-style: italic;">
                Ngày xuất báo cáo: {{ date('d/m/Y H:i') }} - Tổng số: {{ $totalOrders }} đơn hàng
            </th>
        </tr>
        <tr>
            <th colspan="11"></th>
        </tr>
        <tr>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Mã đơn</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Thời gian</th>
            <th style="font-weight: bold; border: 1px solid #000;">Loại hình</th>
            <th style="font-weight: bold; border: 1px solid #000;">Bàn / Khu vực</th>
            <th style="font-weight: bold; border: 1px solid #000;">Khách hàng</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Số điện thoại</th>
            <th style="font-weight: bold; border: 1px solid #000;">Địa chỉ</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Số món</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: right;">Tổng tiền (VNĐ)</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Phương thức</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Trạng thái</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $r)
        <tr>
            <td style="border: 1px solid #000; text-align: center;">#{{ $r['id'] }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $r['created_at'] }}</td>
            <td style="border: 1px solid #000;">{{ $r['order_style'] }}</td>
            <td style="border: 1px solid #000;">{{ $r['table_name'] }}</td>
            <td style="border: 1px solid #000;">{{ $r['customer_name'] }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $r['customer_phone'] }}</td>
            <td style="border: 1px solid #000;">{{ $r['customer_address'] }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $r['items_count'] }}</td>
            <td style="border: 1px solid #000; text-align: right;">{{ number_format($r['total_price'], 0, ',', '.') }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $r['payment_method'] }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $r['status'] }}</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="8" style="text-align: right; font-weight: bold; border: 1px solid #000;">TỔNG CỘNG:</td>
            <td style="font-weight: bold; border: 1px solid #000; text-align: right;">{{ number_format($totalAmount, 0, ',', '.') }}</td>
            <td colspan="2" style="border: 1px solid #000;"></td>
        </tr>
    </tbody>
</table>
