<x-layout-backend>
    <section class="content-header">
        <h1>Thùng rác</h1>
        <a href="{{route('admin.product.index')}}" class="btn btn-primary py-2 mt-2">Quay lại danh sách món ăn</a>
    </section>
    <section class="content">
        <div class="card">
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:30px;">#</th>
                            <th class="text-center" style="width:90px;">Hình</th>
                            <th>Tên món ăn</th>
                            <th>Mô tả</th>
                            <th>Danh mục</th>
                            <th>Thương hiệu</th>
                            <th class="text-center" style="width:200px;">Chức năng</th>
                            <th class="text-center" style="width:30px;">ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" id="checkId" value="1" name="checkId[]">
                                </td>
                                <td class="text-center">
                                    <img src="{{ asset('/images/product/' . $product->image) }}" class="img-fluid"
                                        alt="{{ $product->image }}">
                                </td>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->description }}</td>
                                <td>{{ $product->category_id }}</td>
                                <td>{{ $product->brand_id }}</td>
                                <td class="">
                                    <a href="{{ route('admin.product.restore', ['product' => $product->id]) }}" class="bg-blue-600 hover:bg-blue-700  rounded px-4 py-2 inline-flex items-center space-x-2">
                                        <i class="fa-solid fa-arrow-rotate-left"></i>
                                        
                                    </a>
                                    <form action="{{ route('admin.product.destroy', ['product' => $product->id]) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-600 hover:bg-red-700  rounded px-4 py-2 inline-flex items-center space-x-2">
                                            <i class="fa-solid fa-trash"></i>
                                            
                                        </button>
                                    </form>
                                </td>
                                
                                <td>{{ $product->id }}</td>
                        
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</x-layout-backend>