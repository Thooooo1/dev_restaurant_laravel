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

    <!-- Page header -->
    <div class="mb-4">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-lg font-bold text-[rgba(0,0,0,0.85)] tracking-tight">
                    @if($isEmployee)
                        Quản lý nhân viên
                    @else
                        Quản lý khách hàng
                    @endif
                </h1>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                @if($isEmployee)
                    <a class="ant-btn ant-btn-primary ant-btn-sm font-bold uppercase tracking-wider" href="{{ route('admin.user.create', ['role' => 'employee']) }}">
                        <span>+ Thêm nhân viên</span>
                    </a>
                @else
                    <a class="ant-btn ant-btn-sm font-medium bg-white border border-gray-200 text-gray-700 hover:bg-gray-50" href="{{ route('admin.user.customers.export', request()->all()) }}">
                        <span>Xuất Excel khách hàng</span>
                    </a>
                    <a class="ant-btn ant-btn-primary ant-btn-sm font-bold uppercase tracking-wider" href="{{ route('admin.user.create', ['role' => 'customer']) }}">
                        <span>+ Thêm khách hàng</span>
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Filter Bar (Shopify Polaris IndexFilters Style) -->
    <div class="ant-card p-3.5 mb-4 shadow-sm">
        <form method="GET" action="{{ $isEmployee ? route('admin.user.employees') : route('admin.user.customers') }}" class="flex flex-wrap gap-3 items-center">
            @php $qValue = request('q'); @endphp
            <div class="flex-1 min-w-[220px]">
                <input type="text" name="q" value="{{ $qValue }}" class="polaris-input text-xs w-full" placeholder="Tìm theo họ tên, số điện thoại hoặc email...">
            </div>
            <button type="submit" class="ant-btn ant-btn-primary ant-btn-sm font-bold">
                <i class="fa-solid fa-magnifying-glass text-xs"></i>
                <span>Tìm kiếm</span>
            </button>
            @if(request('q'))
                <a href="{{ $isEmployee ? route('admin.user.employees') : route('admin.user.customers') }}" class="ant-btn ant-btn-sm text-gray-500 hover:text-black">
                    <span>Đặt lại</span>
                </a>
            @endif
        </form>
    </div>

    <!-- Table -->
    <div class="ant-card shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    @if($isEmployee)
                        <tr>
                            <th class="w-8 text-center">#</th>
                            <th class="w-16 text-center">Hình</th>
                            <th>Họ tên</th>
                            <th>Tên đăng nhập</th>
                            <th class="text-center">Vai trò</th>
                            <th>Email</th>
                            <th class="text-center w-24">Trạng thái</th>
                            <th class="text-center w-28">Thao tác</th>
                            <th class="text-center w-12">ID</th>
                        </tr>
                    @else
                        <!-- Bảng Quản lý khách hàng -->
                        <tr>
                            <th class="text-center w-12">STT</th>
                            <th>Họ và tên</th>
                            <th>Số điện thoại</th>
                            <th>Email</th>
                            <th class="text-center w-24">Giới tính</th>
                            <th>Địa chỉ</th>
                            <th class="text-center w-28">Thao tác</th>
                        </tr>
                    @endif
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            @if($isEmployee)
                                <td class="text-center">
                                    <input type="checkbox" id="checkId" value="1" name="checkId[]" class="rounded border-gray-300 text-gray-900 focus:ring-0">
                                </td>
                                <td class="text-center">
                                    @php
                                        $userAvatar = (!empty($user->image) && file_exists(public_path('images/user/' . $user->image)))
                                            ? asset('images/user/' . $user->image)
                                            : asset('images/user/non_image.png');
                                    @endphp
                                    <img src="{{ $userAvatar }}" onerror="this.onerror=null;this.src='{{ asset('images/user/non_image.png') }}';" class="w-8 h-8 rounded-full object-cover border border-gray-200 mx-auto" alt="avatar">
                                </td>
                                <td class="whitespace-nowrap font-semibold text-xs text-[rgba(0,0,0,0.85)]">{{ $user->fullname }}</td>
                                <td class="whitespace-nowrap font-mono text-xs text-[rgba(0,0,0,0.65)]">{{ $user->username }}</td>
                                <td class="whitespace-nowrap text-center text-xs text-[rgba(0,0,0,0.85)] font-medium">
                                    @if($user->roles === 'admin')
                                        Quản trị viên
                                    @elseif($user->roles == 'employee')
                                        Nhân viên
                                    @else
                                        Thành viên
                                    @endif
                                </td>
                                <td class="whitespace-nowrap text-xs text-[rgba(0,0,0,0.65)]">{{ $user->email ?: '---' }}</td>
                                <td class="whitespace-nowrap text-center">
                                    @if (Auth::user()->isAdmin() || Auth::user()->id == $user->id)
                                        <form action="{{ route('admin.user.toggleLine', $user->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-gray-700 hover:text-black text-xs font-medium cursor-pointer" title="{{ $user->line == 1 ? 'Online (Bấm để tắt)' : 'Offline (Bấm để bật)' }}">
                                                <i class="fa-solid {{ $user->line == 1 ? 'fa-toggle-on text-base text-gray-800' : 'fa-toggle-off text-base text-gray-400' }}"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <a href="{{ route('admin.user.show', ['user' => $user->id]) }}" 
                                           class="text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors p-1.5 rounded flex items-center justify-center" 
                                           title="Xem chi tiết">
                                            <i class="fa-regular fa-eye text-xs"></i>
                                        </a>
                                        <a href="{{ route('admin.user.edit', ['user' => $user->id]) }}" 
                                           class="text-gray-400 hover:text-[#F47716] hover:bg-orange-50 transition-colors p-1.5 rounded flex items-center justify-center" 
                                           title="Chỉnh sửa">
                                            <i class="fa-regular fa-pen-to-square text-xs"></i>
                                        </a>
                                        <form action="{{ route('admin.user.delete', ['user' => $user->id]) }}" method="POST" class="inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa?')">
                                            @csrf
                                            <button type="submit" 
                                                    class="text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors p-1.5 rounded flex items-center justify-center cursor-pointer" 
                                                    title="Xóa nhân viên">
                                                <i class="fa-solid fa-trash-can text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap text-center font-mono text-xs text-[rgba(0,0,0,0.45)]">
                                    #{{ $user->id }}
                                </td>
                            @else
                                <!-- Dòng Khách hàng -->
                                <td class="text-center font-mono text-xs text-[rgba(0,0,0,0.45)]">
                                    {{ $loop->iteration }}
                                </td>
                                <td class="whitespace-nowrap font-semibold text-xs text-[rgba(0,0,0,0.85)]">
                                    {{ $user->fullname }}
                                </td>
                                <td class="whitespace-nowrap font-mono text-xs text-[rgba(0,0,0,0.85)]">
                                    {{ $user->phone }}
                                </td>
                                <td class="whitespace-nowrap text-xs text-[rgba(0,0,0,0.65)]">
                                    {{ $user->email ?: '---' }}
                                </td>
                                <td class="whitespace-nowrap text-center text-xs text-[rgba(0,0,0,0.85)] font-medium">
                                    @php
                                        $genderStr = '---';
                                        if (in_array((string)$user->gender, ['1', 'Nam', 'nam'], true)) {
                                            $genderStr = 'Nam';
                                        } elseif (in_array((string)$user->gender, ['0', 'Nữ', 'nu'], true)) {
                                            $genderStr = 'Nữ';
                                        }
                                    @endphp
                                    {{ $genderStr }}
                                </td>
                                <td class="text-xs text-[rgba(0,0,0,0.65)] max-w-xs truncate" title="{{ $user->address }}">
                                    {{ $user->address ?: '---' }}
                                </td>
                                <td class="whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <a href="{{ route('admin.user.show', ['user' => $user->id]) }}" 
                                           class="text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors p-1.5 rounded flex items-center justify-center" 
                                           title="Xem chi tiết">
                                            <i class="fa-regular fa-eye text-xs"></i>
                                        </a>
                                        <a href="{{ route('admin.user.edit', ['user' => $user->id]) }}" 
                                           class="text-gray-400 hover:text-[#F47716] hover:bg-orange-50 transition-colors p-1.5 rounded flex items-center justify-center" 
                                           title="Chỉnh sửa thông tin">
                                            <i class="fa-regular fa-pen-to-square text-xs"></i>
                                        </a>
                                        <form action="{{ route('admin.user.delete', ['user' => $user->id]) }}" method="POST" class="inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa khách hàng {{ $user->fullname }}?')">
                                            @csrf
                                            <button type="submit" 
                                                    class="text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors p-1.5 rounded flex items-center justify-center cursor-pointer" 
                                                    title="Xóa khách hàng">
                                                <i class="fa-solid fa-trash-can text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isEmployee ? 9 : 7 }}" class="py-8 text-center text-xs text-[rgba(0,0,0,0.45)]">
                                Không có dữ liệu người dùng phù hợp
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
                
        <div class="px-4 py-3 border-t border-[#F0F0F0] flex justify-center">
            @if(method_exists($users, 'links'))
                {{ $users->links() }}
            @endif
        </div>
    </div>
</x-layout-backend>
