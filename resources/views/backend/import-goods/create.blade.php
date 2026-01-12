<x-layout-backend>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Thêm mới hàng hoá nhập</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.import-goods.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tên hàng hoá <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                            @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Loại hàng hoá</label>
                            <input type="text" name="type" class="form-control" value="{{ old('type') }}">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Đơn vị tính <span class="text-danger">*</span></label>
                            <select name="unit" class="form-control" required>
                                <option value="">-- Chọn đơn vị --</option>
                                <option value="cái" {{ old('unit') == 'cái' ? 'selected' : '' }}>cái</option>
                                <option value="kg" {{ old('unit') == 'kg' ? 'selected' : '' }}>kg</option>
                                <option value="g" {{ old('unit') == 'g' ? 'selected' : '' }}>g</option>
                                <option value="l" {{ old('unit') == 'l' ? 'selected' : '' }}>l</option>
                                <option value="ml" {{ old('unit') == 'ml' ? 'selected' : '' }}>ml</option>
                                <option value="hộp" {{ old('unit') == 'hộp' ? 'selected' : '' }}>hộp</option>
                                <option value="chai" {{ old('unit') == 'chai' ? 'selected' : '' }}>chai</option>
                                <option value="gói" {{ old('unit') == 'gói' ? 'selected' : '' }}>gói</option>
                            </select>
                            @error('unit') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Số lượng <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" class="form-control" value="{{ old('quantity') }}" required min="0" id="quantity">
                            @error('quantity') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Đơn giá <span class="text-danger">*</span></label>
                            <input type="number" name="price" class="form-control" value="{{ old('price') }}" required min="0" id="price">
                            @error('price') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Thành tiền dự kiến: </label>
                    <span id="total_amount_preview" class="font-weight-bold text-success">0 VNĐ</span>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu lại</button>
                    <a href="{{ route('admin.import-goods.index') }}" class="btn btn-secondary">Quay lại</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const qtyInput = document.getElementById('quantity');
            const priceInput = document.getElementById('price');
            const totalPreview = document.getElementById('total_amount_preview');

            function calculateTotal() {
                const qty = parseFloat(qtyInput.value) || 0;
                const price = parseFloat(priceInput.value) || 0;
                const total = qty * price;
                totalPreview.innerText = new Intl.NumberFormat('vi-VN').format(total) + ' VNĐ';
            }

            qtyInput.addEventListener('input', calculateTotal);
            priceInput.addEventListener('input', calculateTotal);
        });
    </script>
</x-layout-backend>
