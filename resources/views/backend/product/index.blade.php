<x-layout-backend>

    <!-- Page Header (Ant Design Pro Style) -->
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-lg font-bold text-[rgba(0,0,0,0.85)] tracking-tight">Thực đơn & Món ăn</h1>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('admin.product.export', request()->all()) }}" class="ant-btn ant-btn-sm font-medium bg-white border border-gray-200 text-gray-700 hover:bg-gray-50">
                <span>Xuất Excel thực đơn</span>
            </a>
            <a href="{{ url('admin/product/create') }}" class="ant-btn ant-btn-primary ant-btn-sm font-bold uppercase tracking-wider">
                <span>+ Thêm món mới</span>
            </a>
            <a href="{{ url('admin/product/trash') }}" class="ant-btn ant-btn-sm text-gray-500 hover:text-red-600 font-medium bg-white border border-gray-200">
                <span>Thùng rác</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar (Shopify Polaris IndexFilters Style) -->
    <div class="ant-card p-3.5 mb-4 shadow-sm">
        <form method="GET" action="" id="productFilterForm" class="flex flex-wrap gap-3 items-center">
            <div class="flex-1 min-w-[220px]">
                <div class="relative">
                    <input type="text" 
                           name="search" 
                           class="polaris-input text-xs w-full pl-8 pr-3" 
                           placeholder="Tìm theo tên món ăn (nhấn Enter để tìm)..." 
                           value="{{ request('search') }}">
                    <i class="fa-solid fa-magnifying-glass text-gray-400 text-xs absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                </div>
            </div>
            <div class="min-w-[160px]">
                <select name="category" class="polaris-select text-xs w-full" onchange="this.form.submit()">
                    <option value="">Tất cả danh mục</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[150px]">
                <select name="status" class="polaris-select text-xs w-full" onchange="this.form.submit()">
                    <option value="">Tất cả trạng thái</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Đang bán</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Tạm dừng</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="ant-btn ant-btn-primary ant-btn-sm">
                    <i class="fa-solid fa-filter text-xs"></i>
                    <span>Lọc</span>
                </button>
                @if(request()->hasAny(['search', 'category', 'status', 'brand']))
                    <a href="{{ url('admin/product') }}" class="ant-btn ant-btn-sm" title="Xóa bộ lọc">
                        <i class="fa-solid fa-rotate-left text-xs text-gray-400"></i>
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
                        <th class="w-12 text-center" style="text-align: center !important;">ID</th>
                        <th class="w-16 text-center" style="text-align: center !important;">Hình ảnh</th>
                        <th class="text-left" style="text-align: left !important;">Tên món ăn</th>
                        <th class="text-left" style="text-align: left !important;">Danh mục</th>
                        <th class="text-left" style="text-align: left !important;">Khu vực bếp</th>
                        <th class="text-right" style="text-align: right !important;">Giá vốn</th>
                        <th class="text-right" style="text-align: right !important;">Giá bán</th>
                        <th class="text-right" style="text-align: right !important;">Tồn kho</th>
                        <th class="text-center" style="text-align: center !important;">Trạng thái</th>
                        <th class="text-center w-36" style="text-align: center !important;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td class="text-center font-mono text-xs text-[rgba(0,0,0,0.45)]" style="text-align: center !important;">
                                #{{ $product->id }}
                            </td>
                            <td class="text-center py-2" style="text-align: center !important;">
                                <img src="{{ asset('/images/product/' . $product->image) }}" class="w-11 h-11 object-cover rounded border border-[#F0F0F0] mx-auto bg-slate-50" alt="{{ $product->name }}" onerror="this.src='{{ asset('/images/default-product.png') }}'">
                            </td>
                            <td class="text-left" style="text-align: left !important;">
                                <div class="font-semibold text-xs text-[rgba(0,0,0,0.85)]">{{ $product->name }}</div>
                                @if ($product->new_p == 1)
                                    <span class="text-[10px] text-gray-400 font-medium block">Món mới</span>
                                @endif
                            </td>
                            <td class="text-left text-xs text-[rgba(0,0,0,0.65)]" style="text-align: left !important;">
                                {{ $product->category->name ?? '---' }}
                            </td>
                            <td class="text-left text-xs text-[rgba(0,0,0,0.65)]" style="text-align: left !important;">
                                {{ $product->kitchen_area ?: '---' }}
                            </td>
                            <td class="text-right font-mono text-xs text-[rgba(0,0,0,0.55)]" style="text-align: right !important;">
                                {{ number_format($product->price_buy, 0, ',', '.') }} ₫
                            </td>
                            <td class="text-right font-mono font-semibold text-xs text-[#F47716]" style="text-align: right !important;">
                                {{ number_format($product->price_sale, 0, ',', '.') }} ₫
                            </td>
                            <td class="text-right font-mono text-xs text-[rgba(0,0,0,0.85)]" style="text-align: right !important;">
                                <span class="font-semibold">{{ number_format($product->qty, 0, ',', '.') }}</span>
                                <span class="text-[11px] text-gray-400 font-sans ml-0.5">{{ $product->unit }}</span>
                            </td>
                            <td class="text-center whitespace-nowrap text-xs text-[rgba(0,0,0,0.65)]" style="text-align: center !important;">
                                {{ $product->status == 1 ? 'Đang bán' : 'Tạm dừng' }}
                            </td>
                            <td class="text-center" style="text-align: center !important;">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('admin.product.status', ['product' => $product->id]) }}" 
                                       class="text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 transition-colors p-1.5 rounded flex items-center justify-center" 
                                       title="{{ $product->status == 1 ? 'Món đang phục vụ (Bấm để tạm ngưng)' : 'Món đang tạm dừng (Bấm để kích hoạt bán)' }}">
                                        <i class="fa-solid {{ $product->status == 1 ? 'fa-toggle-on text-emerald-600' : 'fa-toggle-off text-gray-400' }} text-base"></i>
                                    </a>
                                    <a href="{{ route('admin.product.show', ['product' => $product->id]) }}" 
                                       class="text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors p-1.5 rounded flex items-center justify-center" 
                                       title="Xem chi tiết món">
                                        <i class="fa-regular fa-eye text-xs"></i>
                                    </a>
                                    <a href="{{ route('admin.product.edit', ['product' => $product->id]) }}" 
                                       class="text-gray-400 hover:text-[#F47716] hover:bg-orange-50 transition-colors p-1.5 rounded flex items-center justify-center" 
                                       title="Chỉnh sửa món">
                                        <i class="fa-regular fa-pen-to-square text-xs"></i>
                                    </a>
                                    <button type="button" 
                                            onclick="openDeleteModal('{{ route('admin.product.delete', ['product' => $product->id]) }}', '{{ addslashes($product->name) }}')" 
                                            class="text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors p-1.5 rounded flex items-center justify-center cursor-pointer" 
                                            title="Xóa vào thùng rác">
                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                    </button>
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

        <!-- Pagination & Summary Stats Footer -->
        <div class="px-5 py-3 border-t border-[#F0F0F0] bg-[#FAFAFA] flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="text-xs text-[rgba(0,0,0,0.65)] font-medium">
                Hiển thị <span class="font-semibold text-gray-900">{{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }}</span> trong tổng số <span class="font-semibold text-[#F47716]">{{ $products->total() }}</span> món ăn
            </div>
            <div>
                {{ $products->appends(request()->query())->links() }}
            </div>
        </div>
    </div>

    <!-- Confirm Delete Modal (Ant Design Modal Style) -->
    <div id="deleteConfirmModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/40 backdrop-blur-xs transition-opacity duration-200">
        <div class="bg-white rounded-xl shadow-2xl border border-gray-100 w-full max-w-sm p-6 transform transition-all scale-95 opacity-0 duration-200" id="deleteModalBox">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-full bg-red-50 text-red-600 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-bold text-gray-900 mb-1">Xác nhận chuyển vào thùng rác</h3>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Bạn có chắc chắn muốn chuyển món <strong id="deleteProductName" class="text-gray-800"></strong> vào thùng rác không?
                    </p>
                </div>
            </div>
            <div class="mt-6 flex items-center justify-end gap-2">
                <button type="button" onclick="closeDeleteModal()" class="px-3.5 py-1.5 rounded-lg border border-gray-200 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition cursor-pointer">
                    Hủy bỏ
                </button>
                <a id="deleteConfirmBtn" href="#" class="px-3.5 py-1.5 rounded-lg bg-red-600 hover:bg-red-700 text-xs font-semibold text-white transition shadow-sm cursor-pointer">
                    Xóa vào thùng rác
                </a>
            </div>
        </div>
    </div>

    <script>
        function openDeleteModal(url, name) {
            const modal = document.getElementById('deleteConfirmModal');
            const box = document.getElementById('deleteModalBox');
            const nameEl = document.getElementById('deleteProductName');
            const btn = document.getElementById('deleteConfirmBtn');

            nameEl.textContent = name;
            btn.href = url;
            modal.classList.remove('hidden');
            setTimeout(() => {
                box.classList.remove('scale-95', 'opacity-0');
                box.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeDeleteModal() {
            const modal = document.getElementById('deleteConfirmModal');
            const box = document.getElementById('deleteModalBox');
            box.classList.remove('scale-100', 'opacity-100');
            box.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 200);
        }
    </script>
</x-layout-backend>