<x-layout-backend>
    <!-- CONTENT -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Cập nhật chủ đề</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('admin/')}}">Trang chủ</a></li>
                        <li class="breadcrumb-item active">Topic</li>
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
                        <a class="btn btn-sm btn-danger" href="{{ url('admin/topic/trash')}}">
                            <i class="fas fa-trash"></i> Thùng rác
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form action="{{route('admin.topic.update',['topic'=>$topic->id])}}" method="post" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                
                    <div class="mb-3">
                        <label for="title">Tên chủ đề</label>
                        <input type="text" value="{{ old('title', $topic->name) }}" name="name" id="name" 
                               class="form-control @error('title') is-invalid @enderror">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                
                    <div class="mb-3">
                        <label for="slug">Slug</label>
                        <input type="text" value="{{ old('slug', $topic->slug) }}" name="slug" id="slug" 
                               class="form-control @error('slug') is-invalid @enderror ">
                        @error('slug')
                             <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="sort_order">Sắp xếp</label>
                        <input type="text" value="{{ old('sort_order', $topic->sort_order) }}" name="sort_order" id="sort_order" 
                               class="form-control ">
                       
                    </div>
                    <div class="mb-3">
                        <label for="name">Mô tả</label>
                        <input type="text" value="{{ old('description', $topic->description) }}" name="description" id="description" 
                               class="form-control @error('description') is-invalid @enderror">
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="status">Trạng thái</label>
                        <select name="status" id="status" class="form-control">
                            <option value="2" {{ old('status', $topic->status) == 2 ? 'selected' : '' }}>Chưa xuất bản</option>
                            <option value="1" {{ old('status', $topic->status) == 1 ? 'selected' : '' }}>Xuất bản</option>
                        </select>
                    </div>
                
                    <div class="mb-3">
                        <button type="submit" class="btn btn-success">Lưu</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
    <!-- /.CONTENT -->
</x-layout-backend>