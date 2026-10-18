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
                        <i class="fa-solid fa-chair text-sm"></i>
                    </span>
                    Quản lý bàn ăn
                </h1>
                <p class="text-slate-500 mt-1 text-sm">Danh sách và quản lý các bàn ăn trong nhà hàng</p>
            </div>
            <div class="flex items-center gap-2">
                <a class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg text-sm font-medium transition-all shadow-lg shadow-green-500/25 hover:from-green-600 hover:to-green-700" href="{{ route('admin.table.create') }}">
                    <i class="fa-solid fa-plus"></i> Thêm bàn
                </a>
                <a class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-lg text-sm font-medium transition-all shadow-lg shadow-red-500/25 hover:from-red-600 hover:to-red-700" href="{{ route('admin.table.trash') }}">
                    <i class="fa-solid fa-trash"></i> Thùng rác
                </a>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-3 mb-4">
        <form method="GET" action="{{ route('admin.table.index') }}" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-slate-700 mb-1">Lọc theo tầng:</label>
                <select name="floor_id" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-400 focus:ring-2 focus:ring-orange-400/20 transition-all outline-none bg-white text-sm" onchange="this.form.submit()">
                    <option value="">-- Tất cả tầng --</option>
                    @foreach ($floors as $floor)
                        <option value="{{ $floor->id }}" {{ request('floor_id') == $floor->id ? 'selected' : '' }}>
                            {{ $floor->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <div class="table-responsive"><table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 text-center text-sm font-semibold text-slate-500 uppercase tracking-wider" style="width:30px;">#</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-500 uppercase tracking-wider">Tên bàn</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-500 uppercase tracking-wider">Tầng</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-500 uppercase tracking-wider">Số chỗ ngồi</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-500 uppercase tracking-wider">Thứ tự</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-500 uppercase tracking-wider">Trạng thái</th>
                        <th class="px-4 py-3 text-center text-sm font-semibold text-slate-500 uppercase tracking-wider" style="width:200px;">Chức năng</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($tables as $table)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3 text-center">
                                <input type="checkbox" name="checkId[]" value="{{ $table->id }}" class="rounded border-slate-300 text-orange-500 focus:ring-orange-500">
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-800 font-medium">{{ $table->name }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600">{{ $table->floor->name ?? 'Không có' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600">{{ $table->seats }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600">{{ $table->sort_order }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if ($table->status == 1)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-green-50 text-green-600 rounded-full text-xs font-medium">
                                        Hoạt động
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-100 text-slate-600 rounded-full text-xs font-medium">
                                        Ẩn
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                @if ($table->status == 1)
                                    <a href="{{ route('admin.table.status', $table->id) }}" class="inline-flex items-center justify-center w-8 h-8 bg-green-50 text-green-600 rounded-lg text-sm transition-all hover:bg-green-100">
                                        <i class="fa-solid fa-toggle-on"></i>
                                    </a>
                                @else
                                    <a href="{{ route('admin.table.status', $table->id) }}" class="inline-flex items-center justify-center w-8 h-8 bg-slate-100 text-slate-600 rounded-lg text-sm transition-all hover:bg-slate-200">
                                        <i class="fa-solid fa-toggle-off"></i>
                                    </a>
                                @endif
                                <a href="{{ route('admin.table.qr', $table->id) }}" class="inline-flex items-center justify-center w-8 h-8 bg-blue-50 text-blue-600 rounded-lg text-sm transition-all hover:bg-blue-100 ml-1" title="Mã QR">
                                    <i class="fa-solid fa-qrcode"></i>
                                </a>
                                <a href="{{ route('admin.table.edit', $table->id) }}" class="inline-flex items-center justify-center w-8 h-8 bg-orange-50 text-orange-600 rounded-lg text-sm transition-all hover:bg-orange-100 ml-1">
                                    <i class="fa-regular fa-pen-to-square"></i>
                                </a>
                                <a href="{{ route('admin.table.delete', $table->id) }}" class="inline-flex items-center justify-center w-8 h-8 bg-red-50 text-red-600 rounded-lg text-sm transition-all hover:bg-red-100 ml-1">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3 border-t border-slate-200 flex justify-center">
            {{ $tables->links() }}
        </div>
    </div>
</x-layout-backend>
