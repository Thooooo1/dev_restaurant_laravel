<x-layout-backend>
    <style>
        table thead th {
            font-size: 14px !important;
            font-weight: 600 !important;
            background: #78b4f1ff !important;
            padding-top: 0.75rem !important;
            padding-bottom: 0.75rem !important;
        }
    </style>
    <!-- Page header -->
    <div class="mb-4">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-800 flex items-center gap-3">
                    <span class="w-8 h-8 bg-gradient-to-br from-orange-400 to-orange-600 rounded-lg flex items-center justify-center text-white">
                        <i class="fa-solid fa-file-import text-sm"></i>
                    </span>
                    Quản lý nhập hàng
                </h1>
                <p class="text-slate-500 mt-1 text-sm">Danh sách và quản lý các đơn nhập hàng</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.import-goods.create') }}" class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg text-sm font-medium transition-all shadow-lg shadow-green-500/25 hover:from-green-600 hover:to-green-700">
                    <i class="fa-solid fa-plus"></i> Thêm mới
                </a>
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-3 mb-4">
        <form action="{{ route('admin.import-goods.index') }}" method="GET" class="flex flex-wrap gap-3 items-end">
            <label for="search" class="block text-sm font-medium text-slate-700">Tìm kiếm:</label>
            <div class="flex-1 min-w-[150px]">
                <input type="text" name="search" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-400 focus:ring-2 focus:ring-orange-400/20 transition-all outline-none bg-white text-sm" placeholder="Tìm tên hàng hoá..." value="{{ request('search') }}">
            </div>
            
            <div class="min-w-[120px]">
                <select name="year" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-400 focus:ring-2 focus:ring-orange-400/20 transition-all outline-none bg-white text-sm">
                    <option value="">-- Năm --</option>
                    @for($y = date('Y'); $y >= 2020; $y--)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            
            <div class="min-w-[120px]">
                <select name="month" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-400 focus:ring-2 focus:ring-orange-400/20 transition-all outline-none bg-white text-sm">
                    <option value="">-- Tháng --</option>
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>Tháng {{ $m }}</option>
                    @endfor
                </select>
            </div>
            
            <button type="submit" class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg text-sm font-medium transition-all shadow-lg shadow-orange-500/25 hover:from-orange-600 hover:to-orange-700">
                <i class="fa-solid fa-search"></i> Tìm kiếm
            </button>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <div class="table-responsive"><table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-500 uppercase tracking-wider">ID</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-500 uppercase tracking-wider">Tên hàng hoá</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-500 uppercase tracking-wider">Loại hàng hoá</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-500 uppercase tracking-wider">Đơn vị tính</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-500 uppercase tracking-wider">Số lượng</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-500 uppercase tracking-wider">Đơn giá</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-500 uppercase tracking-wider">Thành tiền</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-500 uppercase tracking-wider">Ngày nhập</th>
                        <th class="px-4 py-3 text-center text-sm font-semibold text-slate-500 uppercase tracking-wider">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($items as $item)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600">{{ $item->id }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-800 font-medium">{{ $item->name }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600">{{ $item->type }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600">{{ $item->unit }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600">{{ number_format($item->quantity) }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600">{{ number_format($item->price) }} VNĐ</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-orange-600 font-medium">{{ number_format($item->total_amount) }} VNĐ</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600">{{ $item->created_at->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-center">
                            <a href="{{ route('admin.import-goods.edit', $item->id) }}" class="inline-flex items-center justify-center w-8 h-8 bg-orange-50 text-orange-600 rounded-lg text-sm transition-all hover:bg-orange-100">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            <form action="{{ route('admin.import-goods.destroy', $item->id) }}" method="POST" class="inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center justify-center w-8 h-8 bg-red-50 text-red-600 rounded-lg text-sm transition-all hover:bg-red-100 ml-1">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-slate-500 text-sm">
                            Không có dữ liệu
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
            
        <div class="px-4 py-3 border-t border-slate-200 flex justify-center">
            {{ $items->withQueryString()->links() }}
        </div>
    </div>
</x-layout-backend>
