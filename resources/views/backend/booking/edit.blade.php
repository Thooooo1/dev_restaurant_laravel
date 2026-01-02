<x-layout-backend>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Cập nhật đặt bàn</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('admin/') }}">Trang chủ</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.booking.index') }}">Đặt bàn</a></li>
                        <li class="breadcrumb-item active">Cập nhật</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.booking.update', $booking->id) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="status">Trạng thái:</label>
                        <select name="status" id="status" class="form-control">
                            <option value="1" {{ $booking->status == 1 ? 'selected' : '' }}>Đang chờ xử lý</option>
                            <option value="2" {{ $booking->status == 2 ? 'selected' : '' }}>Đã xác nhận</option>
                            <option value="3" {{ $booking->status == 3 ? 'selected' : '' }}>Đã hoàn thành</option>
                            <option value="4" {{ $booking->status == 4 ? 'selected' : '' }}>Đã hủy</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success">Cập nhật</button>
                </form>
            </div>
        </div>
    </section>
</x-layout-backend>
