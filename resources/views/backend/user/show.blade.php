<x-layout-backend>
    @php
        $role = $user->roles;
        $isAdmin = ($role == 'admin');
        $isEmployee = ($role == 'employee');
        $isCustomer = ($role == 'customer');
        $isMale = in_array((string)$user->gender, ['1', 'Nam', 'nam'], true);
    @endphp

    <div class="space-y-5 max-w-5xl mx-auto pb-12">
        <!-- Top Action Bar -->
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 tracking-tight">
                    {{ $isCustomer ? 'Thông tin khách hàng' : 'Hồ sơ chi tiết tài khoản' }}
                    <span class="text-sm font-normal text-gray-500">(#{{ $user->id }} - {{ $user->fullname }})</span>
                </h1>
            </div>
            <div class="flex items-center gap-2.5">
                <a href="{{ ($isAdmin || $isEmployee) ? route('admin.user.employees') : route('admin.user.customers') }}" 
                   class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 text-xs font-semibold shadow-xs transition">
                    &larr; Về danh sách
                </a>
                <a href="{{ route('admin.user.edit', $user->id) }}" 
                   class="inline-flex items-center px-4 py-2 rounded-lg bg-[#F47716] hover:bg-[#e06509] text-white text-xs font-bold shadow-sm transition">
                    Chỉnh sửa
                </a>
            </div>
        </div>

        @if($isCustomer)
            <!-- Giao diện Khách hàng: Chỉ hiển thị đúng 5 thông tin cơ bản: Tên, SĐT, Email, Giới tính, Địa chỉ -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-2xs p-6 max-w-2xl mx-auto">
                <h2 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-3 mb-4">
                    Thông tin cơ bản khách hàng
                </h2>
                <div class="space-y-3.5 text-sm">
                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-500 font-medium">Họ và tên</span>
                        <span class="text-gray-900 font-bold text-base">{{ $user->fullname }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-500 font-medium">Số điện thoại</span>
                        <span class="text-gray-900 font-mono font-semibold">{{ $user->phone }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-500 font-medium">Email</span>
                        <span class="text-gray-800">{{ $user->email ?: 'Chưa cập nhật' }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-500 font-medium">Giới tính</span>
                        <span class="font-medium text-gray-800">{{ $isMale ? 'Nam' : 'Nữ' }}</span>
                    </div>
                    <div class="flex items-start justify-between py-2">
                        <span class="text-gray-500 font-medium shrink-0">Địa chỉ</span>
                        <span class="text-gray-800 text-right max-w-sm">{{ $user->address ?: 'Chưa cập nhật' }}</span>
                    </div>
                </div>
            </div>
        @else
            <!-- 2 Column Layout cho Nhân viên & Quản trị viên -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-5">
            <!-- Left Column: Thông tin cá nhân & Ảnh -->
            <div class="md:col-span-5 space-y-4">
                <div class="bg-white rounded-xl border border-gray-200 shadow-2xs p-5 text-center">
                    <div class="w-24 h-24 rounded-full border-2 border-orange-100 overflow-hidden bg-gray-50 mx-auto mb-3 shadow-xs flex items-center justify-center">
                        @if($user->image && file_exists(public_path('images/user/' . $user->image)))
                            <img src="{{ asset('images/user/' . $user->image) }}" alt="{{ $user->fullname }}" class="w-full h-full object-cover">
                        @else
                            <span class="font-bold text-2xl text-gray-400 uppercase">
                                {{ substr($user->fullname ?? 'NV', 0, 2) }}
                            </span>
                        @endif
                    </div>
                    <h2 class="text-base font-bold text-gray-900">{{ $user->fullname }}</h2>
                    <p class="text-xs text-gray-500 font-mono mt-0.5">{{ '@' . ($user->username ?: $user->phone) }}</p>

                    <!-- Role Badge -->
                    <div class="mt-3 flex items-center justify-center gap-2">
                        @if($isAdmin)
                            <span class="text-xs font-bold text-gray-900">
                                Quản trị viên
                            </span>
                        @elseif($isEmployee)
                            <span class="text-xs font-bold text-gray-900">
                                Nhân viên
                            </span>
                        @else
                            <span class="text-xs font-bold text-gray-900">
                                Thành viên / Khách hàng
                            </span>
                        @endif

                        @if($user->status == 1)
                            <span class="text-xs font-medium text-[rgba(0,0,0,0.85)]">
                                Hoạt động
                            </span>
                        @else
                            <span class="text-xs font-medium text-[rgba(0,0,0,0.85)]">
                                Tạm khóa
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Thông tin liên hệ nhanh -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-2xs p-4 space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Thông tin liên hệ</h3>
                    <div class="space-y-2 text-xs">
                        <div class="flex items-center justify-between py-1 border-b border-gray-100">
                            <span class="text-gray-500 font-medium">Điện thoại</span>
                            <span class="font-mono font-semibold text-gray-800">{{ $user->phone }}</span>
                        </div>
                        <div class="flex items-center justify-between py-1 border-b border-gray-100">
                            <span class="text-gray-500 font-medium">Email</span>
                            <span class="text-gray-800">{{ $user->email ?: 'Chưa cập nhật' }}</span>
                        </div>
                        <div class="flex items-center justify-between py-1 border-b border-gray-100">
                            <span class="text-gray-500 font-medium">Giới tính</span>
                            <span class="font-medium text-gray-800">{{ $isMale ? 'Nam' : 'Nữ' }}</span>
                        </div>
                        <div class="flex items-start justify-between py-1">
                            <span class="text-gray-500 font-medium shrink-0">Địa chỉ</span>
                            <span class="text-gray-800 text-right max-w-[200px]">{{ $user->address ?: 'Chưa cập nhật' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Phân quyền & Chi tiết chức năng -->
            <div class="md:col-span-7 space-y-4">
                <!-- Bảng phân quyền chi tiết của tài khoản -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-2xs p-5 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                        <div>
                            <h2 class="text-sm font-bold text-gray-900">Phân quyền chức năng của tài khoản</h2>
                            <p class="text-[11px] text-gray-500">Phạm vi thao tác và giới hạn quyền trong hệ thống</p>
                        </div>
                        <span class="text-[11px] font-mono font-bold px-2 py-0.5 rounded bg-gray-100 text-gray-600">
                            Role: {{ $user->roles }}
                        </span>
                    </div>

                    @if($isAdmin)
                        <!-- Admin Capabilities -->
                        <div class="p-3.5 rounded-lg border border-orange-200 bg-orange-50/50 space-y-2">
                            <div class="text-xs font-bold text-orange-950">
                                Quản trị viên cấp cao (Admin)
                            </div>
                            <p class="text-[11px] text-gray-600">Tài khoản này có toàn quyền truy cập, điều hành và cấu hình mọi chức năng của hệ thống nhà hàng CIVILIZE.</p>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                            <div class="p-2.5 rounded-lg border border-gray-200/80 bg-gray-50/50 text-gray-800 font-medium text-[11.5px]">
                                Bán hàng POS tại bàn & in hóa đơn
                            </div>
                            <div class="p-2.5 rounded-lg border border-gray-200/80 bg-gray-50/50 text-gray-800 font-medium text-[11.5px]">
                                Quản lý thực đơn & sửa giá bán món
                            </div>
                            <div class="p-2.5 rounded-lg border border-gray-200/80 bg-gray-50/50 text-gray-800 font-medium text-[11.5px]">
                                Quản lý nhập kho & nhà cung cấp
                            </div>
                            <div class="p-2.5 rounded-lg border border-gray-200/80 bg-gray-50/50 text-gray-800 font-medium text-[11.5px]">
                                Phân quyền & quản lý nhân viên
                            </div>
                            <div class="p-2.5 rounded-lg border border-gray-200/80 bg-gray-50/50 text-gray-800 font-medium text-[11.5px]">
                                Báo cáo doanh thu & dòng tiền
                            </div>
                            <div class="p-2.5 rounded-lg border border-gray-200/80 bg-gray-50/50 text-gray-800 font-medium text-[11.5px]">
                                Quản lý bàn phòng & tải mã QR bàn
                            </div>
                        </div>
                    @elseif($isEmployee)
                        <!-- Employee Capabilities -->
                        <div class="p-3.5 rounded-lg border border-blue-200 bg-blue-50/50 space-y-2">
                            <div class="text-xs font-bold text-blue-950">
                                Nhân viên
                            </div>
                            <p class="text-[11px] text-gray-600">Tài khoản này được cấp quyền bán hàng trực tiếp tại bàn ăn, quản lý đơn và nhận chuông gọi phục vụ.</p>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <div class="text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-2">Chức năng được phép thao tác:</div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                    <div class="p-2.5 rounded-lg border border-gray-200/80 bg-gray-50/50 text-gray-800 font-medium text-[11.5px]">
                                        Bán hàng POS (Mở bàn, chọn món, thanh toán)
                                    </div>
                                    <div class="p-2.5 rounded-lg border border-gray-200/80 bg-gray-50/50 text-gray-800 font-medium text-[11.5px]">
                                        Điều phối bàn (Chuyển bàn, gộp bàn ăn)
                                    </div>
                                    <div class="p-2.5 rounded-lg border border-gray-200/80 bg-gray-50/50 text-gray-800 font-medium text-[11.5px]">
                                        Tiếp nhận chuông gọi phục vụ trực tiếp từ bàn
                                    </div>
                                    <div class="p-2.5 rounded-lg border border-gray-200/80 bg-gray-50/50 text-gray-800 font-medium text-[11.5px]">
                                        Cập nhật trạng thái đơn giao & kiểm tra đặt bàn
                                    </div>
                                </div>
                            </div>

                            <div>
                                <div class="text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-2">Giới hạn quyền hạn (Không được phép):</div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                    <div class="p-2.5 rounded-lg border border-gray-200/80 bg-gray-50/50 text-gray-600 text-[11.5px]">
                                        Không được xem giá vốn & tạo phiếu nhập kho
                                    </div>
                                    <div class="p-2.5 rounded-lg border border-gray-200/80 bg-gray-50/50 text-gray-600 text-[11.5px]">
                                        Không được sửa giá món hoặc xóa thực đơn
                                    </div>
                                    <div class="p-2.5 rounded-lg border border-gray-200/80 bg-gray-50/50 text-gray-600 text-[11.5px]">
                                        Không được tạo hoặc sửa tài khoản nhân viên khác
                                    </div>
                                    <div class="p-2.5 rounded-lg border border-gray-200/80 bg-gray-50/50 text-gray-600 text-[11.5px]">
                                        Không được can thiệp vào cài đặt website
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Customer Capabilities -->
                        <div class="p-3.5 rounded-lg border border-gray-200 bg-gray-50/50 space-y-2">
                            <div class="text-xs font-bold text-gray-900">
                                Thành viên / Thực khách (Customer)
                            </div>
                            <p class="text-[11px] text-gray-600">Tài khoản này dành cho khách hàng đặt bàn, mua món mang về và tương tác trên website nhà hàng.</p>
                        </div>
                        <div class="space-y-3">
                            <div class="text-[11px] font-bold text-gray-700 uppercase tracking-wider">Chức năng khả dụng:</div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                <div class="p-2.5 rounded-lg border border-gray-200/80 bg-gray-50/50 text-gray-800 font-medium text-[11.5px]">
                                    Đặt bàn trực tuyến giữ chỗ đẹp (giảm 10%)
                                </div>
                                <div class="p-2.5 rounded-lg border border-gray-200/80 bg-gray-50/50 text-gray-800 font-medium text-[11.5px]">
                                    Đặt món giao về nhà & thanh toán online/COD
                                </div>
                                <div class="p-2.5 rounded-lg border border-gray-200/80 bg-gray-50/50 text-gray-800 font-medium text-[11.5px]">
                                    Quét QR tại bàn gọi món & bấm chuông gọi nhân viên
                                </div>
                                <div class="p-2.5 rounded-lg border border-gray-200/80 bg-gray-50/50 text-gray-800 font-medium text-[11.5px]">
                                    Xem lịch sử bữa ăn & tra cứu trạng thái đơn hàng
                                </div>
                            </div>
                            <div class="p-3 rounded-lg border border-gray-200 bg-gray-50/50 text-gray-700 text-xs">
                                Tài khoản khách hàng <strong>tuyệt đối không thể truy cập</strong> vào trang quản trị nội bộ (/admin).
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Thống kê hệ thống -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-2xs p-4 flex items-center justify-between text-xs text-gray-500">
                    <div>
                        <span>Ngày tạo tài khoản: </span>
                        <strong class="text-gray-800 font-mono">{{ $user->created_at ? date('H:i d/m/Y', strtotime($user->created_at)) : '---' }}</strong>
                    </div>
                    <div>
                        <span>Lần cập nhật cuối: </span>
                        <strong class="text-gray-800 font-mono">{{ $user->updated_at ? date('H:i d/m/Y', strtotime($user->updated_at)) : '---' }}</strong>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</x-layout-backend>
