<x-layout-backend>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Quản lý đặt bàn</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('admin/') }}">Trang chủ</a></li>
                        <li class="breadcrumb-item active">Đặt bàn</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="card">
            <div class="card-header">
                <form method="GET" class="form-inline">
                    <label for="status" class="mr-2">Lọc theo:</label>
                    <select name="status" id="status" class="form-control mr-2">
                        <option value="5" {{ $statusFilter == 5 ? 'selected' : '' }}>Tất cả</option>
                        <option value="1" {{ $statusFilter == 1 ? 'selected' : '' }}>Đang chờ xử lý</option>
                        <option value="2" {{ $statusFilter == 2 ? 'selected' : '' }}>Đã xác nhận</option>
                        <option value="3" {{ $statusFilter == 3 ? 'selected' : '' }}>Đã hoàn thành</option>
                        <option value="4" {{ $statusFilter == 4 ? 'selected' : '' }}>Đã hủy</option>
                    </select>
                    <button type="submit" class="btn btn-primary">Lọc</button>
                </form>
            </div>

            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Họ tên</th>
                            <th>Điện thoại</th>
                            <th>Email</th>
                            <th>Ngày đặt</th>
                            <th>Thời gian</th>
                            <th>Trạng thái</th>
                            <th>Chức năng</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bookings as $index => $booking)
                        <tr>
                            <td>{{ $bookings->firstItem() + $index }}</td>
                            <td>{{ $booking->name }}</td>
                            <td>{{ $booking->phone }}</td>
                            <td>{{ $booking->email }}</td>
                            <td>{{ date('d/m/Y', strtotime($booking->booking_date)) }}</td>
                            <td>{{ date('H:i', strtotime($booking->booking_time)) }}</td>
                            <td>
                                @switch($booking->status)
                                @case(1)
                                Đang chờ xử lý
                                @break
                                @case(2)
                                Đã xác nhận
                                @break
                                @case(3)
                                Đã hoàn thành
                                @break
                                @case(4)
                                    Đã hủy
                                @break
                                @endswitch
                            </td>
                            <td>
                                <a href="{{ route('admin.booking.detail', $booking->id) }}" class="btn btn-sm btn-info">
                                    <i class="far fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.booking.edit', $booking->id) }}" class="btn btn-sm btn-primary">
                                    <i class="far fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="my-4">
                    {{ $bookings->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </section>
</x-layout-backend>
