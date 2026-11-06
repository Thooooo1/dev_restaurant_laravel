<x-layout-backend>
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                    <a href="{{ route('admin.import-goods.index') }}" class="hover:text-[#F47716]">Quản lý nhập kho</a>
                    <span>/</span>
                    <a href="{{ route('admin.import-goods.show', $receipt->id) }}" class="hover:text-[#F47716] font-mono">{{ $receipt->code }}</a>
                    <span>/</span>
                    <span class="text-slate-800 font-semibold">Chỉnh sửa</span>
                </div>
                <h1 class="text-xl font-bold text-slate-800">
                    Chỉnh Sửa Phiếu Nhập Kho: <strong class="font-mono text-[#F47716]">{{ $receipt->code }}</strong>
                </h1>
            </div>
            <div>
                <a href="{{ route('admin.import-goods.show', $receipt->id) }}" 
                   class="inline-flex items-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-sm font-medium transition-colors">
                    <span>Hủy & Quay lại</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Alert lỗi nếu có -->
    @if ($errors->any())
        <div class="mb-5 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
            <div class="font-bold flex items-center gap-2 mb-1.5 text-rose-700">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>Vui lòng kiểm tra lại thông tin:</span>
            </div>
            <ul class="list-disc list-inside text-xs space-y-1 text-rose-600">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form Chỉnh Sửa Phiếu Nhập -->
    <form action="{{ route('admin.import-goods.update', $receipt->id) }}" method="POST" id="importReceiptEditForm">
        @csrf
        @method('PUT')

        <!-- 1. THÔNG TIN CHUNG PHIẾU NHẬP -->
        <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-6 mb-6">
            <h2 class="font-bold text-slate-800 text-base mb-4 flex items-center gap-2 border-b border-slate-100 pb-3">
                <i class="fa-solid fa-circle-info text-[#F47716]"></i>
                <span>1. Thông tin chung phiếu nhập</span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Mã phiếu -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Mã Phiếu Nhập <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           name="code" 
                           class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 font-mono font-bold text-slate-800 bg-slate-50 focus:bg-white focus:border-[#F47716] outline-none text-sm"
                           value="{{ old('code', $receipt->code) }}" 
                           required>
                </div>

                <!-- Ngày giờ nhập -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Ngày Giờ Nhập Kho <span class="text-rose-500">*</span>
                    </label>
                    <input type="datetime-local" 
                           name="import_date" 
                           class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 font-medium text-slate-800 focus:border-[#F47716] outline-none text-sm bg-white"
                           value="{{ old('import_date', $receipt->import_date ? $receipt->import_date->format('Y-m-d\TH:i') : '') }}" 
                           required>
                </div>

                <!-- Người lập phiếu -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Người Lập Phiếu / Thủ Kho
                    </label>
                    <input type="text" 
                           name="creator_name" 
                           class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 font-medium text-slate-800 focus:border-[#F47716] outline-none text-sm bg-white"
                           value="{{ old('creator_name', $receipt->creator_name) }}">
                </div>

                <!-- Tên Nhà cung cấp -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Nhà Cung Cấp / Đơn Vị Giao <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           name="supplier_name" 
                           class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 font-medium text-slate-800 focus:border-[#F47716] outline-none text-sm bg-white"
                           value="{{ old('supplier_name', $receipt->supplier_name) }}" 
                           required>
                </div>

                <!-- SĐT Nhà cung cấp -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Số Điện Thoại NCC
                    </label>
                    <input type="text" 
                           name="supplier_phone" 
                           class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 font-mono text-slate-800 focus:border-[#F47716] outline-none text-sm bg-white"
                           value="{{ old('supplier_phone', $receipt->supplier_phone) }}">
                </div>

                <!-- Địa chỉ NCC -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Địa Chỉ NCC
                    </label>
                    <input type="text" 
                           name="supplier_address" 
                           class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 font-medium text-slate-800 focus:border-[#F47716] outline-none text-sm bg-white"
                           value="{{ old('supplier_address', $receipt->supplier_address) }}">
                </div>

                <!-- Ghi chú đợt nhập -->
                <div class="md:col-span-3">
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Ghi Chú / Diễn Giải Đợt Nhập
                    </label>
                    <input type="text" 
                           name="note" 
                           class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 font-medium text-slate-800 focus:border-[#F47716] outline-none text-sm bg-white"
                           value="{{ old('note', $receipt->note) }}">
                </div>
            </div>
        </div>

        <!-- 2. DANH SÁCH MẶT HÀNG NHẬP (BẢNG ĐỘNG NHIỀU DÒNG) -->
        <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-6 mb-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4 flex-wrap gap-2">
                <h2 class="font-bold text-slate-800 text-base">
                    2. Danh sách mặt hàng nhập kho
                </h2>
                <button type="button" id="btnAddRow" 
                        class="inline-flex items-center px-3 py-1.5 bg-orange-100 hover:bg-[#F47716] text-[#F47716] hover:text-white rounded-lg text-xs font-bold transition-colors cursor-pointer">
                    <span>+ Thêm Dòng Hàng</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse" id="itemsTable">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-600 uppercase tracking-wider">
                            <th class="px-3 py-3 w-10 text-center">#</th>
                            <th class="px-3 py-3 min-w-[200px]">Tên Hàng Hóa / Nguyên Liệu <span class="text-rose-500">*</span></th>
                            <th class="px-3 py-3 w-36">Phân Loại</th>
                            <th class="px-3 py-3 w-32">Đơn Vị Tính <span class="text-rose-500">*</span></th>
                            <th class="px-3 py-3 w-28 text-right">Số Lượng <span class="text-rose-500">*</span></th>
                            <th class="px-3 py-3 w-36 text-right">Đơn Giá (₫) <span class="text-rose-500">*</span></th>
                            <th class="px-3 py-3 w-40 text-right">Thành Tiền (₫)</th>
                            <th class="px-3 py-3 w-12 text-center">Xóa</th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody" class="divide-y divide-slate-100 text-sm">
                        @foreach($receipt->items as $i => $item)
                        <tr class="item-row hover:bg-slate-50/60 transition-colors">
                            <td class="px-3 py-3 text-center text-slate-400 font-bold row-index">{{ $i + 1 }}</td>
                            <td class="px-3 py-3">
                                <input type="text" name="items[{{ $i }}][name]" 
                                       class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-[#F47716] outline-none text-sm bg-white font-medium" 
                                       value="{{ $item->name }}" required>
                            </td>
                            <td class="px-3 py-3">
                                <select name="items[{{ $i }}][type]" class="w-full px-2.5 py-2 rounded-lg border border-slate-300 focus:border-[#F47716] outline-none text-xs bg-white">
                                    <option value="Thực phẩm tươi" {{ $item->type == 'Thực phẩm tươi' ? 'selected' : '' }}>Thực phẩm tươi</option>
                                    <option value="Thịt / Gia cầm" {{ $item->type == 'Thịt / Gia cầm' ? 'selected' : '' }}>Thịt / Gia cầm</option>
                                    <option value="Hải sản" {{ $item->type == 'Hải sản' ? 'selected' : '' }}>Hải sản</option>
                                    <option value="Rau củ quả" {{ $item->type == 'Rau củ quả' ? 'selected' : '' }}>Rau củ quả</option>
                                    <option value="Đồ uống / Bia" {{ $item->type == 'Đồ uống / Bia' ? 'selected' : '' }}>Đồ uống / Bia</option>
                                    <option value="Gia vị / Đồ khô" {{ $item->type == 'Gia vị / Đồ khô' ? 'selected' : '' }}>Gia vị / Đồ khô</option>
                                    <option value="Bao bì / Vật tư" {{ $item->type == 'Bao bì / Vật tư' ? 'selected' : '' }}>Bao bì / Vật tư</option>
                                </select>
                            </td>
                            <td class="px-3 py-3">
                                <select name="items[{{ $i }}][unit]" class="w-full px-2.5 py-2 rounded-lg border border-slate-300 focus:border-[#F47716] outline-none text-sm bg-white" required>
                                    <option value="kg" {{ $item->unit == 'kg' ? 'selected' : '' }}>kg</option>
                                    <option value="con" {{ $item->unit == 'con' ? 'selected' : '' }}>con</option>
                                    <option value="g" {{ $item->unit == 'g' ? 'selected' : '' }}>g</option>
                                    <option value="thùng" {{ $item->unit == 'thùng' ? 'selected' : '' }}>thùng</option>
                                    <option value="chai" {{ $item->unit == 'chai' ? 'selected' : '' }}>chai</option>
                                    <option value="lon" {{ $item->unit == 'lon' ? 'selected' : '' }}>lon</option>
                                    <option value="hộp" {{ $item->unit == 'hộp' ? 'selected' : '' }}>hộp</option>
                                    <option value="gói" {{ $item->unit == 'gói' ? 'selected' : '' }}>gói</option>
                                    <option value="bó" {{ $item->unit == 'bó' ? 'selected' : '' }}>bó</option>
                                    <option value="cái" {{ $item->unit == 'cái' ? 'selected' : '' }}>cái</option>
                                </select>
                            </td>
                            <td class="px-3 py-3">
                                <input type="number" step="any" min="0.01" name="items[{{ $i }}][quantity]" 
                                       class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-[#F47716] outline-none text-sm bg-white text-right font-bold qty-input" 
                                       value="{{ $item->quantity }}" required>
                            </td>
                            <td class="px-3 py-3">
                                <input type="number" step="any" min="0" name="items[{{ $i }}][price]" 
                                       class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-[#F47716] outline-none text-sm bg-white text-right font-mono price-input" 
                                       value="{{ $item->price }}" required>
                            </td>
                            <td class="px-3 py-3 text-right font-bold text-slate-800 font-mono row-total">
                                {{ number_format($item->total_amount, 0, ',', '.') }}₫
                            </td>
                            <td class="px-3 py-3 text-center">
                                <button type="button" class="btn-remove-row text-slate-300 hover:text-rose-600 transition-colors p-1" title="Xóa dòng">
                                    <i class="fa-solid fa-trash-can text-sm"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Nút thêm dòng phụ phía dưới bảng -->
            <div class="mt-4 flex items-center justify-between flex-wrap gap-4 pt-4 border-t border-slate-100">
                <button type="button" id="btnAddRowBottom" 
                        class="inline-flex items-center gap-2 px-3.5 py-2 border border-dashed border-[#F47716] text-[#F47716] hover:bg-orange-50 rounded-lg text-xs font-bold transition-colors">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>+ Thêm mặt hàng tiếp theo</span>
                </button>

                <!-- Tổng giá trị phiếu -->
                <div class="text-right">
                    <span class="text-xs uppercase tracking-wider text-slate-500 font-semibold mr-3">Tổng Tiền Phiếu Nhập:</span>
                    <span class="text-2xl font-black text-[#F47716] font-mono" id="grandTotalPreview">
                        {{ number_format($receipt->total_amount, 0, ',', '.') }}₫
                    </span>
                </div>
            </div>
        </div>

        <!-- 3. HÀNH ĐỘNG SUBMIT -->
        <div class="flex items-center justify-end gap-3 bg-white rounded-xl shadow-xs border border-slate-200 p-4">
            <a href="{{ route('admin.import-goods.show', $receipt->id) }}" 
               class="px-5 py-2.5 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50 text-sm font-semibold transition-colors">
                Hủy bỏ
            </a>

            <button type="submit" 
                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#F47716] hover:bg-[#EA580C] text-white rounded-lg text-sm font-bold transition-all shadow-md shadow-orange-500/20">
                <i class="fa-solid fa-check text-xs"></i>
                <span>Cập Nhật Phiếu Nhập Kho</span>
            </button>
        </div>
    </form>

    <!-- SCRIPT TÍNH TOÁN DÒNG TIỀN VÀ THÊM DÒNG HÀNG ĐỘNG -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let rowIndex = {{ $receipt->items->count() }};
            const itemsBody = document.getElementById('itemsBody');
            const grandTotalEl = document.getElementById('grandTotalPreview');

            function calculateTotals() {
                let grandTotal = 0;
                const rows = itemsBody.querySelectorAll('.item-row');
                
                rows.forEach((row, idx) => {
                    const idxCol = row.querySelector('.row-index');
                    if (idxCol) idxCol.innerText = idx + 1;

                    const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
                    const price = parseFloat(row.querySelector('.price-input').value) || 0;
                    const total = qty * price;
                    grandTotal += total;

                    const rowTotalEl = row.querySelector('.row-total');
                    if (rowTotalEl) {
                        rowTotalEl.innerText = new Intl.NumberFormat('vi-VN').format(total) + '₫';
                    }
                });

                grandTotalEl.innerText = new Intl.NumberFormat('vi-VN').format(grandTotal) + '₫';
            }

            function addRow() {
                const tr = document.createElement('tr');
                tr.className = 'item-row hover:bg-slate-50/60 transition-colors';
                tr.innerHTML = `
                    <td class="px-3 py-3 text-center text-slate-400 font-bold row-index"></td>
                    <td class="px-3 py-3">
                        <input type="text" name="items[${rowIndex}][name]" 
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-[#F47716] outline-none text-sm bg-white font-medium" 
                               placeholder="VD: Rau muống, Bia Tiger..." required>
                    </td>
                    <td class="px-3 py-3">
                        <select name="items[${rowIndex}][type]" class="w-full px-2.5 py-2 rounded-lg border border-slate-300 focus:border-[#F47716] outline-none text-xs bg-white">
                            <option value="Thực phẩm tươi">Thực phẩm tươi</option>
                            <option value="Thịt / Gia cầm">Thịt / Gia cầm</option>
                            <option value="Hải sản">Hải sản</option>
                            <option value="Rau củ quả">Rau củ quả</option>
                            <option value="Đồ uống / Bia">Đồ uống / Bia</option>
                            <option value="Gia vị / Đồ khô">Gia vị / Đồ khô</option>
                            <option value="Bao bì / Vật tư">Bao bì / Vật tư</option>
                        </select>
                    </td>
                    <td class="px-3 py-3">
                        <select name="items[${rowIndex}][unit]" class="w-full px-2.5 py-2 rounded-lg border border-slate-300 focus:border-[#F47716] outline-none text-sm bg-white" required>
                            <option value="kg">kg</option>
                            <option value="con">con</option>
                            <option value="g">g</option>
                            <option value="thùng">thùng</option>
                            <option value="chai">chai</option>
                            <option value="lon">lon</option>
                            <option value="hộp">hộp</option>
                            <option value="gói">gói</option>
                            <option value="bó">bó</option>
                            <option value="cái">cái</option>
                        </select>
                    </td>
                    <td class="px-3 py-3">
                        <input type="number" step="any" min="0.01" name="items[${rowIndex}][quantity]" 
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-[#F47716] outline-none text-sm bg-white text-right font-bold qty-input" 
                               value="1" required>
                    </td>
                    <td class="px-3 py-3">
                        <input type="number" step="any" min="0" name="items[${rowIndex}][price]" 
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-[#F47716] outline-none text-sm bg-white text-right font-mono price-input" 
                               value="0" required>
                    </td>
                    <td class="px-3 py-3 text-right font-bold text-slate-800 font-mono row-total">
                        0₫
                    </td>
                    <td class="px-3 py-3 text-center">
                        <button type="button" class="btn-remove-row text-slate-300 hover:text-rose-600 transition-colors p-1" title="Xóa dòng">
                            <i class="fa-solid fa-trash-can text-sm"></i>
                        </button>
                    </td>
                `;

                itemsBody.appendChild(tr);
                rowIndex++;
                calculateTotals();
            }

            document.getElementById('btnAddRow').addEventListener('click', addRow);
            document.getElementById('btnAddRowBottom').addEventListener('click', addRow);

            itemsBody.addEventListener('click', function(e) {
                const btn = e.target.closest('.btn-remove-row');
                if (btn) {
                    const rows = itemsBody.querySelectorAll('.item-row');
                    if (rows.length <= 1) {
                        alert('Phiếu nhập kho phải có ít nhất 1 mặt hàng.');
                        return;
                    }
                    btn.closest('.item-row').remove();
                    calculateTotals();
                }
            });

            itemsBody.addEventListener('input', function(e) {
                if (e.target.classList.contains('qty-input') || e.target.classList.contains('price-input')) {
                    calculateTotals();
                }
            });

            calculateTotals();
        });
    </script>
</x-layout-backend>
