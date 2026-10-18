<x-layout-backend>
    <!-- CONTENT -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Thống kê</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Thống kê</a></li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">


            {{-- 4 Thống kê chính hôm nay --}}
            <div class="row mb-4">
                <div class="col-md-3 col-6 mb-3 mb-md-0">
                    <div class="card text-center p-3 border-2" style="border-radius: 8px;">
                        <div class="d-flex justify-content-center mb-2">
                            <div class="rounded-circle border border-dark d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fa-solid fa-dollar-sign fa-lg"></i>
                            </div>
                        </div>
                        <div class="font-weight-bold" style="font-size: 14px;">Doanh thu hôm nay</div>
                        <div class="text-xl font-bold text-success" style="font-size: 18px;">
                            {{ number_format($todayRevenue, 0, ',', '.') }} đ
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-6 mb-3 mb-md-0">
                    <div class="card text-center p-3 border-2" style="border-radius: 8px;">
                        <div class="d-flex justify-content-center mb-2">
                            <div class="rounded-circle border border-dark d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fa-solid fa-clipboard-list fa-lg"></i>
                            </div>
                        </div>
                        <div class="font-weight-bold" style="font-size: 14px;">Số đơn hôm nay</div>
                        <div class="text-xl font-bold text-primary" style="font-size: 18px;">
                            {{ number_format($todayOrderCount, 0, ',', '.') }}
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-6 mb-3 mb-md-0">
                    <div class="card text-center p-3 border-2" style="border-radius: 8px;">
                        <div class="d-flex justify-content-center mb-2">
                            <div class="rounded-circle border border-dark d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fa-solid fa-users fa-lg"></i>
                            </div>
                        </div>
                        <div class="font-weight-bold" style="font-size: 14px;">Số khách hôm nay</div>
                        <div class="text-xl font-bold text-warning" style="font-size: 18px;">
                            {{ number_format($todayCustomerCount, 0, ',', '.') }}
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-6">
                    <div class="card text-center p-3 border-2" style="border-radius: 8px;">
                        <div class="d-flex justify-content-center mb-2">
                            <div class="rounded-circle border border-dark d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fa-solid fa-shopping-cart fa-lg"></i>
                            </div>
                        </div>
                        <div class="font-weight-bold" style="font-size: 14px;">Giá trị đơn trung bình</div>
                        <div class="text-xl font-bold text-info" style="font-size: 18px;">
                            {{ number_format($todayAvgOrderValue, 0, ',', '.') }} đ
                        </div>
                    </div>
                </div>
            </div>

            {{-- Phần thống kê doanh thu theo khoảng ngày --}}
            <div class="card border-2" style="border-radius: 8px;">
                <div class="card-header d-flex justify-content-between align-items-center border-bottom-2" style="border-bottom: 2px solid #dee2e6;">
                    <h3 class="card-title font-weight-bold">Doanh thu</h3>

                    <div class="d-flex align-items-center gap-2" style="gap: 10px;">
                        {{-- Tab Ngày/Tháng/Năm --}}
                        <div class="btn-group" role="group">
                            <a href="{{ route('admin.dashboard', ['type' => 'day']) }}"
                               class="btn btn-sm {{ $type === 'day' ? 'btn-primary' : 'btn-outline-secondary' }}">
                                Ngày
                            </a>
                            <a href="{{ route('admin.dashboard', ['type' => 'month']) }}"
                               class="btn btn-sm {{ $type === 'month' ? 'btn-primary' : 'btn-outline-secondary' }}">
                                Tháng
                            </a>
                            <a href="{{ route('admin.dashboard', ['type' => 'year']) }}"
                               class="btn btn-sm {{ $type === 'year' ? 'btn-primary' : 'btn-outline-secondary' }}">
                                Năm
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    {{-- Biểu đồ đường doanh thu --}}
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
    </section>

    {{-- Thêm thư viện Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <script>
        $(document).ready(function() {
            // Khởi tạo biểu đồ đường
            const ctx = document.getElementById('revenueChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: @json($chartLabels),
                        datasets: [{
                            label: 'Doanh thu (VNĐ)',
                            data: @json($chartRevenueData),
                            borderColor: '#28a745',
                            backgroundColor: 'rgba(40, 167, 69, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.parsed.y !== null) {
                                            label += new Intl.NumberFormat('vi-VN').format(context.parsed.y) + ' VNĐ';
                                        }
                                        return label;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                ticks: {
                                    autoSkip: false, // Không bỏ qua bất kỳ nhãn nào
                                    maxRotation: 45,
                                    minRotation: 0
                                }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return new Intl.NumberFormat('vi-VN').format(value);
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
</x-layout-backend>
