<x-layout-backend>
    <!-- CONTENT -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>
                        @if(isset($type) && $type === 'employees')
                            Quản lý nhân viên
                        @else
                            Quản lý khách hàng
                        @endif
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('admin/') }}">Trang chủ</a></li>
                        <li class="breadcrumb-item active">
                            @if(isset($type) && $type === 'employees') Nhân viên @else Khách hàng @endif
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    @php $qValue = request('q'); @endphp
                    <div class="col-auto">
                        <form method="GET" action="{{ isset($type) && $type === 'employees' ? route('admin.user.employees') : route('admin.user.customers') }}">
                            <div class="input-group">
                                <input type="text" name="q" value="{{ $qValue }}" class="form-control" placeholder="Tìm tên hoặc số điện thoại">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="submit">Tìm</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    @if(!isset($type) || $type !== 'employees')
                        <div class="col-auto d-flex align-items-center">
                            <form method="GET" action="{{ route('admin.user.customers') }}">
                                <div class="row align-items-center">
                                    <div>
                                        <span><strong>Lọc theo:</strong></span>
                                    </div>
                                    <div class="col-auto">
                                        <select name="status" onchange="this.form.submit()" class="form-control">
                                            <option value="">Trạng thái tài khoản</option>
                                            <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Hoạt động</option>
                                            <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>Không hoạt động</option>
                                        </select>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="col-auto ml-auto d-flex align-items-center">
                            <a class="btn btn-sm btn-success" href="{{ route('admin.user.create', ['role' => 'customer']) }}">
                                <i class="fas fa-plus"></i> Thêm khách hàng
                            </a>
                            <a class="btn btn-sm btn-danger ml-2" href="{{ url('admin/user/trash') }}">
                                <i class="fas fa-trash"></i> Thùng rác
                            </a>
                        </div>
                    @else
                        <div class="col-12 text-right">
                            <a class="btn btn-sm btn-success" href="{{ route('admin.user.create', ['role' => 'admin']) }}">
                                <i class="fas fa-plus"></i> Thêm nhân viên
                            </a>
                        </div>
                    @endif
                </div>
            </div>
            <div class="card-body">
                @if(isset($type) && $type === 'employees')
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
                                    $isEditable = (Auth::user()->id == $user->id || Auth::user()->admin_lever == 1);
                                @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="text-center">
                                        <img src="{{ asset('/images/user/' . $user->image) }}" class="img-fluid" alt="avatar">
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
                @else
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th class="text-center" style="width:30px;">#</th>
                                <th class="text-center" style="width:90px;">Hình</th>
                                <th>Họ tên</th>
                                <th>Điện thoại</th>
                                <th>Email</th>
                                <th class="text-center" style="width:250px;">Chức năng</th>
                                <th class="text-center" style="width:30px;">ID</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" id="checkId" value="1" name="checkId[]">
                                    </td>
                                    <td class="text-center">
                                        <img src="{{ asset('/images/user/' . $user->image) }}" class="img-fluid rounded"
                                            style="max-width: 80px; max-height: 80px; object-fit: cover;" alt="avatar">
                                    </td>
                                    <td>{{ $user->fullname }}</td>
                                    <td>{{ $user->phone }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td class="text-center">
                                        @if ($user->status == 1)
                                            <a href="{{ route('admin.user.status', ['user' => $user->id]) }}"
                                                class="btn btn-sm btn-success">
                                                <i class="fas fa-toggle-on"></i>
                                            </a>
                                        @else
                                            <a href="{{ route('admin.user.status', ['user' => $user->id]) }}"
                                                class="btn btn-sm btn-success">
                                                <i class="fas fa-toggle-off"></i>
                                            </a>
                                        @endif

                                        <a href="{{ route('admin.user.show', ['user' => $user->id]) }}"
                                            class="btn btn-sm btn-info">
                                            <i class="far fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.user.edit', ['user' => $user->id]) }}"
                                            class="btn btn-sm btn-primary">
                                            <i class="far fa-edit"></i>
                                        </a>
                                        <a href="{{ route('admin.user.delete', ['user' => $user->id]) }}"
                                            class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        {{ $user->id }}
                                    </td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                    <div class="my-4">
                        {{ $users->links() }}
                    </div>
                @endif
            </div>
        </div>
    </section>
    <!-- /.CONTENT -->
</x-layout-backend>
<x-layout-backend>
    <!-- CONTENT -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Quản lý thành viên</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('admin/') }}">Trang chủ</a></li>
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
                    <div class="col-auto d-flex align-items-center">
                        <form method="GET" action="{{ route('admin.user.index') }}">
                            <div class="row align-items-center">
                                <div>
                                    <span><strong>Lọc theo:</strong></span>
                                </div>

                                <div class="col-auto">
                                    <select name="role" onchange="this.form.submit()" class="form-control">
                                        <option value=""> Loại tài khoản </option>
                                        <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}> Quản trị
                                        </option>
                                        <option value="customer" {{ request('role') == 'customer' ? 'selected' : '' }}>
                                            Người dùng</option>
                                    </select>

                                </div>
                                <div class="col-auto">
                                    <select name="status" onchange="this.form.submit()" class="form-control">
                                        <option value="">Trạng thái tài khoản</option>
                                        <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Hoạt động</option>
                                        <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>Không hoạt động</option>
                                    </select>
                                </div>

                            </div>
                        </form>
                    </div>

                    <div class="col-auto ml-auto d-flex align-items-center">
                        <a class="btn btn-sm btn-success" href="{{ url('admin/user/create') }}">
                            <i class="fas fa-plus"></i> Thêm
                        </a>
                        <a class="btn btn-sm btn-danger ml-2" href="{{ url('admin/user/trash') }}">
                            <i class="fas fa-trash"></i> Thùng rác
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:30px;">#</th>
                            <th class="text-center" style="width:90px;">Hình</th>
                            <th>Họ tên</th>
                            <th>Điện thoại</th>
                            <th>Email</th>
                            {{-- <th>Địa chỉ</th> --}}
                            <th>Quyền</th>
                            <th class="text-center" style="width:250px;">Chức năng</th>
                            <th class="text-center" style="width:30px;">ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" id="checkId" value="1" name="checkId[]">
                                </td>
                                <td class="text-center">
                                    <img src="{{ asset('/images/user/' . $user->image) }}" class="img-fluid rounded"
                                        style="max-width: 80px; max-height: 80px; object-fit: cover;" alt="avatar">
                                </td>
                                <td>{{ $user->fullname }}</td>
                                <td>{{ $user->phone }}</td>
                                <td>{{ $user->email }}</td>
                                {{-- <td>{{ $user->address }}</td> --}}

                               
                                @if ($user->roles == "customer")
                                    <td>Người dùng</td>
                                @elseif ($user->roles == "admin")
                                    @if ($user->admin_lever == 1)
                                        <td>Quản trị</td>
                                    @else 
                                        <td>Nhân viên</td>
                                    @endif
                                    
                                @endif
                                <td class="text-center">
                                    @if ($user->status == 1)
                                        <a href="{{ route('admin.user.status', ['user' => $user->id]) }}"
                                            class="btn btn-sm btn-success">
                                            <i class="fas fa-toggle-on"></i>
                                        </a>
                                    @else
                                        <a href="{{ route('admin.user.status', ['user' => $user->id]) }}"
                                            class="btn btn-sm btn-success">
                                            <i class="fas fa-toggle-off"></i>
                                        </a>
                                    @endif

                                    <a href="{{ route('admin.user.show', ['user' => $user->id]) }}"
                                        class="btn btn-sm btn-info">
                                        <i class="far fa-eye"></i>
                                    </a>
                                    @if ($user->roles == "admin")
                                        <a href="{{ route('admin.user.removeAdmin', ['user' => $user->id]) }}"
                                            class="btn btn-sm btn-danger">
                                            <i class="fas fa-user-times"></i> Xóa Admin
                                        </a>
                                    @endif

                                    @if ($user->roles == "customer")
                                        <a href="{{ route('admin.user.addAdmin', ['user' => $user->id]) }}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-user-plus"></i> Thêm Admin
                                        </a>
                                    @endif

                                    {{-- <a href="{{ route('admin.user.edit', ['user' => $user->id]) }}"
                                        class="btn btn-sm btn-primary">
                                        <i class="far fa-edit"></i>
                                    </a>
                                    <a href="{{ route('admin.user.delete', ['user' => $user->id]) }}"
                                        class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </a> --}}
                                </td>
                                <td class="text-center">
                                    {{ $user->id }}
                                </td>
                            </tr>
                        @endforeach

                    </tbody>
                </table>
                <div class="my-4">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </section>
    <!-- /.CONTENT -->
</x-layout-backend>