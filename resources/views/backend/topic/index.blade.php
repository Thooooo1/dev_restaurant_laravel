<x-layout-backend>
    <!-- CONTENT -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Quản lý chủ đề</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('admin/') }}">Trang chủ</a></li>
                        <li class="breadcrumb-item active">Chủ đề</li>
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
                        <a class="btn btn-sm btn-danger" href="{{ url('admin/topic/trash') }}">
                            <i class="fas fa-trash"></i> Thùng rác
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <form action="{{route('admin.topic.store')}}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="name">Tên chủ đề</label>
                                <input type="text" value="" name="name" id="name" 
                                       class="form-control @error('name') is-invalid @enderror">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        
                            <div class="mb-3">
                                <label for="slug">Slug</label>
                                <input type="text" value="" name="slug" id="slug" 
                                       class="form-control @error('slug') is-invalid @enderror ">
                                @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="sort_order">Sắp xếp</label>
                                <input type="text" value="" name="sort_order" id="sort_order" 
                                       class="form-control ">
                            
                            </div>
                            <div class="mb-3">
                                <label for="name">Mô tả</label>
                                <input type="text" value="" name="description" id="description" 
                                       class="form-control @error('description') is-invalid @enderror">
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="status">Trạng thái</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="2">Chưa xuất bản</option>
                                    <option value="1">Xuất bản</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <button type="submit" name="create" class="btn btn-success">Thêm từ khóa</button>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-9">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width:30px;">#</th>
                                    <th>Tên chủ đề</th>
                                    <th>Slug</th>
                                    <th>Mô tả</th>
                                    <th class="text-center" style="width:200px;">Chức năng</th>
                                    <th class="text-center" style="width:30px;">ID</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($topics as $topic)
                                    <tr>

                                        <td class="text-center">
                                            <input type="checkbox" id="checkId" value="1" name="checkId[]">
                                        </td>
                                        <td>{{ $topic->name }}</td>
                                        <td>{{ $topic->slug }}</td>
                                        <td>{{ $topic->description }}</td>
                                        <td class="text-center">
                                            @if ($topic->status == 1)
                                                <a href="{{ route('admin.topic.status', ['topic' => $topic->id]) }}"
                                                    class="btn btn-sm btn-success">
                                                    <i class="fas fa-toggle-on"></i>
                                                </a>
                                            @else
                                                <a href="{{ route('admin.topic.status', ['topic' => $topic->id]) }}"
                                                    class="btn btn-sm btn-success">
                                                    <i class="fas fa-toggle-off"></i>
                                                </a>
                                            @endif

                                            <a href="{{ route('admin.topic.show', ['topic' => $topic->id]) }}"
                                                class="btn btn-sm btn-info">
                                                <i class="far fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.topic.edit', ['topic' => $topic->id]) }}"
                                                class="btn btn-sm btn-primary">
                                                <i class="far fa-edit"></i>
                                            </a>
                                            <a href="{{ route('admin.topic.delete', ['topic' => $topic->id]) }}"
                                                class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            {{ $topic->id }}
                                        </td>
                                    </tr>
                                @endforeach


                            </tbody>
                        </table>
                        <div class= "my-4">
                            {{ $topics->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- /.CONTENT -->
</x-layout-backend>
