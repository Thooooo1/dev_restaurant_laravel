<x-layout-backend>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <strong class="fw-bold h4 text-danger">XOÁ BÀN ĂN</strong>
                </div>
                
            </div>
        </div>
    </section>

    <section class="content">
        <div class="card">
            <div class="card-header">
                <a href="{{ route('admin.table-order.index') }}" class="btn btn-secondary">Quay lại sơ đồ bàn (POS)</a>
            </div>
            <div class="card-body">
                <div class="table-responsive"><table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Tên bàn</th>
                            <th>Tầng</th>
                            <th>Số chỗ ngồi</th>
                            <th class="text-center" style="width:200px;">Chức năng</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tables as $table)
                            <tr>
                                <td>{{ $table->name }}</td>
                                <td>{{ $table->floor->name ?? 'Không có' }}</td>
                                <td>{{ $table->seats }}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.table.restore', $table->id) }}" class="btn btn-sm btn-success">
                                        <i class="fa-solid fa-undo"></i> Khôi phục
                                    </a>

                                    <form action="{{ route('admin.table.destroy', $table->id) }}" method="post" style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" onclick="return confirm('Xóa vĩnh viễn bàn này?')">
                                            <i class="fa-solid fa-times"></i> Xóa vĩnh viễn
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</x-layout-backend>


