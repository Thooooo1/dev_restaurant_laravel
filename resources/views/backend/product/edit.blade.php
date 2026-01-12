<x-layout-backend>
    <!-- CONTENT -->
    <form action="{{ route('admin.product.update', ['product' => $product->id]) }}" method="post"
        enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <strong class="fw-bold h4 text-danger">CẬP NHẬT MÓN ĂN</strong>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ url('admin/') }}">Trang chủ</a></li>
                            <li class="breadcrumb-item active">Sản phẩm</li>
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
                            <a class="btn btn-sm btn-info" href="{{ url('admin/product/') }}">
                                <i class="fa fa-arrow-left"></i> Về danh sách
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-9">
                            <div class="mb-3">
                                <label for="name">Tên sản phẩm</label>
                                <input type="text" value="{{ old('name', $product->name) }}" name="name"
                                    id="name" class="form-control  @error('name') is-invalid @enderror">
                                @error('name')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="slug">Slug</label>
                                <input name="slug" id="slug" value="{{ old('slug', $product->slug) }}" class="form-control @error('slug') is-invalid @enderror"></input>
                                @error('slug')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                            </div>
                            <div class="mb-3">
                                <label for="description">Mô tả</label>
                                <textarea name="description" id="description" rows="4" class="form-control">{{ old('description', $product->description) }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label for="content" class="block text-sm font-medium text-gray-700">Nội dung</label>
                                <div id="editor" class="w-full h-128 border p-2"> {{ old('content', $product->content) }} </div>
                                <input type="hidden" name="content" id="content" value="" >
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="category_id">Danh mục</label>
                                <select name="category_id" id="category_id" class="form-control">
                                    <option value="">Chọn danh mục</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="brand_id">Thương hiệu</label>
                                <select name="brand_id" id="brand_id" class="form-control">
                                    <option value="">Chọn thương hiệu</option>
                                    @foreach ($brands as $brand)
                                        <option value="{{ $brand->id }}"
                                            {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>
                                            {{ $brand->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="price_buy">Giá</label>
                                <input type="number" value="{{ old('price_buy', $product->price_buy) }}" name="price_buy" id="price_buy" class="form-control @error('price_buy') is-invalid @enderror">
                                @error('price_buy')
                                        <div class="text-danger">{{ $message }}</div>
                                @enderror

                            </div>
                            <div class="mb-3">
                                <label for="price_sale">Giá khuyến mãi</label>
                                <input type="number" value="{{ old('price_sale', $product->price_sale) }}" name="price_sale" id="price_sale" class="form-control @error('price_sale') is-invalid @enderror">
                                @error('price_sale')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror

                            </div>
                            <div class="mb-3">
                                <label for="qty">Số lượng</label>
                                <input type="number" value="{{ old('qty', $product->qty) }}" name="qty" id="qty" class="form-control @error('qty') is-invalid @enderror">
                                @error('qty')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror

                            </div>
                            <div class="mb-3">
                                <label for="image">Hình</label>
                                <input type="file" name="image" id="image" class="form-control @error('image') is-invalid @enderror">
                                @if ($product->image)
                                    <img src="{{ asset('images/product/' . $product->image) }}"
                                        alt="{{ $product->name }}" class="img-thumbnail mt-2" width="200">
                                @endif
                                @error('image')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="new_p" name="new_p"
                                        value="1" {{ old('new_p', $product->new_p) == 1 ? 'checked' : '' }}>
                                    <label class="form-check-label" for="new_p">
                                        Sản phẩm mới
                                    </label>

                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="status">Trạng thái</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="2"
                                        {{ old('status', $product->status) == 2 ? 'selected' : '' }}>Chưa xuất bản
                                    </option>
                                    <option value="1"
                                        {{ old('status', $product->status) == 1 ? 'selected' : '' }}>Xuất bản</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </form>
    <!-- /.CONTENT -->
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
         var oldContent = `{!! old('content', $product->content) !!}`;
         quill.root.innerHTML = oldContent;
     
         // Cập nhật nội dung vào input hidden khi submit
         document.querySelector('form').onsubmit = function() {
             var content = document.querySelector('#content');
             content.value = quill.root.innerHTML;
         };
     </script>

</x-layout-backend>
