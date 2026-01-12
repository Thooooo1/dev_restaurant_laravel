<x-layout-backend>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Quản lý nhập hàng</h3>
            <div class="card-tools">
                <a href="{{ route('admin.import-goods.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Thêm mới
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Filter Form -->
            <form action="{{ route('admin.import-goods.index') }}" method="GET" class="form-inline mb-3">
                <div class="form-group mr-2">
                    <input type="text" name="search" class="form-control" placeholder="Tìm tên hàng hoá..." value="{{ request('search') }}">
                </div>
                <div class="form-group mr-2">
                    <select name="year" class="form-control">
                        <option value="">-- Năm --</option>
                        @for($y = date('Y'); $y >= 2020; $y--)
                            <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="form-group mr-2">
                    <select name="month" class="form-control">
                        <option value="">-- Tháng --</option>
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>Tháng {{ $m }}</option>
                        @endfor
                    </select>
                </div>
                <button type="submit" class="btn btn-default"><i class="fas fa-search"></i> Tìm kiếm</button>
            </form>

            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên hàng hoá</th>
                        <th>Loại hàng hoá</th>
                        <th>Đơn vị tính</th>
                        <th>Số lượng</th>
                        <th>Đơn giá</th>
                        <th>Thành tiền</th>
                        <th>Ngày nhập</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                    <tr>
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->type }}</td>
                        <td>{{ $item->unit }}</td>
                        <td>{{ number_format($item->quantity) }}</td>
                        <td>{{ number_format($item->price) }} VNĐ</td>
                        <td>{{ number_format($item->total_amount) }} VNĐ</td>
                        <td>{{ $item->created_at->format('d/m/Y') }}</td>
                        <td>
                            <a href="{{ route('admin.import-goods.edit', $item->id) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.import-goods.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center">Không có dữ liệu</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            
            <div class="mt-3">
                {{ $items->withQueryString()->links() }}
            </div>
        </div>
    </div>
</x-layout-backend>
