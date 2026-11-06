<x-layout-backend>
    @php
        $userRole = $role ?? request('role', 'employee');
        $isEmployee = ($userRole == 'admin' || $userRole == 'employee');
    @endphp

    <div class="max-w-2xl mx-auto py-6 px-4">
        <!-- Header -->
        <div class="flex items-center justify-between pb-4 mb-5 border-b border-gray-200">
            <div>
                <h1 class="text-lg font-bold text-gray-800">
                    {{ $isEmployee ? 'Thêm mới nhân viên' : 'Thêm mới khách hàng' }}
                </h1>
                <p class="text-xs text-gray-500 mt-0.5">
                    {{ $isEmployee ? 'Tạo tài khoản và phân quyền cho nhân viên' : 'Tạo thông tin khách hàng mới' }}
                </p>
            </div>
            <a href="{{ $isEmployee ? route('admin.user.employees') : route('admin.user.customers') }}" 
               class="px-3.5 py-1.5 text-xs font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                Quay lại
            </a>
        </div>

        @if(isset($errors) && $errors->any())
            <div class="mb-5 p-3.5 rounded-lg bg-red-50 border border-red-200 text-red-700 text-xs">
                <div class="font-bold mb-1">Vui lòng kiểm tra lại:</div>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="createUserForm" action="{{ route('admin.user.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-xl border border-gray-200 shadow-xs p-6 space-y-4">
            @csrf

            @if(!$isEmployee)
                <!-- Form Thêm mới Khách hàng -->
                <input type="hidden" name="roles" value="customer">
                <input type="hidden" name="status" value="1">

                <div>
                    <label for="fullname" class="block text-xs font-semibold text-gray-700 mb-1">
                        Họ và tên <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="fullname" id="fullname" 
                           value="{{ old('fullname', '') }}"
                           placeholder="Ví dụ: Nguyễn Văn An"
                           required
                           class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#F47716] focus:border-[#F47716] transition @error('fullname') border-red-500 @enderror">
                    @error('fullname')
                        <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="phone" class="block text-xs font-semibold text-gray-700 mb-1">
                            Số điện thoại <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="phone" id="phone" 
                               value="{{ old('phone', '') }}"
                               placeholder="Ví dụ: 0988123456"
                               required
                               class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#F47716] focus:border-[#F47716] transition @error('phone') border-red-500 @enderror">
                        @error('phone')
                            <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="gender" class="block text-xs font-semibold text-gray-700 mb-1">
                            Giới tính <span class="text-red-500">*</span>
                        </label>
                        <select name="gender" id="gender" 
                                class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#F47716] focus:border-[#F47716] transition bg-white @error('gender') border-red-500 @enderror">
                            <option value="1" {{ old('gender', '1') == '1' ? 'selected' : '' }}>Nam</option>
                            <option value="0" {{ old('gender') == '0' ? 'selected' : '' }}>Nữ</option>
                        </select>
                        @error('gender')
                            <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="address" class="block text-xs font-semibold text-gray-700 mb-1">
                        Địa chỉ <span class="text-gray-400 font-normal">(tùy chọn)</span>
                    </label>
                    <input type="text" name="address" id="address" 
                           value="{{ old('address', '') }}"
                           placeholder="Ví dụ: Cụm 1, TT. Phúc Thọ, Hà Nội"
                           class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#F47716] focus:border-[#F47716] transition @error('address') border-red-500 @enderror">
                    @error('address')
                        <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

            @else
                <!-- Form Thêm mới Nhân viên -->
                <div>
                    <label for="fullname" class="block text-xs font-semibold text-gray-700 mb-1">
                        Họ và tên <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="fullname" id="fullname" 
                           value="{{ old('fullname', '') }}"
                           placeholder="Ví dụ: Trần Văn Nam"
                           required
                           class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#F47716] focus:border-[#F47716] transition @error('fullname') border-red-500 @enderror">
                    @error('fullname')
                        <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="phone" class="block text-xs font-semibold text-gray-700 mb-1">
                            Số điện thoại <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="phone" id="phone" 
                               value="{{ old('phone', '') }}"
                               placeholder="Ví dụ: 0338990538"
                               required
                               class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#F47716] focus:border-[#F47716] transition @error('phone') border-red-500 @enderror">
                        @error('phone')
                            <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="gender" class="block text-xs font-semibold text-gray-700 mb-1">
                            Giới tính <span class="text-red-500">*</span>
                        </label>
                        <select name="gender" id="gender" 
                                class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#F47716] focus:border-[#F47716] transition bg-white @error('gender') border-red-500 @enderror">
                            <option value="1" {{ old('gender', '1') == '1' ? 'selected' : '' }}>Nam</option>
                            <option value="0" {{ old('gender') == '0' ? 'selected' : '' }}>Nữ</option>
                        </select>
                        @error('gender')
                            <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="username" class="block text-xs font-semibold text-gray-700 mb-1">
                            Tên đăng nhập <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="username" id="username" 
                               value="{{ old('username', '') }}"
                               placeholder="Ví dụ: nhanvien1"
                               required
                               class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#F47716] focus:border-[#F47716] transition @error('username') border-red-500 @enderror">
                        @error('username')
                            <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="roles" class="block text-xs font-semibold text-gray-700 mb-1">
                            Vai trò <span class="text-red-500">*</span>
                        </label>
                        <select name="roles" id="roles" 
                                class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#F47716] focus:border-[#F47716] transition bg-white font-medium @error('roles') border-red-500 @enderror">
                            <option value="employee" {{ old('roles', $userRole) == 'employee' ? 'selected' : '' }}>Nhân viên</option>
                            <option value="admin" {{ old('roles', $userRole) == 'admin' ? 'selected' : '' }}>Quản trị viên</option>
                        </select>
                        @error('roles')
                            <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-xs font-semibold text-gray-700 mb-1">
                            Mật khẩu <span class="text-red-500">*</span>
                        </label>
                        <input type="password" name="password" id="password" 
                               placeholder="Tối thiểu 6 ký tự"
                               required
                               class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#F47716] focus:border-[#F47716] transition @error('password') border-red-500 @enderror">
                        @error('password')
                            <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_re" class="block text-xs font-semibold text-gray-700 mb-1">
                            Nhập lại mật khẩu <span class="text-red-500">*</span>
                        </label>
                        <input type="password" name="password_re" id="password_re" 
                               placeholder="Xác nhận lại mật khẩu"
                               required
                               class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#F47716] focus:border-[#F47716] transition @error('password_re') border-red-500 @enderror">
                        @error('password_re')
                            <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="status" class="block text-xs font-semibold text-gray-700 mb-1">
                            Trạng thái
                        </label>
                        <select name="status" id="status" 
                                class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#F47716] focus:border-[#F47716] transition bg-white">
                            <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Hoạt động</option>
                            <option value="2" {{ old('status') == '2' ? 'selected' : '' }}>Tạm khóa</option>
                        </select>
                    </div>

                    <div>
                        <label for="image" class="block text-xs font-semibold text-gray-700 mb-1">
                            Ảnh đại diện <span class="text-gray-400 font-normal">(tùy chọn)</span>
                        </label>
                        <input type="file" name="image" id="image" accept="image/*"
                               class="w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-xs file:font-medium file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 border border-gray-300 rounded-lg p-1 bg-white">
                        @error('image')
                            <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            @endif

            <div class="pt-4 border-t border-gray-200 flex items-center justify-end gap-3">
                <a href="{{ $isEmployee ? route('admin.user.employees') : route('admin.user.customers') }}" 
                   class="px-4 py-2 text-xs font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    Hủy bỏ
                </a>
                <button type="submit" 
                        class="px-5 py-2 text-xs font-bold text-white bg-[#F47716] hover:bg-[#e06509] rounded-lg transition shadow-xs cursor-pointer">
                    {{ $isEmployee ? 'Lưu nhân viên' : 'Lưu khách hàng' }}
                </button>
            </div>
        </form>
    </div>
</x-layout-backend>
