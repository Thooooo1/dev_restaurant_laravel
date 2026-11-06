<x-layout-backend>

    <!-- Page Header (Ant Design Pro Style) -->
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-lg font-bold text-[rgba(0,0,0,0.85)] tracking-tight">Lịch sử Gọi món & Doanh thu</h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.dashboard') }}" class="ant-btn ant-btn-sm">
                <i class="fa-solid fa-chart-pie text-xs"></i>
                <span>Xem báo cáo tổng quan</span>
            </a>
        </div>
    </div>

    <!-- Stats Card (Ant Design Pro / IBM Carbon Style - Phẳng, thanh lịch, không gradient) -->
    <div class="ant-card p-4 mb-4 shadow-sm">
        <div>
            <p class="text-xs text-[rgba(0,0,0,0.45)] font-semibold uppercase tracking-wider">Tổng doanh thu thực tế (theo bộ lọc)</p>
            <p class="text-2xl font-bold font-mono text-[rgba(0,0,0,0.85)] mt-1">
                {{ number_format($totalRevenue, 0, ',', '.') }}
                <span class="text-sm font-normal text-[rgba(0,0,0,0.45)]">₫</span>
            </p>
        </div>
    </div>

    <!-- Filters (Shopify Polaris IndexFilters Style) -->
    <div class="ant-card p-3.5 mb-4 shadow-sm">
        <form action="{{ route('admin.order-history.index') }}" method="GET" class="flex flex-wrap gap-3 items-center">
            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-[rgba(0,0,0,0.65)] uppercase">Năm:</label>
                <select name="year" class="polaris-select text-xs min-w-[120px]" onchange="this.form.submit()">
                    @for($y = 2024; $y <= 2030; $y++)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>Năm {{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-[rgba(0,0,0,0.65)] uppercase">Quý:</label>
                <select name="quarter" class="polaris-select text-xs min-w-[120px]" onchange="this.form.submit()">
                    <option value="">-- Tất cả quý --</option>
                    <option value="1" {{ $quarter == 1 ? 'selected' : '' }}>Quý 1</option>
                    <option value="2" {{ $quarter == 2 ? 'selected' : '' }}>Quý 2</option>
                    <option value="3" {{ $quarter == 3 ? 'selected' : '' }}>Quý 3</option>
                    <option value="4" {{ $quarter == 4 ? 'selected' : '' }}>Quý 4</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-[rgba(0,0,0,0.65)] uppercase">Tháng:</label>
                <select name="month" class="polaris-select text-xs min-w-[130px]" onchange="this.form.submit()">
                    <option value="">-- Tất cả tháng --</option>
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>Tháng {{ $m }}</option>
                    @endfor
                </select>
            </div>

            @if($quarter || $month)
                <a href="{{ route('admin.order-history.index', ['year' => $year]) }}" class="ant-btn ant-btn-sm">
                    <span>Xóa lọc</span>
                </a>
            @endif
        </form>
    </div>

    <!-- Orders Table (Shopify Polaris Table Style) -->
    <div class="ant-card shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr>
                        <th class="w-16 text-center">MÃ HĐ</th>
                        <th>Thời gian</th>
                        <th>Khách hàng</th>
                        <th>Điện thoại</th>
                        <th class="text-right">Tổng tiền</th>
                        <th class="text-center">Phương thức</th>
                        <th class="text-center w-24">Chi tiết</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td class="text-center font-mono font-semibold text-xs text-[rgba(0,0,0,0.85)]">
                                #{{ $order->id }}
                            </td>
                            <td class="font-mono text-xs text-[rgba(0,0,0,0.65)]">
                                {{ $order->created_at->format('H:i d/m/Y') }}
                            </td>
                            <td>
                                <div class="font-semibold text-xs text-[rgba(0,0,0,0.85)]">{{ $order->customer_name }}</div>
                            </td>
                            <td class="font-mono text-xs text-[rgba(0,0,0,0.65)]">
                                {{ $order->customer_phone }}
                            </td>
                            <td class="text-right font-mono font-semibold text-xs text-[rgba(0,0,0,0.85)]">
                                {{ number_format($order->total_price, 0, ',', '.') }} ₫
                            </td>
                            <td class="text-center">
                                @if($order->payment_method == 1)
                                    <span class="polaris-badge polaris-badge-default text-[11px]">Tiền mặt</span>
                                @elseif($order->payment_method == 2)
                                    <span class="polaris-badge polaris-badge-processing text-[11px]">Chuyển khoản</span>
                                @else
                                    <span class="polaris-badge polaris-badge-default text-[11px]">Khác</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <button type="button" class="text-gray-400 hover:text-blue-600 transition-colors p-1" onclick="openOrderDetail({{ $order->id }})" title="Xem hóa đơn">
                                    <i class="fa-regular fa-eye text-xs"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-xs text-[rgba(0,0,0,0.45)]">Không có đơn hàng nào trong khoảng thời gian này</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-3 border-t border-[#F0F0F0] bg-[#FAFAFA] flex justify-center">
            {{ $orders->links() }}
        </div>
    </div>

    <!-- Modal Chi tiết đơn hàng -->
    <div class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4" id="orderDetailModal">
        <div class="bg-white rounded-xl shadow-xl max-w-3xl w-full max-h-[90vh] overflow-auto">
            <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
                <h3 class="text-base font-semibold text-slate-800">Chi tiết đơn hàng</h3>
                <button onclick="closeModal('orderDetailModal')" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-slate-100 transition-colors">
                    <i class="fa-solid fa-times text-slate-400 text-sm"></i>
                </button>
            </div>
            
            <div class="p-4" id="modalContent">
                <div class="text-center py-6">
                    <i class="fa-solid fa-spinner fa-spin text-slate-400 text-xl mb-2"></i>
                    <p class="text-slate-500 text-sm">Đang tải...</p>
                </div>
            </div>
            
            <div class="px-4 py-3 border-t border-slate-200 flex justify-end">
                <button onclick="closeModal('orderDetailModal')" class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 font-medium hover:bg-slate-200 transition-colors text-sm">
                    Đóng
                </button>
            </div>
        </div>
    </div>

    <script>
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        
        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        
        function openOrderDetail(orderId) {
            $('#modalContent').html(`
                <div class="text-center py-6">
                    <i class="fa-solid fa-spinner fa-spin text-slate-400 text-xl mb-2"></i>
                    <p class="text-slate-500 text-sm">Đang tải...</p>
                </div>
            `);
            openModal('orderDetailModal');
            
            $.ajax({
                url: '/admin/order-history/' + orderId,
                type: 'GET',
                success: function(response) {
                    $('#modalContent').html(response.html);
                },
                error: function() {
                    $('#modalContent').html(`
                        <div class="text-center py-6">
                            <i class="fa-solid fa-exclamation-circle text-red-400 text-xl mb-2"></i>
                            <p class="text-red-500 text-sm">Không thể tải thông tin đơn hàng.</p>
                        </div>
                    `);
                }
            });
        }
    </script>
</x-layout-backend>
