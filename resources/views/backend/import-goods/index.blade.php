<x-layout-backend>
    <!-- Tiêu đề trang & Nút hành động -->
    <div class="mb-6">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-800">
                    Quản lý nhập kho
                </h1>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('admin.import-goods.export', request()->all()) }}" 
                   class="ant-btn ant-btn-sm font-medium bg-white border border-gray-200 text-gray-700 hover:bg-gray-50">
                    <span>Xuất Excel kho hàng</span>
                </a>
                <a href="{{ route('admin.import-goods.create') }}" 
                   class="ant-btn ant-btn-primary ant-btn-sm font-bold uppercase tracking-wider shadow-2xs">
                    <span>+ Lập phiếu nhập mới</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Thông báo Alert nếu có -->
    @if(session('success'))
        <div class="mb-5 p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-5 p-4 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <i class="fa-solid fa-circle-exclamation text-rose-600 text-base"></i>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif

    <!-- Thẻ Thống kê nhanh trong kỳ: Phong cách Ant Design Pro / ERP sang trọng, thuần typography, không icon thừa -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white p-4 sm:p-5 rounded-xl border border-slate-200 shadow-xs">
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Tổng Số Phiếu Nhập</div>
            <div class="text-2xl font-black text-slate-800 mt-1">{{ number_format($totalReceipts) }}</div>
            <div class="text-xs text-slate-500 mt-1">phiếu nhập kho</div>
        </div>

        <div class="bg-white p-4 sm:p-5 rounded-xl border border-slate-200 shadow-xs">
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Tổng Chi Phí Nhập Kho</div>
            <div class="text-2xl font-black text-[#C25E00] mt-1">{{ number_format($totalAmount, 0, ',', '.') }}₫</div>
            <div class="text-xs text-slate-500 mt-1">tổng giá trị hàng hóa</div>
        </div>

        <div class="bg-white p-4 sm:p-5 rounded-xl border border-slate-200 shadow-xs">
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Tổng Mặt Hàng Nhập</div>
            <div class="text-2xl font-black text-slate-800 mt-1">{{ number_format($totalItemTypes) }}</div>
            <div class="text-xs text-slate-500 mt-1">dòng hàng / nguyên liệu</div>
        </div>
    </div>

    <!-- Bộ lọc tìm kiếm & thời gian -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-4 mb-6">
        <form action="{{ route('admin.import-goods.index') }}" method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[220px]">
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">Tìm kiếm</label>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" 
                           name="search" 
                           class="w-full pl-9 pr-3 py-2 rounded-lg border border-slate-300 focus:border-[#F47716] focus:ring-2 focus:ring-orange-400/20 outline-none text-sm bg-white" 
                           placeholder="Mã phiếu, tên NCC, tên hàng hoá..." 
                           value="{{ request('search') }}">
                </div>
            </div>
            
            <div class="w-32">
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">Tháng</label>
                <select name="month" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-[#F47716] outline-none text-sm bg-white">
                    <option value="">Tất cả</option>
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>Tháng {{ $m }}</option>
                    @endfor
                </select>
            </div>

            <div class="w-32">
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">Năm</label>
                <select name="year" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-[#F47716] outline-none text-sm bg-white">
                    <option value="">Tất cả</option>
                    @for($y = date('Y'); $y >= 2024; $y--)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>Năm {{ $y }}</option>
                    @endfor
                </select>
            </div>
            
            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-[#F47716] text-white rounded-lg text-sm font-semibold transition-colors flex items-center gap-2">
                    <i class="fa-solid fa-filter text-xs"></i>
                    <span>Lọc phiếu</span>
                </button>
                @if(request()->anyFilled(['search', 'month', 'year']))
                    <a href="{{ route('admin.import-goods.index') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-sm font-medium transition-colors">
                        Đặt lại
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Bảng Danh Sách Phiếu Nhập Kho -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-800 text-base">Danh sách Phiếu nhập kho</h3>
            <span class="text-xs text-slate-500 font-medium">Hiển thị {{ $receipts->count() }} / {{ $receipts->total() }} phiếu</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-600 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3.5">Mã Phiếu</th>
                        <th class="px-4 py-3.5">Ngày Nhập Kho</th>
                        <th class="px-4 py-3.5">Nhà Cung Cấp</th>
                        <th class="px-4 py-3.5">Người Lập</th>
                        <th class="px-4 py-3.5 text-center">Số Mặt Hàng</th>
                        <th class="px-4 py-3.5 text-right">Tổng Tiền Phiếu</th>
                        <th class="px-4 py-3.5 text-center">Trạng Thái</th>
                        <th class="px-4 py-3.5 text-center">Thao Tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($receipts as $receipt)
                    <tr class="hover:bg-orange-50/40 transition-colors">
                        <!-- Mã phiếu (Thuần typography) -->
                        <td class="px-4 py-3.5 whitespace-nowrap font-mono font-bold text-slate-800">
                            <a href="{{ route('admin.import-goods.show', $receipt->id) }}" 
                               class="text-[#F47716] hover:underline"
                               title="Xem chi tiết phiếu nhập">
                                {{ $receipt->code }}
                            </a>
                        </td>

                        <!-- Ngày nhập -->
                        <td class="px-4 py-3.5 whitespace-nowrap text-slate-600 text-xs sm:text-sm">
                            <div class="font-medium text-slate-800">{{ $receipt->import_date ? $receipt->import_date->format('d/m/Y') : '---' }}</div>
                            <div class="text-xs text-slate-400">{{ $receipt->import_date ? $receipt->import_date->format('H:i') : '' }}</div>
                        </td>

                        <!-- Nhà cung cấp -->
                        <td class="px-4 py-3.5 text-slate-800">
                            <div class="font-semibold">{{ $receipt->supplier_name }}</div>
                            @if(!empty($receipt->supplier_phone))
                                <div class="text-xs text-slate-500 font-mono">{{ $receipt->supplier_phone }}</div>
                            @endif
                        </td>

                        <!-- Người lập -->
                        <td class="px-4 py-3.5 whitespace-nowrap text-slate-600">
                            <span class="inline-block text-xs font-medium text-slate-700 bg-slate-100 px-2.5 py-1 rounded">
                                {{ $receipt->creator_name ?? ($receipt->creator->fullname ?? 'Thủ kho') }}
                            </span>
                        </td>

                        <!-- Số lượng mặt hàng -->
                        <td class="px-4 py-3.5 whitespace-nowrap text-center">
                            <span class="inline-block font-bold text-xs bg-orange-100 text-[#F47716] px-2.5 py-1 rounded-full">
                                {{ $receipt->items->count() }} mặt hàng
                            </span>
                        </td>

                        <!-- Tổng tiền -->
                        <td class="px-4 py-3.5 whitespace-nowrap text-right font-bold text-base text-[#F47716]">
                            {{ number_format($receipt->total_amount, 0, ',', '.') }}₫
                        </td>

                        <!-- Trạng thái -->
                        <td class="px-4 py-3.5 whitespace-nowrap text-center text-xs text-[rgba(0,0,0,0.85)] font-medium">
                            Đã nhập kho
                        </td>

                        <!-- Thao Tác (Có icon chuẩn chức năng) -->
                        <td class="px-4 py-3.5 whitespace-nowrap text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('admin.import-goods.show', $receipt->id) }}" 
                                   class="inline-flex items-center justify-center w-8 h-8 bg-blue-50 text-blue-600 rounded-lg text-sm transition-all hover:bg-blue-100 shadow-2xs"
                                   title="Xem chi tiết phiếu nhập">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.import-goods.print', $receipt->id) }}" target="_blank"
                                   class="inline-flex items-center justify-center w-8 h-8 bg-emerald-50 text-emerald-600 rounded-lg text-sm transition-all hover:bg-emerald-100 shadow-2xs"
                                   title="In phiếu nhập kho A4">
                                    <i class="fa-solid fa-print"></i>
                                </a>
                                <a href="{{ route('admin.import-goods.edit', $receipt->id) }}" 
                                   class="inline-flex items-center justify-center w-8 h-8 bg-orange-50 text-orange-600 rounded-lg text-sm transition-all hover:bg-orange-100 shadow-2xs"
                                   title="Chỉnh sửa phiếu nhập">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('admin.import-goods.destroy', $receipt->id) }}" method="POST" class="inline" 
                                      onsubmit="return confirm('Bạn có chắc chắn muốn xóa phiếu nhập #{{ $receipt->code }}? Toàn bộ các dòng mặt hàng thuộc phiếu này sẽ bị xóa.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="inline-flex items-center justify-center w-8 h-8 bg-red-50 text-red-600 rounded-lg text-sm transition-all hover:bg-red-100 cursor-pointer shadow-2xs"
                                            title="Xóa phiếu nhập">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-slate-400">
                            <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mx-auto text-slate-400 text-xl mb-2">
                                <i class="fa-solid fa-file-circle-xmark"></i>
                            </div>
                            <div class="font-semibold text-slate-700 text-base">Chưa có phiếu nhập kho nào</div>
                            <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Bấm nút "Lập Phiếu Nhập Mới" ở góc phải để tạo phiếu nhập kho đầu tiên cho nhà hàng.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($receipts->hasPages())
            <div class="px-5 py-3.5 border-t border-slate-200 flex justify-center">
                {{ $receipts->links() }}
            </div>
        @endif
    </div>
</x-layout-backend>
