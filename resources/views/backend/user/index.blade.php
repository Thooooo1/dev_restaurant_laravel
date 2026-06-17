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
                <h1 class="text-xl font-bold text-slate-800 flex items-center gap-3">
                    <span class="w-8 h-8 bg-gradient-to-br from-orange-400 to-orange-600 rounded-lg flex items-center justify-center text-white">
                        <i class="fas fa-users text-sm"></i>
                    </span>
                    @if($isEmployee)
                        Quản lý nhân viên
                    @else
                        Quản lý khách hàng
                    @endif
                </h1>
                <p class="text-slate-500 mt-1 text-sm">
                    @if($isEmployee)
                        Danh sách và quản lý các nhân viên
                    @else
                        Danh sách và quản lý các khách hàng
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-2">
                @if($isEmployee)
                    <a class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg text-sm font-medium transition-all shadow-lg shadow-green-500/25 hover:from-green-600 hover:to-green-700" href="{{ route('admin.user.create', ['role' => 'admin']) }}">
                        <i class="fas fa-plus"></i> Thêm nhân viên
                    </a>
                @else
                    <a class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg text-sm font-medium transition-all shadow-lg shadow-green-500/25 hover:from-green-600 hover:to-green-700" href="{{ route('admin.user.create', ['role' => 'customer']) }}">
                        <i class="fas fa-plus"></i> Thêm khách hàng
                    </a>
                    <a class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-lg text-sm font-medium transition-all shadow-lg shadow-red-500/25 hover:from-red-600 hover:to-red-700" href="{{ url('admin/user/trash') }}">
                        <i class="fas fa-trash"></i> Thùng rác
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
                <input type="text" name="q" value="{{ $qValue }}" class="flex-1 px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-400 focus:ring-2 focus:ring-orange-400/20 transition-all outline-none bg-white text-sm" placeholder="Tìm tên hoặc số điện thoại">
                <button type="submit" class="px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg text-sm font-medium transition-all shadow-lg shadow-orange-500/25 hover:from-orange-600 hover:to-orange-700">
                    <i class="fas fa-search mr-1"></i> Tìm
                </button>
            </form>
            
            @if(!$isEmployee)
                <form method="GET" action="{{ route('admin.user.customers') }}" class="flex items-end gap-2">
                    <label class="text-sm font-medium text-slate-700">Lọc theo trạng thái:</label>
                    <select name="status" onchange="this.form.submit()" class="px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-400 focus:ring-2 focus:ring-orange-400/20 transition-all outline-none bg-white text-sm">
                        <option value="">Trạng thái tài khoản</option>
                        <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Hoạt động</option>
                        <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>Không hoạt động</option>
                    </select>
                </form>
            @endif
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider" style="width:30px;">#</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider" style="width:90px;">Hình</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Họ tên</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $isEmployee ? 'Tên đăng nhập' : 'Điện thoại' }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Email</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider" style="width:250px;">Chức năng</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider" style="width:30px;">ID</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($users as $user)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3 text-center">
                                <input type="checkbox" id="checkId" value="1" name="checkId[]" class="rounded border-slate-300 text-orange-500 focus:ring-orange-500">
                            </td>
                            <td class="px-4 py-3 text-center">
                                <img src="{{ asset('/images/user/' . $user->image) }}" class="w-12 h-12 rounded-lg object-cover" alt="avatar">
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-800 font-medium">{{ $user->fullname }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600">{{ $isEmployee ? $user->username : $user->phone }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600">{{ $user->email }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                @if($isEmployee)
                                    @if (Auth::user()->admin_lever == 1 || Auth::user()->id == $user->id)
                                        <a href="{{ route('admin.user.toggleLine', $user->id) }}" class="inline-flex items-center justify-center w-8 h-8 {{ $user->line == 1 ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600' }} rounded-lg text-sm transition-all hover:{{ $user->line == 1 ? 'bg-green-100' : 'bg-red-100' }}" title="{{ $user->line == 1 ? 'Online' : 'Offline' }}">
                                            <i class="fas {{ $user->line == 1 ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                        </a>
                                    @endif
                                @else
                                    <a href="{{ route('admin.user.status', ['user' => $user->id]) }}" class="inline-flex items-center justify-center w-8 h-8 {{ $user->status == 1 ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600' }} rounded-lg text-sm transition-all hover:{{ $user->status == 1 ? 'bg-green-100' : 'bg-red-100' }}">
                                        <i class="fas {{ $user->status == 1 ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                    </a>
                                @endif

                                <a href="{{ route('admin.user.show', ['user' => $user->id]) }}" class="inline-flex items-center justify-center w-8 h-8 bg-blue-50 text-blue-600 rounded-lg text-sm transition-all hover:bg-blue-100 ml-1">
                                    <i class="far fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.user.edit', ['user' => $user->id]) }}" class="inline-flex items-center justify-center w-8 h-8 bg-orange-50 text-orange-600 rounded-lg text-sm transition-all hover:bg-orange-100 ml-1">
                                    <i class="far fa-edit"></i>
                                </a>
                                <a href="{{ route('admin.user.delete', ['user' => $user->id]) }}" class="inline-flex items-center justify-center w-8 h-8 bg-red-50 text-red-600 rounded-lg text-sm transition-all hover:bg-red-100 ml-1">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center text-sm text-slate-600">
                                {{ $user->id }}
                            </td>
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
