<x-layout-backend>
    <!-- CONTENT -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Cập nhật ảnh</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('admin/')}}">Trang chủ</a></li>
                        <li class="breadcrumb-item active">Library</li>
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
                        <a class="btn btn-sm btn-danger" href="{{ url('admin/image/trash')}}">
                            <i class="fas fa-trash"></i> Thùng rác
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form action="{{route('admin.image.update',['image'=>$image->id])}}" method="post" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                
                    <div class="mb-3">
                        <label for="title">Tên image</label>
                        <input type="text" value="{{ old('title', $image->title) }}" name="title" id="title" 
                               class="form-control @error('title') is-invalid @enderror">
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                
                    <div class="mb-3">
                        <label for="slug">Slug</label>
                        <input type="text" value="{{ old('slug', $image->slug) }}" name="slug" id="slug" 
                               class="form-control ">
                       
                    </div>
                    <div class="mb-3">
                        <label for="image">Hình</label>
                        <input type="file" name="image" id="image" class="form-control @error('image') is-invalid @enderror">
                        @if($image->image)
                            <img src="{{ asset('images/library/' . $image->image) }}" alt="{{ $image->title }}" class="img-thumbnail mt-2" width="200">
                        @endif
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                
                    <div class="mb-3">
                        <label for="status">Trạng thái</label>
                        <select name="status" id="status" class="form-control">
                            <option value="2" {{ old('status', $image->status) == 2 ? 'selected' : '' }}>Chưa xuất bản</option>
                            <option value="1" {{ old('status', $image->status) == 1 ? 'selected' : '' }}>Xuất bản</option>
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