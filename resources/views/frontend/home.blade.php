<x-layout-frontend>

    <x-slot:footer>
        <!-- scrip pro -->
        <script type="text/javascript">
            $(document).ready(function () {
                $('.slick-slider').slick({
                    infinite: true,
                    slidesToShow: 5,
                    slidesToScroll: 1,
                    vertical: false,
                    arrows: true,
                    dots: true,
                    prevArrow: '<button type="button" class="slick-prev p-2 text-white bg-gray-500 rounded-full"><i class="fas fa-chevron-left"></i></button>',
                    nextArrow: '<button type="button" class="slick-next p-2 text-white bg-gray-500 rounded-full"><i class="fas fa-chevron-right"></i></button>',
                    responsive: [{
                        breakpoint: 1024,
                        settings: {
                            slidesToShow: 2,
                        }
                    },
                    {
                        breakpoint: 600,
                        settings: {
                            slidesToShow: 1,
                        }
                    }
                    ]
                });
            });
        </script>
        <!-- script pronew -->
        <script type="text/javascript">
            $(document).ready(function () {
                $('.slick-slider').slick({
                    infinite: true,
                    slidesToShow: 5,
                    slidesToScroll: 1,
                    arrows: true,
                    dots: true,
                    responsive: [{
                        breakpoint: 1024,
                        settings: {
                            slidesToShow: 2,
                        }
                    },
                    {
                        breakpoint: 600,
                        settings: {
                            slidesToShow: 1,
                        }
                    }
                    ]
                });
            });
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const tabButtons = document.querySelectorAll('.tab-btn');
                const tabContents = document.querySelectorAll('.tab-content');

                function setDefaultTab() {
                    tabButtons.forEach(btn => {
                        if (btn.dataset.tab == 2) {
                            btn.classList.add('bg-orange-500', 'text-white');
                            btn.classList.remove('bg-gray-100', 'hover:bg-gray-300');
                        } else {
                            btn.classList.add('bg-gray-100', 'hover:bg-gray-300');
                            btn.classList.remove('bg-orange-500', 'text-white');
                        }
                    });

                    tabContents.forEach(content => {
                        if (content.id == 2) {
                            content.classList.remove('hidden');
                        } else {
                            content.classList.add('hidden');
                        }
                    });
                }

                setDefaultTab();

                tabButtons.forEach(tabBtn => {
                    tabBtn.addEventListener('click', function () {
                        tabButtons.forEach(btn => {
                            btn.classList.remove('bg-orange-500', 'text-white');
                            btn.classList.add('bg-gray-100', 'hover:bg-gray-300');
                        });

                        tabContents.forEach(content => {
                            content.classList.add('hidden');
                        });

                        tabBtn.classList.remove('bg-gray-100', 'hover:bg-gray-300');
                        tabBtn.classList.add('bg-orange-500', 'text-white');

                        const tabContent = document.getElementById(tabBtn.dataset.tab);
                        if (tabContent) {
                            tabContent.classList.remove('hidden');
                        }
                    });
                });
            });
        </script>
    </x-slot:footer>
    <div>
        <section class="section_slider">
            @foreach ($banners as $item)
                {{-- <img src="../images/gallery/slogan.png" alt=""> --}}
                 <img src="{{ asset('/images/banner/' . $item->image) }}" alt="{{$item->name }}"/>
            @endforeach           
        </section>
        <section class="section_chinhsach py-4">
            <div class="container mx-auto">
                <div class="flex justify-between items-start">
                    <div class="item mx-auto">
                        <div class="flex items-center gap-3">
                            <div class="icon">
                                <div class="rounded-full p-4">
                                    <img style="width: 50px;height: 50px;" src="../images/2.png" alt="cs1">
                                </div>
                            </div>
                            <div class="text">
                                <h4 class="font-bold">Miễn phí vận chuyển</h4>
                                <p>Cho đơn hàng từ 500k</p>
                            </div>
                        </div>
                    </div>
                    <div class="item mx-auto">
                        <div class="flex items-center gap-3">
                            <div class="icon">
                                <div class="rounded-full p-4">
                                    <img style="width: 50px;height: 50px;" src="../images/1.png" alt="cs1">
                                </div>
                            </div>
                            <div class="text">
                                <h4 class="font-bold">Bảo hiểm món ăn</h4>
                                <p>Đảm chất lượng đồ ăn</p>
                            </div>
                        </div>
                    </div>
                    <div class="item mx-auto">
                        <div class="flex items-center gap-3">
                            <div class="icon">
                                <div class="rounded-full p-4">
                                    <img style="width: 50px;height: 50px;" src="../images/cod.png" alt="cs1">
                                </div>
                            </div>
                            <div class="text">
                                <h4 class="font-bold">Thanh toán COD</h4>
                                <p>Hoặc thanh toán quét mã QR</p>
                            </div>
                        </div>
                    </div>
                    <div class="item mx-auto">
                        <div class="flex items-center gap-3">
                            <div class="icon">
                                <div class="rounded-full p-4">
                                    <img style="width: 50px;height: 50px;" src="../images/3.png" alt="cs1">
                                </div>
                            </div>
                            <div class="text">
                                <h4 class="font-bold">Bảo mật thông tin</h4>
                                <p>Đảm bảo thông tin khách hàng</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section class="section_search py-4">
            <div class="container mx-auto">
                <div class=" flex justify-center items-center gap-6">
                    <div class="basis-5/12 mx-auto ml-10">
                        <h3 class="text-3x1 uppercase mb-2 font-bold ">Ăn gì hôm nay?</h3>
                        <form action="{{ route('site.product.gridview') }}" method="GET" id="searchForm">
                            <input type="search" name="search" id="searchInput"
                                class="bg-slate-200 hover:bg-gray-300 rounded-xl p-3 w-full" placeholder="Tìm kiếm" />
                            {{-- <button type="submit" class="mt-2 bg-blue-500 text-white py-2 px-4 rounded-md">Tìm
                                kiếm</button> --}}
                        </form>

                    </div>
                    <div class="basic-7/12 mx-auto">
                        <h3 class="font-bold">Từ khóa nổi bật hôm nay</h3>
                        <x-key-word-hot />
                    </div>
                </div>
            </div>
        </section>

        <section class="section_about bg-white py-4 mr-4">
            <div class="container mx-auto flex flex-wrap items-center">
                <!-- Left Content -->
                <div class="w-full lg:w-6/12 px-6">
                    <p class="text-orange-500 font-semibold text-lg mb-2">CIVILIZE Restaurant</p>
                    <h2 style="font-size: 35px;font-weight: bold;" class="font-bold">
                        Thưởng thức ẩm thực đỉnh cao tại nhà hàng của chúng tôi!
                    </h2>
                    <p class="text-gray-600 mb-8 leading-relaxed">
                        Luôn đặt cái tâm trong công việc, chúng tôi luôn sẵn sàng phục vụ các bạn. Tiêu chí nhà hàng
                        luôn được chúng tôi để tâm
                    </p>
                    <div class="grid grid-cols-2 gap-6">
                        <div class="flex items-start">
                            <i class="fa-solid fa-user text-orange-500 text-3xl mr-4"></i>
                            <p class="text-gray-600">Đầu bếp chuyên nghiệp.</p>
                        </div>
                        <div class="flex items-start">
                            <i class="fa-solid fa-utensils text-orange-500 text-3xl mr-4"></i>
                            <p class="text-gray-600">An toàn vệ sinh thực phẩm.</p>
                        </div>
                        <div class="flex items-start">
                            <i class="fa-solid fa-utensils text-orange-500 text-3xl mr-4"></i>
                            <p class="text-gray-600">Thực đơn phong phú.</p>
                        </div>
                        <div class="flex items-start">
                            <i class="fa-solid fa-user text-orange-500 text-3xl mr-4"></i>
                            <p class="text-gray-600">Khách hàng là thượng đế.</p>
                        </div>
                    </div>
                </div>
                <!-- Right Image -->
                <div class="w-full lg:w-6/12 px-6">
                    <img src="../images/gallery/about.png" alt="Chef" class="rounded-lg shadow-lg w-full">
                </div>
            </div>
        </section>
        {{-- <section class="gallery-area">
            <div class="gallery-top bg-cover bg-center py-20 "
                style="background-image: url('assets/img/gallery/section_bg01.png');">
                <div class="container mx-auto text-center">
                    <div class="mb-10">
                        <p class="text-orange-500 font-semibold text-lg mb-4">Thực đơn</p>
                        <h2 class="text-3xl font-bold">
                            Một số món ăn tiêu biểu theo thực đơn của nhà hàng
                        </h2>
                    </div>
                    <div>
                        <div class="flex justify-center space-x-4">
                            <button class="tab-btn text-sm py-2 px-4 bg-orange-500 text-white rounded-lg"
                                data-tab="home">Nổi bật</button>
                            <button class="tab-btn text-sm py-2 px-4 bg-gray-100 hover:bg-gray-300 rounded-lg"
                                data-tab="sang">Sáng</button>
                            <button class="tab-btn text-sm py-2 px-4 bg-gray-100 hover:bg-gray-300 rounded-lg"
                                data-tab="trua">Trưa</button>
                            <button class="tab-btn text-sm py-2 px-4 bg-gray-100 hover:bg-gray-300 rounded-lg"
                                data-tab="toi">Tối</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="container mx-auto ">
                <div id="home" class="tab-content grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div class="gallery-box relative bg-white rounded-lg shadow-md overflow-hidden group w-full">

                        <div class="relative">
                            <img src="../images/gallery/gallery3.png" alt="Gallery 1"
                                class="w-full h-64 object-cover transition-all duration-300 group-hover:opacity-60">
                            <div
                                class="absolute inset-0 flex justify-center items-center bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                <div class="space-x-4">
                                    <a href="{{ url('/chi-tiet-san-pham/slug')}}" class="text-white text-2xl">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="#" class="text-white text-2xl">
                                        <i class="fas fa-cart-plus"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="p-4">
                            <span class="text-red-500 font-semibold">-3%</span>
                            <span class="text-orange-500 font-semibold text-lg">68,000đ</span>
                            <span class="line-through text-gray-400 ml-2">70,000đ</span>
                            <h4 class="font-bold text-lg mt-2 mb-3">Salad rau mùa sốt cam 1</h4>
                            <a href="../SanPham/Chitietsanpham.html"
                                class="btn mt-2 bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600">Xem
                                chi tiết</a>
                        </div>
                    </div>
                    <div class="gallery-box relative bg-white rounded-lg shadow-md overflow-hidden group w-full">

                        <div class="relative">
                            <img src="../images/gallery/gallery4.png" alt="Gallery 1"
                                class="w-full h-64 object-cover transition-all duration-300 group-hover:opacity-60">
                            <div
                                class="absolute inset-0 flex justify-center items-center bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                <div class="space-x-4">
                                    <a href="../SanPham/Chitietsanpham.html" class="text-white text-2xl">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="#" class="text-white text-2xl">
                                        <i class="fas fa-cart-plus"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="p-4">
                            <span class="text-red-500 font-semibold">-3%</span>
                            <span class="text-orange-500 font-semibold text-lg">68,000đ</span>
                            <span class="line-through text-gray-400 ml-2">70,000đ</span>
                            <h4 class="font-bold text-lg mt-2 mb-3">Salad rau mùa sốt cam 2</h4>
                            <a href="../SanPham/Chitietsanpham.html"
                                class="btn mt-2 bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600">Xem
                                chi tiết</a>
                        </div>
                    </div>
                    <div class="gallery-box relative bg-white rounded-lg shadow-md overflow-hidden group w-full">

                        <div class="relative">
                            <img src="../images/gallery/gallery2.png" alt="Gallery 1"
                                class="w-full h-64 object-cover transition-all duration-300 group-hover:opacity-60">
                            <div
                                class="absolute inset-0 flex justify-center items-center bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                <div class="space-x-4">
                                    <a href="../SanPham/Chitietsanpham.html" class="text-white text-2xl">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="#" class="text-white text-2xl">
                                        <i class="fas fa-cart-plus"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="p-4">
                            <span class="text-red-500 font-semibold">-3%</span>
                            <span class="text-orange-500 font-semibold text-lg">68,000đ</span>
                            <span class="line-through text-gray-400 ml-2">70,000đ</span>
                            <h4 class="font-bold text-lg mt-2 mb-3">Salad rau mùa sốt cam 3</h4>
                            <a href="../SanPham/Chitietsanpham.html"
                                class="btn mt-2 bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600">Xem
                                chi tiết</a>
                        </div>
                    </div>
                    <div class="gallery-box relative bg-white rounded-lg shadow-md overflow-hidden group w-full">

                        <div class="relative">
                            <img src="../images/gallery/gallery1.png" alt="Gallery 1"
                                class="w-full h-64 object-cover transition-all duration-300 group-hover:opacity-60">
                            <div
                                class="absolute inset-0 flex justify-center items-center bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                <div class="space-x-4">
                                    <a href="../SanPham/Chitietsanpham.html" class="text-white text-2xl">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="#" class="text-white text-2xl">
                                        <i class="fas fa-cart-plus"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="p-4">
                            <span class="text-red-500 font-semibold">-3%</span>
                            <span class="text-orange-500 font-semibold text-lg">68,000đ</span>
                            <span class="line-through text-gray-400 ml-2">70,000đ</span>
                            <h4 class="font-bold text-lg mt-2 mb-3">Salad rau mùa sốt cam 4</h4>
                            <a href="../SanPham/Chitietsanpham.html"
                                class="btn mt-2 bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600">Xem
                                chi tiết</a>
                        </div>
                    </div>
                    <div class="gallery-box relative bg-white rounded-lg shadow-md overflow-hidden group w-full">

                        <div class="relative">
                            <img src="../images/gallery/gallery2.png" alt="Gallery 1"
                                class="w-full h-64 object-cover transition-all duration-300 group-hover:opacity-60">
                            <div
                                class="absolute inset-0 flex justify-center items-center bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                <div class="space-x-4">
                                    <a href="../SanPham/Chitietsanpham.html" class="text-white text-2xl">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="#" class="text-white text-2xl">
                                        <i class="fas fa-cart-plus"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="p-4">
                            <span class="text-red-500 font-semibold">-3%</span>
                            <span class="text-orange-500 font-semibold text-lg">68,000đ</span>
                            <span class="line-through text-gray-400 ml-2">70,000đ</span>
                            <h4 class="font-bold text-lg mt-2 mb-3">Salad rau mùa sốt cam 5</h4>
                            <a href="../SanPham/Chitietsanpham.html"
                                class="btn mt-2 bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600">Xem
                                chi tiết</a>
                        </div>
                    </div>
                </div>
                <div id="trua" class="tab-content hidden grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div class="gallery-box relative bg-white rounded-lg shadow-md overflow-hidden group w-full">

                        <div class="relative">
                            <img src="../images/gallery/gallery3.png" alt="Gallery 1"
                                class="w-full h-64 object-cover transition-all duration-300 group-hover:opacity-60">
                            <div
                                class="absolute inset-0 flex justify-center items-center bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                <div class="space-x-4">
                                    <a href="../SanPham/Chitietsanpham.html" class="text-white text-2xl">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="#" class="text-white text-2xl">
                                        <i class="fas fa-cart-plus"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="p-4">
                            <span class="text-red-500 font-semibold">-3%</span>
                            <span class="text-orange-500 font-semibold text-lg">68,000đ</span>
                            <span class="line-through text-gray-400 ml-2">70,000đ</span>
                            <h4 class="font-bold text-lg mt-2 mb-3">Salad rau mùa sốt cam 3</h4>
                            <a href="../SanPham/Chitietsanpham.html"
                                class="btn mt-2 bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600">Xem
                                chi tiết</a>
                        </div>
                    </div>
                    <div class="gallery-box relative bg-white rounded-lg shadow-md overflow-hidden group w-full">

                        <div class="relative">
                            <img src="../images/gallery/gallery4.png" alt="Gallery 1"
                                class="w-full h-64 object-cover transition-all duration-300 group-hover:opacity-60">
                            <div
                                class="absolute inset-0 flex justify-center items-center bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                <div class="space-x-4">
                                    <a href="../SanPham/Chitietsanpham.html" class="text-white text-2xl">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="#" class="text-white text-2xl">
                                        <i class="fas fa-cart-plus"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="p-4">
                            <span class="text-red-500 font-semibold">-3%</span>
                            <span class="text-orange-500 font-semibold text-lg">68,000đ</span>
                            <span class="line-through text-gray-400 ml-2">70,000đ</span>
                            <h4 class="font-bold text-lg mt-2 mb-3">Salad rau mùa sốt cam 3</h4>
                            <a href="../SanPham/Chitietsanpham.html"
                                class="btn mt-2 bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600">Xem
                                chi tiết</a>
                        </div>
                    </div>
                    <div class="gallery-box relative bg-white rounded-lg shadow-md overflow-hidden group w-full">

                        <div class="relative">
                            <img src="../images/gallery/gallery1.png" alt="Gallery 1"
                                class="w-full h-64 object-cover transition-all duration-300 group-hover:opacity-60">
                            <div
                                class="absolute inset-0 flex justify-center items-center bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                <div class="space-x-4">
                                    <a href="../SanPham/Chitietsanpham.html" class="text-white text-2xl">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="#" class="text-white text-2xl">
                                        <i class="fas fa-cart-plus"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="p-4">
                            <span class="text-red-500 font-semibold">-3%</span>
                            <span class="text-orange-500 font-semibold text-lg">68,000đ</span>
                            <span class="line-through text-gray-400 ml-2">70,000đ</span>
                            <h4 class="font-bold text-lg mt-2 mb-3">Salad rau mùa sốt cam 3</h4>
                            <a href="../SanPham/Chitietsanpham.html"
                                class="btn mt-2 bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600">Xem
                                chi tiết</a>
                        </div>
                    </div>
                    <div class="gallery-box relative bg-white rounded-lg shadow-md overflow-hidden group w-full">

                        <div class="relative">
                            <img src="../images/gallery/gallery2.png" alt="Gallery 1"
                                class="w-full h-64 object-cover transition-all duration-300 group-hover:opacity-60">
                            <div
                                class="absolute inset-0 flex justify-center items-center bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                <div class="space-x-4">
                                    <a href="../SanPham/Chitietsanpham.html" class="text-white text-2xl">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="#" class="text-white text-2xl">
                                        <i class="fas fa-cart-plus"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="p-4">
                            <span class="text-red-500 font-semibold">-3%</span>
                            <span class="text-orange-500 font-semibold text-lg">68,000đ</span>
                            <span class="line-through text-gray-400 ml-2">70,000đ</span>
                            <h4 class="font-bold text-lg mt-2 mb-3">Salad rau mùa sốt cam 3</h4>
                            <a href="../SanPham/Chitietsanpham.html"
                                class="btn mt-2 bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600">Xem
                                chi tiết</a>
                        </div>
                    </div>
                    <div class="gallery-box relative bg-white rounded-lg shadow-md overflow-hidden group w-full">

                        <div class="relative">
                            <img src="../images/gallery/gallery1.png" alt="Gallery 1"
                                class="w-full h-64 object-cover transition-all duration-300 group-hover:opacity-60">
                            <div
                                class="absolute inset-0 flex justify-center items-center bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                <div class="space-x-4">
                                    <a href="../SanPham/Chitietsanpham.html" class="text-white text-2xl">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="#" class="text-white text-2xl">
                                        <i class="fas fa-cart-plus"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="p-4">
                            <span class="text-red-500 font-semibold">-3%</span>
                            <span class="text-orange-500 font-semibold text-lg">68,000đ</span>
                            <span class="line-through text-gray-400 ml-2">70,000đ</span>
                            <h4 class="font-bold text-lg mt-2 mb-3">Salad rau mùa sốt cam 3</h4>
                            <a href="../SanPham/Chitietsanpham.html"
                                class="btn mt-2 bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600">Xem
                                chi tiết</a>
                        </div>
                    </div>
                </div>
                <div id="sang" class="tab-content hidden grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div class="gallery-box relative bg-white rounded-lg shadow-md overflow-hidden group w-full">

                        <div class="relative">
                            <img src="../images/gallery/gallery1.png" alt="Gallery 1"
                                class="w-full h-64 object-cover transition-all duration-300 group-hover:opacity-60">
                            <div
                                class="absolute inset-0 flex justify-center items-center bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                <div class="space-x-4">
                                    <a href="../SanPham/Chitietsanpham.html" class="text-white text-2xl">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="#" class="text-white text-2xl">
                                        <i class="fas fa-cart-plus"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="p-4">
                            <span class="text-red-500 font-semibold">-3%</span>
                            <span class="text-orange-500 font-semibold text-lg">68,000đ</span>
                            <span class="line-through text-gray-400 ml-2">70,000đ</span>
                            <h4 class="font-bold text-lg mt-2 mb-3">Salad rau mùa sốt cam 3</h4>
                            <a href="../SanPham/Chitietsanpham.html"
                                class="btn mt-2 bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600">Xem
                                chi tiết</a>
                        </div>
                    </div>
                    <div class="gallery-box relative bg-white rounded-lg shadow-md overflow-hidden group w-full">

                        <div class="relative">
                            <img src="../images/gallery/gallery2.png" alt="Gallery 1"
                                class="w-full h-64 object-cover transition-all duration-300 group-hover:opacity-60">
                            <div
                                class="absolute inset-0 flex justify-center items-center bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                <div class="space-x-4">
                                    <a href="../SanPham/Chitietsanpham.html" class="text-white text-2xl">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="#" class="text-white text-2xl">
                                        <i class="fas fa-cart-plus"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="p-4">
                            <span class="text-red-500 font-semibold">-3%</span>
                            <span class="text-orange-500 font-semibold text-lg">68,000đ</span>
                            <span class="line-through text-gray-400 ml-2">70,000đ</span>
                            <h4 class="font-bold text-lg mt-2 mb-3">Salad rau mùa sốt cam 3</h4>
                            <a href="../SanPham/Chitietsanpham.html"
                                class="btn mt-2 bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600">Xem
                                chi tiết</a>
                        </div>
                    </div>
                    <div class="gallery-box relative bg-white rounded-lg shadow-md overflow-hidden group w-full">

                        <div class="relative">
                            <img src="../images/gallery/gallery4.png" alt="Gallery 1"
                                class="w-full h-64 object-cover transition-all duration-300 group-hover:opacity-60">
                            <div
                                class="absolute inset-0 flex justify-center items-center bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                <div class="space-x-4">
                                    <a href="../SanPham/Chitietsanpham.html" class="text-white text-2xl">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="#" class="text-white text-2xl">
                                        <i class="fas fa-cart-plus"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="p-4">
                            <span class="text-red-500 font-semibold">-3%</span>
                            <span class="text-orange-500 font-semibold text-lg">68,000đ</span>
                            <span class="line-through text-gray-400 ml-2">70,000đ</span>
                            <h4 class="font-bold text-lg mt-2 mb-3">Salad rau mùa sốt cam 3</h4>
                            <a href="../SanPham/Chitietsanpham.html"
                                class="btn mt-2 bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600">Xem
                                chi tiết</a>
                        </div>
                    </div>
                    <div class="gallery-box relative bg-white rounded-lg shadow-md overflow-hidden group w-full">

                        <div class="relative">
                            <img src="../images/gallery/gallery3.png" alt="Gallery 1"
                                class="w-full h-64 object-cover transition-all duration-300 group-hover:opacity-60">
                            <div
                                class="absolute inset-0 flex justify-center items-center bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                <div class="space-x-4">
                                    <a href="../SanPham/Chitietsanpham.html" class="text-white text-2xl">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="#" class="text-white text-2xl">
                                        <i class="fas fa-cart-plus"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="p-4">
                            <span class="text-red-500 font-semibold">-3%</span>
                            <span class="text-orange-500 font-semibold text-lg">68,000đ</span>
                            <span class="line-through text-gray-400 ml-2">70,000đ</span>
                            <h4 class="font-bold text-lg mt-2 mb-3">Salad rau mùa sốt cam 3</h4>
                            <a href="../SanPham/Chitietsanpham.html"
                                class="btn mt-2 bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600">Xem
                                chi tiết</a>
                        </div>
                    </div>
                    <div class="gallery-box relative bg-white rounded-lg shadow-md overflow-hidden group w-full">

                        <div class="relative">
                            <img src="../images/gallery/gallery1.png" alt="Gallery 1"
                                class="w-full h-64 object-cover transition-all duration-300 group-hover:opacity-60">
                            <div
                                class="absolute inset-0 flex justify-center items-center bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                <div class="space-x-4">
                                    <a href="../SanPham/Chitietsanpham.html" class="text-white text-2xl">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="#" class="text-white text-2xl">
                                        <i class="fas fa-cart-plus"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="p-4">
                            <span class="text-red-500 font-semibold">-3%</span>
                            <span class="text-orange-500 font-semibold text-lg">68,000đ</span>
                            <span class="line-through text-gray-400 ml-2">70,000đ</span>
                            <h4 class="font-bold text-lg mt-2 mb-3">Salad rau mùa sốt cam 3</h4>
                            <a href="../SanPham/Chitietsanpham.html"
                                class="btn mt-2 bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600">Xem
                                chi tiết</a>
                        </div>
                    </div>
                </div>
                <div id="toi" class="tab-content hidden grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div class="gallery-box relative bg-white rounded-lg shadow-md overflow-hidden group w-full">

                        <div class="relative">
                            <img src="../images/gallery/gallery3.png" alt="Gallery 1"
                                class="w-full h-64 object-cover transition-all duration-300 group-hover:opacity-60">
                            <div
                                class="absolute inset-0 flex justify-center items-center bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                <div class="space-x-4">
                                    <a href="../SanPham/Chitietsanpham.html" class="text-white text-2xl">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="#" class="text-white text-2xl">
                                        <i class="fas fa-cart-plus"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="p-4">
                            <span class="text-red-500 font-semibold">-3%</span>
                            <span class="text-orange-500 font-semibold text-lg">68,000đ</span>
                            <span class="line-through text-gray-400 ml-2">70,000đ</span>
                            <h4 class="font-bold text-lg mt-2 mb-3">Salad rau mùa sốt cam 3</h4>
                            <a href="../SanPham/Chitietsanpham.html"
                                class="btn mt-2 bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600">Xem
                                chi tiết</a>
                        </div>
                    </div>
                    <div class="gallery-box relative bg-white rounded-lg shadow-md overflow-hidden group w-full">

                        <div class="relative">
                            <img src="../images/gallery/gallery4.png" alt="Gallery 1"
                                class="w-full h-64 object-cover transition-all duration-300 group-hover:opacity-60">
                            <div
                                class="absolute inset-0 flex justify-center items-center bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                <div class="space-x-4">
                                    <a href="../SanPham/Chitietsanpham.html" class="text-white text-2xl">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="#" class="text-white text-2xl">
                                        <i class="fas fa-cart-plus"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="p-4">
                            <span class="text-red-500 font-semibold">-3%</span>
                            <span class="text-orange-500 font-semibold text-lg">68,000đ</span>
                            <span class="line-through text-gray-400 ml-2">70,000đ</span>
                            <h4 class="font-bold text-lg mt-2 mb-3">Salad rau mùa sốt cam 3</h4>
                            <a href="../SanPham/Chitietsanpham.html"
                                class="btn mt-2 bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600">Xem
                                chi tiết</a>
                        </div>
                    </div>
                    <div class="gallery-box relative bg-white rounded-lg shadow-md overflow-hidden group w-full">

                        <div class="relative">
                            <img src="../images/gallery/gallery2.png" alt="Gallery 1"
                                class="w-full h-64 object-cover transition-all duration-300 group-hover:opacity-60">
                            <div
                                class="absolute inset-0 flex justify-center items-center bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                <div class="space-x-4">
                                    <a href="../SanPham/Chitietsanpham.html" class="text-white text-2xl">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="#" class="text-white text-2xl">
                                        <i class="fas fa-cart-plus"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="p-4">
                            <span class="text-red-500 font-semibold">-3%</span>
                            <span class="text-orange-500 font-semibold text-lg">68,000đ</span>
                            <span class="line-through text-gray-400 ml-2">70,000đ</span>
                            <h4 class="font-bold text-lg mt-2 mb-3">Salad rau mùa sốt cam 3</h4>
                            <a href="../SanPham/Chitietsanpham.html"
                                class="btn mt-2 bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600">Xem
                                chi tiết</a>
                        </div>
                    </div>
                    <div class="gallery-box relative bg-white rounded-lg shadow-md overflow-hidden group w-full">

                        <div class="relative">
                            <img src="../images/gallery/gallery1.png" alt="Gallery 1"
                                class="w-full h-64 object-cover transition-all duration-300 group-hover:opacity-60">
                            <div
                                class="absolute inset-0 flex justify-center items-center bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                <div class="space-x-4">
                                    <a href="../SanPham/Chitietsanpham.html" class="text-white text-2xl">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="#" class="text-white text-2xl">
                                        <i class="fas fa-cart-plus"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="p-4">
                            <span class="text-red-500 font-semibold">-3%</span>
                            <span class="text-orange-500 font-semibold text-lg">68,000đ</span>
                            <span class="line-through text-gray-400 ml-2">70,000đ</span>
                            <h4 class="font-bold text-lg mt-2 mb-3">Salad rau mùa sốt cam 3</h4>
                            <a href="../SanPham/Chitietsanpham.html"
                                class="btn mt-2 bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600">Xem
                                chi tiết</a>
                        </div>
                    </div>
                    <div class="gallery-box relative bg-white rounded-lg shadow-md overflow-hidden group w-full">

                        <div class="relative">
                            <img src="../images/gallery/gallery3.png" alt="Gallery 1"
                                class="w-full h-64 object-cover transition-all duration-300 group-hover:opacity-60">
                            <div
                                class="absolute inset-0 flex justify-center items-center bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                <div class="space-x-4">
                                    <a href="\../SanPham/Chitietsanpham.html" class="text-white text-2xl">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="#" class="text-white text-2xl">
                                        <i class="fas fa-cart-plus"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="p-4">
                            <span class="text-red-500 font-semibold">-3%</span>
                            <span class="text-orange-500 font-semibold text-lg">68,000đ</span>
                            <span class="line-through text-gray-400 ml-2">70,000đ</span>
                            <h4 class="font-bold text-lg mt-2 mb-3">Salad rau mùa sốt cam 3</h4>
                            <a href="../SanPham/Chitietsanpham.html"
                                class="btn mt-2 bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600">Xem
                                chi tiết</a>
                        </div>
                    </div>
                </div>
            </div>
        </section> --}}
        <x-home-list-category />
        {{-- <section class="product_new">
            <section class="gallery-area">
                <div class="gallery-top bg-cover bg-center py-20"
                    style="background-image: url('assets/img/gallery/section_bg01.png');">
                    <div class="container mx-auto text-center">
                        <div class="mb-10">
                            <p class="text-orange-500 font-semibold text-lg mb-4">Thực đơn</p>
                            <h2 class="text-3xl font-bold">Món ăn mới</h2>
                        </div>
                    </div>
                    <div class="slick-slider flex overflow-x-auto gap-x-4 ">
                        <div class="gallery-box relative bg-white rounded-lg shadow-md overflow-hidden group w-48">
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
                                <span class="text-orange-500 font-semibold text-lg">68,000đ</span>
                                <span class="line-through text-gray-400 ml-2">70,000đ</span>
                                <h4 class="font-bold text-lg mt-2 mb-3">Salad rau mùa sốt cam 3</h4>
                                <a href="../SanPham/Chitietsanpham.html"
                                    class="btn mt-2 bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600">Xem
                                    chi tiết</a>
                            </div>
                        </div>

                        <div class="gallery-box relative bg-white rounded-lg shadow-md overflow-hidden group w-48">
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
                                <span class="text-orange-500 font-semibold text-lg">68,000đ</span>
                                <span class="line-through text-gray-400 ml-2">70,000đ</span>
                                <h4 class="font-bold text-lg mt-2 mb-3">Salad rau mùa sốt cam 3</h4>
                                <a href="../SanPham/Chitietsanpham.html"
                                    class="btn mt-2 bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600">Xem
                                    chi tiết</a>
                            </div>
                        </div>
                        <div class="gallery-box relative bg-white rounded-lg shadow-md overflow-hidden group w-48">
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
                                <span class="text-orange-500 font-semibold text-lg">68,000đ</span>
                                <span class="line-through text-gray-400 ml-2">70,000đ</span>
                                <h4 class="font-bold text-lg mt-2 mb-3">Salad rau mùa sốt cam 3</h4>
                                <a href="../SanPham/Chitietsanpham.html"
                                    class="btn mt-2 bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600">Xem
                                    chi tiết</a>
                            </div>
                        </div>
                        <div class="gallery-box relative bg-white rounded-lg shadow-md overflow-hidden group w-48">
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
                                <span class="text-orange-500 font-semibold text-lg">68,000đ</span>
                                <span class="line-through text-gray-400 ml-2">70,000đ</span>
                                <h4 class="font-bold text-lg mt-2 mb-3">Salad rau mùa sốt cam 3</h4>
                                <a href="../SanPham/Chitietsanpham.html"
                                    class="btn mt-2 bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600">Xem
                                    chi tiết</a>
                            </div>
                        </div>
                        <div class="gallery-box relative bg-white rounded-lg shadow-md overflow-hidden group w-48">
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
                                <span class="text-orange-500 font-semibold text-lg">68,000đ</span>
                                <span class="line-through text-gray-400 ml-2">70,000đ</span>
                                <h4 class="font-bold text-lg mt-2 mb-3">Salad rau mùa sốt cam 3</h4>
                                <a href="../SanPham/Chitietsanpham.html"
                                    class="btn mt-2 bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600">Xem
                                    chi tiết</a>
                            </div>
                        </div>
                        <div class="gallery-box relative bg-white rounded-lg shadow-md overflow-hidden group w-48">
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
                                <span class="text-orange-500 font-semibold text-lg">68,000đ</span>
                                <span class="line-through text-gray-400 ml-2">70,000đ</span>
                                <h4 class="font-bold text-lg mt-2 mb-3">Salad rau mùa sốt cam 3</h4>
                                <a href="../SanPham/Chitietsanpham.html"
                                    class="btn mt-2 bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600">Xem
                                    chi tiết</a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </section> --}}
        <x-product-new />
        <section class="section_booktable">
            <div class="booking-area bg-cover bg-center pt-32 pb-32"
                style="background-image: url('../images/gallery/section_bg04.png');">
                <div class="container mx-auto">
                    <div class="row justify-center">
                        <div class="xl:w-10/12 lg:w-9/12 md:w-10/12">
                            <div class="text-center mb-16 ml-10">
                                <span class="text-orange-500 font-semibold uppercase text-sm tracking-wide">Đặt
                                    bàn</span>
                                <h2 class="text-3xl font-bold text-gray-800 mt-2">Lên lịch đặt bàn</h2>
                            </div>
                        </div>
                    </div>
                    <div class="row justify-center">
                        <div class="col-12">
                            <form action="{{ route('site.booking.store') }}" method="POST" class="max-w-3xl mx-auto bg-white p-6 rounded-lg shadow-md">
                                @csrf
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="w-full mb-4">
                                        <input type="text" name="name" placeholder="Tên của bạn" required class="w-full px-4 py-2 border rounded-md" />
                                    </div>
                                    <div class="w-full mb-4">
                                        <input type="email" name="email" placeholder="Email của bạn" required class="w-full px-4 py-2 border rounded-md" />
                                    </div>
                                    <div class="w-full mb-4">
                                        <input type="text" name="phone" placeholder="Số điện thoại" required class="w-full px-4 py-2 border rounded-md" />
                                    </div>
                                    <div class="w-full mb-4">
                                        <input type="number" name="number_of_people" placeholder="Số người" required class="w-full px-4 py-2 border rounded-md" />
                                    </div>
                                    <div class="w-full mb-4">
                                        <input type="date" name="booking_date" required class="w-full px-4 py-2 border rounded-md" />
                                    </div>
                                    <div class="w-full mb-4">
                                        <input type="time" name="booking_time" required class="w-full px-4 py-2 border rounded-md" />
                                    </div>
                                </div>
                                <div class="w-full mb-4">
                                    <textarea name="note" placeholder="Ghi chú" rows="3" class="w-full px-4 py-2 border rounded-md"></textarea>
                                </div>
                                <div class="w-full">
                                    <button type="submit" class="w-full px-6 py-3 bg-orange-500 text-white rounded-md">Đặt bàn ngay</button>
                                </div>
                                <div class="text-center mt-4">
                                    <p class="text-gray-700 text-sm">
                                        Khách đặt tiệc hội nghị, liên hoan vui lòng gọi trực tiếp:
                                        <a href="tel:981487674" class="text-orange-500 font-semibold">981487674</a>
                                    </p>
                                </div>
                            </form>
                            
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section class="section-dichvu">
            <div class="our-services py-10 ">
                <div class="container mx-auto px-4">
                    <div class="text-center mb-16">
                        <span class="text-orange-500 font-semibold uppercase text-sm tracking-wide">Về dịch vụ</span>
                        <h2 class="text-3xl font-bold text-gray-800 mt-2">Dịch vụ tốt nhất của chúng tôi</h2>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                        <div
                            class="single-service text-center bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition">
                            <div class="services-icon text-orange-500 text-5xl mb-4">
                                <i class="flaticon-restaurant"></i>
                            </div>
                            <div class="services-caption">
                                <h5 class="text-xl font-semibold mb-2">
                                    <a href="#" class="text-gray-800 hover:text-orange-500 transition">Đầu bếp
                                        hàng
                                        đầu</a>
                                </h5>
                                <p class="text-gray-600">
                                    Đi đầu về lĩnh vực đầu bếp, các đầu bếp được trao truốt kĩ năng đem đến cho thực
                                    khách những món ăn chất lượng.
                                </p>
                            </div>
                        </div>
                        <div
                            class="single-service text-center bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition border-2 border-gray-500">
                            <div class="services-icon text-orange-500 text-5xl mb-4">
                                <i class="flaticon-tools-and-utensils-1"></i>
                            </div>
                            <div class="services-caption ">
                                <h5 class="text-xl font-semibold mb-2">
                                    <a href="#" class="text-gray-800 hover:text-orange-500 transition">Chất
                                        lượng thực
                                        phẩm</a>
                                </h5>
                                <p class="text-gray-600">
                                    Tất cả các loại thực phẩm chế biến điều được nhà hàng chọn lọc, phân loại kĩ càng để
                                    đảm bảo sự tươi mới và chất lượng bậc nhất !
                                </p>
                            </div>
                        </div>
                        <div
                            class="single-service text-center bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition">
                            <div class="services-icon text-orange-500 text-5xl mb-4">
                                <i class="flaticon-restaurant"></i>
                            </div>
                            <div class="services-caption">
                                <h5 class="text-xl font-semibold mb-2">
                                    <a href="#" class="text-gray-800 hover:text-orange-500 transition">Không
                                        gian nhà
                                        hàng</a>
                                </h5>
                                <p class="text-gray-600">
                                    Không chỉ đặt bàn ăn mà có là điểm dừng chân lí tưởng để tổ chức tiệc, CIVILIZE
                                    Restaurant sẽ luôn là sự lựa chọn hàng đầu của bạn.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        {{-- <section class="blogs-area py-24">
            <div class="container mx-auto">
                <div class="flex justify-center mb-5">
                    <div class="lg:w-1/2 text-center">
                        <div class="section-title mb-16">
                            <span class="text-orange-500 font-semibold uppercase text-sm tracking-wide">Về Tin
                                tức</span>
                            <h2 class="text-3xl font-bold text-gray-800 mt-2">Tin tức mới nhất</h2>
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <div class="overflow-x-auto">
                        <div class="flex space-x-10" id="blog-container">
                            <div
                                class="single-blogs flex-shrink-0 w-80 group relative rounded-lg overflow-hidden transition-all transform hover:scale-105 hover:shadow-lg">
                                <div class="blog-img mb-6">
                                    <img src="../images/gallery/blog1.png" alt=""
                                        class="w-300 h-300 object-cover rounded-lg transition-all duration-300 group-hover:opacity-80">
                                </div>
                                <div class="blog-cap p-6 bg-white rounded-lg">
                                    <span class="text-orange-500 block mb-2">23 Dec, 2020</span>
                                    <h4 class="text-xl font-semibold text-gray-800 group-hover:text-orange-500">
                                        <a href="blog_details.html">Addiction When Food Plate Becomes</a>
                                    </h4>
                                </div>
                            </div>
                            <div
                                class="single-blogs flex-shrink-0 w-80 group relative rounded-lg overflow-hidden transition-all transform hover:scale-105 hover:shadow-lg">
                                <div class="blog-img mb-6">
                                    <img src="../images/gallery/blog2.png" alt=""
                                        class="w-300 h-300 object-cover rounded-lg transition-all duration-300 group-hover:opacity-80">
                                </div>
                                <div class="blog-cap p-6 bg-white rounded-lg">
                                    <span class="text-orange-500 block mb-2">23 Dec, 2020</span>
                                    <h4 class="text-xl font-semibold text-gray-800 group-hover:text-orange-500">
                                        <a href="blog_details.html">Addiction When Food Plate Becomes</a>
                                    </h4>
                                </div>
                            </div>
                            <div
                                class="single-blogs flex-shrink-0 w-80 group relative rounded-lg overflow-hidden transition-all transform hover:scale-105 hover:shadow-lg">
                                <div class="blog-img mb-6">
                                    <img src="../images/gallery/blog3.png" alt=""
                                        class="w-300 h-300 object-cover rounded-lg transition-all duration-300 group-hover:opacity-80">
                                </div>
                                <div class="blog-cap p-6 bg-white rounded-lg">
                                    <span class="text-orange-500 block mb-2">23 Dec, 2020</span>
                                    <h4 class="text-xl font-semibold text-gray-800 group-hover:text-orange-500">
                                        <a href="blog_details.html">Addiction When Food Plate Becomes</a>
                                    </h4>
                                </div>
                            </div>
                            <div
                                class="single-blogs flex-shrink-0 w-80 group relative rounded-lg overflow-hidden transition-all transform hover:scale-105 hover:shadow-lg">
                                <div class="blog-img mb-6">
                                    <img src="../images/gallery/about.png" alt=""
                                        class="w-300 h-300 object-cover rounded-lg transition-all duration-300 group-hover:opacity-80">
                                </div>
                                <div class="blog-cap p-6 bg-white rounded-lg">
                                    <span class="text-orange-500 block mb-2">23 Dec, 2020</span>
                                    <h4 class="text-xl font-semibold text-gray-800 group-hover:text-orange-500">
                                        <a href="blog_details.html">Addiction When Food Plate Becomes</a>
                                    </h4>
                                </div>
                            </div>
                            <div
                                class="single-blogs flex-shrink-0 w-80 group relative rounded-lg overflow-hidden transition-all transform hover:scale-105 hover:shadow-lg">
                                <div class="blog-img mb-6">
                                    <img src="../images/gallery/blog3.png" alt=""
                                        class="w-300 h-300 object-cover rounded-lg transition-all duration-300 group-hover:opacity-80">
                                </div>
                                <div class="blog-cap p-6 bg-white rounded-lg">
                                    <span class="text-orange-500 block mb-2">23 Dec, 2020</span>
                                    <h4 class="text-xl font-semibold text-gray-800 group-hover:text-orange-500">
                                        <a href="blog_details.html">Addiction When Food Plate Becomes</a>
                                    </h4>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section> --}}
        <x-post-new />
    </div>
</x-layout-frontend>