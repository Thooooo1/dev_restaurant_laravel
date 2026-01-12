<x-layout-backend>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <strong class="fw-bold h4 text-danger">CHI TIẾT ĐẶT BÀN</strong>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('admin/') }}">Trang chủ</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.booking.index') }}">Đặt bàn</a></li>
                        <li class="breadcrumb-item active">Chi tiết</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="card">
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th>Họ tên:</th>
                        <td>{{ $booking->name }}</td>
                    </tr>
                    <tr>
                        <th>Điện thoại:</th>
                        <td>{{ $booking->phone }}</td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td>{{ $booking->email }}</td>
                    </tr>
                    <tr>
                        <th>Ngày đặt:</th>
                        <td>{{ date('d/m/Y', strtotime($booking->booking_date)) }}</td>
                    </tr>
                    <tr>
                        <th>Thời gian:</th>
                        <td>{{ date('H:i', strtotime($booking->booking_time)) }}</td>
                    </tr>
                    <tr>
                        <th>Số người:</th>
                        <td>{{ $booking->number_of_people }}</td>
                    </tr>
                    <tr>
                        <th>Ghi chú:</th>
                        <td>{{ $booking->note }}</td>
                    </tr>
                    <tr>
                        <th>Trạng thái:</th>
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
                    </tr>
                </table>
                <a href="{{ route('admin.booking.index') }}" class="btn btn-secondary mt-3">Quay lại</a>
            </div>
        </div>
    </section>
</x-layout-backend>
