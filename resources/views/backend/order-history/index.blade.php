<x-layout-backend>
    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-md-6">
                    <strong class="text-danger">LỊCH SỬ ĐƠN HÀNG</strong>
                </div>
                <div class="col-md-6 text-right">
                    <a href="{{ route('admin.order-history.export', request()->all()) }}" class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel"></i> Xuất Excel
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <form action="{{ route('admin.order-history.index') }}" method="GET" class="form-inline mb-4">
                <div class="form-group mr-3">
                    <label class="mr-2">Năm:</label>
                    <select name="year" class="form-control" onchange="this.form.submit()">
                        @for($y = 2024; $y <= 2030; $y++)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <div class="form-group mr-3">
                    <label class="mr-2">Quý:</label>
                    <select name="quarter" class="form-control" onchange="this.form.submit()">
                        <option value="">-- Tất cả --</option>
                        <option value="1" {{ $quarter == 1 ? 'selected' : '' }}>Quý 1</option>
                        <option value="2" {{ $quarter == 2 ? 'selected' : '' }}>Quý 2</option>
                        <option value="3" {{ $quarter == 3 ? 'selected' : '' }}>Quý 3</option>
                        <option value="4" {{ $quarter == 4 ? 'selected' : '' }}>Quý 4</option>
                    </select>
                </div>

                <div class="form-group mr-3">
                    <label class="mr-2">Tháng:</label>
                    <select name="month" class="form-control" onchange="this.form.submit()">
                        <option value="">-- Tất cả --</option>
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>Tháng {{ $m }}</option>
                        @endfor
                    </select>
                </div>
            </form>

            <!-- Stats -->
            <div class="alert alert-info">
                <strong>Tổng doanh thu (theo bộ lọc): </strong> {{ number_format($totalRevenue, 0, ',', '.') }} VNĐ
            </div>

            <!-- Table -->
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Mã HĐ</th>
                        <th>Ngày tạo</th>
                        <th>Khách hàng</th>
                        <th>SĐT</th>
                        <th>Tổng tiền</th>
                        <th>PT Thanh toán</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                    <tr>
                        <td>{{ $order->id }}</td>
                        <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $order->user->fullname ?? $order->name ?? 'Khách lẻ' }}</td>
                        <td>{{ $order->user->phone ?? $order->phone }}</td>
                        <td>{{ number_format($order->total_price, 0, ',', '.') }} VNĐ</td>
                        <td>
                            @if($order->payment_method == 1)
                                <span class="badge badge-secondary">Tiền mặt</span>
                            @elseif($order->payment_method == 2)
                                <span class="badge badge-primary">Chuyển khoản</span>
                            @else
                                <span class="badge badge-warning">Khác</span>
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-sm btn-info btn-view-detail" data-id="{{ $order->id }}" data-toggle="modal" data-target="#orderDetailModal">
                                <i class="fas fa-eye"></i> Xem
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="d-flex justify-content-center">
                {{ $orders->links() }}
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="orderDetailModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Chi tiết đơn hàng</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modalContent">
                    <div class="text-center"><i class="fas fa-spinner fa-spin"></i> Đang tải...</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('.btn-view-detail').click(function() {
                var orderId = $(this).data('id');
                $('#modalContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Đang tải...</div>');
                
                $.ajax({
                    url: '/admin/order-history/' + orderId,
                    type: 'GET',
                    success: function(response) {
                        $('#modalContent').html(response.html);
                    },
                    error: function() {
                        $('#modalContent').html('<div class="text-danger">Không thể tải thông tin đơn hàng.</div>');
                    }
                });
            });
        });
    </script>
</x-layout-backend>
