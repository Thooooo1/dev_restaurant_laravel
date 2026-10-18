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
                        <i class="fa-solid fa-calendar-check text-sm"></i>
                    </span>
                    Quản lý đặt bàn
                </h1>
                <p class="text-slate-500 mt-1 text-sm">Danh sách các yêu cầu đặt bàn</p>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-3 mb-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-slate-700 mb-1">Lọc theo:</label>
                <select name="status" id="status" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-400 focus:ring-2 focus:ring-orange-400/20 transition-all outline-none bg-white text-sm" onchange="this.form.submit()">
                    <option value="5" {{ $statusFilter == 5 ? 'selected' : '' }}>Tất cả</option>
                    <option value="1" {{ $statusFilter == 1 ? 'selected' : '' }}>Đang chờ xử lý</option>
                    <option value="2" {{ $statusFilter == 2 ? 'selected' : '' }}>Đã xác nhận</option>
                    <option value="3" {{ $statusFilter == 3 ? 'selected' : '' }}>Đã hoàn thành</option>
                    <option value="4" {{ $statusFilter == 4 ? 'selected' : '' }}>Đã hủy</option>
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
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-500 uppercase tracking-wider">#</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-500 uppercase tracking-wider">Họ tên</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-500 uppercase tracking-wider">Điện thoại</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-500 uppercase tracking-wider">Email</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-500 uppercase tracking-wider">Ngày đặt</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-500 uppercase tracking-wider">Thời gian</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-500 uppercase tracking-wider">Trạng thái</th>
                        <th class="px-4 py-3 text-center text-sm font-semibold text-slate-500 uppercase tracking-wider">Chức năng</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($bookings as $index => $booking)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600">{{ $bookings->firstItem() + $index }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-800 font-medium">{{ $booking->name }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600">{{ $booking->phone }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600">{{ $booking->email }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600">{{ date('d/m/Y', strtotime($booking->booking_date)) }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600">{{ date('H:i', strtotime($booking->booking_time)) }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @switch($booking->status)
                                @case(1)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-50 text-amber-600 rounded-full text-xs font-medium">
                                        Đang chờ xử lý
                                    </span>
                                    @break
                                @case(2)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-50 text-blue-600 rounded-full text-xs font-medium">
                                        Đã xác nhận
                                    </span>
                                    @break
                                @case(3)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-green-50 text-green-600 rounded-full text-xs font-medium">
                                        Đã hoàn thành
                                    </span>
                                    @break
                                @case(4)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-red-50 text-red-600 rounded-full text-xs font-medium">
                                        Đã hủy
                                    </span>
                                    @break
                            @endswitch
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-center">
                            <a href="{{ route('admin.booking.detail', $booking->id) }}" class="inline-flex items-center justify-center w-10 h-10 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-lg transition-colors ml-1">
                                <i class="fa-regular fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.booking.edit', $booking->id) }}" class="inline-flex items-center justify-center w-10 h-10 bg-orange-50 hover:bg-orange-100 text-orange-600 rounded-lg transition-colors ml-1">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3 border-t border-slate-200 flex justify-center">
            {{ $bookings->appends(request()->query())->links() }}
        </div>
    </div>
</x-layout-backend>
