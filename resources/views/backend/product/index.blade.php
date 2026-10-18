<x-layout-backend>
    <style>
        table thead th {
            font-size: 14px !important;
            font-weight: 600 !important;
            background: #78b4f1ff !important;
            padding-top: 0.75rem !important;
            padding-bottom: 0.75rem !important;
        }
    </style>
    <!-- Page header -->
    <div class="mb-4">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-800 flex items-center gap-3">
                    <span class="w-8 h-8 bg-gradient-to-br from-orange-400 to-orange-600 rounded-lg flex items-center justify-center text-white">
                        <i class="fa-solid fa-utensils text-sm"></i>
                    </span>
                    Quản lý món ăn
                </h1>
                <p class="text-slate-500 mt-1 text-sm">Danh sách và quản lý các món ăn trong nhà hàng</p>
            </div>
            <div class="flex items-center gap-2">
                <a class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg text-sm font-medium transition-all shadow-lg shadow-green-500/25 hover:from-green-600 hover:to-green-700" href="{{ url('admin/product/create') }}">
                    <i class="fa-solid fa-plus"></i> Thêm
                </a>
                <a class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-lg text-sm font-medium transition-all shadow-lg shadow-red-500/25 hover:from-red-600 hover:to-red-700" href="{{ url('admin/product/trash') }}">
                    <i class="fa-solid fa-trash"></i> Thùng rác
                </a>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-3 mb-4">
        <form method="GET" action="" class="flex flex-wrap gap-3 items-end">
            <div class="flex items-center gap-2">
                <span class="text-sm font-medium text-slate-700">Lọc theo:</span>
            </div>
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-400 focus:ring-2 focus:ring-orange-400/20 transition-all outline-none bg-white text-sm" placeholder="Tìm kiếm sản phẩm..." value="">
            </div>
            <div class="min-w-[150px]">
                <select name="category" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-400 focus:ring-2 focus:ring-orange-400/20 transition-all outline-none bg-white text-sm">
                    <option value="">Danh mục</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[150px]">
                <select name="brand" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-orange-400 focus:ring-2 focus:ring-orange-400/20 transition-all outline-none bg-white text-sm">
                    <option value="">Thương hiệu</option>
                    @foreach ($brands as $brand)
                        <option value="{{ $brand->id }}" {{ request('brand') == $brand->id ? 'selected' : '' }}>
                            {{ $brand->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg text-sm font-medium transition-all shadow-lg shadow-orange-500/25 hover:from-orange-600 hover:to-orange-700">
                    <i class="fa-solid fa-search"></i> Tìm kiếm
                </button>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <div class="table-responsive"><table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 text-center text-sm font-semibold text-slate-500 uppercase tracking-wider" style="width:30px;">#</th>
                        <th class="px-4 py-3 text-center text-sm font-semibold text-slate-500 uppercase tracking-wider" style="width:90px;">Hình</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-500 uppercase tracking-wider">Tên sản phẩm</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-500 uppercase tracking-wider">Danh mục</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-500 uppercase tracking-wider">Thương hiệu</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-500 uppercase tracking-wider">Giá gốc</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-500 uppercase tracking-wider">Giá kmai</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-500 uppercase tracking-wider">Số lượng</th>
                        <th class="px-4 py-3 text-center text-sm font-semibold text-slate-500 uppercase tracking-wider">Kiểu</th>
                        <th class="px-4 py-3 text-center text-sm font-semibold text-slate-500 uppercase tracking-wider" style="width:200px;">Chức năng</th>
                        <th class="px-4 py-3 text-center text-sm font-semibold text-slate-500 uppercase tracking-wider" style="width:30px;">ID</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($products as $product)
                        <tr onclick="window.location='{{ route('admin.product.reviews', ['product' => $product->id]) }}'" style="cursor: pointer;" class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3 text-center">
                                <input type="checkbox" id="checkId" value="1" name="checkId[]" class="rounded border-slate-300 text-orange-500 focus:ring-orange-500">
                            </td>
                            <td class="px-4 py-3 text-center">
                                <img src="{{asset('/images/product/'.$product->image)}}" class="w-12 h-12 object-cover rounded-lg" alt="{{$product->image}}">
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-800 font-medium">{{$product->name}}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600">{{$product->category->name??"None"}}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600">{{$product->brand->name??"None"}}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600">{{ number_format($product->price_buy, 0, ',', '.') }} VND</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-orange-600 font-medium">{{ number_format($product->price_sale, 0, ',', '.') }} VND</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600">{{$product->qty}}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                @if ($product->new_p == 1)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-green-50 text-green-600 rounded-full text-xs font-medium">
                                        Sản phẩm mới
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                @if ($product->status == 1)
                                    <a href="{{route('admin.product.status',['product'=> $product->id])}}" class="inline-flex items-center justify-center w-8 h-8 bg-green-50 text-green-600 rounded-lg text-sm transition-all hover:bg-green-100">
                                        <i class="fa-solid fa-toggle-on"></i>
                                    </a>
                                @else
                                    <a href="{{route('admin.product.status',['product'=> $product->id])}}" class="inline-flex items-center justify-center w-8 h-8 bg-slate-100 text-slate-600 rounded-lg text-sm transition-all hover:bg-slate-200">
                                        <i class="fa-solid fa-toggle-off"></i>
                                    </a>
                                @endif
                                <a href="{{route('admin.product.show',['product'=> $product->id])}}" class="inline-flex items-center justify-center w-8 h-8 bg-blue-50 text-blue-600 rounded-lg text-sm transition-all hover:bg-blue-100 ml-1">
                                    <i class="fa-regular fa-eye"></i>
                                </a>
                                <a href="{{route('admin.product.edit',['product'=> $product->id])}}" class="inline-flex items-center justify-center w-8 h-8 bg-orange-50 text-orange-600 rounded-lg text-sm transition-all hover:bg-orange-100 ml-1">
                                    <i class="fa-regular fa-pen-to-square"></i>
                                </a>
                                <a href="{{ route('admin.product.delete', ['product' => $product->id]) }}" class="inline-flex items-center justify-center w-8 h-8 bg-red-50 text-red-600 rounded-lg text-sm transition-all hover:bg-red-100 ml-1">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center text-sm text-slate-600">
                                {{$product->id}}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3 border-t border-slate-200 flex justify-center">
            {{$products->appends(request()->query())->links()}}
        </div>
    </div>

    <script>let table = new DataTable('#myTable');</script>
</x-layout-backend>
