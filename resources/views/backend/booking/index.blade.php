<x-layout-backend>

    <!-- Page Header (Ant Design Pro Style) -->
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-lg font-bold text-[rgba(0,0,0,0.85)] tracking-tight">Quản lý Đặt bàn</h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.table-order.index') }}" class="ant-btn ant-btn-primary ant-btn-sm">
                <span>Xem sơ đồ bàn</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar (Shopify Polaris IndexFilters Style) -->
    <div class="ant-card p-3.5 mb-4 shadow-sm">
        <form method="GET" class="flex flex-wrap gap-3 items-center">
            <div class="flex items-center gap-2">
                <label for="status" class="text-xs font-semibold text-[rgba(0,0,0,0.65)] uppercase">Trạng thái đặt chỗ:</label>
                <select name="status" id="status" class="polaris-select text-xs min-w-[200px]" onchange="this.form.submit()">
                    <option value="5" {{ $statusFilter == 5 ? 'selected' : '' }}>Tất cả trạng thái</option>
                    <option value="1" {{ $statusFilter == 1 ? 'selected' : '' }}>Đang chờ xử lý</option>
                    <option value="2" {{ $statusFilter == 2 ? 'selected' : '' }}>Đã xác nhận</option>
                    <option value="3" {{ $statusFilter == 3 ? 'selected' : '' }}>Đã hoàn thành</option>
                    <option value="4" {{ $statusFilter == 4 ? 'selected' : '' }}>Đã hủy</option>
                </select>
            </div>
            @if($statusFilter != 5)
                <a href="{{ url('admin/booking') }}" class="ant-btn ant-btn-sm">
                    <span>Xóa lọc</span>
                </a>
            @endif
        </form>
    </div>

    <!-- Data Table (Shopify Polaris / IBM Carbon / Ant Design Pro Style) -->
    <div class="ant-card shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr>
                        <th class="w-12 text-center">ID</th>
                        <th>Khách hàng</th>
                        <th>Điện thoại</th>
                        <th>Email</th>
                        <th class="text-center">Số khách</th>
                        <th>Ngày hẹn</th>
                        <th>Giờ hẹn</th>
                        <th>TRẠNG THÁI</th>
                        <th class="text-center w-28">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bookings as $index => $booking)
                        <tr>
                            <td class="text-center font-mono text-xs text-[rgba(0,0,0,0.45)]">
                                #{{ $booking->id }}
                            </td>
                            <td>
                                <div class="font-semibold text-xs text-[rgba(0,0,0,0.85)]">{{ $booking->name }}</div>
                            </td>
                            <td class="font-mono text-xs text-[rgba(0,0,0,0.65)]">{{ $booking->phone }}</td>
                            <td class="text-xs text-[rgba(0,0,0,0.45)]">{{ $booking->email ?? '---' }}</td>
                            <td class="text-center font-mono text-xs font-semibold text-[rgba(0,0,0,0.85)]">
                                {{ $booking->number_of_people ?? 2 }} khách
                            </td>
                            <td class="font-mono text-xs text-[rgba(0,0,0,0.65)]">
                                {{ date('d/m/Y', strtotime($booking->booking_date)) }}
                            </td>
                            <td class="font-mono text-xs font-semibold text-[rgba(0,0,0,0.85)]">
                                {{ date('H:i', strtotime($booking->booking_time)) }}
                            </td>
                            <td class="whitespace-nowrap text-xs text-[rgba(0,0,0,0.65)]">
                                @switch($booking->status)
                                    @case(1) Chờ xử lý @break
                                    @case(2) Đã xác nhận @break
                                    @case(3) Hoàn tất @break
                                    @case(4) Đã hủy @break
                                    @default Mới
                                @endswitch
                            </td>
                            <td class="text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.booking.detail', $booking->id) }}" class="text-[#F47716] hover:underline text-xs font-medium" title="Xem chi tiết">
                                        Chi tiết
                                    </a>
                                    <span class="text-gray-300">|</span>
                                    <a href="{{ route('admin.booking.edit', $booking->id) }}" class="text-gray-500 hover:text-gray-800 text-xs font-medium" title="Cập nhật trạng thái">
                                        Sửa
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-8 text-center text-xs text-[rgba(0,0,0,0.45)]">Không có yêu cầu đặt bàn nào phù hợp</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-3 border-t border-[#F0F0F0] bg-[#FAFAFA] flex justify-center">
            {{ $bookings->appends(request()->query())->links() }}
        </div>
    </div>
</x-layout-backend>
