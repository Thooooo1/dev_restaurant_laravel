<x-layout-backend>

    <!-- Page Header (Ant Design Pro Style) -->
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-lg font-bold text-[rgba(0,0,0,0.85)] tracking-tight">Đơn hàng Giao tận nơi & Mang đi</h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.table-order.index') }}" class="ant-btn ant-btn-primary ant-btn-sm">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Tạo đơn tại quầy</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar (Shopify Polaris IndexFilters Style) -->
    <div class="ant-card p-3.5 mb-4 shadow-sm">
        <form method="GET" class="flex flex-wrap gap-3 items-center">
            <div class="flex items-center gap-2">
                <label for="status" class="text-xs font-semibold text-[rgba(0,0,0,0.65)] uppercase">Trạng thái vận chuyển:</label>
                <select name="status" id="status" class="polaris-select text-xs min-w-[200px]" onchange="this.form.submit()">
                    <option value="5" {{ $statusFilter == 5 ? 'selected' : '' }}>Tất cả trạng thái</option>
                    <option value="0" {{ $statusFilter == 0 ? 'selected' : '' }}>Đang chờ xử lý</option>
                    <option value="1" {{ $statusFilter == 1 ? 'selected' : '' }}>Đang vận chuyển</option>
                    <option value="2" {{ $statusFilter == 2 ? 'selected' : '' }}>Đã giao hàng</option>
                    <option value="3" {{ $statusFilter == 3 ? 'selected' : '' }}>Đã hủy</option>
                </select>
            </div>
            @if($statusFilter != 5)
                <a href="{{ url('admin/order') }}" class="ant-btn ant-btn-sm">
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
                        <th class="w-16 text-center">MÃ ĐƠN</th>
                        <th>Khách hàng</th>
                        <th>Điện thoại</th>
                        <th>Địa chỉ giao hàng</th>
                        <th class="text-center">Thanh toán</th>
                        <th>Thời gian đặt</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-center w-28">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $index => $order)
                        @php
                            $pm = $order->payment_method_info;
                        @endphp
                        <tr>
                            <td class="text-center font-mono font-semibold text-xs text-[rgba(0,0,0,0.85)]">
                                #{{ $order->id }}
                            </td>
                            <td>
                                <div class="font-semibold text-xs text-[rgba(0,0,0,0.85)]">{{ $order->name }}</div>
                            </td>
                            <td class="font-mono text-xs text-[rgba(0,0,0,0.65)]">{{ $order->phone }}</td>
                            <td class="text-xs text-[rgba(0,0,0,0.65)] max-w-[200px] truncate" title="{{ $order->address }}">
                                {{ $order->address }}
                            </td>
                            <td class="text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold {{ $pm['bg'] }} {{ $pm['text'] }} border {{ $pm['border'] }}">
                                    {{ $pm['name'] }}
                                </span>
                            </td>
                            <td class="font-mono text-xs text-[rgba(0,0,0,0.45)]">
                                {{ date('H:i d/m/Y', strtotime($order->created_at)) }}
                            </td>
                            <td class="text-center">
                                @switch($order->status)
                                    @case(0)
                                        <span class="polaris-badge polaris-badge-warning text-[11px]">Chờ xử lý</span>
                                        @break
                                    @case(1)
                                        <span class="polaris-badge polaris-badge-processing text-[11px]">Đang giao</span>
                                        @break
                                    @case(2)
                                        <span class="polaris-badge polaris-badge-success text-[11px]">Đã giao hàng</span>
                                        @break
                                    @case(3)
                                        <span class="polaris-badge polaris-badge-error text-[11px]">Đã hủy</span>
                                        @break
                                    @default
                                        <span class="polaris-badge polaris-badge-default text-[11px]">Mới</span>
                                @endswitch
                            </td>
                            <td class="text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('admin.order.orderdetail', $order->id) }}" class="text-gray-400 hover:text-blue-600 transition-colors p-1" title="Chi tiết đơn hàng">
                                        <i class="fa-regular fa-eye text-xs"></i>
                                    </a>
                                    <a href="{{ route('admin.order.edit', $order->id) }}" class="text-gray-400 hover:text-[#F47716] transition-colors p-1" title="Cập nhật trạng thái">
                                        <i class="fa-regular fa-pen-to-square text-xs"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-xs text-[rgba(0,0,0,0.45)]">Không có đơn hàng nào phù hợp với bộ lọc</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="px-5 py-3 border-t border-[#F0F0F0] bg-[#FAFAFA] flex justify-center">
            {{ $orders->appends(request()->query())->links() }}
        </div>
    </div>
</x-layout-backend>
