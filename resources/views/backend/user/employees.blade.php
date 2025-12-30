
<x-layout-backend>
    <!-- CONTENT -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Quản lý nhân viên</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('admin/') }}">Trang chủ</a></li>
                        <li class="breadcrumb-item active">Nhân viên</li>
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
                        {{-- <a class="btn btn-sm btn-success" href="{{ url('admin/user/create') }}">
                            <i class="fas fa-plus"></i> Thêm
                        </a>
                        <a class="btn btn-sm btn-danger" href="{{ url('admin/user.trash') }}">
                            <i class="fas fa-trash"></i> Thùng rác
                        </a> --}}
                    </div>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th class="text-center" style="width:90px;">Hình</th>
                            <th>Tên nhân viên</th>
                            <th>Họ tên</th>
                            <th>Email</th>
                            <th>Trạng thái</th>
                            <th>Chức năng</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $index => $user)
                            @php
                                $isEditable = (Auth::user()->id == $user->id || Auth::user()->admin_level == 1);
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="text-center">
                                    <img src="{{ asset('/images/user/' . $user->image) }}" class="img-fluid"
                                        alt="avatar">
                                </td>
                                <td>{{ $user->username }}</td>
                                <td>{{ $user->fullname }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->line == 1 ? 'Online' : 'Offline' }}</td>
                                <td>
                                    @if (Auth::user()->admin_lever == 1 || Auth::user()->id == $user->id)
                                        <a href="{{ route('admin.user.toggleLine', $user->id) }}" class="btn btn-sm btn-warning">
                                            Đổi trạng thái
                                        </a>
                                    @else
                                        <span class="text-muted">Không được phép</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class= "my-4">
                    
                </div>
            </div>
        </div>
    </section>
    <!-- /.CONTENT -->
</x-layout-backend>

