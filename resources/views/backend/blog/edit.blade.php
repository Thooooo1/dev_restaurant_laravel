<x-layout-backend>
    <!-- CONTENT -->
    <form action="{{route('admin.blog.update',['blog'=>$post->id])}}" method="post" enctype="multipart/form-data">
        @csrf
        @method('PUT')
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
                            <button type="submit" class="btn btn-sm btn-success">
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
                                <input type="text" value="{{ old('title', $post->title) }}" name="title" id="title" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="slug">Slug</label>
                                <textarea name="slug" id="slug" rows="1" class="form-control"> {{ old('slug', $post->slug) }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label for="description">Mô tả</label>
                                <textarea name="description" id="description" class="form-control">{{ old('description', $post->description) }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label for="content" class="block text-sm font-medium text-gray-700">Nội dung</label>
                                <div id="editor" class="w-full h-128 border p-2"> {{ old('content', $post->content) }} </div>
                                <input type="hidden" name="content" id="content" value="">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="topic_id">Chủ đề</label>
                                <select name="topic_id" id="topic_id" class="form-control">
                                    <option value="0">Chọn chủ đề</option>
                                    @foreach ($topics as $topic)
                                        <option value="{{ $topic->id }}" {{ old('topic_id', $post->topic_id) == $topic->id ? 'selected' : '' }}>
                                            {{ $topic->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('topic_id')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="type">Kiểu</label>
                                <select name="type" id="type" class="form-control">
                                    <option value="post" {{ old('type', $post->type) == 'post' ? 'selected' : '' }}>Bài viết</option>
                                    <option value="page" {{ old('type', $post->type) == 'page' ? 'selected' : '' }}>Trang đơn</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="image">Hình</label>
                                <input type="file" name="image" id="image" class="form-control">
                                @if($post->image)
                            <img src="{{ asset('images/post/' . $post->image) }}" alt="{{ $post->title }}" class="img-thumbnail mt-2" width="200">
                            @endif
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="new" name="new"
                                        value="1" {{ old('new', $post->new) == 1 ? 'checked' : '' }}>
                                    <label class="form-check-label" for="new">
                                        Bài viết mới
                                    </label>

                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="status">Trạng thái</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="2" {{ old('status', $post->status) == 2 ? 'selected' : '' }}>Chưa xuất bản</option>
                                    <option value="1" {{ old('status', $post->status) == 1 ? 'selected' : '' }}>Xuất bản</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </form>
    <!-- Initialize Quill -->
    <div id="editor" class="w-full h-128 border p-2"></div>
<input type="hidden" name="content" id="content" value="">

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

    // Gán nội dung cũ vào Quill
    var oldContent = `{!! old('content', $post->content) !!}`;
    quill.root.innerHTML = oldContent;

    // Cập nhật nội dung vào input hidden khi submit
    document.querySelector('form').onsubmit = function() {
        var content = document.querySelector('#content');
        content.value = quill.root.innerHTML;
    };
</script>

        <!-- /.CONTENT -->
</x-layout-backend>