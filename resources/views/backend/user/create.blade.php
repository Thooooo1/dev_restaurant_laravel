<x-layout-backend>
    <!-- CONTENT -->
    <form action="{{route('admin.user.store')}}" method="post" enctype="multipart/form-data">
        @csrf
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <strong class="fw-bold h4 text-danger">
                            @if($role == 'admin' || $role == 'employee')
                                THÊM NHÂN VIÊN
                            @else
                                THÊM KHÁCH HÀNG
                            @endif
                        </strong>
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
                        <div class="col-12 text-right">
                            <button type="submit" name="create" class="btn btn-sm btn-success">
                                <i class="fa fa-save"></i> Lưu
                            </button>
                            @if($role == 'admin' || $role == 'employee')
                                <a class="btn btn-sm btn-info" href="{{ route('admin.user.employees') }}">
                                    <i class="fa fa-arrow-left"></i> Về danh sách
                                </a>
                            @else
                                <a class="btn btn-sm btn-info" href="{{ route('admin.user.customers') }}">
                                    <i class="fa fa-arrow-left"></i> Về danh sách
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="fullname">Họ tên</label>
                                <input type="text" value="{{ old('fullname', '') }}" name="fullname" id="fullname" class="form-control @error('fullname') is-invalid @enderror">
                                @error('fullname')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="phone">Số điện thoại <span class="text-danger">*</span> <small class="text-muted">(dùng để đăng nhập)</small></label>
                                <input type="text" value="{{ old('phone', '') }}" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" placeholder="0912345678">
                                @error('phone')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="email">Email</label>
                                <input type="text" value="{{ old('email', '') }}" name="email" id="email" class="form-control @error('email') is-invalid @enderror" autocomplete="off">
                                @error('email')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Để trống nếu tạo khách hàng (customer)</small>
                            </div>
                            <div class="mb-3">
                                <label for="gender">Giới tính</label>
                                <select name="gender" id="gender" class="form-control">
                                    <option value="1">Nam</option>
                                    <option value="0">Nữ</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="address">Địa chỉ</label>
                                <input type="text" value="{{ old('address', '') }}" name="address" id="address" class="form-control @error('address') is-invalid @enderror">
                                @error('address')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="username">Tên người dùng</label>
                                <input type="text" value="{{ old('username', '') }}" name="username" id="username" class="form-control @error('username') is-invalid @enderror" autocomplete="off">
                                @error('username')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Để trống nếu tạo khách hàng (customer)</small>
                            </div>
                            <div class="mb-3">
                                <label for="password">Mật khẩu <span class="text-danger">*</span></label>
                                <input type="password" value="" name="password" id="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
                                @error('password')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Để trống nếu tạo khách hàng (customer)</small>
                            </div>
                            <div class="mb-3">
                                <label for="password_re">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                                <input type="password" value="" name="password_re" id="password_re" class="form-control @error('password_re') is-invalid @enderror">
                                @error('password_re')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="roles">Quyền</label>
                                <select name="roles" id="roles" class="form-control">
                                    <option value="customer" {{ $role == 'customer' ? 'selected' : '' }}>Khách hàng</option>
                                    <option value="employee" {{ $role == 'employee' ? 'selected' : '' }}>Nhân viên</option>
                                    <option value="admin" {{ $role == 'admin' ? 'selected' : '' }}>Quản lý</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="image">Hình</label>
                                <input type="file" name="image" id="image" class="form-control @error('image') is-invalid @enderror">
                                
                                @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="status">Trạng thái</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="2">Không hoạt động</option>
                                    <option value="1">Hoạt động</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </form>
        <!-- /.CONTENT -->
</x-layout-backend>