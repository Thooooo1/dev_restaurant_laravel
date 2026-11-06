<x-layout-backend>

    <!-- Page Header (Ant Design Pro Style) -->
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-lg font-bold text-[rgba(0,0,0,0.85)] tracking-tight">Thực đơn & Món ăn</h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ url('admin/product/create') }}" class="ant-btn ant-btn-primary ant-btn-sm">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Thêm món mới</span>
            </a>
            <a href="{{ url('admin/product/trash') }}" class="ant-btn ant-btn-sm">
                <i class="fa-solid fa-trash text-xs text-gray-400"></i>
                <span>Thùng rác</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar (Shopify Polaris IndexFilters Style) -->
    <div class="ant-card p-3.5 mb-4 shadow-sm">
        <form method="GET" action="" class="flex flex-wrap gap-3 items-center">
            <div class="flex-1 min-w-[220px]">
                <input type="text" name="search" class="polaris-input text-xs" placeholder="Tìm theo tên món ăn..." value="{{ request('search') }}">
            </div>
            <div class="min-w-[160px]">
                <select name="category" class="polaris-select text-xs w-full">
                    <option value="">Tất cả danh mục</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[160px]">
                <select name="brand" class="polaris-select text-xs w-full">
                    <option value="">Tất cả thương hiệu</option>
                    @foreach ($brands as $brand)
                        <option value="{{ $brand->id }}" {{ request('brand') == $brand->id ? 'selected' : '' }}>
                            {{ $brand->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="ant-btn ant-btn-primary ant-btn-sm">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    <span>Lọc</span>
                </button>
                @if(request('search') || request('category') || request('brand'))
                    <a href="{{ url('admin/product') }}" class="ant-btn ant-btn-sm">
                        <span>Đặt lại</span>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Data Table (Shopify Polaris / IBM Carbon / Ant Design Pro Style) -->
    <div class="ant-card shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr>
                        <th class="w-12 text-center">ID</th>
                        <th class="w-16 text-center">Hình ảnh</th>
                        <th>Tên món ăn</th>
                        <th>Danh mục</th>
                        <th>Thương hiệu</th>
                        <th class="text-right">Giá vốn</th>
                        <th class="text-right">Giá bán</th>
                        <th class="text-center">Tồn kho</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-center w-32">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td class="text-center font-mono text-xs text-[rgba(0,0,0,0.45)]">
                                #{{ $product->id }}
                            </td>
                            <td class="text-center py-2">
                                <img src="{{ asset('/images/product/' . $product->image) }}" class="w-11 h-11 object-cover rounded border border-[#F0F0F0] mx-auto bg-slate-50" alt="{{ $product->name }}" onerror="this.src='{{ asset('/images/default-product.png') }}'">
                            </td>
                            <td>
                                <div class="font-semibold text-xs text-[rgba(0,0,0,0.85)]">{{ $product->name }}</div>
                                @if ($product->new_p == 1)
                                    <span class="polaris-badge polaris-badge-processing text-[10px] mt-0.5">Món mới</span>
                                @endif
                            </td>
                            <td class="text-xs text-[rgba(0,0,0,0.65)]">{{ $product->category->name ?? '---' }}</td>
                            <td class="text-xs text-[rgba(0,0,0,0.65)]">{{ $product->brand->name ?? '---' }}</td>
                            <td class="text-right font-mono text-xs text-[rgba(0,0,0,0.45)]">
                                {{ number_format($product->price_buy, 0, ',', '.') }} ₫
                            </td>
                            <td class="text-right font-mono font-semibold text-xs text-[#F47716]">
                                {{ number_format($product->price_sale, 0, ',', '.') }} ₫
                            </td>
                            <td class="text-center font-mono text-xs text-[rgba(0,0,0,0.85)]">
                                {{ $product->qty }}
                            </td>
                            <td class="text-center">
                                @if ($product->status == 1)
                                    <span class="polaris-badge polaris-badge-success text-[11px]">Đang bán</span>
                                @else
                                    <span class="polaris-badge polaris-badge-default text-[11px]">Tạm dừng</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('admin.product.status', ['product' => $product->id]) }}" class="text-gray-400 hover:text-emerald-600 transition-colors p-1" title="{{ $product->status == 1 ? 'Tạm ngưng phục vụ' : 'Kích hoạt bán' }}">
                                        <i class="fa-solid {{ $product->status == 1 ? 'fa-toggle-on text-emerald-600' : 'fa-toggle-off text-gray-400' }} text-base"></i>
                                    </a>
                                    <a href="{{ route('admin.product.show', ['product' => $product->id]) }}" class="text-gray-400 hover:text-blue-600 transition-colors p-1" title="Xem chi tiết">
                                        <i class="fa-regular fa-eye text-xs"></i>
                                    </a>
                                    <a href="{{ route('admin.product.edit', ['product' => $product->id]) }}" class="text-gray-400 hover:text-[#F47716] transition-colors p-1" title="Chỉnh sửa">
                                        <i class="fa-regular fa-pen-to-square text-xs"></i>
                                    </a>
                                    <a href="{{ route('admin.product.delete', ['product' => $product->id]) }}" class="text-gray-400 hover:text-red-500 transition-colors p-1" title="Xóa vào thùng rác" onclick="return confirm('Bạn có chắc chắn muốn chuyển món này vào thùng rác?')">
                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-8 text-center text-xs text-[rgba(0,0,0,0.45)]">Không tìm thấy món ăn nào phù hợp với bộ lọc</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-3 border-t border-[#F0F0F0] bg-[#FAFAFA] flex justify-center">
            {{ $products->appends(request()->query())->links() }}
        </div>
    </div>
</x-layout-backend>
