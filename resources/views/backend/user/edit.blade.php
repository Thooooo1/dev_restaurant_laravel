<x-layout-backend>
    @php
        $userRole = $role ?? ($user->roles ?? 'customer');
        $isEmployee = ($userRole == 'admin' || $userRole == 'employee');
        $genderVal = old('gender', $user->gender);
        $isMale = in_array((string)$genderVal, ['1', 'Nam', 'nam'], true);
        $isFemale = in_array((string)$genderVal, ['0', 'Nữ', 'nu', 'Nu'], true);
    @endphp

    <div class="max-w-2xl mx-auto py-6 px-4">
        <!-- Header -->
        <div class="flex items-center justify-between pb-4 mb-5 border-b border-gray-200">
            <div>
                <h1 class="text-lg font-bold text-gray-800">
                    {{ $isEmployee ? 'Cập nhật nhân viên' : 'Cập nhật khách hàng' }}
                </h1>
                <p class="text-xs text-gray-500 mt-0.5">
                    Đang chỉnh sửa: <span class="font-semibold text-gray-700">{{ $user->fullname }}</span>
                </p>
            </div>
            <a href="{{ $isEmployee ? route('admin.user.employees') : route('admin.user.customers') }}" 
               class="px-3.5 py-1.5 text-xs font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                Quay lại
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-xs font-medium">
                {{ session('error') }}
            </div>
        @endif

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

        <form id="editUserForm" action="{{ route('admin.user.update', ['user' => $user->id]) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-xl border border-gray-200 shadow-xs p-6 space-y-4">
            @csrf
            @method('PUT')

            @if(!$isEmployee)
                <!-- Form Khách hàng -->
                <input type="hidden" name="roles" value="customer">
                <input type="hidden" name="status" value="{{ $user->status ?? 1 }}">

                <div>
                    <label for="fullname" class="block text-xs font-semibold text-gray-700 mb-1">
                        Họ và tên <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="fullname" id="fullname" 
                           value="{{ old('fullname', $user->fullname) }}"
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
                               value="{{ old('phone', $user->phone) }}"
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
                            <option value="1" {{ $isMale ? 'selected' : '' }}>Nam</option>
                            <option value="0" {{ $isFemale ? 'selected' : '' }}>Nữ</option>
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
                           value="{{ old('address', $user->address) }}"
                           placeholder="Ví dụ: Cụm 1, TT. Phúc Thọ, Hà Nội"
                           class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#F47716] focus:border-[#F47716] transition @error('address') border-red-500 @enderror">
                    @error('address')
                        <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

            @else
                <!-- Form Nhân viên -->
                <div>
                    <label for="fullname" class="block text-xs font-semibold text-gray-700 mb-1">
                        Họ và tên <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="fullname" id="fullname" 
                           value="{{ old('fullname', $user->fullname) }}"
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
                               value="{{ old('phone', $user->phone) }}"
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
                            <option value="1" {{ $isMale ? 'selected' : '' }}>Nam</option>
                            <option value="0" {{ $isFemale ? 'selected' : '' }}>Nữ</option>
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
                               value="{{ old('username', $user->username) }}"
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
                            <option value="employee" {{ old('roles', $user->roles) == 'employee' ? 'selected' : '' }}>Nhân viên</option>
                            <option value="admin" {{ old('roles', $user->roles) == 'admin' ? 'selected' : '' }}>Quản trị viên</option>
                        </select>
                        @error('roles')
                            <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-gray-700 mb-1">
                        Mật khẩu mới <span class="text-gray-400 font-normal">(để trống nếu không muốn đổi mật khẩu)</span>
                    </label>
                    <input type="password" name="password" id="password" 
                           placeholder="Nhập mật khẩu mới nếu muốn thay đổi"
                           class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#F47716] focus:border-[#F47716] transition @error('password') border-red-500 @enderror">
                    @error('password')
                        <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="status" class="block text-xs font-semibold text-gray-700 mb-1">
                            Trạng thái
                        </label>
                        <select name="status" id="status" 
                                class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#F47716] focus:border-[#F47716] transition bg-white">
                            <option value="1" {{ old('status', $user->status) == '1' ? 'selected' : '' }}>Hoạt động</option>
                            <option value="2" {{ old('status', $user->status) == '2' ? 'selected' : '' }}>Tạm khóa</option>
                        </select>
                    </div>

                    <div>
                        <label for="image" class="block text-xs font-semibold text-gray-700 mb-1">
                            Ảnh đại diện <span class="text-gray-400 font-normal">(tùy chọn)</span>
                        </label>
                        <input type="file" name="image" id="image" accept="image/*"
                               class="w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-xs file:font-medium file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 border border-gray-300 rounded-lg p-1 bg-white">
                        @if($user->image && $user->image !== 'non_image.png')
                            <p class="text-[11px] text-gray-500 mt-1">Ảnh hiện tại: <span class="font-mono text-gray-700">{{ $user->image }}</span></p>
                        @endif
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
                    Lưu thay đổi
                </button>
            </div>
        </form>
    </div>
</x-layout-backend>
