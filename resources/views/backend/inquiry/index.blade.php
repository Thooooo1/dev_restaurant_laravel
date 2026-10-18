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
                        <i class="fa-solid fa-comment-dots text-sm"></i>
                    </span>
                    Quản lý yêu cầu
                </h1>
                <p class="text-slate-500 mt-1 text-sm">Danh sách và quản lý các yêu cầu từ khách hàng</p>
            </div>
        </div>
    </div>
    
    <!-- Filter -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-3 mb-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <label for="status" class="block text-sm font-medium text-slate-700">Lọc theo trạng thái:</label>
            <select name="status" id="status" class="px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-400 focus:ring-2 focus:ring-orange-400/20 transition-all outline-none bg-white text-sm">
                <option value="0" {{ $statusFilter == 0 ? 'selected' : '' }}>Tất cả</option>
                <option value="1" {{ $statusFilter == 1 ? 'selected' : '' }}>Chờ xử lý</option>
                <option value="2" {{ $statusFilter == 2 ? 'selected' : '' }}>Đã liên hệ</option>
                <option value="3" {{ $statusFilter == 3 ? 'selected' : '' }}>Hoàn thành</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg text-sm font-medium transition-all shadow-lg shadow-orange-500/25 hover:from-orange-600 hover:to-orange-700">
                <i class="fa-solid fa-filter mr-1"></i> Lọc
            </button>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <div class="table-responsive"><table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-500 uppercase tracking-wider">#</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-500 uppercase tracking-wider">Họ tên</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-500 uppercase tracking-wider">Điện thoại</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-500 uppercase tracking-wider">Nội dung</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-500 uppercase tracking-wider">Ngày gửi</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-500 uppercase tracking-wider">Trạng thái</th>
                        <th class="px-4 py-3 text-center text-sm font-semibold text-slate-500 uppercase tracking-wider">Chức năng</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($inquiries as $index => $inquiry)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600">{{ $inquiries->firstItem() + $index }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-800 font-medium">{{ $inquiry->name }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600">{{ $inquiry->phone }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600 max-w-[200px] truncate">{{ Str::limit($inquiry->message, 50) }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600">{{ date('H:i d/m/Y', strtotime($inquiry->created_at)) }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @switch($inquiry->status)
                                @case(1)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-50 text-amber-600 rounded-full text-xs font-medium">Chờ xử lý</span>
                                    @break
                                @case(2)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-50 text-blue-600 rounded-full text-xs font-medium">Đã liên hệ</span>
                                    @break
                                @case(3)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-green-50 text-green-600 rounded-full text-xs font-medium">Hoàn thành</span>
                                    @break
                            @endswitch
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-center">
                            <a href="{{ route('admin.inquiry.show', $inquiry->id) }}" class="inline-flex items-center justify-center w-8 h-8 bg-blue-50 text-blue-600 rounded-lg text-sm transition-all hover:bg-blue-100">
                                <i class="fa-regular fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
                
        <div class="px-4 py-3 border-t border-slate-200 flex justify-center">
            {{ $inquiries->appends(request()->query())->links() }}
        </div>
    </div>
</x-layout-backend>
