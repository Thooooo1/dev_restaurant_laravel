<x-layout-backend>

    <!-- Page Header (Ant Design Pro Style) -->
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-lg font-bold text-[rgba(0,0,0,0.85)] tracking-tight">Đơn hàng giao</h1>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            @if(Auth::user()->isAdmin())
            <a href="{{ route('admin.order.export', request()->all()) }}" class="ant-btn ant-btn-sm font-medium bg-white border border-gray-200 text-gray-700 hover:bg-gray-50">
                <span>Xuất Excel đơn hàng</span>
            </a>
            @endif
            <a href="{{ route('admin.table-order.index') }}" class="ant-btn ant-btn-primary ant-btn-sm font-bold uppercase tracking-wider">
                <span>+ Tạo đơn tại quầy</span>
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
                        <th>THANH TOÁN</th>
                        <th>Thời gian đặt</th>
                        <th>TRẠNG THÁI</th>
                        <th class="text-center w-28">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $index => $order)
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
                            <td class="whitespace-nowrap text-xs">
                                <div class="font-semibold text-xs text-[rgba(0,0,0,0.85)]">{{ $order->payment_method == 2 || $order->payment_method === 'VietQR' ? 'Chuyển khoản VietQR' : ($order->payment_method == 3 ? 'Quẹt thẻ' : ($order->payment_method === 'Momo' ? 'MoMo' : 'Tiền mặt')) }}</div>
                                <div class="text-xs mt-0.5">
                                    <span class="inline-flex items-center gap-1 font-semibold {{ $order->payment_status === 'paid' || $order->isPaid() ? 'text-emerald-600' : ($order->payment_status === 'failed' ? 'text-rose-600' : ($order->payment_status === 'refunded' ? 'text-purple-600' : 'text-amber-600')) }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $order->payment_status === 'paid' || $order->isPaid() ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                        {{ $order->payment_status_label }}
                                    </span>
                                </div>
                            </td>
                            <td class="font-mono text-xs text-[rgba(0,0,0,0.85)] whitespace-nowrap">
                                {{ date('H:i d/m/Y', strtotime($order->created_at)) }}
                            </td>
                            <td class="whitespace-nowrap text-xs">
                                <div class="font-semibold text-xs text-[rgba(0,0,0,0.85)]">
                                    @switch((int)$order->status)
                                        @case(1)
                                            Đang chuẩn bị / vận chuyển
                                            @break
                                        @case(2)
                                            Đã giao hàng
                                            @break
                                        @case(3)
                                            Đã hủy
                                            @break
                                        @default
                                            Đang chờ xử lý
                                    @endswitch
                                </div>

                                @if(empty($order->table_id))
                                    <div class="text-xs text-[rgba(0,0,0,0.65)] mt-0.5">
                                        <span>Giao: {{ $order->delivery_status_label }}</span>
                                        @if($order->shipper_name)
                                            <span class="font-medium text-[rgba(0,0,0,0.85)]">({{ $order->shipper_name }})</span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('admin.order.orderdetail', $order->id) }}" class="w-7 h-7 rounded border border-gray-200 bg-white hover:bg-gray-100 text-gray-600 flex items-center justify-center text-xs transition-colors" title="Chi tiết đơn hàng">
                                        <i class="fa-regular fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.order.edit', $order->id) }}" class="w-7 h-7 rounded border border-gray-200 bg-white hover:bg-gray-100 text-gray-600 flex items-center justify-center text-xs transition-colors" title="Cập nhật trạng thái">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-xs text-[rgba(0,0,0,0.45)]">Không có đơn hàng nào phù hợp với bộ lọc</td>
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
