<table>
    <thead>
        <tr>
            <th colspan="8" style="text-align: center; font-size: 16px; font-weight: bold;">BÁO CÁO PHƯƠNG THỨC THANH TOÁN</th>
        </tr>
        <tr>
            <th colspan="8" style="text-align: center; font-weight: bold;">Nhà hàng: CIVILIZE</th>
        </tr>
        <tr>
            <th colspan="8" style="text-align: center; font-style: italic;">
                Thời gian: Năm {{ $year ?? date('Y') }} 
                {{ $quarter ? '- Quý ' . $quarter : '' }} 
                {{ $month ? '- Tháng ' . $month : '' }}
            </th>
        </tr>
        <tr>
            <th colspan="8"></th>
        </tr>
        <tr>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Mã đơn</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Thời gian</th>
            <th style="font-weight: bold; border: 1px solid #000;">Loại hình</th>
            <th style="font-weight: bold; border: 1px solid #000;">Vị trí / Bàn</th>
            <th style="font-weight: bold; border: 1px solid #000;">Khách hàng</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Số điện thoại</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Phương thức</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: right;">Số tiền (VNĐ)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $r)
        <tr>
            <td style="border: 1px solid #000; text-align: center;">#{{ $r['id'] }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $r['created_at'] }}</td>
            <td style="border: 1px solid #000;">{{ $r['order_style'] }}</td>
            <td style="border: 1px solid #000;">{{ $r['location'] }}</td>
            <td style="border: 1px solid #000;">{{ $r['customer_name'] }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $r['customer_phone'] }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $r['payment_method'] }}</td>
            <td style="border: 1px solid #000; text-align: right;">{{ number_format($r['amount'], 0, ',', '.') }}</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="7" style="text-align: right; font-weight: bold; border: 1px solid #000;">TỔNG THANH TOÁN TIỀN MẶT:</td>
            <td style="font-weight: bold; border: 1px solid #000; text-align: right;">{{ number_format($cashTotal, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td colspan="7" style="text-align: right; font-weight: bold; border: 1px solid #000;">TỔNG CHUYỂN KHOẢN VIETQR:</td>
            <td style="font-weight: bold; border: 1px solid #000; text-align: right;">{{ number_format($vietqrTotal, 0, ',', '.') }}</td>
        </tr>
        @if($otherTotal > 0)
        <tr>
            <td colspan="7" style="text-align: right; font-weight: bold; border: 1px solid #000;">PHƯƠNG THỨC KHÁC:</td>
            <td style="font-weight: bold; border: 1px solid #000; text-align: right;">{{ number_format($otherTotal, 0, ',', '.') }}</td>
        </tr>
        @endif
        <tr>
            <td colspan="7" style="text-align: right; font-weight: bold; border: 1px solid #000; background-color: #FFF2E8;">TỔNG DOANH THU GIAO DỊCH ({{ $totalTransactions }} giao dịch):</td>
            <td style="font-weight: bold; border: 1px solid #000; text-align: right; background-color: #FFF2E8;">{{ number_format($grandTotal, 0, ',', '.') }}</td>
        </tr>
    </tbody>
</table>
