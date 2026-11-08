<x-layout-backend>
    <!-- Page Header (Ant Design Pro Style) -->
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-lg font-bold text-[rgba(0,0,0,0.85)] tracking-tight">Danh mục món</h1>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ url('admin/category/trash') }}" class="ant-btn ant-btn-sm text-gray-500 hover:text-red-600 font-medium bg-white border border-gray-200">
                <i class="fa-solid fa-trash text-xs"></i>
                <span>Thùng rác</span>
            </a>
        </div>
    </div>

    <!-- Main content: Form (Left) & Table (Right) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start">
        <!-- Form create (Ant Design Pro Card) -->
        <div class="lg:col-span-1">
            <div class="ant-card p-4 shadow-sm">
                <h3 class="text-sm font-bold text-[rgba(0,0,0,0.85)] mb-3 pb-2 border-b border-[#F0F0F0] flex items-center gap-2">
                    <i class="fa-solid fa-plus text-xs text-gray-500"></i>
                    <span>Thêm danh mục mới</span>
                </h3>
                <form action="{{ route('admin.category.store') }}" method="post" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <div>
                        <label for="name" class="block text-xs font-semibold text-[rgba(0,0,0,0.65)] mb-1">
                            Tên danh mục <span class="text-red-500">*</span>
                        </label>
                        <input type="text" value="{{ old('name') }}" name="name" id="name" class="polaris-input text-xs w-full" placeholder="Ví dụ: Khai vị, Món chính...">
                        @if($errors->has('name'))
                            <div class="text-red-500 text-[11px] mt-1">{{ $errors->first('name') }}</div>
                        @endif
                    </div>
                    
                    <div>
                        <label for="slug" class="block text-xs font-semibold text-[rgba(0,0,0,0.65)] mb-1">Slug</label>
                        <input type="text" name="slug" id="slug" value="{{ old('slug') }}" class="polaris-input text-xs w-full" placeholder="Để trống sẽ tự tạo theo tên">
                    </div>

                    <div>
                        <label for="description" class="block text-xs font-semibold text-[rgba(0,0,0,0.65)] mb-1">Mô tả</label>
                        <textarea name="description" id="description" class="polaris-input text-xs w-full" rows="2" placeholder="Mô tả ngắn gọn về nhóm món...">{{ old('description') }}</textarea>
                    </div>

                    <div>
                        <label for="parent_id" class="block text-xs font-semibold text-[rgba(0,0,0,0.65)] mb-1">Danh mục cha</label>
                        <select name="parent_id" id="parent_id" class="polaris-select text-xs w-full">
                            <option value="0">--- Không có danh mục cha ---</option>
                            @foreach($categors as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="sort_order" class="block text-xs font-semibold text-[rgba(0,0,0,0.65)] mb-1">Thứ tự hiển thị</label>
                        <select name="sort_order" id="sort_order" class="polaris-select text-xs w-full">
                            <option value="0">Mặc định (0)</option>
                            @for($i = 1; $i <= 20; $i++)
                                <option value="{{ $i }}">Thứ tự {{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <div>
                        <label for="image" class="block text-xs font-semibold text-[rgba(0,0,0,0.65)] mb-1">Hình ảnh đại diện</label>
                        <input type="file" name="image" id="image" class="polaris-input text-xs w-full">
                    </div>

                    <div>
                        <label for="status" class="block text-xs font-semibold text-[rgba(0,0,0,0.65)] mb-1">Trạng thái</label>
                        <select name="status" id="status" class="polaris-select text-xs w-full">
                            <option value="1">Xuất bản (Hiển thị)</option>
                            <option value="2">Chưa xuất bản (Tạm ẩn)</option>
                        </select>
                    </div>

                    <div class="pt-2">
                        <button type="submit" name="create" class="ant-btn ant-btn-primary ant-btn-sm w-full justify-center font-bold uppercase tracking-wider py-2">
                            <span>+ Thêm danh mục</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table (Ant Design Pro / Shopify Polaris Style) -->
        <div class="lg:col-span-2">
            <div class="ant-card shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr>
                                <th class="w-12 text-center" style="text-align: center !important;">ID</th>
                                <th class="w-16 text-center" style="text-align: center !important;">Hình ảnh</th>
                                <th class="text-left" style="text-align: left !important;">Tên danh mục</th>
                                <th class="text-left" style="text-align: left !important;">Danh mục cha</th>
                                <th class="text-left" style="text-align: left !important;">Mô tả</th>
                                <th class="text-center w-24" style="text-align: center !important;">Trạng thái</th>
                                <th class="text-center w-28" style="text-align: center !important;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($categories as $category)
                                <tr>
                                    <td class="text-center font-mono text-xs text-[rgba(0,0,0,0.45)]" style="text-align: center !important;">
                                        #{{ $category->id }}
                                    </td>
                                    <td class="text-center py-2" style="text-align: center !important;">
                                        <img src="{{ asset('/images/category/' . $category->image) }}" class="w-11 h-11 object-cover rounded border border-[#F0F0F0] mx-auto bg-slate-50" alt="{{ $category->name }}" onerror="this.src='{{ asset('/images/default-product.png') }}'">
                                    </td>
                                    <td class="text-left" style="text-align: left !important;">
                                        <div class="font-semibold text-xs text-[rgba(0,0,0,0.85)]">{{ $category->name }}</div>
                                        <div class="text-[11px] text-[rgba(0,0,0,0.45)] font-mono">{{ $category->slug }}</div>
                                    </td>
                                    <td class="text-left text-xs text-[rgba(0,0,0,0.65)]" style="text-align: left !important;">
                                        {{ $category->parent_id == 0 ? '---' : ($category->parent ? $category->parent->name : '#' . $category->parent_id) }}
                                    </td>
                                    <td class="text-left text-xs text-[rgba(0,0,0,0.65)] max-w-[150px] truncate" style="text-align: left !important;" title="{{ $category->description }}">
                                        {{ $category->description ?: '---' }}
                                    </td>
                                    <td class="text-center whitespace-nowrap" style="text-align: center !important;">
                                        @if ($category->status == 1)
                                            <a href="{{ route('admin.category.status', ['category' => $category->id]) }}" class="text-gray-700 hover:text-black text-xs font-medium" title="Đang hiển thị - bấm để tắt">
                                                <i class="fa-solid fa-toggle-on text-base text-gray-800"></i>
                                            </a>
                                        @else
                                            <a href="{{ route('admin.category.status', ['category' => $category->id]) }}" class="text-gray-400 hover:text-gray-600 text-xs font-medium" title="Đang ẩn - bấm để bật">
                                                <i class="fa-solid fa-toggle-off text-base text-gray-400"></i>
                                            </a>
                                        @endif
                                    </td>
                                    <td class="text-center whitespace-nowrap" style="text-align: center !important;">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <a href="{{ route('admin.category.show', ['category' => $category->id]) }}" class="w-7 h-7 rounded border border-gray-200 hover:bg-gray-100 text-gray-600 flex items-center justify-center text-xs transition-colors" title="Xem chi tiết">
                                                <i class="fa-regular fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.category.edit', ['category' => $category->id]) }}" class="w-7 h-7 rounded border border-gray-200 hover:bg-gray-100 text-gray-600 flex items-center justify-center text-xs transition-colors" title="Chỉnh sửa">
                                                <i class="fa-regular fa-pen-to-square"></i>
                                            </a>
                                            <a href="{{ route('admin.category.delete', ['category' => $category->id]) }}" class="w-7 h-7 rounded border border-gray-200 hover:bg-red-50 text-gray-600 hover:text-red-600 flex items-center justify-center text-xs transition-colors" title="Xóa vào thùng rác">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-xs text-[rgba(0,0,0,0.45)]">
                                        Chưa có danh mục nào
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-3 border-t border-[#F0F0F0] flex justify-center">
                    {{ $categories->links() }}
                </div>
            </div>
        </div>
    </div>
</x-layout-backend>
