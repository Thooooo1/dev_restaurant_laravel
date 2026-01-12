<x-layout-backend>
    <!-- CONTENT -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <strong class="fw-bold h4 text-danger">QUẢN LÝ MÓN ĂN</strong>
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
                    <div class="col-auto d-flex align-items-center">
                        <form method="GET" action="">
                            <div class="row align-items-center">
                                <div>
                                    <span><strong>Lọc theo:</strong></span>
                                </div>
                                <div class="col-auto">
                                    <input type="text" name="search" class="form-control"
                                           placeholder="Tìm kiếm sản phẩm..."
                                           value="">
                                </div>
                                <div class="col-auto">
                                    <select name="category" class="form-control">
                                        <option value="">Danh mục</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <select name="brand" class="form-control">
                                        <option value="">Thương hiệu</option>
                                        @foreach ($brands as $brand)
                                            <option value="{{ $brand->id }}" {{ request('brand') == $brand->id ? 'selected' : '' }}>
                                                {{ $brand->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-search"></i> Tìm kiếm</button>
                                </div>
                            </div>
                        </form>
                    </div>
            
                    <div class="col-auto ml-auto d-flex align-items-center">
                        <a class="btn btn-sm btn-success" href="{{ url('admin/product/create') }}">
                            <i class="fas fa-plus"></i> Thêm
                        </a>
                        <a class="btn btn-sm btn-danger ml-2" href="{{ url('admin/product/trash') }}">
                            <i class="fas fa-trash"></i> Thùng rác
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <table class="table table-bordered table-striped table-hover" id="myTable" class="display">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:30px;">#</th>
                            <th class="text-center" style="width:90px;">Hình</th>
                            <th>Tên sản phẩm</th>
                            <th>Danh mục</th>
                            <th>Thương hiệu</th>
                            {{-- <th>Mô tả</th> --}}
                           
                            <th>Giá gốc</th>
                            <th>Giá kmai</th>
                            <th>Số lượng</th>
                            <th>Kiểu </th>
                            <th class="text-center" style="width:200px;">Chức năng</th>
                            <th class="text-center" style="width:30px;">ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                        <tr onclick="window.location='{{ route('admin.product.reviews', ['product' => $product->id]) }}'" style="cursor: pointer;">
                            <td class="text-center">
                                <input type="checkbox" id="checkId" value="1" name="checkId[]">
                            </td>
                            <td class="text-center">
                                <img src="{{asset('/images/product/'.$product->image)}}" class="img-fluid" alt="{{$product->image}}">
                            </td>
                            <td>{{$product->name}}</td>
                            <td>{{$product->category->name??"None"}}</td>
                            <td>{{$product->brand->name??"None"}}</td>
                            {{-- <td>{{$product->description}}</td> --}}
                            
                            
                            <td>{{ number_format($product->price_buy, 0, ',', '.') }} VND</td>
                            <td>{{ number_format($product->price_sale, 0, ',', '.') }} VND</td>
                            <td>{{$product->qty}}</td>
                            <td class="text-center">
                                @if ($product->new_p == 1)
                                    <span class="badge badge-success">Sản phẩm mới</span>
                                @else
                                    
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($product->status == 1)
                                    <a href="{{route('admin.product.status',['product'=> $product->id])}}" class="btn btn-sm btn-success">
                                        <i class="fas fa-toggle-on"></i>
                                    </a>
                                @else
                                <a href="{{route('admin.product.status',['product'=> $product->id])}}" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-toggle-off"></i>
                                </a>
                                @endif
                                {{-- <a href="{{ route('admin.product.reviews',['product'=> $product->id])}}" class="btn btn-info">Xem đánh giá</a> --}}
                                <a href="{{route('admin.product.show',['product'=> $product->id])}}" class="btn btn-sm btn-info">
                                    <i class="far fa-eye"></i>
                                </a>
                                <a href="{{route('admin.product.edit',['product'=> $product->id])}}" class="btn btn-sm btn-primary">
                                    <i class="far fa-edit"></i>
                                </a>
                                <a href="{{ route('admin.product.delete', ['product' => $product->id]) }}" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </a>
                                
                            </td>
                            <td class="text-center">
                                {{$product->id}}
                            </td>
                        </tr>
                        @endforeach
                        
                    </tbody>
                </table>
                <div class= "my-4">
                    {{$products->appends(request()->query())->links()}}

                </div>
            </div>
        </div>
    </section>
    <!-- /.CONTENT -->
    <script>let table = new DataTable('#myTable');</script>
</x-layout-backend>
