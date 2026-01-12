<x-layout-backend>
    @php
        $isEmployee = false;
        if (isset($type) && $type == 'employees') {
            $isEmployee = true;
        }
        if (request('role') == 'admin') {
            $isEmployee = true;
        }
    @endphp
    <!-- CONTENT -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <strong class="fw-bold h4 text-danger">
                        @if($isEmployee)
                            QUẢN LÝ NHÂN VIÊN
                        @else
                            QUẢN LÝ KHÁCH HÀNG
                        @endif
                    </strong>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('admin/') }}">Trang chủ</a></li>
                        <li class="breadcrumb-item active">
                            @if($isEmployee) Nhân viên @else Khách hàng @endif
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
                        <form method="GET" action="{{ $isEmployee ? route('admin.user.employees') : route('admin.user.customers') }}">
                            <div class="input-group">
                                <input type="text" name="q" value="{{ $qValue }}" class="form-control" placeholder="Tìm tên hoặc số điện thoại">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="submit">Tìm</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    @if(!$isEmployee)
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
                        <div class="col-auto ml-auto d-flex align-items-center">
                            <a class="btn btn-sm btn-success" href="{{ route('admin.user.create', ['role' => 'admin']) }}">
                                <i class="fas fa-plus"></i> Thêm nhân viên
                            </a>
                        </div>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:30px;">#</th>
                            <th class="text-center" style="width:90px;">Hình</th>
                            <th>Họ tên</th>
                            <th>{{ $isEmployee ? 'Tên đăng nhập' : 'Điện thoại' }}</th>
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
                                <td>{{ $isEmployee ? $user->username : $user->phone }}</td>
                                <td>{{ $user->email }}</td>
                                <td class="text-center">
                                    @if($isEmployee)
                                        @if (Auth::user()->admin_lever == 1 || Auth::user()->id == $user->id)
                                            <a href="{{ route('admin.user.toggleLine', $user->id) }}" class="btn btn-sm {{ $user->line == 1 ? 'btn-success' : 'btn-danger' }}" title="{{ $user->line == 1 ? 'Online' : 'Offline' }}">
                                                <i class="fas {{ $user->line == 1 ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                            </a>
                                        @endif
                                    @else
                                        <a href="{{ route('admin.user.status', ['user' => $user->id]) }}"
                                            class="btn btn-sm {{ $user->status == 1 ? 'btn-success' : 'btn-danger' }}">
                                            <i class="fas {{ $user->status == 1 ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
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
                    @if(method_exists($users, 'links'))
                        {{ $users->links() }}
                    @endif
                </div>
            </div>
        </div>
    </section>
    <!-- /.CONTENT -->
</x-layout-backend>
