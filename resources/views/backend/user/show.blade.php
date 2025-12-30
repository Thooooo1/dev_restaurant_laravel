<x-layout-backend>
    <!-- CONTENT -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Chi tiết người dùng</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('admin/')}}">Trang chủ</a></li>
                        <li class="breadcrumb-item active">Người dùng</li>
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
                        <a href="{{ route('admin.user.edit', $user->id) }}" class="btn btn-sm btn-primary">
                            <i class="far fa-edit"></i> Sửa
                        </a>
                        <a href="{{ route('admin.user.delete', $user->id) }}" class="btn btn-sm btn-danger"
                           onclick="return confirm('Bạn có chắc muốn xóa?')">
                            <i class="fas fa-trash"></i> Xóa
                        </a>
                        <a href="{{ route('admin.user.index') }}" class="btn btn-sm btn-info">
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
                            <td>{{ $user->id }}</td>
                        </tr>
                        <tr>
                            <td>Tên người dùng</td>
                            <td>{{ $user->username }}</td>
                        </tr>
                        <tr>
                            <td>Hình ảnh</td>
                            <td>
                                <img src="{{ asset('images/user/' . $user->image) }}" alt="user Image" style="max-width: 200px;">
                            </td>
                        </tr>
                        <tr>
                            <td>Tên đầy đủ</td>
                            <td>{{ $user->fullname }}</td>
                        </tr>
                        
                        <tr>
                            <td>Giới tính</td>
                            <td>{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <td>Điện thoại</td>
                            <td>{{ $user->phone }}</td>
                        </tr>
                        <tr>
                            <td>Email</td>
                            <td>{{$user->email }}</td>
                        </tr>
                        <tr>
                            <td>Địa chỉ</td>
                            <td>{{$user->address }}</td>
                        </tr>
                        {{-- <tr>
                            <td>Quyền</td>
                            <td>{{$user->roles }}</td>
                        </tr> --}}
                        @if($user->roles == "customer")
                        <tr>
                            <td>Loại tài khoản</td>
                            <td>Người dùng</td>
                        </tr>
                        @endif
                        @if ($user->roles == "admin")
                        <tr>
                            <td>Loại tài khoản</td>
                                {{-- {{$user->admin_lever }} --}}
                            @if($user->admin_lever == 2)
                                <td>Nhân viên</td>

                            @else
                                <td>Quản trị</td>
                            @endif
                        </tr>
                        @endif
                        <tr>
                            <td>Created By</td>
                            <td>{{ $user->created_by }}</td>
                        </tr>
                        <tr>
                            <td>Created At</td>
                            <td>{{ $user->created_at }}</td>
                        </tr>
                        <tr>
                            <td>Updated By</td>
                            <td>{{ $user->updated_by }}</td>
                        </tr>
                        <tr>
                            <td>Updated At</td>
                            <td>{{ $user->updated_at }}</td>
                        </tr>
                        <tr>
                            <td>Trạng thái</td>
                            <td>{{ $user->status == 1 ? 'Hoạt động' : 'Không hoạt động' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
    <!-- /.CONTENT -->
</x-layout-backend>