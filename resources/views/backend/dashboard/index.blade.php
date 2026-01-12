<x-layout-backend>
    <!-- CONTENT -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Trang chủ</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ url('admin/')}}">Trang chủ</a></li>
                </ol>
            </div>
        </div>
    </div>
</section>
<section class="content">
    <div class="container-fluid">
        <style>
            .dash-link-card {
                border-radius: 14px;
                transition: transform 0.15s ease, box-shadow 0.15s ease;
            }
            .dash-link-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 22px rgba(0, 0, 0, 0.10);
            }
            .dash-icon {
                width: 44px;
                height: 44px;
                border-radius: 999px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #fff;
                flex: 0 0 auto;
            }
            .dash-metric .small-box {
                border-radius: 14px;
            }
        </style>

        <div class="row">
            <div class="col-12 col-md-6 dash-metric">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ number_format($todayRevenue, 0, ',', '.') }} đ</h3>
                        <p>Doanh thu hôm nay</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-coins"></i>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 dash-metric">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3>{{ number_format($todayOrderCount, 0, ',', '.') }}</h3>
                        <p>Số đơn hoàn tất hôm nay</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-receipt"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card" style="border-radius: 14px;">
                    <div class="card-header" style="border-top-left-radius: 14px; border-top-right-radius: 14px;">
                        <h3 class="card-title mb-0">Truy cập nhanh</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-sm-6 col-lg-3 mb-3 mb-lg-0">
                                <a href="{{ route('admin.table-order.index') }}" class="text-reset text-decoration-none">
                                    <div class="card dash-link-card h-100">
                                        <div class="card-body d-flex align-items-center justify-content-between">
                                            <div>
                                                <div class="text-muted small">POS</div>
                                                <div class="h6 mb-0 font-weight-bold">Quản lý order</div>
                                            </div>
                                            <div class="dash-icon" style="background: #3b82f6;">
                                                <i class="fa-solid fa-cash-register"></i>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <div class="col-12 col-sm-6 col-lg-3 mb-3 mb-lg-0">
                                <a href="{{ route('admin.import-goods.index') }}" class="text-reset text-decoration-none">
                                    <div class="card dash-link-card h-100">
                                        <div class="card-body d-flex align-items-center justify-content-between">
                                            <div>
                                                <div class="text-muted small">Kho</div>
                                                <div class="h6 mb-0 font-weight-bold">Quản lý nhập hàng</div>
                                            </div>
                                            <div class="dash-icon" style="background: #10b981;">
                                                <i class="fa-solid fa-file-import"></i>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <div class="col-12 col-sm-6 col-lg-3 mb-3 mb-lg-0">
                                <a href="{{ route('admin.booking.index') }}" class="text-reset text-decoration-none">
                                    <div class="card dash-link-card h-100">
                                        <div class="card-body d-flex align-items-center justify-content-between">
                                            <div>
                                                <div class="text-muted small">Booking</div>
                                                <div class="h6 mb-0 font-weight-bold">Đặt bàn</div>
                                            </div>
                                            <div class="dash-icon" style="background: #f59e0b;">
                                                <i class="fa-solid fa-clipboard"></i>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <div class="col-12 col-sm-6 col-lg-3">
                                <a href="{{ route('admin.order.index') }}" class="text-reset text-decoration-none">
                                    <div class="card dash-link-card h-100">
                                        <div class="card-body d-flex align-items-center justify-content-between">
                                            <div>
                                                <div class="text-muted small">E-commerce</div>
                                                <div class="h6 mb-0 font-weight-bold">Đơn hàng</div>
                                            </div>
                                            <div class="dash-icon" style="background: #ef4444;">
                                                <i class="fas fa-shopping-bag"></i>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- /.CONTENT -->
</x-layout-backend>
