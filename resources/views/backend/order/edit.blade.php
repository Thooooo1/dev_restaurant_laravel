<x-layout-backend>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Cập nhật trạng thái đơn hàng</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('admin/') }}">Trang chủ</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.order.index') }}">Danh sách đơn hàng</a></li>
                        <li class="breadcrumb-item active">Cập nhật trạng thái</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="card">
            <div class="card-header">
                <a href="{{ route('admin.order.index') }}" class="btn btn-sm btn-info">
                    <i class="fa fa-arrow-left"></i> Về danh sách
                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.order.update', $order->id) }}">
                    @csrf
                    <div class="form-group">
                        <label for="status">Trạng thái đơn hàng:</label>
                        <select name="status" id="status" class="form-control">
                            <option value="0" {{ $order->status == 0 ? 'selected' : '' }}>Đang chờ xử lý</option>
                            <option value="1" {{ $order->status == 1 ? 'selected' : '' }}>Đang vận chuyển</option>
                            <option value="2" {{ $order->status == 2 ? 'selected' : '' }}>Đã giao hàng</option>
                            <option value="3" {{ $order->status == 3 ? 'selected' : '' }}>Đã hủy</option>
                            
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                </form>
            </div>
        </div>
    </section>
</x-layout-backend>
