<x-layout-backend>
    <!-- Page header -->
    <div class="mb-4">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-800 flex items-center gap-3">
                    <span class="w-8 h-8 bg-gradient-to-br from-orange-400 to-orange-600 rounded-lg flex items-center justify-center text-white">
                        <i class="fas fa-list text-sm"></i>
                    </span>
                    Quản lý danh mục
                </h1>
                <p class="text-slate-500 mt-1 text-sm">Danh sách và quản lý các danh mục món ăn</p>
            </div>
            <div class="flex items-center gap-2">
                <a class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-lg text-sm font-medium transition-all shadow-lg shadow-red-500/25 hover:from-red-600 hover:to-red-700" href="{{ url('admin/category/trash') }}">
                    <i class="fas fa-trash"></i> Thùng rác
                </a>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Form create -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <h3 class="text-base font-semibold text-slate-800 mb-4">Thêm danh mục mới</h3>
                <form action="{{route('admin.category.store')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Tên danh mục</label>
                        <input type="text" value="" name="name" id="name" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-400 focus:ring-2 focus:ring-orange-400/20 transition-all outline-none bg-white text-sm">
                        @if($errors->has('name'))
                            <div class="text-red-500 text-xs mt-1">{{ $errors->first('name') ;}}</div>
                        @endif
                    </div>
                    
                    <div class="mb-3">
                        <label for="slug" class="block text-sm font-medium text-slate-700 mb-1">Slug</label>
                        <textarea name="slug" id="slug" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-400 focus:ring-2 focus:ring-orange-400/20 transition-all outline-none bg-white text-sm" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="block text-sm font-medium text-slate-700 mb-1">Mô tả</label>
                        <textarea name="description" id="description" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-400 focus:ring-2 focus:ring-orange-400/20 transition-all outline-none bg-white text-sm" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="parent_id" class="block text-sm font-medium text-slate-700 mb-1">Danh mục cha</label>
                        <select name="parent_id" id="parent_id" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-400 focus:ring-2 focus:ring-orange-400/20 transition-all outline-none bg-white text-sm">
                            <option value="0">None</option>
                            @foreach($categors as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>                           
                    <div class="mb-3">
                        <label for="sort_order" class="block text-sm font-medium text-slate-700 mb-1">Sắp xếp</label>
                        <select name="sort_order" id="sort_order" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-400 focus:ring-2 focus:ring-orange-400/20 transition-all outline-none bg-white text-sm">
                            <option value="0">None</option>
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
                            <i class="fas fa-plus"></i> Thêm danh mục
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider" style="width:30px;">#</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider" style="width:90px;">Hình</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Tên danh mục</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Slug</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Danh mục cha</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Mô tả</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider" style="width:200px;">Chức năng</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider" style="width:30px;">ID</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($categories as $category)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-3 text-center">
                                        <input type="checkbox" id="checkId" value="1" name="checkId[]" class="rounded border-slate-300 text-orange-500 focus:ring-orange-500">
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <img src="{{ asset('/images/category/' . $category->image) }}" class="w-12 h-12 object-cover rounded-lg" alt="{{ $category->image }}">
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-800 font-medium">{{ $category->name }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600">{{ $category->slug }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600">{{ $category->parent_id }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600 max-w-[150px] truncate">{{ $category->description }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-center">
                                        @if ($category->status == 1)
                                            <a href="{{ route('admin.category.status', ['category' => $category->id]) }}" class="inline-flex items-center justify-center w-8 h-8 bg-green-50 text-green-600 rounded-lg text-sm transition-all hover:bg-green-100">
                                                <i class="fas fa-toggle-on"></i>
                                            </a>
                                        @else
                                            <a href="{{ route('admin.category.status', ['category' => $category->id]) }}" class="inline-flex items-center justify-center w-8 h-8 bg-slate-100 text-slate-600 rounded-lg text-sm transition-all hover:bg-slate-200">
                                                <i class="fas fa-toggle-off"></i>
                                            </a>
                                        @endif
                                        <a href="{{ route('admin.category.show', ['category' => $category->id]) }}" class="inline-flex items-center justify-center w-8 h-8 bg-blue-50 text-blue-600 rounded-lg text-sm transition-all hover:bg-blue-100 ml-1">
                                            <i class="far fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.category.edit', ['category' => $category->id]) }}" class="inline-flex items-center justify-center w-8 h-8 bg-orange-50 text-orange-600 rounded-lg text-sm transition-all hover:bg-orange-100 ml-1">
                                            <i class="far fa-edit"></i>
                                        </a>
                                        <a href="{{ route('admin.category.delete', ['category' => $category->id]) }}" class="inline-flex items-center justify-center w-8 h-8 bg-red-50 text-red-600 rounded-lg text-sm transition-all hover:bg-red-100 ml-1">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-center text-sm text-slate-600">
                                        {{ $category->id }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-3 border-t border-slate-200 flex justify-center">
                    {{ $categories->links() }}
                </div>
            </div>
        </div>
    </div>
</x-layout-backend>
