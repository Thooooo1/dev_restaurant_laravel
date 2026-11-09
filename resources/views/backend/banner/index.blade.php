<x-layout-backend>
    <!-- Page Header (Ant Design Pro Style) -->
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-lg font-bold text-[rgba(0,0,0,0.85)] tracking-tight">Banner quảng cáo</h1>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ url('admin/banner/trash') }}" class="ant-btn ant-btn-sm text-gray-500 hover:text-red-600 font-medium bg-white border border-gray-200">
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
                    <span>Thêm banner mới</span>
                </h3>
                <form action="{{ route('admin.banner.store') }}" method="post" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <div>
                        <label for="name" class="block text-xs font-semibold text-[rgba(0,0,0,0.65)] mb-1">
                            Tên banner <span class="text-red-500">*</span>
                        </label>
                        <input type="text" value="{{ old('name') }}" name="name" id="name" class="polaris-input text-xs w-full" placeholder="Ví dụ: Banner tiệc cưới, Mùa hè...">
                        @if($errors->has('name'))
                            <div class="text-red-500 text-[11px] mt-1">{{ $errors->first('name') }}</div>
                        @endif
                    </div>
                    
                    <div>
                        <label for="link" class="block text-xs font-semibold text-[rgba(0,0,0,0.65)] mb-1">Liên kết (URL)</label>
                        <input type="text" value="{{ old('link') }}" name="link" id="link" class="polaris-input text-xs w-full" placeholder="https://... hoặc #">
                    </div>

                    <div>
                        <label for="position" class="block text-xs font-semibold text-[rgba(0,0,0,0.65)] mb-1">Vị trí hiển thị</label>
                        <select name="position" id="position" class="polaris-select text-xs w-full">
                            <option value="Slideshow">Trang chủ (Slideshow chính)</option>
                            <option value="ads">Banner quảng cáo phụ (Ads)</option>
                            <option value="0">Khác</option>
                        </select>
                    </div>

                    <div>
                        <label for="description" class="block text-xs font-semibold text-[rgba(0,0,0,0.65)] mb-1">Mô tả</label>
                        <textarea name="description" id="description" class="polaris-input text-xs w-full" rows="2" placeholder="Ghi chú về banner...">{{ old('description') }}</textarea>
                    </div>

                    <div>
                        <label for="image" class="block text-xs font-semibold text-[rgba(0,0,0,0.65)] mb-1">Tệp hình ảnh</label>
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
                            <span>+ Thêm banner</span>
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
                                <th class="w-24 text-center" style="text-align: center !important;">Hình ảnh</th>
                                <th class="text-left" style="text-align: left !important;">Tên banner</th>
                                <th class="text-left" style="text-align: left !important;">Vị trí</th>
                                <th class="text-center w-24" style="text-align: center !important;">Trạng thái</th>
                                <th class="text-center w-28" style="text-align: center !important;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($banners as $banner)
                                <tr>
                                    <td class="text-center font-mono text-xs text-[rgba(0,0,0,0.45)]" style="text-align: center !important;">
                                        #{{ $banner->id }}
                                    </td>
                                    <td class="text-center py-2" style="text-align: center !important;">
                                        <img src="{{ asset('/images/banner/' . $banner->image) }}" class="w-20 h-10 object-cover rounded border border-[#F0F0F0] mx-auto bg-slate-50" alt="{{ $banner->name }}" onerror="this.src='{{ asset('/images/default-product.png') }}'">
                                    </td>
                                    <td class="text-left" style="text-align: left !important;">
                                        <div class="font-semibold text-xs text-[rgba(0,0,0,0.85)]">{{ $banner->name }}</div>
                                        @if($banner->link)
                                            <div class="text-[11px] text-[rgba(0,0,0,0.45)] truncate max-w-[200px]" title="{{ $banner->link }}">{{ $banner->link }}</div>
                                        @endif
                                    </td>
                                    <td class="text-left text-xs text-[rgba(0,0,0,0.65)]" style="text-align: left !important;">
                                        {{ $banner->position }}
                                    </td>
                                    <td class="text-center whitespace-nowrap" style="text-align: center !important;">
                                        @if ($banner->status == 1)
                                            <a href="{{ route('admin.banner.status', ['banner' => $banner->id]) }}" class="text-gray-700 hover:text-black text-xs font-medium" title="Đang hiển thị - bấm để tắt">
                                                <i class="fa-solid fa-toggle-on text-base text-gray-800"></i>
                                            </a>
                                        @else
                                            <a href="{{ route('admin.banner.status', ['banner' => $banner->id]) }}" class="text-gray-400 hover:text-gray-600 text-xs font-medium" title="Đang ẩn - bấm để bật">
                                                <i class="fa-solid fa-toggle-off text-base text-gray-400"></i>
                                            </a>
                                        @endif
                                    </td>
                                    <td class="text-center whitespace-nowrap" style="text-align: center !important;">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <a href="{{ route('admin.banner.show', ['banner' => $banner->id]) }}" class="w-7 h-7 rounded border border-gray-200 hover:bg-gray-100 text-gray-600 flex items-center justify-center text-xs transition-colors" title="Xem chi tiết">
                                                <i class="fa-regular fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.banner.edit', ['banner' => $banner->id]) }}" class="w-7 h-7 rounded border border-gray-200 hover:bg-gray-100 text-gray-600 flex items-center justify-center text-xs transition-colors" title="Chỉnh sửa">
                                                <i class="fa-regular fa-pen-to-square"></i>
                                            </a>
                                            <a href="{{ route('admin.banner.delete', ['banner' => $banner->id]) }}" class="w-7 h-7 rounded border border-gray-200 hover:bg-red-50 text-gray-600 hover:text-red-600 flex items-center justify-center text-xs transition-colors" title="Xóa vào thùng rác">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-xs text-[rgba(0,0,0,0.45)]">
                                        Chưa có banner nào
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-3 border-t border-[#F0F0F0] flex justify-center">
                    {{ $banners->links() }}
                </div>
            </div>
        </div>
    </div>
</x-layout-backend>
