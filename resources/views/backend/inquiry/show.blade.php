<x-layout-backend>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Chi tiết yêu cầu</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('admin/') }}">Trang chủ</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.inquiry.index') }}">Yêu cầu</a></li>
                        <li class="breadcrumb-item active">Chi tiết</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    
    <section class="content">
        <div class="card">
            <div class="card-body">
                <p><strong>Họ tên:</strong> {{ $inquiry->name }}</p>
                <p><strong>Số điện thoại:</strong> {{ $inquiry->phone }}</p>
                <p><strong>Nội dung:</strong> {{ $inquiry->message }}</p>
                <p><strong>Ngày gửi:</strong> {{ date('H:i d/m/Y', strtotime($inquiry->created_at)) }}</p>

                <form action="{{ route('admin.inquiry.updateStatus', $inquiry->id) }}" method="POST">
                    @csrf
                    <label for="status">Cập nhật trạng thái:</label>
                    <select name="status" class="form-control mb-3">
                        <option value="1">Chờ xử lý</option>
                        <option value="2">Đã liên hệ</option>
                        <option value="3">Hoàn thành</option>
                    </select>
                    <button type="submit" class="btn btn-success">Cập nhật</button>
                </form>
            </div>
        </div>
    </section>
</x-layout-backend>
