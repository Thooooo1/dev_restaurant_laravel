<x-layout-backend>
    <!-- CONTENT -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Quản lý Thư viện </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('admin/') }}">Trang chủ</a></li>
                        <li class="breadcrumb-item active">Từ khóa</li>
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
                        <a class="btn btn-sm btn-danger" href="{{ url('admin/keyword/trash') }}">
                            <i class="fas fa-trash"></i> Thùng rác
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <form action="{{route('admin.keyword.store')}}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="title">Tên keyword</label>
                                <input type="text" value="" name="title" id="title" 
                                       class="form-control @error('title') is-invalid @enderror">
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        
                            <div class="mb-3">
                                <label for="slug">Slug</label>
                                <input type="text" value="" name="slug" id="slug" 
                                       class="form-control ">
                               
                            </div>
                            <div class="mb-3">
                                <label for="type">Loại từ khóa</label>
                                <select name="type" id="type" class="form-control">
                                    <option value="0">None</option>
                                    <option value="search">Search</option>
                                    <option value="blog">Blog</option>
                                    
                                </select>
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
                                    <th>Tên </th>
                                    <th>Slug </th>
                                    <th>Loại</th>
                                    <th class="text-center" style="width:200px;">Chức năng</th>
                                    <th class="text-center" style="width:30px;">ID</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($keywords as $keyword)
                                    <tr>

                                        <td class="text-center">
                                            <input type="checkbox" id="checkId" value="1" name="checkId[]">
                                        </td>

                                        <td>{{ $keyword->title }} </td>
                                        <td>{{ $keyword->slug }}</td>
                                        <td>{{ $keyword->type }}</td>
                                        <td class="text-center">
                                            @if ($keyword->status == 1)
                                                <a href="{{ route('admin.keyword.status', ['keyword' => $keyword->id]) }}"
                                                    class="btn btn-sm btn-success">
                                                    <i class="fas fa-toggle-on"></i>
                                                </a>
                                            @else
                                                <a href="{{ route('admin.keyword.status', ['keyword' => $keyword->id]) }}"
                                                    class="btn btn-sm btn-success">
                                                    <i class="fas fa-toggle-off"></i>
                                                </a>
                                            @endif

                                            <a href="{{ route('admin.keyword.show', ['keyword' => $keyword->id]) }}"
                                                class="btn btn-sm btn-info">
                                                <i class="far fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.keyword.edit', ['keyword' => $keyword->id]) }}"
                                                class="btn btn-sm btn-primary">
                                                <i class="far fa-edit"></i>
                                            </a>
                                            <a href="{{ route('admin.keyword.delete', ['keyword' => $keyword->id]) }}"
                                                class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            {{ $keyword->id }}
                                        </td>
                                    </tr>
                                @endforeach


                            </tbody>
                        </table>
                        <div class= "my-4">
                            {{ $keywords->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- /.CONTENT -->
</x-layout-backend>
