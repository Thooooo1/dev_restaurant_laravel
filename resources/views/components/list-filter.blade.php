<div class="flex flex-col md:flex-row justify-center items-center gap-4 filter-bar bg-white sticky top-0 z-10 shadow-md py-4 px-6">
    <div class="relative w-full md:flex-grow md:max-w-2xl flex items-center mt-6 md:mt-0">
        <select
            class="block px-4 py-2 border border-orange-500 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-orange-500 w-auto md:w-auto">
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
            <i class="fa fa-search mr-2"></i> <span class="hidden md:inline">Tìm</span>
        </button>
    </div>
</div>
<div class="flex flex-col md:flex-row justify-center items-center gap-4 mb-8 filter-bar bg-white sticky top-16 md:top-20 z-10 shadow-md py-4 px-4">
    <span class="md:absolute md:top-0 md:left-10 md:mt-4 mr-4 w-full text-center md:text-left md:w-auto">Tùy chọn lọc theo</span>
    <div class="w-full md:w-auto">
        <label for="brandFilter" class="block text-sm font-medium text-gray-700">Thương hiệu</label>
        <select id="brandFilter" class="block w-full md:w-48 p-2 border border-gray-300 rounded-none sm:rounded-sm">
            <option value="all" {{ $branditem == 'all' ? 'selected' : '' }}>Tất cả</option>
            @foreach ($brands as $item)
                <option value="{{ $item->id }}" {{ $branditem == $item->id ? 'selected' : '' }}>{{ $item->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="w-full md:w-auto">
        <label for="categoryFilter" class="block text-sm font-medium text-gray-700">Danh mục</label>
        <select id="categoryFilter" class="block w-full md:w-48 p-2 border border-gray-300 rounded-none sm:rounded-sm">
            <option value="all" {{ $categoryitem == 'all' ? 'selected' : '' }}>Tất cả</option>
            @foreach ($categories as $item)
                <option value="{{ $item->id }}" {{ $categoryitem == $item->id ? 'selected' : '' }}>
                    {{ $item->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="w-full md:w-auto">
        <label for="sortFilter" class="block text-sm font-medium text-gray-700">Sắp xếp</label>
        <select id="sortFilter" class="block w-full md:w-48 p-2 border border-gray-300 rounded-none sm:rounded-sm">
            <option value="default" {{ request()->get('sort') == 'default' ? 'selected' : '' }}>Mặc định</option>
            <option value="priceAsc" {{ request()->get('sort') == 'priceAsc' ? 'selected' : '' }}>Giá tăng dần</option>
            <option value="priceDesc" {{ request()->get('sort') == 'priceDesc' ? 'selected' : '' }}>Giá giảm dần
            </option>
            <option value="alphabet" {{ request()->get('sort') == 'alphabet' ? 'selected' : '' }}>Theo chữ cái</option>
            <option value="newest" {{ request()->get('sort') == 'newest' ? 'selected' : '' }}>Mới nhất</option>
            <option value="oldest" {{ request()->get('sort') == 'oldest' ? 'selected' : '' }}>Cũ nhất</option>
        </select>
    </div>

    <button id="resetButton" class="bg-[#C25E00] text-white px-4 py-2 rounded-none sm:rounded-sm hover:bg-[#A34E00] mt-4 w-full md:w-auto cursor-pointer">Đặt
        lại</button>

    <div class="md:absolute md:top-0 md:right-0 md:mt-4 md:mr-4 flex justify-center space-x-1 w-full md:w-auto mt-4">
        <!-- Nút List -->
        <a href="{{ url('/thuc-don') }}"
            class="flex items-center px-4 py-2 bg-gray-200 text-gray-600 rounded-none sm:rounded-sm hover:bg-gray-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <span class="ml-2"></span>
        </a>
        <!-- Nút Grid -->
        <a href="{{ url('/danh-sach') }}"
            class="flex items-center px-4 py-2 bg-gray-200 text-gray-600 rounded-none sm:rounded-sm hover:bg-gray-300">
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

<x-list-product :categoryitem="$categoryitem" :branditem="$branditem" />

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
