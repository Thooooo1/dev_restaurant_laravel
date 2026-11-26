<x-layout-backend>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <strong class="fw-bold h4 text-danger">Cập nhật đặt bàn </strong>
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
                    <div class="form-group">
                        <label for="table_id">Gán bàn giữ chỗ (tùy chọn):</label>
                        <select name="table_id" id="table_id" class="form-control">
                            <option value="">-- Chưa gán bàn --</option>
                            @foreach(($tables ?? []) as $t)
                                <option value="{{ $t->id }}" {{ (int)($booking->table_id ?? 0) === (int)$t->id ? 'selected' : '' }}>
                                    {{ $t->name }} ({{ $t->floor->name ?? 'Tầng' }} • {{ $t->seats }} chỗ)
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success">Cập nhật</button>
                </form>
            </div>
        </div>
    </section>
</x-layout-backend>
