<x-layout-backend>
    <!-- CONTENT -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <strong class="fw-bold h4 text-danger">QUẢN LÝ LIÊN HỆ</strong>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('admin/') }}">Trang chủ</a></li>
                        <li class="breadcrumb-item active">Liên hệ</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-12 text-right">
                        <a class="btn btn-sm btn-danger" href="{{ url('admin/contact/trash') }}">
                            <i class="fas fa-trash"></i> Thùng rác
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:30px;">#</th>
                            <th>Họ tên</th>
                            <th>Điện thoại</th>
                            <th>Email</th>
                            <th>Tiêu đề</th>
                            <th>Nội dung</th>
                            {{-- <th>ID người dùng</th> --}}
                            <th class="text-center" style="width:200px;">Chức năng</th>
                            <th class="text-center" style="width:30px;">ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($contacts as $contact)
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" id="checkId" value="1" name="checkId[]">
                                </td>
                                <td>{{ $contact->name }}</td>
                                <td>{{ $contact->phone }}</td>
                                <td>{{ $contact->email }}</td>
                                <td>{{ $contact->title }}</td>
                                <td>{{ $contact->content }}</td>
                                {{-- <td>{{ $contact->user_id }}</td> --}}
                                <td class="text-center">
                                    @if ($contact->status == 1)
                                        <a href="{{ route('admin.contact.status', ['contact' => $contact->id]) }}"
                                            class="btn btn-sm btn-success">
                                            <i class="fas fa-toggle-on"></i>
                                        </a>
                                    @else
                                        <a href="{{ route('admin.contact.status', ['contact' => $contact->id]) }}"
                                            class="btn btn-sm btn-secondary">
                                            <i class="fas fa-toggle-off"></i>
                                        </a>
                                    @endif

                                    <a href="{{ route('admin.contact.show', ['contact' => $contact->id]) }}"
                                        class="btn btn-sm btn-info">
                                        <i class="far fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.contact.edit', ['contact' => $contact->id]) }}"
                                        class="btn btn-sm btn-primary">
                                        <i class="far fa-edit"></i>
                                    </a>
                                    <a href="{{ route('admin.contact.delete', ['contact' => $contact->id]) }}"
                                        class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                                <td class="text-center">
                                    {{$contact->id}}
                                </td>
                            </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>
        </div>
    </section>
    <!-- /.CONTENT -->
</x-layout-backend>
