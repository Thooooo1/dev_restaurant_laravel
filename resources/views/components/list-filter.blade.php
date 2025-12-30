<div class="flex justify-center items-center gap-4 filter-bar bg-white sticky top-0 z-10 shadow-md py-4 px-6">
    <div class="breadcrumb flex items-center text-gray-600 text-sm absolute top-0 left-10 mt-4">
        <span class="mr-4">Bạn đang ở đây:</span>
        <a href="{{ url('/') }}" class="hover:text-orange-500">Trang chủ</a>
        <span class="mx-2">></span>
        <span class="font-semibold text-gray-800">Thực đơn</span>
    </div>
    <div class="relative flex-grow max-w-2xl flex items-center">
        <select
            class="block px-4 py-2 border border-orange-500 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
            <option value="all">Loại</option>
            <option value="main">Chính</option>
            <option value="dessert">Tráng miệng</option>
            <option value="drink">Đồ uống</option>
        </select>
        <input type="text" id="searchInput" placeholder="Tìm món ăn"
            class="w-full px-4 py-2 border-t border-b border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500" />

        <button
            class="flex items-center px-4 py-2 text-white bg-orange-500 border border-orange-500 rounded-r-lg hover:bg-orange-600 focus:ring-2 focus:ring-orange-500"
            id="searchButton">
            <i class="fa fa-search mr-2"></i> Tìm
        </button>
    </div>
</div>
<div class="flex justify-center items-center gap-4 mb-8 filter-bar bg-white sticky top-0 z-10 shadow-md py-4">
    <span class="absolute top-0 left-10 mt-4 mr-4">Tùy chọn lọc theo</span>
    <div>
        <label for="brandFilter" class="block text-sm font-medium text-gray-700">Thương hiệu</label>
        <select id="brandFilter" class="block w-48 p-2 border border-gray-300 rounded-lg">
            <option value="all" {{ $branditem == 'all' ? 'selected' : '' }}>Tất cả</option>
            @foreach ($brands as $item)
                <option value="{{ $item->id }}" {{ $branditem == $item->id ? 'selected' : '' }}>{{ $item->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="categoryFilter" class="block text-sm font-medium text-gray-700">Danh mục</label>
        <select id="categoryFilter" class="block w-48 p-2 border border-gray-300 rounded-lg">
            <option value="all" {{ $categoryitem == 'all' ? 'selected' : '' }}>Tất cả</option>
            @foreach ($categories as $item)
                <option value="{{ $item->id }}" {{ $categoryitem == $item->id ? 'selected' : '' }}>
                    {{ $item->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="sortFilter" class="block text-sm font-medium text-gray-700">Sắp xếp</label>
        <select id="sortFilter" class="block w-48 p-2 border border-gray-300 rounded-lg">
            <option value="default" {{ request()->get('sort') == 'default' ? 'selected' : '' }}>Mặc định</option>
            <option value="priceAsc" {{ request()->get('sort') == 'priceAsc' ? 'selected' : '' }}>Giá tăng dần</option>
            <option value="priceDesc" {{ request()->get('sort') == 'priceDesc' ? 'selected' : '' }}>Giá giảm dần
            </option>
            <option value="alphabet" {{ request()->get('sort') == 'alphabet' ? 'selected' : '' }}>Theo chữ cái</option>
            <option value="newest" {{ request()->get('sort') == 'newest' ? 'selected' : '' }}>Mới nhất</option>
            <option value="oldest" {{ request()->get('sort') == 'oldest' ? 'selected' : '' }}>Cũ nhất</option>
        </select>
    </div>

    <button id="resetButton" class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600 mt-4">Đặt
        lại</button>

    <div class="absolute top-0 right-0 mt-4 mr-4 flex space-x-1 ">
        <!-- Nút List -->
        <a href="{{ url('/thuc-don') }}"
            class="flex items-center px-4 py-2 bg-gray-200 text-gray-600 rounded hover:bg-gray-300 mt-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <span class="ml-2"></span>
        </a>
        <!-- Nút Grid -->
        <a href="{{ url('/danh-sach') }}"
            class="flex items-center px-4 py-2 bg-gray-200 text-gray-600 rounded hover:bg-gray-300 mt-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 6h7M4 12h7M4 18h7M14 6h6M14 12h6M14 18h6" />
            </svg>
            <span class="ml-2"></span>
        </a>
    </div>
</div>
<!-- product -->

{{-- <div class="menu-item bg-white rounded-lg shadow-md hover:shadow-lg transition-all p-4 group" data-time="morning"
        data-rate="high">
        <div class="relative">
            <img src="../images/gallery/gallery1.png" alt="Gallery 1"
                class="w-full h-64 object-cover transition-all duration-300 group-hover:opacity-60">
            <div
                class="absolute inset-0 flex justify-center items-center bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-all duration-300">
                <div class="space-x-4">
                    <a href="../SanPham/Chitietsanpham.html" class="text-white text-2xl"><i class="fas fa-eye"></i></a>
                    <a href="#" class="text-white text-2xl"><i class="fas fa-cart-plus"></i></a>
                </div>
            </div>
        </div>
        <div class="p-4">
            <span class="text-red-500 font-semibold">-3%</span>
            <span class="text-orange-500 font-semibold text-lg">100,000đ</span>
            <span class="line-through text-gray-400 ml-2">120,000đ</span>
            <h4 class="font-bold text-lg mt-2 mb-3">Salad rau mùa sốt cam 3</h4>
            <a href="../SanPham/Chitietsanpham.html"
                class="btn mt-2 bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600">Xem
                chi tiết</a>
        </div>
    </div> --}}
<x-list-product :categoryitem="$categoryitem" :branditem="$branditem" />

{{-- <div class="flex justify-center items-center mt-8">
    <button id="prevPage" class="px-4 py-2 bg-gray-300 rounded-lg mr-2">Trước</button>
    <span id="currentPage" class="text-lg font-semibold">1</span>
    <button id="nextPage" class="px-4 py-2 bg-gray-300 rounded-lg ml-2">Sau</button>
</div> --}}
<script>
    document.getElementById('searchButton').addEventListener('click', function() {
        let searchValue = document.getElementById('searchInput').value;
        let category = document.getElementById('categoryFilter').value;
        let brand = document.getElementById('brandFilter').value;
        let sort = document.getElementById('sortFilter').value;
        let url = `?search=${searchValue}&category=${category}&brand=${brand}&sort=${sort}`;
        window.location.href = url;
    });

    document.getElementById('categoryFilter').addEventListener('change', function() {
        let category = this.value;
        let brand = document.getElementById('brandFilter').value;
        let sort = document.getElementById('sortFilter').value;
        let searchValue = document.getElementById('searchInput').value;
        window.location.href = `?search=${searchValue}&category=${category}&brand=${brand}&sort=${sort}`;
    });

    document.getElementById('brandFilter').addEventListener('change', function() {
        let brand = this.value;
        let category = document.getElementById('categoryFilter').value;
        let sort = document.getElementById('sortFilter').value;
        let searchValue = document.getElementById('searchInput').value;
        window.location.href = `?search=${searchValue}&category=${category}&brand=${brand}&sort=${sort}`;
    });

    document.getElementById('sortFilter').addEventListener('change', function() {
        let sort = this.value;
        let brand = document.getElementById('brandFilter').value;
        let category = document.getElementById('categoryFilter').value;
        let searchValue = document.getElementById('searchInput').value;
        window.location.href = `?search=${searchValue}&category=${category}&brand=${brand}&sort=${sort}`;
    });
    document.getElementById('resetButton').addEventListener('click', function() {
        document.getElementById('categoryFilter').value = 'all';
        document.getElementById('brandFilter').value = 'all';
        document.getElementById('sortFilter').value = 'default';
        document.getElementById('searchInput').value = '';

        window.location.href = '?search=&category=all&brand=all&sort=default';
    });
</script>
