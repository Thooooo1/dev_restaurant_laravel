<x-layout-backend>
    <!-- CONTENT -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Chi tiết danh mục</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#{{ url('admin/')}}">Trang chủ</a></li>
                        <li class="breadcrumb-item active">Danh mục</li>
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
                        <a href="category_edit.html" class="btn btn-sm btn-primary">
                            <i class="far fa-edit"></i> Sửa
                        </a>
                        <a href="#" class="btn btn-sm btn-danger">
                            <i class="fas fa-trash"></i> Xóa
                        </a>
                        <a class="btn btn-sm btn-info" href="{{ url('admin/category')}}">
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
                            <td>Tên trường</td>
                            <td>Giá trị</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
    <!-- /.CONTENT -->
</x-layout-backend>