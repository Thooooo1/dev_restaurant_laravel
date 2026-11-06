<table>
    <thead>
        <tr>
            <th colspan="9" style="text-align: center; font-size: 16px; font-weight: bold;">BÁO CÁO MÓN BÁN CHẠY & HIỆU QUẢ KINH DOANH</th>
        </tr>
        <tr>
            <th colspan="9" style="text-align: center; font-weight: bold;">Nhà hàng: CIVILIZE</th>
        </tr>
        <tr>
            <th colspan="9" style="text-align: center; font-style: italic;">
                Thời gian: {{ $dateFilterLabel }} - Tổng số lượng đã bán: {{ number_format($grandQty, 0, ',', '.') }} phần
            </th>
        </tr>
        <tr>
            <th colspan="9"></th>
        </tr>
        <tr>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Hạng</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Mã món</th>
            <th style="font-weight: bold; border: 1px solid #000;">Tên món ăn / Đồ uống</th>
            <th style="font-weight: bold; border: 1px solid #000;">Danh mục</th>
            <th style="font-weight: bold; border: 1px solid #000;">Khu vực bếp</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center;">ĐVT</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: right;">Đơn giá (VNĐ)</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: right;">Số lượng bán</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: right;">Doanh thu (VNĐ)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $r)
        <tr>
            <td style="border: 1px solid #000; text-align: center;">{{ $r['rank'] }}</td>
            <td style="border: 1px solid #000; text-align: center;">#{{ $r['product_id'] }}</td>
            <td style="border: 1px solid #000;">{{ $r['name'] }}</td>
            <td style="border: 1px solid #000;">{{ $r['category'] }}</td>
            <td style="border: 1px solid #000;">{{ $r['kitchen_area'] }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $r['unit'] }}</td>
            <td style="border: 1px solid #000; text-align: right;">{{ number_format($r['price_sale'], 0, ',', '.') }}</td>
            <td style="border: 1px solid #000; text-align: right; font-weight: bold;">{{ number_format($r['total_qty'], 0, ',', '.') }}</td>
            <td style="border: 1px solid #000; text-align: right;">{{ number_format($r['total_revenue'], 0, ',', '.') }}</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="7" style="text-align: right; font-weight: bold; border: 1px solid #000;">TỔNG CỘNG DOANH THU MÓN:</td>
            <td style="font-weight: bold; border: 1px solid #000; text-align: right;">{{ number_format($grandQty, 0, ',', '.') }}</td>
            <td style="font-weight: bold; border: 1px solid #000; text-align: right;">{{ number_format($grandRevenue, 0, ',', '.') }}</td>
        </tr>
    </tbody>
</table>
