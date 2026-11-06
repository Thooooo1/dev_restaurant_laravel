<x-layout-backend>
    <!-- Page header -->
    <div class="mb-4">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-800">Quản lý banner</h1>
            </div>
            <div class="flex items-center gap-2">
                <a class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-lg text-sm font-medium transition-all shadow-lg shadow-red-500/25 hover:from-red-600 hover:to-red-700" href="{{ url('admin/banner/trash') }}">
                    <i class="fa-solid fa-trash"></i> Thùng rác
                </a>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Form create -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <h3 class="text-base font-semibold text-slate-800 mb-4">Thêm banner mới</h3>
                <form action="{{route('admin.banner.store')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Tên banner</label>
                        <input type="text" value="" name="name" id="name" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-400 focus:ring-2 focus:ring-orange-400/20 transition-all outline-none bg-white text-sm">
                        @if($errors->has('name'))
                            <div class="text-red-500 text-xs mt-1">{{ $errors->first('name') ;}}</div>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label for="link" class="block text-sm font-medium text-slate-700 mb-1">Liên kết</label>
                        <input type="text" value="" name="link" id="link" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-400 focus:ring-2 focus:ring-orange-400/20 transition-all outline-none bg-white text-sm">
                    </div>
                    <div class="mb-3">
                        <label for="description" class="block text-sm font-medium text-slate-700 mb-1">Mô tả</label>
                        <textarea name="description" id="description" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-400 focus:ring-2 focus:ring-orange-400/20 transition-all outline-none bg-white text-sm" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="position" class="block text-sm font-medium text-slate-700 mb-1">Vị trí</label>
                        <select name="position" id="position" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-400 focus:ring-2 focus:ring-orange-400/20 transition-all outline-none bg-white text-sm">
                            <option value="0">None</option>
                            <option value="Slideshow">Slideshow</option>
                            <option value="ads">Ads</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="image" class="block text-sm font-medium text-slate-700 mb-1">Hình</label>
                        <input type="file" name="image" id="image" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-400 focus:ring-2 focus:ring-orange-400/20 transition-all outline-none bg-white text-sm">
                    </div>
                    <div class="mb-3">
                        <label for="status" class="block text-sm font-medium text-slate-700 mb-1">Trạng thái</label>
                        <select name="status" id="status" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-400 focus:ring-2 focus:ring-orange-400/20 transition-all outline-none bg-white text-sm">
                            <option value="2">Chưa xuất bản</option>
                            <option value="1">Xuất bản</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <button type="submit" name="create" class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg text-sm font-medium transition-all shadow-lg shadow-green-500/25 hover:from-green-600 hover:to-green-700">
                            <i class="fa-solid fa-plus"></i> Thêm banner
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <div class="table-responsive"><table class="w-full">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-3 text-center text-sm font-semibold text-slate-500 uppercase tracking-wider" style="width:30px;">#</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold text-slate-500 uppercase tracking-wider" style="width:90px;">Hình</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-slate-500 uppercase tracking-wider">Tên banner</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-slate-500 uppercase tracking-wider">Liên kết</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-slate-500 uppercase tracking-wider">Vị trí</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold text-slate-500 uppercase tracking-wider" style="width:200px;">Chức năng</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold text-slate-500 uppercase tracking-wider" style="width:30px;">ID</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($banners as $banner)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-3 text-center">
                                        <input type="checkbox" id="checkId" value="1" name="checkId[]" class="rounded border-slate-300 text-orange-500 focus:ring-orange-500">
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <img src="{{ asset('/images/banner/' . $banner->image) }}" class="w-20 h-12 object-cover rounded-lg" alt="{{ $banner->image }}">
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-800 font-medium">{{ $banner->name }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600 max-w-[150px] truncate">{{ $banner->link }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600">{{ $banner->position }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-center">
                                        @if ($banner->status == 1)
                                            <a href="{{ route('admin.banner.status', ['banner' => $banner->id]) }}" class="inline-flex items-center justify-center w-8 h-8 bg-green-50 text-green-600 rounded-lg text-sm transition-all hover:bg-green-100">
                                                <i class="fa-solid fa-toggle-on"></i>
                                            </a>
                                        @else
                                            <a href="{{ route('admin.banner.status', ['banner' => $banner->id]) }}" class="inline-flex items-center justify-center w-8 h-8 bg-slate-100 text-slate-600 rounded-lg text-sm transition-all hover:bg-slate-200">
                                                <i class="fa-solid fa-toggle-off"></i>
                                            </a>
                                        @endif
                                        <a href="{{ route('admin.banner.show', ['banner' => $banner->id]) }}" class="inline-flex items-center justify-center w-8 h-8 bg-blue-50 text-blue-600 rounded-lg text-sm transition-all hover:bg-blue-100 ml-1">
                                            <i class="fa-regular fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.banner.edit', ['banner' => $banner->id]) }}" class="inline-flex items-center justify-center w-8 h-8 bg-orange-50 text-orange-600 rounded-lg text-sm transition-all hover:bg-orange-100 ml-1">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </a>
                                        <a href="{{ route('admin.banner.delete', ['banner' => $banner->id]) }}" class="inline-flex items-center justify-center w-8 h-8 bg-red-50 text-red-600 rounded-lg text-sm transition-all hover:bg-red-100 ml-1">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-center text-sm text-slate-600">
                                        {{ $banner->id }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-3 border-t border-slate-200 flex justify-center">
                    {{ $banners->links() }}
                </div>
            </div>
        </div>
    </div>
</x-layout-backend>
