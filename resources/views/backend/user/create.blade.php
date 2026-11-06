<x-layout-backend>
    @php
        $userRole = $role ?? request('role', 'customer');
        $isEmployee = ($userRole == 'admin' || $userRole == 'employee');
    @endphp

    <div class="space-y-5 max-w-5xl mx-auto pb-12">
        <!-- Top Bar -->
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 tracking-tight flex items-center gap-2">
                    <i class="fa-solid fa-user-plus text-[#F47716]"></i>
                    {{ $isEmployee ? 'Thêm mới nhân viên' : 'Thêm mới khách hàng' }}
                </h1>
            </div>
            <div class="flex items-center gap-2.5">
                <a href="{{ $isEmployee ? route('admin.user.employees') : route('admin.user.customers') }}" 
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 text-xs font-semibold shadow-xs transition">
                    <i class="fa-solid fa-arrow-left text-[11px]"></i> Quay lại
                </a>
                <button type="submit" form="createUserForm" 
                        class="inline-flex items-center gap-1.5 px-5 py-2 rounded-lg bg-[#F47716] hover:bg-[#e06509] text-white text-xs font-bold shadow-sm transition cursor-pointer">
                    <i class="fa-solid fa-floppy-disk text-[11px]"></i> {{ $isEmployee ? 'Lưu tài khoản' : 'Lưu khách hàng' }}
                </button>
            </div>
        </div>

        @if(isset($errors) && $errors->any())
            <div class="p-3.5 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                <div class="font-bold mb-1 flex items-center gap-1.5 text-rose-900">
                    <i class="fa-solid fa-circle-exclamation"></i> Vui lòng kiểm tra lại thông tin:
                </div>
                <ul class="list-disc list-inside space-y-0.5 text-rose-700">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Form Create User -->
        <form id="createUserForm" action="{{ route('admin.user.store') }}" method="POST" enctype="multipart/form-data" autocomplete="off">
            @csrf

            @if(!$isEmployee)
                <!-- Form Quản lý khách hàng: Chỉ lưu trữ tên, sđt, email, giới tính, địa chỉ -->
                <div class="max-w-xl mx-auto bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-4">
                    <div class="flex items-center gap-2.5 pb-3 border-b border-gray-100">
                        <span class="w-8 h-8 rounded-lg bg-orange-100 text-[#F47716] flex items-center justify-center text-sm">
                            <i class="fa-solid fa-user-plus"></i>
                        </span>
                        <div>
                            <h2 class="text-sm font-bold text-gray-900">Thông tin cơ bản của khách hàng</h2>
                        </div>
                    </div>

                    <input type="hidden" name="roles" value="customer">
                    <input type="hidden" name="status" value="1">

                    <!-- 1. Họ và tên -->
                    <div>
                        <label for="fullname" class="block text-xs font-semibold text-gray-700 mb-1">
                            Họ và tên <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <i class="fa-solid fa-user absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                            <input type="text" name="fullname" id="fullname" 
                                   value="{{ old('fullname', '') }}"
                                   placeholder="Ví dụ: Nguyễn Văn An"
                                   required
                                   class="w-full pl-8 pr-3 py-2.5 text-xs border rounded-lg focus:outline-hidden focus:ring-1 focus:ring-[#F47716] focus:border-[#F47716] transition @error('fullname') border-rose-400 bg-rose-50/30 @else border-gray-300 @enderror">
                        </div>
                        @error('fullname')
                            <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 2. Số điện thoại & 3. Giới tính -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label for="phone" class="block text-xs font-semibold text-gray-700 mb-1">
                                Số điện thoại <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <i class="fa-solid fa-phone absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                                <input type="text" name="phone" id="phone" 
                                       value="{{ old('phone', '') }}"
                                       placeholder="Ví dụ: 0988123456"
                                       required
                                       class="w-full pl-8 pr-3 py-2.5 text-xs font-mono border rounded-lg focus:outline-hidden focus:ring-1 focus:ring-[#F47716] focus:border-[#F47716] transition @error('phone') border-rose-400 bg-rose-50/30 @else border-gray-300 @enderror">
                            </div>
                            @error('phone')
                                <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="gender" class="block text-xs font-semibold text-gray-700 mb-1">
                                Giới tính
                            </label>
                            <div class="relative">
                                <i class="fa-solid fa-venus-mars absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                                <select name="gender" id="gender" 
                                        class="w-full pl-8 pr-3 py-2.5 text-xs border border-gray-300 rounded-lg focus:outline-hidden focus:ring-1 focus:ring-[#F47716] focus:border-[#F47716] transition bg-white">
                                    <option value="1" {{ old('gender', '1') == '1' ? 'selected' : '' }}>Nam</option>
                                    <option value="0" {{ old('gender') == '0' ? 'selected' : '' }}>Nữ</option>
                                </select>
                            </div>
                            @error('gender')
                                <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- 4. Email -->
                    <div>
                        <label for="email" class="block text-xs font-semibold text-gray-700 mb-1">
                            Email <span class="text-gray-400 font-normal">(tùy chọn)</span>
                        </label>
                        <div class="relative">
                            <i class="fa-solid fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                            <input type="email" name="email" id="email" 
                                   value="{{ old('email', '') }}"
                                   placeholder="Ví dụ: khachhang@gmail.com"
                                   class="w-full pl-8 pr-3 py-2.5 text-xs border rounded-lg focus:outline-hidden focus:ring-1 focus:ring-[#F47716] focus:border-[#F47716] transition @error('email') border-rose-400 bg-rose-50/30 @else border-gray-300 @enderror">
                        </div>
                        @error('email')
                            <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 5. Địa chỉ -->
                    <div>
                        <label for="address" class="block text-xs font-semibold text-gray-700 mb-1">
                            Địa chỉ thường trú <span class="text-gray-400 font-normal">(tùy chọn)</span>
                        </label>
                        <div class="relative">
                            <i class="fa-solid fa-location-dot absolute left-3 top-3 text-gray-400 text-xs"></i>
                            <textarea name="address" id="address" rows="3" 
                                      placeholder="Ví dụ: Cụm 1, TT. Phúc Thọ, Huyện Phúc Thọ, Hà Nội"
                                      class="w-full pl-8 pr-3 py-2.5 text-xs border rounded-lg focus:outline-hidden focus:ring-1 focus:ring-[#F47716] focus:border-[#F47716] transition @error('address') border-rose-400 bg-rose-50/30 @else border-gray-300 @enderror">{{ old('address', '') }}</textarea>
                        </div>
                        @error('address')
                            <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-12 gap-5">
                    <!-- Left Column: Thông tin cá nhân & Liên hệ -->
                    <div class="md:col-span-7 bg-white rounded-xl border border-gray-200 shadow-2xs p-5 space-y-4">
                        <div class="flex items-center gap-2 pb-3 border-b border-gray-100">
                            <span class="w-7 h-7 rounded-lg bg-orange-100 text-[#F47716] flex items-center justify-center text-xs">
                                <i class="fa-solid fa-id-card"></i>
                            </span>
                            <div>
                                <h2 class="text-sm font-bold text-gray-900">Thông tin cá nhân & Liên hệ</h2>
                            </div>
                        </div>

                        <!-- Họ và tên -->
                        <div>
                            <label for="fullname" class="block text-xs font-semibold text-gray-700 mb-1">
                                Họ và tên <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <i class="fa-solid fa-user absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                                <input type="text" name="fullname" id="fullname" 
                                       value="{{ old('fullname', '') }}"
                                       placeholder="Ví dụ: Trần Văn Nam"
                                       class="w-full pl-8 pr-3 py-2 text-xs border rounded-lg focus:outline-hidden focus:ring-1 focus:ring-[#F47716] focus:border-[#F47716] transition @error('fullname') border-rose-400 bg-rose-50/30 @else border-gray-300 @enderror">
                            </div>
                            @error('fullname')
                                <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Số điện thoại & Giới tính -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label for="phone" class="block text-xs font-semibold text-gray-700 mb-1">
                                    Số điện thoại <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <i class="fa-solid fa-phone absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                                    <input type="text" name="phone" id="phone" 
                                           value="{{ old('phone', '') }}"
                                           placeholder="0338990538"
                                           class="w-full pl-8 pr-3 py-2 text-xs font-mono border rounded-lg focus:outline-hidden focus:ring-1 focus:ring-[#F47716] focus:border-[#F47716] transition @error('phone') border-rose-400 bg-rose-50/30 @else border-gray-300 @enderror">
                                </div>
                                @error('phone')
                                    <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="gender" class="block text-xs font-semibold text-gray-700 mb-1">
                                    Giới tính
                                </label>
                                <div class="relative">
                                    <i class="fa-solid fa-venus-mars absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                                    <select name="gender" id="gender" 
                                            class="w-full pl-8 pr-3 py-2 text-xs border border-gray-300 rounded-lg focus:outline-hidden focus:ring-1 focus:ring-[#F47716] focus:border-[#F47716] transition bg-white">
                                        <option value="1" {{ old('gender', '1') == '1' ? 'selected' : '' }}>Nam</option>
                                        <option value="0" {{ old('gender') == '0' ? 'selected' : '' }}>Nữ</option>
                                    </select>
                                </div>
                                @error('gender')
                                    <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-xs font-semibold text-gray-700 mb-1">
                                Email <span class="text-gray-400 font-normal">(tùy chọn)</span>
                            </label>
                            <div class="relative">
                                <i class="fa-solid fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                                <input type="email" name="email" id="email" 
                                       value="{{ old('email', '') }}"
                                       placeholder="nhanvien@gmail.com"
                                       class="w-full pl-8 pr-3 py-2 text-xs border rounded-lg focus:outline-hidden focus:ring-1 focus:ring-[#F47716] focus:border-[#F47716] transition @error('email') border-rose-400 bg-rose-50/30 @else border-gray-300 @enderror">
                            </div>
                            @error('email')
                                <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Địa chỉ -->
                        <div>
                            <label for="address" class="block text-xs font-semibold text-gray-700 mb-1">
                                Địa chỉ thường trú <span class="text-gray-400 font-normal">(tùy chọn)</span>
                            </label>
                            <div class="relative">
                                <i class="fa-solid fa-location-dot absolute left-3 top-2.5 text-gray-400 text-xs"></i>
                                <textarea name="address" id="address" rows="2" 
                                          placeholder="Ví dụ: 120 Phúc Hòa, Phúc Thọ, Hà Nội"
                                          class="w-full pl-8 pr-3 py-2 text-xs border rounded-lg focus:outline-hidden focus:ring-1 focus:ring-[#F47716] focus:border-[#F47716] transition @error('address') border-rose-400 bg-rose-50/30 @else border-gray-300 @enderror">{{ old('address', '') }}</textarea>
                            </div>
                            @error('address')
                                <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Right Column: Tài khoản, Mật khẩu & Quyền hạn -->
                    <div class="md:col-span-5 space-y-4">
                        <!-- Card Tài khoản & Phân quyền -->
                        <div class="bg-white rounded-xl border border-gray-200 shadow-2xs p-5 space-y-4">
                            <div class="flex items-center gap-2 pb-3 border-b border-gray-100">
                                <span class="w-7 h-7 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-xs">
                                    <i class="fa-solid fa-shield-halved"></i>
                                </span>
                                <div>
                                    <h2 class="text-sm font-bold text-gray-900">Tài khoản & Phân quyền</h2>
                                </div>
                            </div>

                            <!-- Tên đăng nhập -->
                            <div>
                                <label for="username" class="block text-xs font-semibold text-gray-700 mb-1">
                                    Tên đăng nhập <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <i class="fa-solid fa-at absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                                    <input type="text" name="username" id="username" 
                                           value="{{ old('username', '') }}"
                                           placeholder="nhanvien1"
                                           class="w-full pl-8 pr-3 py-2 text-xs font-mono border rounded-lg focus:outline-hidden focus:ring-1 focus:ring-[#F47716] focus:border-[#F47716] transition @error('username') border-rose-400 bg-rose-50/30 @else border-gray-300 @enderror">
                                </div>
                                @error('username')
                                    <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Quyền (Role) -->
                            <div>
                                <label for="roles" class="block text-xs font-semibold text-gray-700 mb-1">
                                    Vai trò / Phân quyền <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <i class="fa-solid fa-user-gear absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                                    <select name="roles" id="roles" 
                                            class="w-full pl-8 pr-3 py-2 text-xs border border-gray-300 rounded-lg focus:outline-hidden focus:ring-1 focus:ring-[#F47716] focus:border-[#F47716] transition bg-white font-semibold">
                                        <option value="employee" {{ old('roles', $role) == "employee" ? 'selected' : '' }}>
                                            Nhân viên
                                        </option>
                                        <option value="admin" {{ old('roles', $role) == "admin" ? 'selected' : '' }}>
                                            Quản trị viên
                                        </option>
                                    </select>
                                </div>
                                @error('roles')
                                    <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                                @enderror

                                <!-- Bảng làm rõ phân quyền chức năng trực quan -->
                                <div class="mt-3 space-y-2">
                                    <div class="text-[11px] font-bold uppercase tracking-wider text-gray-500 flex items-center justify-between">
                                        <span>Phạm vi chức năng vai trò</span>
                                        <span id="roleBadgeLive" class="px-2 py-0.5 rounded-full text-[10px] font-bold"></span>
                                    </div>

                                    <!-- Box Quyền Quản trị viên -->
                                    <div id="roleBoxAdmin" class="p-3.5 rounded-lg border border-orange-200 bg-orange-50/50 space-y-2 transition-all">
                                        <div class="font-bold text-orange-950 flex items-center gap-1.5 text-xs">
                                            Quản trị viên
                                        </div>
                                        <div class="grid grid-cols-1 gap-1 text-[11px] text-gray-700">
                                            <div class="flex items-center gap-1.5 text-emerald-700">
                                                <i class="fa-solid fa-check text-[10px]"></i> Xem toàn bộ báo cáo doanh thu & tài chính
                                            </div>
                                            <div class="flex items-center gap-1.5 text-emerald-700">
                                                <i class="fa-solid fa-check text-[10px]"></i> Quản lý kho, nhập hàng, giá bán & thực đơn
                                            </div>
                                            <div class="flex items-center gap-1.5 text-emerald-700">
                                                <i class="fa-solid fa-check text-[10px]"></i> Quản trị phân quyền tài khoản nhân viên
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Box Quyền Nhân viên -->
                                    <div id="roleBoxEmployee" class="p-3.5 rounded-lg border border-blue-200 bg-blue-50/50 space-y-2 transition-all">
                                        <div class="font-bold text-blue-950 flex items-center gap-1.5 text-xs">
                                            Nhân viên
                                        </div>
                                        <div class="grid grid-cols-1 gap-1 text-[11px] text-gray-700">
                                            <div class="flex items-center gap-1.5 text-blue-800">
                                                <i class="fa-solid fa-check text-[10px]"></i> Bán hàng POS, nhận order bàn & phục vụ khách
                                            </div>
                                            <div class="flex items-center gap-1.5 text-blue-800">
                                                <i class="fa-solid fa-check text-[10px]"></i> Thanh toán đơn hàng bàn & xuất hóa đơn VietQR
                                            </div>
                                            <div class="flex items-center gap-1.5 text-gray-500">
                                                <i class="fa-solid fa-xmark text-[10px] text-rose-500"></i> Giới hạn xem cấu hình & doanh thu tổng
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Mật khẩu & Nhập lại -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 pt-2 border-t border-gray-100">
                                <div>
                                    <label for="password" class="block text-xs font-semibold text-gray-700 mb-1">
                                        Mật khẩu <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="password" name="password" id="password" 
                                           placeholder="••••••••"
                                           class="w-full px-3 py-2 text-xs border rounded-lg focus:outline-hidden focus:ring-1 focus:ring-[#F47716] focus:border-[#F47716] transition @error('password') border-rose-400 bg-rose-50/30 @else border-gray-300 @enderror">
                                    @error('password')
                                        <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="password_re" class="block text-xs font-semibold text-gray-700 mb-1">
                                        Nhập lại mật khẩu <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="password" name="password_re" id="password_re" 
                                           placeholder="••••••••"
                                           class="w-full px-3 py-2 text-xs border rounded-lg focus:outline-hidden focus:ring-1 focus:ring-[#F47716] focus:border-[#F47716] transition @error('password_re') border-rose-400 bg-rose-50/30 @else border-gray-300 @enderror">
                                    @error('password_re')
                                        <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Ảnh đại diện & Trạng thái -->
                            <div class="pt-2 border-t border-gray-100 space-y-3.5">
                                <div>
                                    <label for="image" class="block text-xs font-semibold text-gray-700 mb-1">
                                        Ảnh đại diện
                                    </label>
                                    <input type="file" name="image" id="image" accept="image/*"
                                           class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-orange-50 file:text-[#F47716] hover:file:bg-orange-100 cursor-pointer border border-gray-200 rounded-lg p-1 bg-white">
                                    @error('image')
                                        <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="status" class="block text-xs font-semibold text-gray-700 mb-1">
                                        Trạng thái hoạt động
                                    </label>
                                    <select name="status" id="status" 
                                            class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:outline-hidden focus:ring-1 focus:ring-[#F47716] focus:border-[#F47716] transition bg-white">
                                        <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Hoạt động bình thường</option>
                                        <option value="2" {{ old('status') == '2' ? 'selected' : '' }}>Tạm dừng hoạt động</option>
                                    </select>
                                    @error('status')
                                        <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
                            <div>
                                <h2 class="text-sm font-bold text-gray-900">Tài khoản & Phân quyền</h2>
                            </div>
                        </div>

                        <!-- Tên đăng nhập -->
                        <div>
                            <label for="username" class="block text-xs font-semibold text-gray-700 mb-1">
                                Tên đăng nhập <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <i class="fa-solid fa-at absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                                <input type="text" name="username" id="username" 
                                       value="{{ old('username', '') }}"
                                       placeholder="nhanvien1"
                                       class="w-full pl-8 pr-3 py-2 text-xs font-mono border rounded-lg focus:outline-hidden focus:ring-1 focus:ring-[#F47716] focus:border-[#F47716] transition @error('username') border-rose-400 bg-rose-50/30 @else border-gray-300 @enderror">
                            </div>
                            @error('username')
                                <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Quyền (Role) -->
                        <div>
                            <label for="roles" class="block text-xs font-semibold text-gray-700 mb-1">
                                Vai trò / Phân quyền <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <i class="fa-solid fa-user-gear absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                                <select name="roles" id="roles" 
                                        class="w-full pl-8 pr-3 py-2 text-xs border border-gray-300 rounded-lg focus:outline-hidden focus:ring-1 focus:ring-[#F47716] focus:border-[#F47716] transition bg-white font-semibold">
                                    <option value="employee" {{ old('roles', $role) == "employee" ? 'selected' : '' }}>
                                        Nhân viên
                                    </option>
                                    <option value="admin" {{ old('roles', $role) == "admin" ? 'selected' : '' }}>
                                        Quản trị viên
                                    </option>
                                    <option value="customer" {{ old('roles', $role) == "customer" ? 'selected' : '' }}>
                                        Thành viên / Khách hàng (Đặt bàn, gọi món website, xem đơn cá nhân)
                                    </option>
                                </select>
                            </div>
                            @error('roles')
                                <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                            @enderror

                            <!-- Bảng làm rõ phân quyền chức năng trực quan -->
                            <div class="mt-3 space-y-2">
                                <div class="text-[11px] font-bold uppercase tracking-wider text-gray-500 flex items-center justify-between">
                                    <span>Phạm vi chức năng vai trò</span>
                                    <span id="roleBadgeLive" class="px-2 py-0.5 rounded-full text-[10px] font-bold"></span>
                                </div>

                                <!-- Box Quyền Quản trị viên -->
                                <div id="roleBoxAdmin" class="p-3.5 rounded-lg border border-orange-200 bg-orange-50/50 space-y-2 transition-all">
                                    <div class="font-bold text-orange-950 flex items-center gap-1.5 text-xs">
                                        Quản trị viên
                                    </div>
                                    <div class="grid grid-cols-1 gap-1 text-[11px] text-gray-700">
                                        <div class="flex items-center gap-1.5 text-emerald-700 font-medium">
                                            <i class="fa-solid fa-check text-xs"></i> <span>Toàn quyền bán hàng POS & sơ đồ bàn phòng</span>
                                        </div>
                                        <div class="flex items-center gap-1.5 text-emerald-700 font-medium">
                                            <i class="fa-solid fa-check text-xs"></i> <span>Quản lý & thiết lập giá bán thực đơn</span>
                                        </div>
                                        <div class="flex items-center gap-1.5 text-emerald-700 font-medium">
                                            <i class="fa-solid fa-check text-xs"></i> <span>Quản lý kho hàng & lập phiếu nhập hàng</span>
                                        </div>
                                        <div class="flex items-center gap-1.5 text-emerald-700 font-medium">
                                            <i class="fa-solid fa-check text-xs"></i> <span>Phân quyền tài khoản nhân viên</span>
                                        </div>
                                        <div class="flex items-center gap-1.5 text-emerald-700 font-medium">
                                            <i class="fa-solid fa-check text-xs"></i> <span>Báo cáo doanh thu & dòng tiền</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Box Quyền Nhân viên -->
                                <div id="roleBoxEmployee" class="p-3.5 rounded-lg border border-blue-200 bg-blue-50/50 space-y-2 transition-all">
                                    <div class="font-bold text-blue-950 flex items-center gap-1.5 text-xs">
                                        Nhân viên
                                    </div>
                                    <div class="grid grid-cols-1 gap-1 text-[11px]">
                                        <div class="flex items-center gap-1.5 text-emerald-700 font-medium">
                                            <i class="fa-solid fa-check text-xs"></i> <span>Bán hàng POS: Mở bàn, gọi món, in hóa đơn</span>
                                        </div>
                                        <div class="flex items-center gap-1.5 text-emerald-700 font-medium">
                                            <i class="fa-solid fa-check text-xs"></i> <span>Chuyển bàn, gộp bàn và thanh toán</span>
                                        </div>
                                        <div class="flex items-center gap-1.5 text-emerald-700 font-medium">
                                            <i class="fa-solid fa-check text-xs"></i> <span>Nhận chuông báo gọi phục vụ từ bàn ăn</span>
                                        </div>
                                        <div class="flex items-center gap-1.5 text-emerald-700 font-medium">
                                            <i class="fa-solid fa-check text-xs"></i> <span>Cập nhật trạng thái đơn giao & kiểm tra đặt bàn</span>
                                        </div>
                                        <div class="flex items-center gap-1.5 text-rose-600 font-medium pt-1 border-t border-blue-100">
                                            <i class="fa-solid fa-xmark text-xs font-bold"></i> <span>Không thể xem giá vốn kho hàng & nhập kho</span>
                                        </div>
                                        <div class="flex items-center gap-1.5 text-rose-600 font-medium">
                                            <i class="fa-solid fa-xmark text-xs font-bold"></i> <span>Không thể sửa giá món hoặc xóa thực đơn</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Box Quyền Khách hàng / Thành viên -->
                                <div id="roleBoxCustomer" class="p-3.5 rounded-lg border border-emerald-200 bg-emerald-50/50 space-y-2 transition-all">
                                    <div class="font-bold text-emerald-950 flex items-center gap-1.5 text-xs">
                                        <i class="fa-solid fa-user text-emerald-600"></i> Thành viên (Customer - Khách hàng)
                                    </div>
                                    <div class="grid grid-cols-1 gap-1 text-[11px]">
                                        <div class="flex items-center gap-1.5 text-emerald-700 font-medium">
                                            <i class="fa-solid fa-check text-xs"></i> <span>Đăng nhập xem lịch sử đơn hàng cá nhân</span>
                                        </div>
                                        <div class="flex items-center gap-1.5 text-emerald-700 font-medium">
                                            <i class="fa-solid fa-check text-xs"></i> <span>Đặt bàn trước trực tuyến (nhận giảm 10%)</span>
                                        </div>
                                        <div class="flex items-center gap-1.5 text-emerald-700 font-medium">
                                            <i class="fa-solid fa-check text-xs"></i> <span>Đặt món giao tận nơi & gọi Trợ lý ảo AI</span>
                                        </div>
                                        <div class="flex items-center gap-1.5 text-rose-600 font-medium pt-1 border-t border-emerald-100">
                                            <i class="fa-solid fa-ban text-xs"></i> <span>Không có quyền truy cập vào trang quản trị (/admin)</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Trạng thái -->
                        <div>
                            <label for="status" class="block text-xs font-semibold text-gray-700 mb-1">
                                Trạng thái hoạt động
                            </label>
                            <select name="status" id="status" 
                                    class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:outline-hidden focus:ring-1 focus:ring-[#F47716] focus:border-[#F47716] transition bg-white font-medium">
                                <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>
                                    Đang hoạt động (Bình thường)
                                </option>
                                <option value="2" {{ old('status') == '2' ? 'selected' : '' }}>
                                    Tạm khóa (Không thể đăng nhập)
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Card Mật khẩu -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-2xs p-5 space-y-3.5">
                        <div class="flex items-center gap-2 pb-2.5 border-b border-gray-100">
                            <span class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs">
                                <i class="fa-solid fa-key"></i>
                            </span>
                            <div>
                                <h2 class="text-sm font-bold text-gray-900">Mật khẩu tài khoản <span class="text-rose-500">*</span></h2>
                            </div>
                        </div>

                        <div>
                            <label for="password" class="block text-xs font-semibold text-gray-700 mb-1">
                                Mật khẩu <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <i class="fa-solid fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                                <input type="password" name="password" id="password" 
                                       autocomplete="new-password"
                                       placeholder="••••••••"
                                       class="w-full pl-8 pr-3 py-2 text-xs border rounded-lg focus:outline-hidden focus:ring-1 focus:ring-[#F47716] focus:border-[#F47716] transition @error('password') border-rose-400 bg-rose-50/30 @else border-gray-300 @enderror">
                            </div>
                            @error('password')
                                <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_re" class="block text-xs font-semibold text-gray-700 mb-1">
                                Nhập lại mật khẩu <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <i class="fa-solid fa-lock-open absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                                <input type="password" name="password_re" id="password_re" 
                                       autocomplete="new-password"
                                       placeholder="••••••••"
                                       class="w-full pl-8 pr-3 py-2 text-xs border rounded-lg focus:outline-hidden focus:ring-1 focus:ring-[#F47716] focus:border-[#F47716] transition @error('password_re') border-rose-400 bg-rose-50/30 @else border-gray-300 @enderror">
                            </div>
                            @error('password_re')
                                <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Card Ảnh đại diện -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-2xs p-5 space-y-3.5">
                        <div class="flex items-center gap-2 pb-2.5 border-b border-gray-100">
                            <span class="w-7 h-7 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center text-xs">
                                <i class="fa-solid fa-image"></i>
                            </span>
                            <div>
                                <h2 class="text-sm font-bold text-gray-900">Ảnh đại diện</h2>
                            </div>
                        </div>

                        <div>
                            <input type="file" name="image" id="image" accept="image/*"
                                   class="block w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 transition">
                            <p class="text-[10px] text-gray-400 mt-1">Định dạng JPG, PNG, GIF (Tối đa 2MB)</p>
                        </div>
                        @error('image')
                            <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Bottom Sticky Submit Bar -->
            <div class="flex items-center justify-end gap-3 pt-5 border-t border-gray-200">
                <a href="{{ $isEmployee ? route('admin.user.employees') : route('admin.user.customers') }}" 
                   class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 text-xs font-semibold shadow-xs transition">
                    Hủy bỏ
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 rounded-lg bg-[#F47716] hover:bg-[#e06509] text-white text-xs font-bold shadow-sm transition flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-floppy-disk text-xs"></i>
                    Lưu tài khoản mới
                </button>
            </div>
        </form>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const rolesSelect = document.getElementById('roles');
        const boxAdmin = document.getElementById('roleBoxAdmin');
        const boxEmployee = document.getElementById('roleBoxEmployee');
        const boxCustomer = document.getElementById('roleBoxCustomer');
        const badge = document.getElementById('roleBadgeLive');

        function updateRoleDisplay() {
            if (!rolesSelect) return;
            const val = rolesSelect.value;
            if (boxAdmin) boxAdmin.classList.add('hidden');
            if (boxEmployee) boxEmployee.classList.add('hidden');
            if (boxCustomer) boxCustomer.classList.add('hidden');

            if (val === 'admin') {
                if (boxAdmin) boxAdmin.classList.remove('hidden');
                if (badge) {
                    badge.className = 'px-2 py-0.5 rounded-full text-[10px] font-bold bg-orange-100 text-[#F47716] border border-orange-200';
                    badge.innerHTML = 'Quản trị viên';
                }
            } else if (val === 'employee') {
                if (boxEmployee) boxEmployee.classList.remove('hidden');
                if (badge) {
                    badge.className = 'px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700 border border-blue-200';
                    badge.innerHTML = 'Nhân viên';
                }
            } else {
                if (boxCustomer) boxCustomer.classList.remove('hidden');
                if (badge) {
                    badge.className = 'px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200';
                    badge.innerHTML = 'Thành viên / Khách hàng';
                }
            }
        }

        if (rolesSelect) {
            rolesSelect.addEventListener('change', updateRoleDisplay);
            updateRoleDisplay();
        }
    });
    </script>
</x-layout-backend>
