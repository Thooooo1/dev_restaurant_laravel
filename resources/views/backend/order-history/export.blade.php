<table>
    <thead>
        <tr>
            <th colspan="8" style="text-align: center; font-size: 16px; font-weight: bold;">BÁO CÁO DOANH THU</th>
        </tr>
        <tr>
            <th colspan="8" style="text-align: center; font-weight: bold;">Nhà hàng: CIVILIZE</th>
        </tr>
        <tr>
            <th colspan="8" style="text-align: center; font-style: italic;">
                Thời gian: Năm {{ $year }} 
                {{ $quarter ? '- Quý ' . $quarter : '' }} 
                {{ $month ? '- Tháng ' . $month : '' }}
            </th>
        </tr>
        <tr>
            <th colspan="8"></th>
        </tr>
        <tr>
            <th style="font-weight: bold; border: 1px solid #000;">Mã HĐ</th>
            <th style="font-weight: bold; border: 1px solid #000;">Ngày tạo</th>
            <th style="font-weight: bold; border: 1px solid #000;">Tên KH</th>
            <th style="font-weight: bold; border: 1px solid #000;">Số ĐT</th>
            <th style="font-weight: bold; border: 1px solid #000;">Tên món ăn</th>
            <th style="font-weight: bold; border: 1px solid #000;">Số lượng</th>
            <th style="font-weight: bold; border: 1px solid #000;">Đơn giá</th>
            <th style="font-weight: bold; border: 1px solid #000;">Thành tiền</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $row)
        <tr>
            <td style="border: 1px solid #000;">{{ $row['order_id'] }}</td>
            <td style="border: 1px solid #000;">{{ $row['created_at'] }}</td>
            <td style="border: 1px solid #000;">{{ $row['customer_name'] }}</td>
            <td style="border: 1px solid #000;">{{ $row['phone'] }}</td>
            <td style="border: 1px solid #000;">{{ $row['product_name'] }}</td>
            <td style="border: 1px solid #000;">{{ $row['qty'] }}</td>
            <td style="border: 1px solid #000;">{{ $row['price'] }}</td>
            <td style="border: 1px solid #000;">{{ $row['amount'] }}</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="7" style="text-align: right; font-weight: bold; border: 1px solid #000;">TỔNG DOANH THU:</td>
            <td style="font-weight: bold; border: 1px solid #000;">{{ $totalRevenue }}</td>
        </tr>
    </tbody>
</table>
