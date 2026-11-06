<x-layout-frontend>

    <x-slot:footer>
        <!-- Slick Slider Scripts -->
        <script type="text/javascript">
            $(document).ready(function () {
                if ($('.home-hero-slider').length) {
                    $('.home-hero-slider').slick({
                        infinite: true,
                        slidesToShow: 1,
                        slidesToScroll: 1,
                        fade: true,
                        cssEase: 'cubic-bezier(0.7, 0, 0.3, 1)',
                        autoplay: true,
                        autoplaySpeed: 5000,
                        arrows: true,
                        dots: true,
                        prevArrow: '<button type="button" class="slick-prev absolute left-4 sm:left-8 top-1/2 -translate-y-1/2 z-20 w-11 h-11 sm:w-12 sm:h-12 rounded-lg bg-stone-900/80 hover:bg-[#F47716] text-white flex items-center justify-center transition-colors shadow-lg cursor-pointer"><i class="fas fa-chevron-left text-sm sm:text-base"></i></button>',
                        nextArrow: '<button type="button" class="slick-next absolute right-4 sm:right-8 top-1/2 -translate-y-1/2 z-20 w-11 h-11 sm:w-12 sm:h-12 rounded-lg bg-stone-900/80 hover:bg-[#F47716] text-white flex items-center justify-center transition-colors shadow-lg cursor-pointer"><i class="fas fa-chevron-right text-sm sm:text-base"></i></button>'
                    });
                }
            });
        </script>
    </x-slot:footer>

    <div>
        <!-- ======================================================== -->
        <!-- ======================================================== -->
        <!-- SECTION 1: HERO CINEMATIC SLIDER                         -->
        <!-- ======================================================== -->
        <section class="hero-slider-section relative bg-stone-950 overflow-hidden w-full">
            <div class="home-hero-slider h-full">
                @php
                    $slideDefaults = [
                        0 => [
                            'name' => 'Ẩm Thực Truyền Thống & Đậm Đà Bản Sắc',
                            'description' => 'Hương vị đậm đà trong từng món ăn Việt, chế biến từ nguyên liệu tươi ngon chọn lọc mỗi ngày.',
                            'image' => 'hero_slide_1.jpg',
                        ],
                        1 => [
                            'name' => 'Món Ăn Đặc Sản & Hương Vị Khó Quên',
                            'description' => 'Trải nghiệm ẩm thực phong phú với các món lẩu, nướng và đặc sản đồng quê độc đáo.',
                            'image' => 'hero_slide_2.jpg',
                        ],
                        2 => [
                            'name' => 'Không Gian Thoáng Mát & Nhận Đặt Tiệc Trọn Gói',
                            'description' => 'Sắp xếp bàn ghế rộng rãi, phục vụ nhiệt tình cho các bữa cơm gia đình, tiệc sinh nhật và liên hoan bạn bè.',
                            'image' => 'hero_slide_3.jpg',
                        ],
                    ];
                    $slideItems = (isset($banners) && $banners->isNotEmpty()) ? $banners : $slideDefaults;
                @endphp

                @foreach ($slideItems as $index => $item)
                    @php
                        $fallback = $slideDefaults[$index % count($slideDefaults)];
                        $titleText = is_object($item) && !empty($item->name) ? $item->name : ($item['name'] ?? $fallback['name']);
                        $descText = is_object($item) && !empty($item->description) ? $item->description : ($item['description'] ?? $fallback['description']);
                        $imgFile = is_object($item) && !empty($item->image) ? $item->image : ($item['image'] ?? $fallback['image']);
                        $imgUrl = file_exists(public_path('images/banner/' . $imgFile))
                            ? asset('images/banner/' . $imgFile)
                            : asset('images/banner/' . $fallback['image']);
                    @endphp
                    <div class="hero-slide-item relative w-full min-h-[520px] sm:min-h-[580px] lg:min-h-[640px] flex items-center outline-none">
                        <!-- High-Res Background Image -->
                        <div class="absolute inset-0 overflow-hidden">
                            <img src="{{ $imgUrl }}" 
                                 alt="{{ $titleText }}" 
                                 class="w-full h-full object-cover brightness-[0.80] scale-100 transition-transform duration-700 ease-out">
                            <div class="absolute inset-0 bg-black/40"></div>
                        </div>

                        <!-- Slide Content: Đơn giản, thoáng đãng, chữ trắng to rõ như trước -->
                        <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 w-full relative z-10 py-16 sm:py-20">
                            <div class="max-w-2xl text-left text-white">

                                <!-- Headline To Rõ Toàn Bộ Chữ Trắng Chuẩn Ảnh Cũ -->
                                <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold text-white mb-4 leading-tight tracking-tight drop-shadow-md">
                                    {{ $titleText }}
                                </h1>

                                <!-- Subtitle Dễ Đọc -->
                                <p class="text-sm sm:text-base md:text-lg text-white/95 font-normal leading-relaxed max-w-xl mb-7 drop-shadow-sm">
                                    {{ $descText }}
                                </p>

                                <!-- Hai Nút Chuẩn Trước Đó: XEM THỰC ĐƠN & ĐẶT BÀN TRƯỚC -->
                                <div class="flex flex-wrap items-center gap-3.5">
                                    <a href="{{ route('site.product.gridview') }}" 
                                       class="px-6 sm:px-8 py-3 rounded-none sm:rounded-sm bg-[#F47716] hover:bg-[#EA580C] text-white font-extrabold text-xs uppercase tracking-wider transition-colors shadow-md">
                                        <span>XEM THỰC ĐƠN</span>
                                    </a>
                                    <a href="{{ route('site.booking') }}" 
                                       class="px-6 sm:px-8 py-3 rounded-none sm:rounded-sm bg-stone-900/40 hover:bg-white text-white hover:text-stone-900 border border-white/60 hover:border-white font-extrabold text-xs uppercase tracking-wider backdrop-blur-xs transition-colors">
                                        <span>ĐẶT BÀN TRƯỚC</span>
                                    </a>
                                </div>

                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- ======================================================== -->
        <!-- SECTION 2: THỰC ĐƠN TINH HOA THEO DANH MỤC               -->
        <!-- ======================================================== -->
        <x-home-list-category />

        <!-- ======================================================== -->
        <!-- SECTION 3: KHÔNG GIAN BÀN TIỆC & ĐẶT ĐOÀN                 -->
        <!-- ======================================================== -->
        <section class="section-dichvu py-12 sm:py-16 bg-[#FAF6EE] border-t border-b border-stone-200/70">
            <div class="max-w-[1200px] mx-auto px-4 sm:px-6 w-full">
                <!-- Section Header: Căn giữa chuẩn xác đồng bộ với mục Góc chia sẻ -->
                <div class="text-center max-w-2xl mx-auto mb-8 sm:mb-10">
                    <span class="inline-block text-[#F47716] font-bold uppercase text-xs tracking-wider mb-1">
                        Bàn tiệc đoàn & Gia đình
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-stone-900 tracking-tight mb-2.5">
                        Dịch vụ bàn tiệc và phòng riêng theo đoàn
                    </h2>
                    <p class="text-stone-600 text-xs sm:text-sm leading-relaxed">
                        Bố trí bàn linh hoạt theo quy mô đoàn khách từ bữa cơm gia đình nhỏ đến tiệc liên hoan đông người với set menu thiết kế riêng.
                    </p>
                </div>

                <!-- 3 Visual Showcase Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 sm:gap-8">
                    <!-- Dịch vụ 1: Tiệc Gia Đình -->
                    <div class="group bg-white rounded-none sm:rounded-sm overflow-hidden border border-stone-200 hover:border-stone-400 shadow-2xs hover:shadow-xs transition-all duration-300">
                        <div class="relative aspect-[16/10] overflow-hidden bg-stone-100">
                            <img src="{{ asset('images/gallery/space_family.jpg') }}" 
                                 alt="Không gian tiệc gia đình ấm cúng" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out">
                            <div class="absolute bottom-2.5 right-2.5 bg-stone-950/75 text-white font-medium text-[11px] px-2.5 py-0.5 rounded-none sm:rounded-xs shadow-xs">
                                4 – 16 khách
                            </div>
                        </div>
                        <div class="p-5 sm:p-6">
                            <h3 class="font-bold text-stone-900 text-base sm:text-lg group-hover:text-[#F47716] transition-colors mb-2">
                                Mâm cơm gia đình & sum vầy
                            </h3>
                            <p class="text-stone-600 text-xs sm:text-sm leading-relaxed">
                                Bàn ghế gỗ mộc tự nhiên rộng rãi, ánh đèn vàng ấm áp cùng thực đơn mâm cơm thuần Việt và lẩu nướng kết nối mọi thế hệ trong gia đình.
                            </p>
                        </div>
                    </div>

                    <!-- Dịch vụ 2: Bàn Tiệc Bạn Bè & Họp Mặt -->
                    <div class="group bg-white rounded-none sm:rounded-sm overflow-hidden border border-stone-200 hover:border-stone-400 shadow-2xs hover:shadow-xs transition-all duration-300">
                        <div class="relative aspect-[16/10] overflow-hidden bg-stone-100">
                            <img src="{{ asset('images/gallery/space_vip.jpg') }}" 
                                 alt="Góc bàn tiệc bạn bè và phòng VIP" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out">
                            <div class="absolute bottom-2.5 right-2.5 bg-stone-950/75 text-white font-medium text-[11px] px-2.5 py-0.5 rounded-none sm:rounded-xs shadow-xs">
                                6 – 20 khách
                            </div>
                        </div>
                        <div class="p-5 sm:p-6">
                            <h3 class="font-bold text-stone-900 text-base sm:text-lg group-hover:text-[#F47716] transition-colors mb-2">
                                Gặp gỡ bạn bè & bàn tiệc nhậu
                            </h3>
                            <p class="text-stone-600 text-xs sm:text-sm leading-relaxed">
                                Không gian thoáng đãng, bàn ghế xếp linh hoạt với vách ngăn gỗ riêng tư, lý tưởng cho các buổi tụ họp cuối tuần nhâm nhi nướng lẩu và trò chuyện.
                            </p>
                        </div>
                    </div>

                    <!-- Dịch vụ 3: Tiệc Sinh Nhật & Sự Kiện -->
                    <div class="group bg-white rounded-none sm:rounded-sm overflow-hidden border border-stone-200 hover:border-stone-400 shadow-2xs hover:shadow-xs transition-all duration-300">
                        <div class="relative aspect-[16/10] overflow-hidden bg-stone-100">
                            <img src="{{ asset('images/gallery/space_event.jpg') }}" 
                                 alt="Sảnh tiệc sinh nhật và liên hoan công ty" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out">
                            <div class="absolute bottom-2.5 right-2.5 bg-stone-950/75 text-white font-medium text-[11px] px-2.5 py-0.5 rounded-none sm:rounded-xs shadow-xs">
                                20 – 60 khách
                            </div>
                        </div>
                        <div class="p-5 sm:p-6">
                            <h3 class="font-bold text-stone-900 text-base sm:text-lg group-hover:text-[#F47716] transition-colors mb-2">
                                Tiệc sinh nhật & liên hoan công ty
                            </h3>
                            <p class="text-stone-600 text-xs sm:text-sm leading-relaxed">
                                Sảnh tiệc rộng rãi, hỗ trợ trang trí backdrop, hoa tươi, âm thanh ánh sáng và thiết kế set menu tiệc linh hoạt theo ngân sách của đoàn.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Bottom CTA: Căn giữa đồng bộ với mục Góc chia sẻ -->
                <div class="mt-10 sm:mt-12 text-center">
                    <a href="{{ route('site.image') }}" 
                       class="inline-flex items-center justify-center px-8 py-3 rounded-none sm:rounded-sm bg-white hover:bg-stone-50 text-stone-700 font-bold text-xs uppercase tracking-wider border border-stone-300 shadow-2xs transition-colors cursor-pointer">
                        <span>Xem thêm ảnh không gian quán</span>
                        <i class="fas fa-arrow-right text-[11px] ml-2"></i>
                    </a>
                </div>
            </div>
        </section>

        <!-- ======================================================== -->
        <!-- SECTION 4: TIN TỨC QUÁN                                  -->
        <!-- ======================================================== -->
        <x-post-new />
    </div>

</x-layout-frontend>
