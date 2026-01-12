<x-layout-backend>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <strong class="fw-bold h4 text-danger">THÊM BÀN ĂN</strong>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('admin/') }}">Trang chủ</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.table.index') }}">Bàn ăn</a></li>
                        <li class="breadcrumb-item active">Thêm mới</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <form action="{{ route('admin.table.store') }}" method="post">
            @csrf
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Thông tin bàn ăn</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Tầng <span class="text-danger">*</span></label>
                        <select name="floor_id" class="form-control @error('floor_id') is-invalid @enderror">
                            <option value="">-- Chọn tầng --</option>
                            @foreach ($floors as $floor)
                                <option value="{{ $floor->id }}" {{ old('floor_id') == $floor->id ? 'selected' : '' }}>
                                    {{ $floor->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('floor_id')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Tên bàn <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name') }}" placeholder="VD: Bàn 1, Bàn VIP 01">
                        @error('name')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Số lượng chỗ ngồi <span class="text-danger">*</span></label>
                        <input type="number" name="seats" class="form-control @error('seats') is-invalid @enderror" 
                               min="1" value="{{ old('seats') }}">
                        @error('seats')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Thứ tự</label>
                        <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" 
                               value="{{ old('sort_order', 0) }}">
                        @error('sort_order')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Trạng thái <span class="text-danger">*</span></label>
                        <select name="status" class="form-control @error('status') is-invalid @enderror">
                            <option value="1" {{ old('status', 1) == 1 ? 'selected' : '' }}>Hoạt động</option>
                            <option value="2" {{ old('status') == 2 ? 'selected' : '' }}>Ẩn</option>
                        </select>
                        @error('status')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-success" type="submit">Lưu</button>
                    <a href="{{ route('admin.table.index') }}" class="btn btn-secondary">Hủy</a>
                </div>
            </div>
        </form>
    </section>
</x-layout-backend>


