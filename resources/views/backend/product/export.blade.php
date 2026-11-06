<table>
    <thead>
        <tr>
            <th colspan="10" style="text-align: center; font-size: 16px; font-weight: bold;">DANH MỤC MÓN ĂN & THỰC ĐƠN</th>
        </tr>
        <tr>
            <th colspan="10" style="text-align: center; font-weight: bold;">Nhà hàng: CIVILIZE</th>
        </tr>
        <tr>
            <th colspan="10" style="text-align: center; font-style: italic;">
                Ngày xuất file: {{ date('d/m/Y H:i') }} - Quy mô thực đơn: {{ $totalItems }} món
            </th>
        </tr>
        <tr>
            <th colspan="10"></th>
        </tr>
        <tr>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Mã món</th>
            <th style="font-weight: bold; border: 1px solid #000;">Tên món ăn / Đồ uống</th>
            <th style="font-weight: bold; border: 1px solid #000;">Danh mục</th>
            <th style="font-weight: bold; border: 1px solid #000;">Khu vực bếp</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center;">ĐVT</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: right;">Giá vốn (VNĐ)</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: right;">Giá bán (VNĐ)</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: right;">Lợi nhuận (VNĐ)</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: right;">Tồn kho</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Trạng thái</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $r)
        <tr>
            <td style="border: 1px solid #000; text-align: center;">#{{ $r['id'] }}</td>
            <td style="border: 1px solid #000;">{{ $r['name'] }}</td>
            <td style="border: 1px solid #000;">{{ $r['category_name'] }}</td>
            <td style="border: 1px solid #000;">{{ $r['kitchen_area'] }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $r['unit'] }}</td>
            <td style="border: 1px solid #000; text-align: right;">{{ number_format($r['price_buy'], 0, ',', '.') }}</td>
            <td style="border: 1px solid #000; text-align: right;">{{ number_format($r['price_sale'], 0, ',', '.') }}</td>
            <td style="border: 1px solid #000; text-align: right;">{{ number_format($r['profit'], 0, ',', '.') }}</td>
            <td style="border: 1px solid #000; text-align: right;">{{ $r['qty'] }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $r['status'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
