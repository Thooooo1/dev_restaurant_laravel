<table>
    <thead>
        <tr>
            <th colspan="10" style="text-align: center; font-size: 16px; font-weight: bold;">DANH SÁCH KHÁCH HÀNG & LỊCH SỬ TIÊU DÙNG</th>
        </tr>
        <tr>
            <th colspan="10" style="text-align: center; font-weight: bold;">Nhà hàng: CIVILIZE</th>
        </tr>
        <tr>
            <th colspan="10" style="text-align: center; font-style: italic;">
                Thời gian xuất: {{ date('d/m/Y H:i') }} - Quy mô: {{ $totalCustomers }} khách hàng
            </th>
        </tr>
        <tr>
            <th colspan="10"></th>
        </tr>
        <tr>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Mã KH</th>
            <th style="font-weight: bold; border: 1px solid #000;">Họ và tên</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Số điện thoại</th>
            <th style="font-weight: bold; border: 1px solid #000;">Email</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Giới tính</th>
            <th style="font-weight: bold; border: 1px solid #000;">Địa chỉ</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Số đơn hàng</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: right;">Tổng chi tiêu (VNĐ)</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Ngày đăng ký</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Trạng thái</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $r)
        <tr>
            <td style="border: 1px solid #000; text-align: center;">#{{ $r['id'] }}</td>
            <td style="border: 1px solid #000;">{{ $r['fullname'] }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $r['phone'] }}</td>
            <td style="border: 1px solid #000;">{{ $r['email'] }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $r['gender'] }}</td>
            <td style="border: 1px solid #000;">{{ $r['address'] }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $r['order_count'] }}</td>
            <td style="border: 1px solid #000; text-align: right;">{{ number_format($r['total_spent'], 0, ',', '.') }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $r['created_at'] }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $r['status'] }}</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="6" style="text-align: right; font-weight: bold; border: 1px solid #000;">TỔNG CỘNG:</td>
            <td style="font-weight: bold; border: 1px solid #000; text-align: center;">{{ number_format($grandTotalOrders, 0, ',', '.') }} đơn</td>
            <td style="font-weight: bold; border: 1px solid #000; text-align: right;">{{ number_format($grandTotalSpent, 0, ',', '.') }}</td>
            <td colspan="2" style="border: 1px solid #000;"></td>
        </tr>
    </tbody>
</table>
