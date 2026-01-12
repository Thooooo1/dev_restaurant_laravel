<div class="container mx-auto px-4 flex flex-col md:flex-row gap-4">
    <div class="w-full md:w-1/4 bg-white rounded-lg shadow p-4">
        <h2 class="text-lg font-bold mb-4">Danh mục</h2>
        <form method="GET" action="{{ url()->current() }}">
            <ul class="space-y-2">
                <li>
                    <label class="flex items-center">
                        <input type="radio" name="category" value="all"
                            {{ $categoryitem === 'all' ? 'checked' : '' }}>
                        <span class="ml-2 text-gray-700">Tất cả</span>
                    </label>
                </li>
                @foreach ($categories as $category)
                    <li>
                        <label class="flex items-center">
                            <input type="radio" name="category" value="{{ $category->id }}"
                                {{ $categoryitem == $category->id ? 'checked' : '' }}>
                            <span class="ml-2 text-gray-700">{{ $category->name }}</span>
                        </label>
                    </li>
                @endforeach
            </ul>

            <h2 class="text-lg font-bold mt-6 mb-4">Thương hiệu</h2>
            <ul class="space-y-2">
                <li>
                    <label class="flex items-center">
                        <input type="radio" name="brand" value="all" {{ $branditem === 'all' ? 'checked' : '' }}>
                        <span class="ml-2 text-gray-700">Tất cả</span>
                    </label>
                </li>
                @foreach ($brands as $brand)
                    <li>
                        <label class="flex items-center">
                            <input type="radio" name="brand" value="{{ $brand->id }}"
                                {{ $branditem == $brand->id ? 'checked' : '' }}>
                            <span class="ml-2 text-gray-700">{{ $brand->name }}</span>
                        </label>
                    </li>
                @endforeach
            </ul>

            <h2 class="text-lg font-bold mt-6 mb-4">Lọc theo giá</h2>
            <div class="flex items-center gap-2">
                <input type="text" name="price_min" value="{{ request()->get('price_min') }}" placeholder="Từ"
                    class="border rounded p-2 w-24">
                <span>-</span>
                <input type="text" name="price_max" value="{{ request()->get('price_max') }}" placeholder="Đến"
                    class="border rounded p-2 w-24">
            </div>


            <button type="submit" class="mt-4 w-full bg-orange-600 text-white py-2 rounded hover:bg-orange-500">
                Áp dụng
            </button>
        </form>
    </div>

    <div class="w-full md:w-3/4">
        <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
            <span class="text-gray-700">{{ $productCount }} Món ăn</span>
            <div class="flex items-center w-full md:w-auto">
                <input type="text" id="searchInput" placeholder="Tìm món ăn"
                    class="w-full md:w-96 px-4 py-2 border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-orange-500">

                <button id="searchButton"
                    class="px-4 py-2 bg-orange-500 text-white border border-orange-500 rounded-r-lg hover:bg-orange-600 focus:ring-2 focus:ring-orange-500">
                    <i class="fa fa-search mr-2"></i> Tìm
                </button>
            </div>
            <div class="flex items-center space-x-2 w-full md:w-auto justify-between md:justify-end">
                <div>
                    <select id="sortFilter" class="block w-full md:w-48 p-2 border border-gray-300 rounded-lg">
                        <option value="default" {{ request()->get('sort') == 'default' ? 'selected' : '' }}>Mặc định
                        </option>
                        <option value="priceAsc" {{ request()->get('sort') == 'priceAsc' ? 'selected' : '' }}>Giá tăng
                            dần</option>
                        <option value="priceDesc" {{ request()->get('sort') == 'priceDesc' ? 'selected' : '' }}>Giá
                            giảm dần
                        </option>
                        <option value="alphabet" {{ request()->get('sort') == 'alphabet' ? 'selected' : '' }}>Theo chữ
                            cái</option>
                        <option value="newest" {{ request()->get('sort') == 'newest' ? 'selected' : '' }}>Mới nhất
                        </option>
                        <option value="oldest" {{ request()->get('sort') == 'oldest' ? 'selected' : '' }}>Cũ nhất
                        </option>
                    </select>
                </div>
                <!-- Nút List -->
                <a href="{{ url('/thuc-don') }}"
                    class="flex items-center px-2 py-2 bg-gray-200 text-gray-600 rounded hover:bg-gray-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <span class="ml-2"></span>
                </a>
                <!-- Nút Grid -->
                <a href="{{ url('/danh-sach') }}"
                    class="flex items-center px-2 py-2 bg-gray-200 text-gray-600 rounded hover:bg-gray-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h7M4 12h7M4 18h7M14 6h6M14 12h6M14 18h6" />
                    </svg>
                    <span class="ml-2"></span>
                </a>
                <!-- Select -->

            </div>
        </div>
        <x-list-product-grid :categoryitem="$categoryitem" :branditem="$branditem" />
    </div>
</div>
<script>
    document.getElementById('searchButton').addEventListener('click', function() {
        let searchValue = document.getElementById('searchInput').value;
        let category = document.querySelector('input[name="category"]:checked')?.value || 'all';
        let brand = document.querySelector('input[name="brand"]:checked')?.value || 'all';
        let sort = document.getElementById('sortFilter').value;
        let priceMin = document.querySelector('input[name="price_min"]').value;
        let priceMax = document.querySelector('input[name="price_max"]').value;

        let url =
            `?search=${searchValue}&category=${category}&brand=${brand}&sort=${sort}&price_min=${priceMin}&price_max=${priceMax}`;
        window.location.href = url;
    });

    document.getElementById('searchInput').addEventListener('keypress', function(event) {
        if (event.key === 'Enter') {
            document.getElementById('searchButton').click();
        }
    });
</script>
