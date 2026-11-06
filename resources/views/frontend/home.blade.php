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
        <!-- SECTION 1: HERO CINEMATIC SLIDER                         -->
        <!-- ======================================================== -->
        <section class="hero-slider-section relative bg-stone-950 overflow-hidden w-full">
            <div class="home-hero-slider h-full">
                @php
                    $slideConfigs = [
                        0 => [
                            'tag' => 'MÓN SIGNATURE • BẾP TRƯỞNG KHUYÊN DÙNG',
                            'dish_badge_title' => 'Lẩu Bắp Bò Nhúng Dấm',
                            'dish_badge_desc' => 'Dấm bỗng tự nhiên • Bò hoa tươi mỗi ngày',
                            'title_primary' => 'Lẩu Bắp Bò Nhúng Dấm',
                            'title_accent' => 'Hương Vị Sum Vầy',
                            'desc' => 'Nước dùng dấm bỗng thanh ngọt ninh từ xương tủy 12 tiếng, bắp bò hoa thái mỏng giòn ngọt cùng mẹt rau sạch tươi non sông Đáy.',
                            'image' => 'hero_slide_1.jpg',
                            'tab_label' => '01. Lẩu Bắp Bò',
                        ],
                        1 => [
                            'tag' => 'MÓN NƯỚNG ĐẶC SẮC • CHẢO GANG THAN HỒNG',
                            'dish_badge_title' => 'Nầm & Bò Nướng Ngũ Vị',
                            'dish_badge_desc' => 'Tẩm ướp thảo mộc • Sốt me & mè rang đậm đà',
                            'title_primary' => 'Chảo Nướng Ngũ Vị',
                            'title_accent' => 'Món Ngon Đậm Vị',
                            'desc' => 'Từng miếng thịt tẩm ướp thảo mộc gia truyền xèo xèo thơm nức mũi trên chảo gang than hồng, chấm quyện cùng sốt tiêu rừng ấm nồng khó quên.',
                            'image' => 'hero_slide_2.jpg',
                            'tab_label' => '02. Chảo Nướng',
                        ],
                        2 => [
                            'tag' => 'ĐẶC SẢN MÂM CỖ • TIỆC ĐOÀN SUM HỌP',
                            'dish_badge_title' => 'Cá Nướng Than Hoa & Mâm Cỗ',
                            'dish_badge_desc' => 'Nướng than tại chỗ • Cuốn bánh tráng rau rừng',
                            'title_primary' => 'Cá Nướng Than Hoa',
                            'title_accent' => 'Bàn Tiệc Sum Vầy',
                            'desc' => 'Cá tươi nướng than hoa vàng ươm giòn da ngọt thịt, hòa quyện mắm nêm đậm đà cùng mâm tiệc tròn đầy cho gia đình và bạn bè quây quần nồng ấm.',
                            'image' => 'hero_slide_3.jpg',
                            'tab_label' => '03. Cá Nướng & Tiệc',
                        ],
                    ];
                    $slideItems = (isset($banners) && $banners->isNotEmpty()) ? $banners : array_keys($slideConfigs);
                @endphp

                @foreach ($slideItems as $index => $item)
                    @php
                        $cfg = $slideConfigs[$index % count($slideConfigs)];
                        $rawName = is_object($item) && !empty($item->name) ? $item->name : ($cfg['title_primary'] . ' & ' . $cfg['title_accent']);
                        
                        // Tách tiêu đề thành 2 vế để tạo điểm nhấn màu nghệ thuật
                        if (str_contains($rawName, ' & ')) {
                            $parts = explode(' & ', $rawName, 2);
                            $titlePrimary = $parts[0];
                            $titleAccent = $parts[1];
                        } elseif (str_contains($rawName, ' &amp; ')) {
                            $parts = explode(' &amp; ', $rawName, 2);
                            $titlePrimary = $parts[0];
                            $titleAccent = $parts[1];
                        } else {
                            $titlePrimary = $cfg['title_primary'];
                            $titleAccent = $cfg['title_accent'];
                        }

                        $descText = is_object($item) && !empty($item->description) ? $item->description : $cfg['desc'];
                        $imgFile = is_object($item) && !empty($item->image) ? $item->image : $cfg['image'];
                        $imgUrl = file_exists(public_path('images/banner/' . $imgFile))
                            ? asset('images/banner/' . $imgFile)
                            : asset('images/banner/' . $cfg['image']);
                        $tagText = $cfg['tag'];
                    @endphp
                    <div class="hero-slide-item relative w-full min-h-[580px] sm:min-h-[640px] lg:min-h-[700px] flex items-center outline-none">
                        <!-- High-Res Background Image -->
                        <div class="absolute inset-0 overflow-hidden">
                            <img src="{{ $imgUrl }}" 
                                 alt="{{ $titlePrimary }}" 
                                 class="w-full h-full object-cover brightness-[0.88] scale-100 hover:scale-103 transition-transform duration-1000 ease-out">
                            
                            <!-- Subtle Text Scrim: Lớp gradient phủ sâu và êm hơn ở góc trái để tôn trọn chữ lớn siêu sắc nét mà không làm mất vẻ đẹp của ảnh món ăn -->
                            <div class="absolute inset-0 bg-gradient-to-r from-stone-950/95 via-stone-950/70 to-transparent w-full md:w-4/5 lg:w-3/5"></div>
                            <div class="absolute inset-0 bg-gradient-to-t from-stone-950/80 via-transparent to-stone-950/40"></div>
                        </div>

                        <!-- Slide Content: Đặt ở vị trí bên trái sang trọng, căn chỉnh hoàn hảo -->
                        <div class="max-w-[1360px] mx-auto px-4 sm:px-6 lg:px-8 w-full relative z-10 pt-20 sm:pt-24 lg:pt-28 pb-14 sm:pb-16">
                            <div class="max-w-2xl lg:max-w-3xl text-left text-white">

                                <!-- Eyebrow Pill Badge: Tinh hoa nhận diện thương hiệu -->
                                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-stone-900/80 border border-white/20 text-[#F47716] text-xs font-bold uppercase tracking-wider backdrop-blur-md mb-4 shadow-sm">
                                    <span class="w-2 h-2 rounded-full bg-[#F47716] animate-pulse"></span>
                                    <span>{{ $tagText }}</span>
                                </div>

                                <!-- Headline to rõ, phân tầng màu sắc nghệ thuật -->
                                <h1 class="text-3xl sm:text-5xl md:text-6xl lg:text-[54px] xl:text-[62px] font-black leading-[1.12] text-white mb-4 sm:mb-5 tracking-tight drop-shadow-[0_4px_16px_rgba(0,0,0,0.95)]">
                                    <span>{{ $titlePrimary }}</span>
                                    <span class="block sm:inline text-stone-300 font-light">&</span>
                                    <span class="text-[#F47716]">{{ $titleAccent }}</span>
                                </h1>

                                <!-- Subtitle to rõ, dễ đọc, văn phong ẩm thực lôi cuốn -->
                                <p class="text-base sm:text-lg lg:text-xl text-stone-100 font-normal leading-relaxed max-w-xl lg:max-w-2xl mb-7 sm:mb-8 drop-shadow-[0_2px_8px_rgba(0,0,0,0.9)]">
                                    {{ $descText }}
                                </p>

                                <!-- Action Buttons: Kích thước cân xứng, bo góc mềm mại, icon đẹp -->
                                <div class="flex flex-wrap items-center justify-start gap-3.5 sm:gap-4 mb-7 sm:mb-9">
                                    <a href="#tim-kiem-mon-an" 
                                       class="px-7 sm:px-9 py-3.5 sm:py-4 rounded-xl bg-gradient-to-r from-[#F47716] to-[#EA580C] hover:from-[#EA580C] hover:to-[#C2410C] text-white font-extrabold text-xs sm:text-sm uppercase tracking-wider shadow-lg shadow-orange-500/30 hover:shadow-orange-500/50 hover:-translate-y-0.5 active:translate-y-0 transition-all flex items-center gap-2.5 cursor-pointer">
                                        <i class="fas fa-utensils text-xs"></i>
                                        <span>Khám phá thực đơn</span>
                                    </a>
                                    <a href="{{ route('site.booking') }}" 
                                       class="px-7 sm:px-9 py-3.5 sm:py-4 rounded-xl bg-stone-900/70 hover:bg-white text-white hover:text-stone-900 border border-white/70 hover:border-white font-extrabold text-xs sm:text-sm uppercase tracking-wider backdrop-blur-md shadow-lg hover:-translate-y-0.5 active:translate-y-0 transition-all flex items-center gap-2.5 cursor-pointer">
                                        <i class="fas fa-calendar-check text-[#F47716]"></i>
                                        <span>Đặt bàn trước</span>
                                    </a>
                                </div>

                                <!-- Dải bảo chứng uy tín (Trust Bar) -->
                                <div class="flex items-center flex-wrap gap-4 sm:gap-6 pt-4 sm:pt-5 border-t border-white/20 text-xs sm:text-sm text-stone-200">
                                    <div class="flex items-center gap-2">
                                        <div class="flex text-amber-400 text-xs">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                        </div>
                                        <span class="font-bold text-white">4.9/5</span>
                                        <span class="text-stone-300">(1,200+ đánh giá)</span>
                                    </div>
                                    <div class="hidden sm:flex items-center gap-2 border-l border-white/20 pl-4 sm:pl-6">
                                        <i class="fas fa-shield-halved text-emerald-400"></i>
                                        <span>100% Thực phẩm tươi sạch VietGAP</span>
                                    </div>
                                    <div class="hidden md:flex items-center gap-2 border-l border-white/20 pl-4 sm:pl-6">
                                        <i class="fas fa-clock text-amber-400"></i>
                                        <span>Phục vụ: 07:00 – 22:30</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Thẻ nổi giới thiệu món ăn góc phải (Floating Dish Badge) -->
                        <div class="hidden lg:flex absolute top-24 right-8 xl:right-16 z-20 max-w-xs bg-stone-950/80 backdrop-blur-md rounded-2xl p-4 border border-white/20 shadow-2xl text-left text-white items-center gap-3.5 hover:scale-105 transition-transform duration-300">
                            <div class="w-12 h-12 rounded-xl bg-[#F47716]/20 border border-[#F47716]/40 flex items-center justify-center shrink-0 text-[#F47716] text-xl shadow-inner">
                                <i class="fas fa-fire-flame-curved"></i>
                            </div>
                            <div>
                                <span class="text-[10px] uppercase font-bold tracking-wider text-[#F47716] block">Món ngon phục vụ tại bàn</span>
                                <h4 class="font-bold text-white text-sm leading-snug">{{ $cfg['dish_badge_title'] }}</h4>
                                <p class="text-stone-300 text-xs mt-0.5">{{ $cfg['dish_badge_desc'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Thanh chuyển nhanh món / slide theo chủ đề ở góc dưới bên trái -->
            <div class="hidden sm:flex absolute left-4 sm:left-8 lg:left-12 bottom-5 z-30 items-center gap-2">
                <button type="button" onclick="$('.home-hero-slider').slick('slickGoTo', 0)" class="hero-tab-btn px-3 py-1.5 rounded-lg bg-stone-900/80 hover:bg-stone-900 text-[11px] font-bold text-white/90 hover:text-white border border-white/20 hover:border-[#F47716] backdrop-blur-md transition-all flex items-center gap-1.5 cursor-pointer">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#F47716]"></span>
                    <span>01. Lẩu Bắp Bò</span>
                </button>
                <button type="button" onclick="$('.home-hero-slider').slick('slickGoTo', 1)" class="hero-tab-btn px-3 py-1.5 rounded-lg bg-stone-900/80 hover:bg-stone-900 text-[11px] font-bold text-white/90 hover:text-white border border-white/20 hover:border-[#F47716] backdrop-blur-md transition-all flex items-center gap-1.5 cursor-pointer">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#F47716]"></span>
                    <span>02. Chảo Nướng</span>
                </button>
                <button type="button" onclick="$('.home-hero-slider').slick('slickGoTo', 2)" class="hero-tab-btn px-3 py-1.5 rounded-lg bg-stone-900/80 hover:bg-stone-900 text-[11px] font-bold text-white/90 hover:text-white border border-white/20 hover:border-[#F47716] backdrop-blur-md transition-all flex items-center gap-1.5 cursor-pointer">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#F47716]"></span>
                    <span>03. Cá Nướng & Tiệc</span>
                </button>
            </div>

            <!-- Nút cuộn xuống tìm kiếm món ăn -->
            <a href="#tim-kiem-mon-an" 
               class="hidden sm:inline-flex absolute right-6 sm:right-10 bottom-6 sm:bottom-7 z-30 items-center gap-2 text-white/80 hover:text-white text-xs font-semibold uppercase tracking-wider transition-colors drop-shadow cursor-pointer select-none group"
               title="Cuộn xuống tìm kiếm món ăn">
                <span class="group-hover:text-[#F47716] transition-colors">Cuộn xem món</span>
                <i class="fas fa-chevron-down text-[11px] animate-bounce text-[#F47716]"></i>
            </a>
        </section>

        <!-- ======================================================== -->
        <!-- SECTION 1.5: DẢI CAM KẾT & GIỚI THIỆU GIÁ TRỊ NHÀ HÀNG    -->
        <!-- ======================================================== -->
        <section class="bg-white border-b border-stone-200/80 py-6 sm:py-7 relative z-20 shadow-2xs">
            <div class="max-w-[1200px] mx-auto px-4 sm:px-6 w-full">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-orange-50 text-[#F47716] flex items-center justify-center shrink-0 text-base sm:text-lg shadow-xs">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <div>
                            <h4 class="font-extrabold text-stone-900 text-xs sm:text-sm">100% Tươi Mới</h4>
                            <p class="text-stone-500 text-[11px] sm:text-xs">Rau sạch sông Đáy, bò tươi mỗi ngày</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-orange-50 text-[#F47716] flex items-center justify-center shrink-0 text-base sm:text-lg shadow-xs">
                            <i class="fas fa-hat-chef"></i>
                        </div>
                        <div>
                            <h4 class="font-extrabold text-stone-900 text-xs sm:text-sm">Bếp Trưởng 12+ Năm</h4>
                            <p class="text-stone-500 text-[11px] sm:text-xs">Công thức gia truyền đậm vị xứ Đoài</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-orange-50 text-[#F47716] flex items-center justify-center shrink-0 text-base sm:text-lg shadow-xs">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <h4 class="font-extrabold text-stone-900 text-xs sm:text-sm">Sảnh Tiệc & Phòng VIP</h4>
                            <p class="text-stone-500 text-[11px] sm:text-xs">Sức chứa 150 khách, 3 phòng riêng</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-orange-50 text-[#F47716] flex items-center justify-center shrink-0 text-base sm:text-lg shadow-xs">
                            <i class="fas fa-shield-halved"></i>
                        </div>
                        <div>
                            <h4 class="font-extrabold text-stone-900 text-xs sm:text-sm">Chuẩn ATVSTP</h4>
                            <p class="text-stone-500 text-[11px] sm:text-xs">Không đồ đông lạnh, không phụ gia</p>
                        </div>
                    </div>
                </div>
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
