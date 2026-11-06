<table>
    <thead>
        <tr>
            <th colspan="11" style="text-align: center; font-size: 16px; font-weight: bold;">BÁO CÁO NHẬP KHO & NGUYÊN VẬT LIỆU</th>
        </tr>
        <tr>
            <th colspan="11" style="text-align: center; font-weight: bold;">Nhà hàng: CIVILIZE</th>
        </tr>
        <tr>
            <th colspan="11" style="text-align: center; font-style: italic;">
                Thời gian xuất: {{ date('d/m/Y H:i') }} - Quy mô: {{ $totalReceipts }} phiếu nhập
                {{ $year ? ' - Năm ' . $year : '' }} {{ $month ? ' - Tháng ' . $month : '' }}
            </th>
        </tr>
        <tr>
            <th colspan="11"></th>
        </tr>
        <tr>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Mã phiếu</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Ngày nhập</th>
            <th style="font-weight: bold; border: 1px solid #000;">Nhà cung cấp</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Số ĐT NCC</th>
            <th style="font-weight: bold; border: 1px solid #000;">Tên nguyên vật liệu</th>
            <th style="font-weight: bold; border: 1px solid #000;">Phân loại</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center;">ĐVT</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: right;">Số lượng</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: right;">Đơn giá nhập (VNĐ)</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: right;">Thành tiền (VNĐ)</th>
            <th style="font-weight: bold; border: 1px solid #000;">Người lập phiếu</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $r)
        <tr>
            <td style="border: 1px solid #000; text-align: center;">{{ $r['code'] }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $r['import_date'] }}</td>
            <td style="border: 1px solid #000;">{{ $r['supplier_name'] }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $r['supplier_phone'] }}</td>
            <td style="border: 1px solid #000;">{{ $r['item_name'] }}</td>
            <td style="border: 1px solid #000;">{{ $r['item_type'] }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $r['unit'] }}</td>
            <td style="border: 1px solid #000; text-align: right;">{{ number_format($r['quantity'], 0, ',', '.') }}</td>
            <td style="border: 1px solid #000; text-align: right;">{{ number_format($r['price'], 0, ',', '.') }}</td>
            <td style="border: 1px solid #000; text-align: right;">{{ number_format($r['total_amount'], 0, ',', '.') }}</td>
            <td style="border: 1px solid #000;">{{ $r['creator'] }}</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="9" style="text-align: right; font-weight: bold; border: 1px solid #000;">TỔNG TIỀN NHẬP KHO:</td>
            <td style="font-weight: bold; border: 1px solid #000; text-align: right;">{{ number_format($grandTotal, 0, ',', '.') }}</td>
            <td style="border: 1px solid #000;"></td>
        </tr>
    </tbody>
</table>
