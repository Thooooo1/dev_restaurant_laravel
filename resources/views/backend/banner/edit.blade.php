<x-layout-backend>
    <!-- CONTENT -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Cập nhật banner</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('admin/')}}">Trang chủ</a></li>
                        <li class="breadcrumb-item active">Banner</li>
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
                        <a class="btn btn-sm btn-danger" href="{{ url('admin/banner/trash')}}">
                            <i class="fas fa-trash"></i> Thùng rác
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form action="{{route('admin.banner.update',['banner'=>$banner->id])}}" method="post" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="name">Tên banner</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $banner->name) }}" class="form-control">
                        @if($errors->has('name'))
                            <div class="text-red-500">{{$errors->first('name')}}</div>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label for="link">Liên kết</label>
                        <input type="text" value="{{old('link',$banner->link)}}" name="link" id="link" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="description">Mô tả:</label>
                        <textarea name="description" id="description" class="form-control">{{ old('description', $banner->description) }}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="position">Vị trí:</label>
                        <select name="position" id="position" class="form-control">
                            <option value="Slideshow" {{ old('position', $banner->position) == 'Slideshow' ? 'selected' : '' }}>Slideshow</option>
                            <option value="Ads" {{ old('position', $banner->position) == 'ads' ? 'selected' : '' }}>Ads</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="image">Hình</label>
                        <input type="file" name="image" id="image" class="form-control">
                        @if($banner->image)
                            <img src="{{ asset('images/banner/' . $banner->image) }}" alt="{{ $banner->name }}" class="img-thumbnail mt-2" width="200">
                        @endif
                    </div>
                    
                    <div class="mb-3">
                        <label for="status">Trạng thái</label>
                        <select name="status" id="status" class="form-control">
                            <option value="2" {{ old('status', $banner->status) == 2 ? 'selected' : '' }}>Chưa xuất bản</option>
                            <option value="1" {{ old('status', $banner->status) == 1 ? 'selected' : '' }}>Xuất bản</option>
                        </select>
                        
                    </div>
                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary">Lưu</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
    <!-- /.CONTENT -->
</x-layout-backend>