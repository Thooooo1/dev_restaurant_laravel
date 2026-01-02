<x-layout-backend>
    <!-- CONTENT -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Chi tiết thương hiệu</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('admin/')}}">Trang chủ</a></li>
                        <li class="breadcrumb-item active">Thương hiệu</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-12 text-right">
                        <a href="{{ route('admin.brand.edit', $brand->id) }}" class="btn btn-sm btn-primary">
                            <i class="far fa-edit"></i> Sửa
                        </a>
                        <a href="{{ route('admin.brand.destroy', $brand->id) }}" class="btn btn-sm btn-danger"
                           onclick="return confirm('Bạn có chắc muốn xóa?')">
                            <i class="fas fa-trash"></i> Xóa
                        </a>
                        <a href="{{ route('admin.brand.index') }}" class="btn btn-sm btn-info">
                            <i class="fa fa-arrow-left"></i> Về danh sách
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:30%;">
                                <strong>Tên trường</strong>
                            </th>
                            <th class="text-center" style="width:70%;">Giá trị</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>ID</td>
                            <td>{{ $brand->id }}</td>
                        </tr>
                        <tr>
                            <td>Tên Banner</td>
                            <td>{{ $brand->name }}</td>
                        </tr>
                        <tr>
                            <td>Hình ảnh</td>
                            <td>
                                <img src="{{ asset('images/brand/' . $brand->image) }}" alt="brand Image" style="max-width: 200px;">
                            </td>
                        </tr>
                        <tr>
                            <td>Slug</td>
                            <td>{{ $brand->slug }}</td>
                        </tr>
                        <tr>
                            <td>Mô tả</td>
                            <td>{{ $brand->description }}</td>
                        </tr>
                        <tr>
                            <td>Sắp xếp</td>
                            <td>{{ $brand->sort_order }}</td>
                        </tr>
                        <tr>
                            <td>Created By</td>
                            <td>{{ $brand->created_by }}</td>
                        </tr>
                        <tr>
                            <td>Created At</td>
                            <td>{{ $brand->created_at }}</td>
                        </tr>
                        <tr>
                            <td>Updated By</td>
                            <td>{{ $brand->updated_by }}</td>
                        </tr>
                        <tr>
                            <td>Updated At</td>
                            <td>{{ $brand->updated_at }}</td>
                        </tr>
                        <tr>
                            <td>Status</td>
                            <td>{{ $brand->status == 1 ? 'Hiển thị' : 'Không hiển thị' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
    <!-- /.CONTENT -->

</x-layout-backend>