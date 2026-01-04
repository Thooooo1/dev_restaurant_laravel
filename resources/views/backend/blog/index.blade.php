<x-layout-backend>
    <!-- CONTENT -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Quản lý bài viết</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('admin/') }}">Trang chủ</a></li>
                        <li class="breadcrumb-item active">Bài viết</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-auto d-flex align-items-center">
                        <form method="GET" action="">
                            <div class="row align-items-center">
                                <div class="">
                                    <span><strong>Lọc theo:</strong></span>
                                </div>
                                <div class="col-auto">
                                    <input type="text" name="search" class="form-control" placeholder="Tìm kiếm sản phẩm..." value="">
                                </div>
                                <div class="col-auto">
                                    <select name="topic" class="form-control">
                                        <option value="">Danh mục</option>
                                        @foreach ($topics as $topic)
                                            <option value="{{ $topic->id }}"
                                                {{ request('topic') == $topic->id ? 'selected' : '' }}>
                                                {{ $topic->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-search"></i> Tìm kiếm
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
            
                    <!-- Nút Thêm và Thùng Rác căn phải -->
                    <div class="col ml-auto d-flex align-items-center justify-content-end">
                        <a class="btn btn-sm btn-success" href="{{ url('admin/blog/create') }}">
                            <i class="fas fa-plus"></i> Thêm
                        </a>
                        <a class="btn btn-sm btn-danger ml-2" href="{{ url('admin/blog/trash') }}">
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
                            <th class="text-center" style="width:90px;">Hình</th>
                            <th>Tiêu đề</th>
                            {{-- <th>Slug</th> --}}
                            <th>Mô tả</th>
                            {{-- <th style="width:250px;">Content</th> --}}
                            <th>Chủ đề</th>
                            <th>Loại</th>
                            <th>Kiểu</th>
                            <th class="text-center" style="width:200px;">Chức năng</th>
                            <th class="text-center" style="width:30px;">ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($posts as $post)
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" id="checkId" value="1" name="checkId[]">
                                </td>
                                <td class="text-center">
                                    <img src="{{ asset('/images/post/' . $post->image) }}" class="img-fluid"
                                        alt="{{ $post->image }}">
                                </td>
                                <td>{{ $post->title }}</td>
                                {{-- <td>{{ $post->slug }}</td> --}}
                                <td>{{ $post->description }}</td>
                                {{-- <td class="p-2 text-sm leading-relaxed text-gray-700 whitespace-normal">
                                    {{ \Illuminate\Support\Str::limit(strip_tags($post->content), 100) }}
                                </td> --}}
                                
                                <td>{{ $post->topic->name ?? 'None' }}</td>
                                <td>{{ $post->type }}</td>
                                <td class="text-center">
                                    @if ($post->new == 1)
                                        <span class="badge badge-success">Bài viết mới</span>
                                    @else
                                        
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($post->status == 1)
                                        <a href="{{ route('admin.blog.status', ['blog' => $post->id]) }}"
                                            class="btn btn-sm btn-success">
                                            <i class="fas fa-toggle-on"></i>
                                        </a>
                                    @else
                                        <a href="{{ route('admin.blog.status', ['blog' => $post->id]) }}"
                                            class="btn btn-sm btn-secondary">
                                            <i class="fas fa-toggle-off"></i>
                                        </a>
                                    @endif

                                    <a href="{{ route('admin.blog.show', ['blog' => $post->id]) }}"
                                        class="btn btn-sm btn-info">
                                        <i class="far fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.blog.edit', ['blog' => $post->id]) }}"
                                        class="btn btn-sm btn-primary">
                                        <i class="far fa-edit"></i>
                                    </a>
                                    <a href="{{ route('admin.blog.delete', ['blog' => $post->id]) }}"
                                        class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                                <td class="text-center">
                                    {{$post->id}}
                                </td>
                            </tr>
                        @endforeach

                    </tbody>
                </table>
                <div class= "my-4">
                    {{$posts->appends(request()->query())->links()}}
                </div>
            </div>
        </div>
    </section>
    <!-- /.CONTENT -->
</x-layout-backend>
