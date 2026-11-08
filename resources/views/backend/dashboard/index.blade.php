<x-layout-backend :no-padding="true">
    <!-- Ant Design Pro PageContainer Header (Thanh tiêu đề trang chuẩn pro.ant.design) -->
    <div class="bg-white border-b border-[#F0F0F0] px-6 sm:px-8 py-5 shadow-2xs">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-xl font-semibold text-[rgba(0,0,0,0.85)] tracking-tight">Tổng quan</h1>
            </div>
            
            <div class="flex items-center gap-3">
                <!-- Ant Design Segmented Control (Radio Button style) -->
                <div class="ant-segmented">
                    <a href="{{ route('admin.dashboard', ['type' => 'today']) }}" 
                       class="ant-segmented-item {{ $type === 'today' ? 'ant-segmented-item-active' : '' }}">
                        Hôm nay
                    </a>
                    <a href="{{ route('admin.dashboard', ['type' => '7day']) }}" 
                       class="ant-segmented-item {{ $type === '7day' ? 'ant-segmented-item-active' : '' }}">
                        7 ngày qua
                    </a>
                    <a href="{{ route('admin.dashboard', ['type' => '30day']) }}" 
                       class="ant-segmented-item {{ $type === '30day' ? 'ant-segmented-item-active' : '' }}">
                        30 ngày qua
                    </a>
                    <a href="{{ route('admin.dashboard', ['type' => '12month']) }}" 
                       class="ant-segmented-item {{ $type === '12month' ? 'ant-segmented-item-active' : '' }}">
                        12 tháng qua
                    </a>
                </div>

                <button type="button" onclick="window.location.reload()" class="ant-btn ant-btn-sm font-medium bg-white border border-gray-200 text-gray-700 hover:bg-gray-50" title="Tải lại số liệu mới nhất">
                    <span>Làm mới</span>
                </button>
                <a href="{{ route('admin.order-history.export') }}" class="ant-btn ant-btn-sm font-medium bg-white border border-gray-200 text-gray-700 hover:bg-gray-50">
                    <span>Xuất Excel doanh thu</span>
                </a>
                <a href="{{ route('admin.dashboard.exportBestSelling', ['type' => $type ?? 'today']) }}" class="ant-btn ant-btn-sm font-medium bg-white border border-gray-200 text-gray-700 hover:bg-gray-50">
                    <span>Xuất Excel món bán chạy</span>
                </a>

                <!-- Ant Design Primary Button: + Tạo đơn mới -->
                <a href="{{ route('admin.table-order.index') }}" class="ant-btn ant-btn-primary ant-btn-sm font-bold uppercase tracking-wider">
                    <span>+ Tạo đơn mới</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Ant Design Pro Page Content Canvas (Nền #F0F2F5, padding 24px - 32px rộng rãi) -->
    <div class="p-6 sm:p-8 space-y-6">
        <!-- 4 Ant Design Pro Statistic Cards (ProCard / StatisticCard) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- CARD 1: DOANH THU -->
            <div class="ant-card p-5">
                <div class="flex items-center justify-between text-xs text-[rgba(0,0,0,0.45)] font-medium mb-1">
                    <span>Tổng doanh thu {{ strtolower($periodLabel) }}</span>
                    @if($revenueGrowth >= 0)
                        <span class="text-[#52C41A] font-semibold text-xs flex items-center gap-1">
                            <span class="text-[10px]">▲</span> +{{ number_format($revenueGrowth, 1) }}%
                        </span>
                    @else
                        <span class="text-[#FF4D4F] font-semibold text-xs flex items-center gap-1">
                            <span class="text-[10px]">▼</span> {{ number_format($revenueGrowth, 1) }}%
                        </span>
                    @endif
                </div>
                <div class="text-[28px] font-semibold text-[rgba(0,0,0,0.85)] tracking-tight font-mono leading-tight my-1">
                    {{ number_format($revenue, 0, ',', '.') }}
                    <span class="text-sm font-normal text-[rgba(0,0,0,0.45)] ml-0.5">₫</span>
                </div>
                <div class="mt-3 pt-2.5 border-t border-[#F0F0F0] text-xs text-[rgba(0,0,0,0.65)] flex items-center justify-between font-normal">
                    <span>Tiền mặt: <strong class="text-[rgba(0,0,0,0.85)] font-semibold">{{ number_format($cashRevenue, 0, ',', '.') }} ₫</strong></span>
                    <span class="text-gray-300">|</span>
                    <span>CK: <strong class="text-[rgba(0,0,0,0.85)] font-semibold">{{ number_format($transferRevenue, 0, ',', '.') }} ₫</strong></span>
                </div>
            </div>

            <!-- CARD 2: ĐƠN HÀNG -->
            <div class="ant-card p-5">
                <div class="flex items-center justify-between text-xs text-[rgba(0,0,0,0.45)] font-medium mb-1">
                    <span>Số đơn hàng {{ strtolower($periodLabel) }}</span>
                    @if($orderGrowth >= 0)
                        <span class="text-[#52C41A] font-semibold text-xs flex items-center gap-1">
                            <span class="text-[10px]">▲</span> +{{ number_format($orderGrowth, 1) }}%
                        </span>
                    @else
                        <span class="text-[#FF4D4F] font-semibold text-xs flex items-center gap-1">
                            <span class="text-[10px]">▼</span> {{ number_format($orderGrowth, 1) }}%
                        </span>
                    @endif
                </div>
                <div class="text-[28px] font-semibold text-[rgba(0,0,0,0.85)] tracking-tight font-mono leading-tight my-1">
                    {{ number_format($orderCount) }}
                    <span class="text-sm font-normal text-[rgba(0,0,0,0.45)] ml-0.5">đơn</span>
                </div>
                <div class="mt-3 pt-2.5 border-t border-[#F0F0F0] text-xs text-[rgba(0,0,0,0.65)] flex items-center justify-between font-normal">
                    <span>Tại bàn: <strong class="text-[rgba(0,0,0,0.85)] font-semibold">{{ $dineInCount }}</strong></span>
                    <span class="text-gray-300">|</span>
                    <span>Mang về: <strong class="text-[rgba(0,0,0,0.85)] font-semibold">{{ $takeawayCount }}</strong></span>
                </div>
            </div>

            <!-- CARD 3: GIÁ TRỊ TRUNG BÌNH (AOV) -->
            <div class="ant-card p-5">
                <div class="flex items-center justify-between text-xs text-[rgba(0,0,0,0.45)] font-medium mb-1">
                    <span>Giá trị đơn trung bình (AOV)</span>
                    @if($aovGrowth >= 0)
                        <span class="text-[#52C41A] font-semibold text-xs flex items-center gap-1">
                            <span class="text-[10px]">▲</span> +{{ number_format($aovGrowth, 1) }}%
                        </span>
                    @else
                        <span class="text-[#FF4D4F] font-semibold text-xs flex items-center gap-1">
                            <span class="text-[10px]">▼</span> {{ number_format($aovGrowth, 1) }}%
                        </span>
                    @endif
                </div>
                <div class="text-[28px] font-semibold text-[rgba(0,0,0,0.85)] tracking-tight font-mono leading-tight my-1">
                    {{ number_format($avgOrderValue, 0, ',', '.') }}
                    <span class="text-sm font-normal text-[rgba(0,0,0,0.45)] ml-0.5">₫</span>
                </div>
                <div class="mt-3 pt-2.5 border-t border-[#F0F0F0] text-xs text-[rgba(0,0,0,0.65)] flex items-center justify-between font-normal">
                    <span>Khách phục vụ: <strong class="text-[rgba(0,0,0,0.85)] font-semibold">{{ $customersCount }}</strong></span>
                    <span class="text-gray-300">|</span>
                    <span>Bàn: <strong class="text-[rgba(0,0,0,0.85)] font-semibold">{{ $occupiedTables }}/{{ $totalTables }}</strong></span>
                </div>
            </div>

            <!-- CARD 4: LƯỢT ĐẶT BÀN -->
            <div class="ant-card p-5">
                <div class="flex items-center justify-between text-xs text-[rgba(0,0,0,0.45)] font-medium mb-1">
                    <span>Lượt đặt bàn {{ strtolower($periodLabel) }}</span>
                    @if($bookingGrowth >= 0)
                        <span class="text-[#52C41A] font-semibold text-xs flex items-center gap-1">
                            <span class="text-[10px]">▲</span> +{{ number_format($bookingGrowth, 1) }}%
                        </span>
                    @else
                        <span class="text-[#FF4D4F] font-semibold text-xs flex items-center gap-1">
                            <span class="text-[10px]">▼</span> {{ number_format($bookingGrowth, 1) }}%
                        </span>
                    @endif
                </div>
                <div class="text-[28px] font-semibold text-[rgba(0,0,0,0.85)] tracking-tight font-mono leading-tight my-1">
                    {{ number_format($bookingsCount) }}
                    <span class="text-sm font-normal text-[rgba(0,0,0,0.45)] ml-0.5">lượt</span>
                </div>
                <div class="mt-3 pt-2.5 border-t border-[#F0F0F0] text-xs text-[rgba(0,0,0,0.65)] flex items-center justify-between font-normal">
                    <span>Đã xác nhận: <strong class="text-[#52C41A] font-semibold">{{ $confirmedBookings }}</strong></span>
                    <span class="text-gray-300">|</span>
                    <span>Chờ xử lý: <strong class="text-[#FAAD14] font-semibold">{{ $pendingBookings }}</strong></span>
                </div>
            </div>
        </div>

        <!-- Ant Design Chart Card (Biểu đồ doanh thu thực tế phong cách AntV) -->
        <div class="ant-card p-5">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-3 mb-4 border-b border-[#F0F0F0] gap-2">
                <div>
                    <h3 class="font-semibold text-base text-[rgba(0,0,0,0.85)]">Diễn biến doanh thu ({{ $periodLabel }})</h3>
                </div>
                <div class="flex items-center gap-2 text-xs text-[rgba(0,0,0,0.65)] font-medium">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#F47716]"></span>
                    <span>Doanh thu (VNĐ)</span>
                </div>
            </div>
            <div style="height: 260px; position: relative;">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- Ant Design ProTable 1: ĐƠN HÀNG GẦN ĐÂY -->
        <div class="ant-card overflow-hidden">
            <!-- Toolbar tiêu đề -->
            <div class="px-5 py-4 border-b border-[#F0F0F0] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold text-[rgba(0,0,0,0.85)]">Danh sách đơn hàng gần đây</h2>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ url('admin/order') }}" class="ant-btn text-xs">
                        <span>Quản lý đơn hàng</span>
                    </a>
                    <a href="{{ route('admin.table-order.index') }}" class="ant-btn ant-btn-primary text-xs">
                        <span>Tạo đơn mới</span>
                    </a>
                </div>
            </div>

            <!-- Ant Design Filter Bar (Tabs style) -->
            <div class="px-5 py-2.5 border-b border-[#F0F0F0] bg-[#FAFAFA] flex flex-wrap items-center gap-4 text-xs font-medium">
                <span class="text-[#F47716] border-b-2 border-[#F47716] pb-1 cursor-pointer font-semibold">
                    Tất cả ({{ $totalOrdersCount }})
                </span>
                <span class="text-[rgba(0,0,0,0.65)] hover:text-[#F47716] pb-1 cursor-pointer transition-colors flex items-center gap-1.5">
                    <span class="ant-badge-status-dot ant-badge-status-warning"></span>
                    Chờ xác nhận ({{ $pendingOrdersCount }})
                </span>
                <span class="text-[rgba(0,0,0,0.65)] hover:text-[#F47716] pb-1 cursor-pointer transition-colors flex items-center gap-1.5">
                    <span class="ant-badge-status-dot ant-badge-status-success"></span>
                    Đã thanh toán ({{ $paidOrdersCount }})
                </span>
                <span class="text-[rgba(0,0,0,0.65)] hover:text-[#F47716] pb-1 cursor-pointer transition-colors flex items-center gap-1.5">
                    <span class="ant-badge-status-dot ant-badge-status-processing"></span>
                    Hoàn tất ({{ $completedOrdersCount }})
                </span>
                <span class="text-[rgba(0,0,0,0.65)] hover:text-[#F47716] pb-1 cursor-pointer transition-colors flex items-center gap-1.5">
                    <span class="ant-badge-status-dot ant-badge-status-error"></span>
                    Đã hủy ({{ $cancelledOrdersCount }})
                </span>
            </div>

            <!-- Table View -->
            <div class="overflow-x-auto">
                <table>
                    <thead>
                        <tr>
                            <th>MÃ ĐƠN</th>
                            <th>KHÁCH HÀNG</th>
                            <th>HÌNH THỨC</th>
                            <th class="text-right">TỔNG TIỀN</th>
                            <th>TRẠNG THÁI</th>
                            <th>THANH TOÁN</th>
                            <th>THỜI GIAN</th>
                            <th class="text-center">THAO TÁC</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrders as $ro)
                        <tr>
                            <td class="font-mono font-medium text-[rgba(0,0,0,0.85)] whitespace-nowrap text-xs">
                                #{{ $ro->id }}
                            </td>
                            <td class="whitespace-nowrap">
                                <div>
                                    <div class="font-medium text-xs text-[rgba(0,0,0,0.85)]">{{ $ro->customer_name }}</div>
                                    <div class="text-[11px] text-[rgba(0,0,0,0.45)]">{{ $ro->customer_phone }}</div>
                                </div>
                            </td>
                            <td class="text-xs text-[rgba(0,0,0,0.65)] whitespace-nowrap">
                                @if($ro->orderStyle == 1 || !empty($ro->table_id))
                                    <span>Tại bàn {{ $ro->table_id ?? '' }}</span>
                                @else
                                    <span>Giao hàng tận nơi</span>
                                @endif
                            </td>
                            <td class="text-right font-semibold text-xs text-[rgba(0,0,0,0.85)] whitespace-nowrap font-mono">
                                {{ number_format($ro->total_price, 0, ',', '.') }} ₫
                            </td>
                            <td class="whitespace-nowrap text-xs">
                                @if($ro->status == 1)
                                    <span class="text-[#52C41A] font-medium flex items-center">
                                        <span class="ant-badge-status-dot ant-badge-status-success"></span> Đã thanh toán
                                    </span>
                                @elseif($ro->status == 2)
                                    <span class="text-[#1890FF] font-medium flex items-center">
                                        <span class="ant-badge-status-dot ant-badge-status-processing"></span> Hoàn tất
                                    </span>
                                @elseif($ro->status == 3)
                                    <span class="text-[#FF4D4F] font-medium flex items-center">
                                        <span class="ant-badge-status-dot ant-badge-status-error"></span> Đã hủy
                                    </span>
                                @else
                                    <span class="text-[#FAAD14] font-medium flex items-center">
                                        <span class="ant-badge-status-dot ant-badge-status-warning"></span> Chờ xác nhận
                                    </span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap text-xs text-[rgba(0,0,0,0.65)]">
                                {{ $ro->payment_method == 2 ? 'Chuyển khoản' : 'Tiền mặt' }}
                            </td>
                            <td class="text-xs text-[rgba(0,0,0,0.45)] whitespace-nowrap">
                                <div>{{ $ro->created_at ? $ro->created_at->format('H:i') : '' }}</div>
                                <div class="text-[11px]">{{ $ro->created_at ? $ro->created_at->format('d/m/Y') : '' }}</div>
                            </td>
                            <td class="text-center whitespace-nowrap">
                                <a href="{{ route('admin.order.orderdetail', $ro->id) }}" class="text-[#F47716] hover:underline text-xs font-medium">
                                    Chi tiết
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-[rgba(0,0,0,0.45)] text-xs">Chưa có đơn hàng nào phát sinh</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Ant Design ProTable 2: LỊCH ĐẶT BÀN GẦN ĐÂY -->
        <div class="ant-card overflow-hidden">
            <!-- Toolbar tiêu đề -->
            <div class="px-5 py-4 border-b border-[#F0F0F0] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold text-[rgba(0,0,0,0.85)]">Danh sách đặt bàn gần đây</h2>
                </div>
                <a href="{{ url('admin/booking') }}" class="ant-btn text-xs">
                    <span>Quản lý đặt bàn</span>
                </a>
            </div>

            <!-- Ant Design Filter Bar -->
            <div class="px-5 py-2.5 border-b border-[#F0F0F0] bg-[#FAFAFA] flex flex-wrap items-center gap-4 text-xs font-medium">
                <span class="text-[#F47716] border-b-2 border-[#F47716] pb-1 cursor-pointer font-semibold">
                    Tất cả ({{ $totalBookingsCount }})
                </span>
                <span class="text-[rgba(0,0,0,0.65)] hover:text-[#F47716] pb-1 cursor-pointer transition-colors flex items-center gap-1.5">
                    <span class="ant-badge-status-dot ant-badge-status-warning"></span>
                    Chờ xác nhận ({{ $totalPendingBookings }})
                </span>
                <span class="text-[rgba(0,0,0,0.65)] hover:text-[#F47716] pb-1 cursor-pointer transition-colors flex items-center gap-1.5">
                    <span class="ant-badge-status-dot ant-badge-status-success"></span>
                    Đã xác nhận ({{ $totalConfirmedBookings }})
                </span>
                <span class="text-[rgba(0,0,0,0.65)] hover:text-[#F47716] pb-1 cursor-pointer transition-colors flex items-center gap-1.5">
                    <span class="ant-badge-status-dot ant-badge-status-error"></span>
                    Đã hủy ({{ $totalCancelledBookings }})
                </span>
            </div>

            <!-- Table View -->
            <div class="overflow-x-auto">
                <table>
                    <thead>
                        <tr>
                            <th>MÃ ĐẶT</th>
                            <th>KHÁCH HÀNG</th>
                            <th class="text-center">SỐ KHÁCH</th>
                            <th>NGÀY & GIỜ HẸN</th>
                            <th>NGUỒN ĐẶT</th>
                            <th>TRẠNG THÁI</th>
                            <th class="text-center">THAO TÁC</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentBookings as $rb)
                        <tr>
                            <td class="font-mono font-medium text-[rgba(0,0,0,0.85)] whitespace-nowrap text-xs">
                                #{{ $rb->id }}
                            </td>
                            <td class="whitespace-nowrap">
                                <div>
                                    <div class="font-medium text-xs text-[rgba(0,0,0,0.85)]">{{ $rb->name }}</div>
                                    <div class="text-[11px] text-[rgba(0,0,0,0.45)]">{{ $rb->phone }}</div>
                                </div>
                            </td>
                            <td class="text-center font-medium text-xs text-[rgba(0,0,0,0.85)] whitespace-nowrap">
                                {{ $rb->number_of_people ?? 2 }} khách
                            </td>
                            <td class="text-xs whitespace-nowrap">
                                <div class="font-medium text-[rgba(0,0,0,0.85)]">{{ $rb->booking_time ?? '19:00' }}</div>
                                <div class="text-[11px] text-[rgba(0,0,0,0.45)]">{{ !empty($rb->booking_date) ? \Carbon\Carbon::parse($rb->booking_date)->format('d/m/Y') : '' }}</div>
                            </td>
                            <td class="text-xs text-[rgba(0,0,0,0.65)] whitespace-nowrap">
                                Website
                            </td>
                            <td class="whitespace-nowrap text-xs">
                                @if($rb->status == 2)
                                    <span class="text-[#52C41A] font-medium flex items-center">
                                        <span class="ant-badge-status-dot ant-badge-status-success"></span> Đã xác nhận
                                    </span>
                                @elseif($rb->status == 0)
                                    <span class="text-[#FF4D4F] font-medium flex items-center">
                                        <span class="ant-badge-status-dot ant-badge-status-error"></span> Đã hủy
                                    </span>
                                @else
                                    <span class="text-[#FAAD14] font-medium flex items-center">
                                        <span class="ant-badge-status-dot ant-badge-status-warning"></span> Chờ xác nhận
                                    </span>
                                @endif
                            </td>
                            <td class="text-center whitespace-nowrap">
                                <a href="{{ url('admin/booking') }}" class="text-[#F47716] hover:underline text-xs font-medium">
                                    Chi tiết
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-[rgba(0,0,0,0.45)] text-xs">Chưa có lịch đặt bàn nào</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Script Chart.js (Chuẩn AntV Line Chart) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('revenueChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: @json($chartLabels),
                        datasets: [{
                            label: 'Doanh thu',
                            data: @json($chartRevenueData),
                            borderColor: '#F47716',
                            backgroundColor: 'rgba(244, 119, 22, 0.04)',
                            borderWidth: 2,
                            pointBackgroundColor: '#FFFFFF',
                            pointBorderColor: '#F47716',
                            pointBorderWidth: 2,
                            pointRadius: 3,
                            pointHoverRadius: 5,
                            fill: true,
                            tension: 0.25
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.75)',
                                titleColor: '#FFFFFF',
                                bodyColor: '#FFFFFF',
                                padding: 8,
                                cornerRadius: 2,
                                displayColors: false,
                                callbacks: {
                                    label: function(context) {
                                        return 'Doanh thu: ' + new Intl.NumberFormat('vi-VN').format(context.parsed.y) + ' ₫';
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: 'rgba(0, 0, 0, 0.45)',
                                    font: { size: 12 }
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: '#F0F0F0'
                                },
                                ticks: {
                                    color: 'rgba(0, 0, 0, 0.45)',
                                    font: { size: 12 },
                                    callback: function(value) {
                                        if (value >= 1000000) return (value / 1000000) + ' Tr';
                                        if (value >= 1000) return (value / 1000) + ' k';
                                        return value;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Tự động làm mới khi quay lại tab Dashboard từ màn hình khác
            let lastFocusTime = Date.now();
            window.addEventListener('focus', function() {
                if (Date.now() - lastFocusTime > 5000) {
                    lastFocusTime = Date.now();
                    window.location.reload();
                }
            });

            // Tự động làm mới dữ liệu định kỳ mỗi 30 giây
            setInterval(function() {
                window.location.reload();
            }, 30000);
        });
    </script>
</x-layout-backend>
