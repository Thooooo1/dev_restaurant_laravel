<x-layout-backend>
    <!-- CONTENT -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Quản lý bàn ăn</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('admin/') }}">Trang chủ</a></li>
                        <li class="breadcrumb-item active">Bàn ăn</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <div>
                    <a class="btn btn-sm btn-success" href="{{ route('admin.table.create') }}">
                        <i class="fas fa-plus"></i> Thêm bàn
                    </a>
                    <a class="btn btn-sm btn-danger ml-2" href="{{ route('admin.table.trash') }}">
                        <i class="fas fa-trash"></i> Thùng rác
                    </a>
                </div>
            </div>

            <div class="card-body">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:30px;">#</th>
                            <th>Tên bàn</th>
                            <th>Tầng</th>
                            <th>Số chỗ ngồi</th>
                            <th>Thứ tự</th>
                            <th>Trạng thái</th>
                            <th class="text-center" style="width:200px;">Chức năng</th>
                            <th class="text-center" style="width:30px;">ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tables as $table)
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" name="checkId[]" value="{{ $table->id }}">
                                </td>
                                <td>{{ $table->name }}</td>
                                <td>{{ $table->floor->name ?? 'Không có' }}</td>
                                <td>{{ $table->seats }}</td>
                                <td>{{ $table->sort_order }}</td>
                                <td>
                                    @if ($table->status == 1)
                                        <span class="badge badge-success">Hoạt động</span>
                                    @else
                                        <span class="badge badge-secondary">Ẩn</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($table->status == 1)
                                        <a href="{{ route('admin.table.status', $table->id) }}" class="btn btn-sm btn-success">
                                            <i class="fas fa-toggle-on"></i>
                                        </a>
                                    @else
                                        <a href="{{ route('admin.table.status', $table->id) }}" class="btn btn-sm btn-secondary">
                                            <i class="fas fa-toggle-off"></i>
                                        </a>
                                    @endif

                                    <a href="{{ route('admin.table.edit', $table->id) }}" class="btn btn-sm btn-primary">
                                        <i class="far fa-edit"></i>
                                    </a>

                                    <a href="{{ route('admin.table.delete', $table->id) }}" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                                <td class="text-center">{{ $table->id }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="my-3">
                    {{ $tables->links() }}
                </div>
            </div>
        </div>
    </section>
</x-layout-backend>


