<x-layout-backend>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Sửa bàn ăn</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('admin/') }}">Trang chủ</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.table.index') }}">Bàn ăn</a></li>
                        <li class="breadcrumb-item active">Sửa</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <form action="{{ route('admin.table.update', $table->id) }}" method="post">
            @csrf
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Thông tin bàn ăn</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Tầng</label>
                        <select name="floor_id" class="form-control" required>
                            <option value="">-- Chọn tầng --</option>
                            @foreach ($floors as $floor)
                                <option value="{{ $floor->id }}" {{ $table->floor_id == $floor->id ? 'selected' : '' }}>
                                    {{ $floor->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Tên bàn</label>
                        <input type="text" name="name" class="form-control" value="{{ $table->name }}" required>
                    </div>

                    <div class="form-group">
                        <label>Số lượng chỗ ngồi</label>
                        <input type="number" name="seats" class="form-control" min="1" value="{{ $table->seats }}" required>
                    </div>

                    <div class="form-group">
                        <label>Thứ tự</label>
                        <input type="number" name="sort_order" class="form-control" value="{{ $table->sort_order }}">
                    </div>

                    <div class="form-group">
                        <label>Trạng thái</label>
                        <select name="status" class="form-control">
                            <option value="1" {{ $table->status == 1 ? 'selected' : '' }}>Hoạt động</option>
                            <option value="2" {{ $table->status == 2 ? 'selected' : '' }}>Ẩn</option>
                        </select>
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-success" type="submit">Cập nhật</button>
                    <a href="{{ route('admin.table.index') }}" class="btn btn-secondary">Hủy</a>
                </div>
            </div>
        </form>
    </section>
</x-layout-backend>


