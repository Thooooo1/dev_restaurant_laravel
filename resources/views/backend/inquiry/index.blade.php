<x-layout-backend>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Quản lý yêu cầu</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('admin/') }}">Trang chủ</a></li>
                        <li class="breadcrumb-item active">Yêu cầu</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    
    <section class="content">
        <div class="card">
            <div class="card-header">
                <form method="GET" class="form-inline">
                    <label for="status" class="mr-2">Lọc theo trạng thái:</label>
                    <select name="status" id="status" class="form-control mr-2">
                        <option value="0" {{ $statusFilter == 0 ? 'selected' : '' }}>Tất cả</option>
                        <option value="1" {{ $statusFilter == 1 ? 'selected' : '' }}>Chờ xử lý</option>
                        <option value="2" {{ $statusFilter == 2 ? 'selected' : '' }}>Đã liên hệ</option>
                        <option value="3" {{ $statusFilter == 3 ? 'selected' : '' }}>Hoàn thành</option>
                    </select>
                    <button type="submit" class="btn btn-primary">Lọc</button>
                </form>
            </div>

            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Họ tên</th>
                            <th>Điện thoại</th>
                            <th>Nội dung</th>
                            <th>Ngày gửi</th>
                            <th>Trạng thái</th>
                            <th>Chức năng</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($inquiries as $index => $inquiry)
                        <tr>
                            <td>{{ $inquiries->firstItem() + $index }}</td>
                            <td>{{ $inquiry->name }}</td>
                            <td>{{ $inquiry->phone }}</td>
                            <td>{{ Str::limit($inquiry->message, 50) }}</td>
                            <td>{{ date('H:i d/m/Y', strtotime($inquiry->created_at)) }}</td>
                            <td>
                                @switch($inquiry->status)
                                    @case(1) Chờ xử lý @break
                                    @case(2) Đã liên hệ @break
                                    @case(3) Hoàn thành @break
                                @endswitch
                            </td>
                            <td>
                                <a href="{{ route('admin.inquiry.show', $inquiry->id) }}" class="btn btn-sm btn-info">Xem</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                
                <div class="my-4">
                    {{ $inquiries->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </section>
</x-layout-backend>
