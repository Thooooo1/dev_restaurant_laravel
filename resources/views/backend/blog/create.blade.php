<x-layout-backend>
    <!-- CONTENT -->
    <form action="{{route('admin.blog.store')}}" method="post" enctype="multipart/form-data">
        @csrf
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Thêm bài viết</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ url('admin/')}}">Trang chủ</a></li>
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
                        <div class="col-12 text-right">
                            <button type="submit" name="create" class="btn btn-sm btn-success">
                                <i class="fa fa-save"></i> Lưu
                            </button>
                            <a class="btn btn-sm btn-info" href="{{ url('admin/blog/')}}">
                                <i class="fa fa-arrow-left"></i> Về danh sách
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-9">
                            <div class="mb-3">
                                <label for="title">Tiêu đề</label>
                                <input type="text" value="" name="title" id="title" class="form-control">
                                @if($errors->has('title'))
                                <div class="text-danger">{{ $errors->first('title') ;}}</div>
                            @endif
                            </div>
                            <div class="mb-3">
                                <label for="slug">Slug</label>
                                <textarea name="slug" id="slug" rows="1" class="form-control"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="description">Mô tả</label>
                                <textarea name="description" id="description" class="form-control"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="content" class="block text-sm font-medium text-gray-700">Nội dung</label>
                                <div id="editor" class="w-full h-128 border p-2"></div>
                                <input type="hidden" name="content" id="content">
                            </div>
                            
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="topic_id">Chủ đề</label>
                                <select name="topic_id" id="topic_id" class="form-control">
                                    <option value="">Chọn chủ đề</option>
                                    @foreach ($topics as $topic)
                                    <option value="{{ $topic->id }}"
                                        {{ old('topic_id') == $topic->id ? 'selected' : '' }}>
                                        {{ $topic->name }}
                                    </option>
                                @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="type">Kiểu</label>
                                <select name="type" id="type" class="form-control">
                                    <option value="post">Bài viết</option>
                                    <option value="page">Trang đơn</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="image">Hình</label>
                                <input type="file" name="image" id="image" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="status">Trạng thái</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="2">Chưa xuất bản</option>
                                    <option value="1">Xuất bản</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </form>
    <!-- Initialize Quill -->
    <script>
        var quill = new Quill('#editor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': '1' }, { 'header': '2' }, { 'font': [] }],
                    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                    [{ 'align': [] }],
                    ['bold', 'italic', 'underline'],
                    ['link'],
                    ['image']
                ]
            }
        });

        // Cập nhật nội dung từ Quill vào input hidden khi form submit
        document.querySelector('form').onsubmit = function() {
            var content = document.querySelector('#content');
            content.value = quill.root.innerHTML;
        };
    </script>
        <!-- /.CONTENT -->
</x-layout-backend>