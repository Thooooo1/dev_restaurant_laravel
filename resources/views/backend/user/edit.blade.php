<x-layout-backend>
    <!-- CONTENT -->
    <form action="{{route('admin.user.update',['user'=>$user->id])}}" method="post" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Thêm thành viên</h1>
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
                            <a class="btn btn-sm btn-info" href="{{ url('admin/user/') }}">
                                <i class="fa fa-arrow-left"></i> Về danh sách
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="fullname">Họ tên</label>
                                <input type="text" value="{{ old('fullname', $user->fullname) }}" name="fullname" id="fullname" class="form-control @error('fullname') is-invalid @enderror">
                                @error('fullname')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="phone">Điện thoại</label>
                                <input type="text" value="{{ old('phone', $user->phone) }}" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror">
                                @error('phone')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="email">Email</label>
                                <input type="text" value="{{ old('email', $user->email) }}" name="email" id="email" class="form-control @error('email') is-invalid @enderror">
                                @error('phone')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="gender">Giới tính</label>
                                <select name="gender" id="gender" class="form-control">
                                    <option value="1"  {{ old('gender', $user->gender) == "1" ? 'selected' : '' }}>Nam</option>
                                    <option value="0" {{ old('gender', $user->gender) == "0" ? 'selected' : '' }}>Nữ</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="address">Địa chỉ</label>
                                <input type="text" value="{{ old('address', $user->address) }}" name="address" id="address" class="form-control @error('address') is-invalid @enderror">
                                @error('address')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="username">Tên người dùng</label>
                                <input type="text" value="{{ old('username', $user->username) }}" name="username" id="username" class="form-control @error('username') is-invalid @enderror">
                                @error('username')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="password">Mật khẩu</label>
                                <input type="password" value="{{ old('password', $user->password) }}" name="password" id="password" class="form-control @error('password') is-invalid @enderror">
                                @error('password')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="roles">Quyền</label>
                                <select name="roles" id="roles" class="form-control">
                                    <option value="customer" {{ old('roles', $user->roles) == "customer" ? 'selected' : '' }} > Khách hàng</option>
                                    <option value="admin" {{ old('roles', $user->roles) == "admin" ? 'selected' : '' }}>Quản lý</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="image">Hình</label>
                                <input type="file" name="image" id="image" class="form-control @error('image') is-invalid @enderror">
                                @if($user->image)
                                    <img src="{{ asset('images/user/' . $user->image) }}" alt="avatar" class="img-thumbnail mt-2" width="200">
                                @endif
                                @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="status">Trạng thái</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="2" {{ old('status', $user->status) == 2 ? 'selected' : '' }}>Không hoạt động</option>
                                <option value="1" {{ old('status', $user->status) == 1 ? 'selected' : '' }}>Hoạt động</option>
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