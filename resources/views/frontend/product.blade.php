<x-layout-frontend>
    <main>
        <section class="menu-area py-5 bg-gray-50">
            <div class="container mx-auto">
                <!-- Title -->
                <!-- <div class="text-center mb-16">
                    <h2 class="text-4xl font-semibold text-gray-800">Thực Đơn</h2>
                    <p class="text-gray-500 mt-2">Khám phá các món ăn hấp dẫn của chúng tôi</p>
                </div>
         -->
                <!-- timkiem  -->
                
                <!-- filter -->
                <div
                    class="flex flex-col md:flex-row justify-center items-center gap-4 mb-8 filter-bar bg-white sticky top-0 z-10 shadow-md py-4 px-4">
                    <span class="md:absolute md:top-0 md:left-10 md:mt-4 md:mr-4 w-full text-center md:text-left md:w-auto font-medium text-gray-700">Tùy chọn lọc theo </span>
                    <div class="w-full md:w-auto">
                        <label for="brandFilter" class="block text-sm font-medium text-gray-700">Thương hiệu</label>
                        <select id="brandFilter" class="block w-full md:w-48 p-2 border border-gray-300 rounded-lg">
                            <option value="all">Tất cả</option>
                            <option value="morning">Buổi sáng</option>
                            <option value="afternoon">Buổi trưa</option>
                            <option value="evening">Buổi tối</option>
                        </select>
                        
                    </div>
                    <div class="w-full md:w-auto">
                        <label for="categoryFilter" class="block text-sm font-medium text-gray-700">Danh mục</label>
                        <select id="categoryFilter" class="block w-full md:w-48 p-2 border border-gray-300 rounded-lg">
                            <option value="all">Tất cả</option>
                            <option value="rate">Đánh giá cao</option>
                            <option value="des">Phổ biến</option>
                            <option value="high">Độ phức tạp</option>
                        </select>
                    </div>

                    <div class="w-full md:w-auto">
                        <label for="sortFilter" class="block text-sm font-medium text-gray-700">Sắp xếp</label>
                        <select id="sortFilter" class="block w-full md:w-48 p-2 border border-gray-300 rounded-lg">
                            <option value="default">Mặc định</option>
                            <option value="priceAsc">Giá tăng dần</option>
                            <option value="priceDesc">Giá giảm dần</option>
                            <option value="alphabet">Theo chữ cái</option>
                            <option value="newest">Mới nhất</option>
                            <option value="oldest">Cũ nhất</option>
                        </select>
                    </div>
                    <div class="w-full md:w-auto ml-0 md:ml-5 mr-0 md:mr-2">
                        <label class="flex mt-3 items-center justify-center md:justify-start">
                            <input type="checkbox" class="custom-control-input">
                            <div class="ml-2"> Free ship
                            </div>
                        </label>
                    </div>
                    <button id="resetFilter"
                        class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600 mt-4 w-full md:w-auto">Đặt lại</button>
                    <div class="md:absolute md:top-0 md:right-0 md:mt-4 md:mr-4 flex justify-center space-x-1 w-full md:w-auto mt-4">
                        <!-- Nút List -->
                        <a href="{{ url('/thuc-don')}}"
                            class="flex items-center px-4 py-2 bg-gray-200 text-gray-600 rounded hover:bg-gray-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            <span class="ml-2"></span>
                        </a>
                        <!-- Nút Grid -->
                        <a href="{{ url('/danh-sach')}}"
                            class="flex items-center px-4 py-2 bg-gray-200 text-gray-600 rounded hover:bg-gray-300">
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
                <div id="menuContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-8">
                    <div class="menu-item bg-white rounded-lg shadow-md hover:shadow-lg transition-all p-4 group"
                        data-time="morning" data-rate="high">
                        <div class="relative">
                            <img src="../images/gallery/gallery1.png" alt="Gallery 1"
                                class="w-full h-64 object-cover transition-all duration-300 group-hover:opacity-60">
                            <div
                                class="absolute inset-0 flex justify-center items-center bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                <div class="space-x-4">
                                    <a href="../SanPham/Chitietsanpham.html" class="text-white text-2xl"><i
                                            class="fas fa-eye"></i></a>
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
                    </div>

                    <div class="menu-item bg-white rounded-lg shadow-md hover:shadow-lg transition-all p-4 group"
                        data-time="morning" data-rate="high">
                        <div class="relative">
                            <img src="../images/gallery/gallery2.png" alt="Gallery 1"
                                class="w-full h-64 object-cover transition-all duration-300 group-hover:opacity-60">
                            <div
                                class="absolute inset-0 flex justify-center items-center bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                <div class="space-x-4">
                                    <a href="../SanPham/Chitietsanpham.html" class="text-white text-2xl"><i
                                            class="fas fa-eye"></i></a>
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
                    </div>
                    
                    <div class="menu-item bg-white rounded-lg shadow-md hover:shadow-lg transition-all p-4 group"
                        data-time="morning" data-rate="high">
                        <div class="relative">
                            <img src="../images/gallery/gallery3.png" alt="Gallery 1"
                                class="w-full h-64 object-cover transition-all duration-300 group-hover:opacity-60">
                            <div
                                class="absolute inset-0 flex justify-center items-center bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                <div class="space-x-4">
                                    <a href="../SanPham/Chitietsanpham.html" class="text-white text-2xl"><i
                                            class="fas fa-eye"></i></a>
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
                    </div>

                    <div class="menu-item bg-white rounded-lg shadow-md hover:shadow-lg transition-all p-4 group"
                        data-time="morning" data-rate="high">
                        <div class="relative">
                            <img src="../images/gallery/gallery4.png" alt="Gallery 1"
                                class="w-full h-64 object-cover transition-all duration-300 group-hover:opacity-60">
                            <div
                                class="absolute inset-0 flex justify-center items-center bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                <div class="space-x-4">
                                    <a href="../SanPham/Chitietsanpham.html" class="text-white text-2xl"><i
                                            class="fas fa-eye"></i></a>
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
                    </div>

                    <div class="menu-item bg-white rounded-lg shadow-md hover:shadow-lg transition-all p-4 group"
                        data-time="morning" data-rate="high">
                        <div class="relative">
                            <img src="../images/gallery/gallery2.png" alt="Gallery 1"
                                class="w-full h-64 object-cover transition-all duration-300 group-hover:opacity-60">
                            <div
                                class="absolute inset-0 flex justify-center items-center bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                <div class="space-x-4">
                                    <a href="../SanPham/Chitietsanpham.html" class="text-white text-2xl"><i
                                            class="fas fa-eye"></i></a>
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
                    </div>

                    <div class="menu-item bg-white rounded-lg shadow-md hover:shadow-lg transition-all p-4 group"
                        data-time="morning" data-rate="high">
                        <div class="relative">
                            <img src="../images/gallery/gallery2.png" alt="Gallery 1"
                                class="w-full h-64 object-cover transition-all duration-300 group-hover:opacity-60">
                            <div
                                class="absolute inset-0 flex justify-center items-center bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                <div class="space-x-4">
                                    <a href="../SanPham/Chitietsanpham.html" class="text-white text-2xl"><i
                                            class="fas fa-eye"></i></a>
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
                    </div>


                </div>
                <div class="flex justify-center items-center mt-8">
                    <button id="prevPage" class="px-4 py-2 bg-gray-300 rounded-lg mr-2">Trước</button>
                    <span id="currentPage" class="text-lg font-semibold">1</span>
                    <button id="nextPage" class="px-4 py-2 bg-gray-300 rounded-lg ml-2">Sau</button>
                </div> --}}
                <x-list-filter/>
            </div>
        </section>
    </main>
</x-layout-frontend>