<x-layout-backend>
    <!-- CONTENT -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <strong class="fw-bold h4 text-danger">CẬP NHẬT THƯƠNG HIỆU</strong>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('admin/')}}">Trang chủ</a></li>
                        <li class="breadcrumb-item active">Thương hiệu</li>
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
                        <a class="btn btn-sm btn-danger" href="{{ url('admin/brand/trash')}}">
                            <i class="fas fa-trash"></i> Thùng rác
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form action="{{route('admin.brand.update',['brand'=>$brand->id])}}" method="post" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="name">Tên thương hiệu</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $brand->name) }}" class="form-control">
                        @if($errors->has('name'))
                            <div class="text-red-500">{{$errors->first('name')}}</div>
                        @endif

                    </div>
                    <div class="mb-3">
                        <label for="slug">Slug</label>
                        <input type="text" value="{{old('slug',$brand->slug)}}" name="slug" id="slug" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="description">Mô tả</label>
                        <textarea name="description" id="description" class="form-control">{{ old('description', $brand->description) }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="sort_order">Sắp xếp</label>
                        <select name="sort_order" id="sort_order" class="form-control">
                            <option value="0">{{ old('sort_order', $brand->sort_order) }}</option>
                            <option value="1">1</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="image">Hình</label>
                        <input type="file" name="image" id="image" class="form-control">
                        @if($brand->image)
                            <img src="{{ asset('images/brand/' . $brand->image) }}" alt="{{ $brand->name }}" class="img-thumbnail mt-2" width="200">
                        @endif
                    </div>
                    <div class="mb-3">
                        <label for="status">Trạng thái</label>
                        <select name="status" id="status" class="form-control">
                            <option value="2" {{ old('status', $brand->status) == 2 ? 'selected' : '' }}>Chưa xuất bản</option>
                            <option value="1" {{ old('status', $brand->status) == 1 ? 'selected' : '' }}>Xuất bản</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <button type="submit"  class="btn btn-success">Lưu</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
    <!-- /.CONTENT -->
</x-layout-backend>