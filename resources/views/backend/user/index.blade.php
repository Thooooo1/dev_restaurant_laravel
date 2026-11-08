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
                <h1 class="text-xl font-bold text-slate-800">
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

    <!-- Filter -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-3 mb-4">
        <div class="flex flex-wrap gap-3 items-end">
            <form method="GET" action="{{ $isEmployee ? route('admin.user.employees') : route('admin.user.customers') }}" class="flex-1 min-w-[200px] flex gap-3 items-end">
                @php $qValue = request('q'); @endphp
                <input type="text" name="q" value="{{ $qValue }}" class="flex-1 px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-400 focus:ring-2 focus:ring-orange-400/20 transition-all outline-none bg-white text-sm" placeholder="Tìm theo họ tên, số điện thoại hoặc email...">
                <button type="submit" class="px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg text-sm font-medium transition-all shadow-lg shadow-orange-500/25 hover:from-orange-600 hover:to-orange-700">
                    <i class="fa-solid fa-search mr-1"></i> Tìm
                </button>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <div class="table-responsive"><table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    @if($isEmployee)
                        <tr>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-slate-500 uppercase tracking-wider" style="width:30px;">#</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-slate-500 uppercase tracking-wider" style="width:70px;">Hình</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-slate-500 uppercase tracking-wider">Họ tên</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-slate-500 uppercase tracking-wider">Tên đăng nhập</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-slate-500 uppercase tracking-wider">Vai trò & Quyền hạn</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-slate-500 uppercase tracking-wider">Email</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-slate-500 uppercase tracking-wider" style="width:250px;">Chức năng</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-slate-500 uppercase tracking-wider" style="width:30px;">ID</th>
                        </tr>
                    @else
                        <!-- Bảng Quản lý khách hàng: Chỉ hiển thị tên, sđt, email, giới tính, địa chỉ & thao tác -->
                        <tr>
                            <th class="px-4 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider" style="width:50px;">STT</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Họ và tên</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Số điện thoại</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Email</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider" style="width:100px;">Giới tính</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Địa chỉ</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider" style="width:120px;">Thao tác</th>
                        </tr>
                    @endif
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($users as $user)
                        <tr class="hover:bg-slate-50 transition-colors">
                            @if($isEmployee)
                                <td class="px-4 py-3 text-center">
                                    <input type="checkbox" id="checkId" value="1" name="checkId[]" class="rounded border-slate-300 text-orange-500 focus:ring-orange-500">
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @php
                                        $userAvatar = (!empty($user->image) && file_exists(public_path('images/user/' . $user->image)))
                                            ? asset('images/user/' . $user->image)
                                            : asset('images/user/non_image.png');
                                    @endphp
                                    <img src="{{ $userAvatar }}" onerror="this.onerror=null;this.src='{{ asset('images/user/non_image.png') }}';" class="w-10 h-10 rounded-full object-cover border border-slate-200 mx-auto shadow-2xs" alt="avatar">
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-800 font-medium">{{ $user->fullname }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600 font-mono">{{ $user->username }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-center text-xs text-[rgba(0,0,0,0.85)] font-medium">
                                    @if($user->roles === 'admin')
                                        Quản trị viên
                                    @elseif($user->roles == 'employee')
                                        Nhân viên
                                    @else
                                        Thành viên
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600">{{ $user->email ?: '---' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    @if (Auth::user()->admin_lever == 1 || Auth::user()->id == $user->id)
                                        <form action="{{ route('admin.user.toggleLine', $user->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center justify-center w-8 h-8 {{ $user->line == 1 ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600' }} rounded-lg text-sm transition-all hover:{{ $user->line == 1 ? 'bg-green-100' : 'bg-red-100' }} cursor-pointer" title="{{ $user->line == 1 ? 'Online' : 'Offline' }}">
                                                <i class="fas {{ $user->line == 1 ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('admin.user.show', ['user' => $user->id]) }}" class="inline-flex items-center justify-center w-8 h-8 bg-blue-50 text-blue-600 rounded-lg text-sm transition-all hover:bg-blue-100 ml-1">
                                        <i class="fa-regular fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.user.edit', ['user' => $user->id]) }}" class="inline-flex items-center justify-center w-8 h-8 bg-orange-50 text-orange-600 rounded-lg text-sm transition-all hover:bg-orange-100 ml-1">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                    </a>
                                    <form action="{{ route('admin.user.delete', ['user' => $user->id]) }}" method="POST" class="inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa?')">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center justify-center w-8 h-8 bg-red-50 text-red-600 rounded-lg text-sm transition-all hover:bg-red-100 ml-1 cursor-pointer" title="Xóa nhân viên">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center text-sm text-slate-600">
                                    {{ $user->id }}
                                </td>
                            @else
                                <!-- Dòng Khách hàng: Chỉ gồm STT, Tên, SĐT, Email, Giới tính, Địa chỉ, Thao tác -->
                                <td class="px-4 py-3 text-center text-xs font-mono text-slate-500 font-semibold">
                                    {{ $loop->iteration }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-xs text-slate-900 font-bold">
                                    {{ $user->fullname }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-xs text-slate-700 font-mono font-semibold">
                                    {{ $user->phone }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-xs text-slate-600">
                                    {{ $user->email ?: '---' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center text-xs text-[rgba(0,0,0,0.85)] font-medium">
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
                                <td class="px-4 py-3 text-xs text-slate-600 max-w-xs truncate" title="{{ $user->address }}">
                                    {{ $user->address ?: '---' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    <a href="{{ route('admin.user.edit', ['user' => $user->id]) }}" 
                                       class="inline-flex items-center justify-center w-7 h-7 bg-orange-50 text-[#F47716] rounded-md text-xs hover:bg-[#F47716] hover:text-white transition-colors" 
                                       title="Chỉnh sửa thông tin">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                    </a>
                                    <form action="{{ route('admin.user.delete', ['user' => $user->id]) }}" method="POST" class="inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa khách hàng {{ $user->fullname }}?')">
                                        @csrf
                                        <button type="submit" 
                                                class="inline-flex items-center justify-center w-7 h-7 bg-red-50 text-rose-600 rounded-md text-xs hover:bg-rose-600 hover:text-white transition-colors ml-1 cursor-pointer" 
                                                title="Xóa khách hàng">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
                
        <div class="px-4 py-3 border-t border-slate-200 flex justify-center">
            @if(method_exists($users, 'links'))
                {{ $users->links() }}
            @endif
        </div>
    </div>
</x-layout-backend>
