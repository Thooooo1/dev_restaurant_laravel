<x-layout-backend>
    <!-- CONTENT -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Chi tiết thương hiệu</h1>
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
                            <i class="fa-regular fa-pen-to-square"></i>
                        </a>
                        <a href="{{ route('admin.brand.destroy', $brand->id) }}" class="btn btn-sm btn-danger"
                           onclick="return confirm('Bạn có chắc muốn xóa?')">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                        <a href="{{ route('admin.brand.index') }}" class="btn btn-sm btn-info">
                            <i class="fa fa-arrow-left"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive"><table class="table table-bordered table-striped table-hover">
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