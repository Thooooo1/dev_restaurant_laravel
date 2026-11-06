<x-layout-backend>
    <!-- Thanh công cụ phía trên (Ẩn khi in) -->
    <div class="mb-6 flex items-center justify-between flex-wrap gap-4 print:hidden">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.import-goods.index') }}" 
               class="inline-flex items-center px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-sm font-medium transition-colors">
                <span>Quay lại danh sách</span>
            </a>
            <span class="text-slate-300">|</span>
            <div class="text-xs text-slate-500 font-medium">
                Mã phiếu: <strong class="font-mono text-slate-800 text-sm font-bold">{{ $receipt->code }}</strong>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('admin.import-goods.edit', $receipt->id) }}" 
               class="inline-flex items-center px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-lg text-sm font-semibold transition-colors">
                <span>Chỉnh sửa</span>
            </a>

            <button type="button" onclick="window.print()" 
                    class="inline-flex items-center px-5 py-2 bg-[#F47716] hover:bg-[#EA580C] text-white rounded-lg text-sm font-semibold transition-all shadow-md shadow-orange-500/20 cursor-pointer">
                <span>In Phiếu Nhập Kho</span>
            </button>
        </div>
    </div>

    <!-- MẪU PHIẾU NHẬP KHO CHUẨN IN ẤN (A4 Ready) -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 max-w-4xl mx-auto p-8 sm:p-12 print:shadow-none print:border-none print:p-0 print:m-0 text-slate-900 font-sans" id="printableSlip">
        
        <!-- Header đơn vị kinh doanh -->
        <div class="flex items-start justify-between border-b-2 border-slate-900 pb-6 mb-6">
            <div class="flex items-center gap-4">
                <img src="{{ asset('images/logo/logo.png') }}" alt="CIVILIZE" class="h-14 w-auto object-contain">
                <div>
                    <h2 class="font-black text-lg uppercase tracking-wide text-slate-900">CIVILIZE RESTAURANT</h2>
                    <p class="text-xs text-slate-600 mt-0.5">Địa chỉ: Số 120 Phúc Hòa, Huyện Phúc Thọ, TP. Hà Nội</p>
                    <p class="text-xs text-slate-600">Hotline: 0338 990 538 &bull; Email: contact@civilize.vn</p>
                </div>
            </div>

            <div class="text-right text-xs">
                <div class="font-bold text-slate-800 uppercase tracking-wider text-[11px]">Mẫu số: 01 - VT</div>
                <div class="text-[10px] text-slate-500 italic mt-0.5">(Ban hành theo TT 200/2014/TT-BTC)</div>
                <div class="mt-2 font-mono text-sm font-bold text-[#F47716] bg-orange-50 px-2.5 py-1 rounded border border-orange-200 inline-block">
                    Số: {{ $receipt->code }}
                </div>
            </div>
        </div>

        <!-- Tiêu đề Phiếu -->
        <div class="text-center my-6">
            <h1 class="text-2xl sm:text-3xl font-black uppercase tracking-wider text-slate-900">PHIẾU NHẬP KHO</h1>
            <p class="text-xs sm:text-sm text-slate-600 italic mt-1.5">
                Ngày {{ $receipt->import_date ? $receipt->import_date->format('d') : '...' }} 
                tháng {{ $receipt->import_date ? $receipt->import_date->format('m') : '...' }} 
                năm {{ $receipt->import_date ? $receipt->import_date->format('Y') : '...' }}
                (Giờ nhập: {{ $receipt->import_date ? $receipt->import_date->format('H:i') : '' }})
            </p>
        </div>

        <!-- Thông tin bên giao & nhận -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-2.5 gap-x-8 text-sm mb-6 pb-6 border-b border-dashed border-slate-300">
            <div>
                <span class="text-slate-500 font-medium">Đơn vị / Người giao hàng:</span>
                <span class="font-bold text-slate-900 ml-1.5">{{ $receipt->supplier_name }}</span>
            </div>
            <div>
                <span class="text-slate-500 font-medium">Người tiếp nhận / Thủ kho:</span>
                <span class="font-bold text-slate-900 ml-1.5">{{ $receipt->creator_name ?? 'Thủ kho' }}</span>
            </div>
            <div>
                <span class="text-slate-500 font-medium">Số điện thoại liên hệ:</span>
                <span class="font-mono text-slate-900 ml-1.5">{{ $receipt->supplier_phone ?? 'Chưa cập nhật' }}</span>
            </div>
            <div>
                <span class="text-slate-500 font-medium">Nhập tại kho:</span>
                <span class="font-semibold text-slate-900 ml-1.5">Kho Tổng Nhà Hàng CIVILIZE</span>
            </div>
            @if(!empty($receipt->supplier_address))
                <div class="sm:col-span-2">
                    <span class="text-slate-500 font-medium">Địa chỉ nhà cung cấp:</span>
                    <span class="text-slate-800 ml-1.5">{{ $receipt->supplier_address }}</span>
                </div>
            @endif
            @if(!empty($receipt->note))
                <div class="sm:col-span-2">
                    <span class="text-slate-500 font-medium">Diễn giải / Ghi chú:</span>
                    <span class="text-slate-800 italic ml-1.5">{{ $receipt->note }}</span>
                </div>
            @endif
        </div>

        <!-- Bảng danh sách hàng hoá nhập kho -->
        <div class="overflow-x-auto mb-6">
            <table class="w-full text-left border-collapse border border-slate-900 text-sm">
                <thead class="bg-slate-100 text-slate-900 font-bold uppercase text-xs tracking-wider">
                    <tr class="border-b border-slate-900">
                        <th class="border-r border-slate-900 px-3 py-2.5 text-center w-12">STT</th>
                        <th class="border-r border-slate-900 px-3 py-2.5">Tên Hàng Hóa / Nguyên Liệu</th>
                        <th class="border-r border-slate-900 px-3 py-2.5 text-center w-28">Phân Loại</th>
                        <th class="border-r border-slate-900 px-3 py-2.5 text-center w-24">Đơn Vị Tính</th>
                        <th class="border-r border-slate-900 px-3 py-2.5 text-right w-24">Số Lượng</th>
                        <th class="border-r border-slate-900 px-3 py-2.5 text-right w-32">Đơn Giá</th>
                        <th class="px-3 py-2.5 text-right w-36">Thành Tiền (VNĐ)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-300">
                    @foreach($receipt->items as $idx => $item)
                    <tr class="border-b border-slate-300">
                        <td class="border-r border-slate-900 px-3 py-2 text-center text-slate-600">{{ $idx + 1 }}</td>
                        <td class="border-r border-slate-900 px-3 py-2 font-bold text-slate-900">{{ $item->name }}</td>
                        <td class="border-r border-slate-900 px-3 py-2 text-center text-xs text-slate-600">{{ $item->type ?: 'Nguyên liệu' }}</td>
                        <td class="border-r border-slate-900 px-3 py-2 text-center font-medium">{{ $item->unit }}</td>
                        <td class="border-r border-slate-900 px-3 py-2 text-right font-bold text-slate-900">{{ number_format($item->quantity, 0, ',', '.') }}</td>
                        <td class="border-r border-slate-900 px-3 py-2 text-right text-slate-700 font-mono">{{ number_format($item->price, 0, ',', '.') }}₫</td>
                        <td class="px-3 py-2 text-right font-black text-slate-900 font-mono">{{ number_format($item->total_amount, 0, ',', '.') }}₫</td>
                    </tr>
                    @endforeach

                    <!-- Dòng Tổng cộng -->
                    <tr class="bg-slate-50 font-black text-slate-900 border-t-2 border-slate-900">
                        <td colspan="6" class="border-r border-slate-900 px-4 py-3 text-right uppercase tracking-wider text-xs">
                            Tổng Giá Trị Phiếu Nhập Kho:
                        </td>
                        <td class="px-3 py-3 text-right text-base text-[#F47716] font-mono">
                            {{ number_format($receipt->total_amount, 0, ',', '.') }}₫
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Tổng tiền bằng chữ -->
        <div class="mb-10 text-sm text-slate-800">
            <span class="font-bold">Tổng số tiền (viết bằng chữ):</span>
            <span class="italic font-medium text-slate-900 ml-1">{{ $amountInWords }}</span>
        </div>

        <!-- Chữ ký các bên liên quan (Chuẩn chứng từ kế toán 3 bên) -->
        <div class="grid grid-cols-3 gap-4 text-center mt-12 text-sm">
            <div>
                <div class="font-bold uppercase tracking-wider text-slate-900">Người Giao Hàng</div>
                <div class="text-xs text-slate-500 italic mt-0.5">(Ký, ghi rõ họ tên)</div>
                <div class="h-24"></div>
                <div class="font-semibold text-slate-800 text-xs sm:text-sm">{{ $receipt->supplier_name }}</div>
            </div>

            <div>
                <div class="font-bold uppercase tracking-wider text-slate-900">Thủ Kho Tiếp Nhận</div>
                <div class="text-xs text-slate-500 italic mt-0.5">(Ký, ghi rõ họ tên)</div>
                <div class="h-24"></div>
                <div class="font-semibold text-slate-800 text-xs sm:text-sm">{{ $receipt->creator_name ?? 'Thủ kho' }}</div>
            </div>

            <div>
                <div class="font-bold uppercase tracking-wider text-slate-900">Kế Toán / Quản Lý Duyệt</div>
                <div class="text-xs text-slate-500 italic mt-0.5">(Ký, đóng dấu nếu có)</div>
                <div class="h-24"></div>
                <div class="font-semibold text-slate-800 text-xs sm:text-sm">BQL CIVILIZE Restaurant</div>
            </div>
        </div>

        <!-- Chân trang chứng từ in -->
        <div class="mt-14 pt-4 border-t border-slate-200 text-center text-[11px] text-slate-400 italic">
            Chứng từ được tạo và lưu trữ trên hệ thống Quản lý Nhà hàng CIVILIZE • Hotline hỗ trợ kỹ thuật: 0338 990 538
        </div>

    </div>

    <!-- Tối ưu CSS khi in ấn -->
    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            #printableSlip, #printableSlip * {
                visibility: visible;
            }
            #printableSlip {
                position: absolute;
                left: 0;
                top: 0;
                width: 100% !important;
                max-width: 100% !important;
                padding: 10mm 15mm !important;
                margin: 0 !important;
                border: none !important;
                box-shadow: none !important;
            }
            @page {
                size: A4 portrait;
                margin: 8mm 10mm;
            }
        }
    </style>
</x-layout-backend>
