<x-layout-frontend>
    <div class="bg-white min-h-screen">  
        
        <!-- Section 1: Câu chuyện bếp trưởng & ẩm thực sum vầy -->
        <section class="py-10 sm:py-14 md:py-16 bg-white border-b border-stone-200/80">
            <div class="max-w-[1200px] mx-auto px-4 sm:px-6 w-full">
                
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                    <!-- Left: Nội dung kể chuyện chân thực, có địa danh & chi tiết cụ thể -->
                    <div class="lg:col-span-7">
                        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-stone-900 tracking-tight leading-tight mb-5">
                            Bữa cơm sum vầy, trọn vị thân quen
                        </h1>

                        <div class="space-y-4 text-stone-600 text-sm sm:text-[15px] leading-relaxed mb-8">
                            <p>
                                CIVILIZE mở cửa đón khách từ năm 2021 tại số 120 đường Phúc Hòa, thị trấn Phúc Thọ. Giữa vùng đất xứ Đoài giàu truyền thống, quán ra đời từ mong muốn mộc mạc: tạo dựng một điểm hẹn ấm cúng cho bà con địa phương và thực khách ghé qua — nơi mọi người cùng quây quần bên mâm cơm nóng sốt, nồi lẩu nghi ngút khói hay đĩa đồ nướng thơm lừng sau ngày làm việc.
                            </p>
                            <p>
                                Gian bếp CIVILIZE do Bếp trưởng Nguyễn Văn Hải trực tiếp phụ trách với hơn 12 năm kinh nghiệm gắn bó cùng ẩm thực truyền thống. Mỗi sớm, bếp tự tay chọn rau củ tươi non từ các nhà vườn ven sông Đáy vùng Phúc Thọ, thịt bò ta mổ mới trong ngày và hải sản tươi sống bơi tại bể. Quán kiên quyết không dùng đồ đông lạnh lưu cữu hay phụ gia công nghiệp, để từng đĩa thức ăn bưng ra bàn luôn giữ trọn vị ngọt tươi tự nhiên.
                            </p>
                            <p>
                                Thực khách quen ghé quán thường nhớ nhất nồi <strong class="text-stone-900 font-bold">Lẩu bắp bò nhúng dấm</strong> chua thanh dịu ngọt cốt dấm gạo ngâm ớt tỏi, đĩa <strong class="text-stone-900 font-bold">Nầm nướng ngũ vị</strong> giòn sần sật xì xèo trên chảo gang, hay tảng <strong class="text-stone-900 font-bold">Bò nướng tảng</strong> đậm đà quyện sốt tiêu rừng đặc trưng.
                            </p>
                        </div>

                        <!-- 3 Số liệu & Cam kết thật: Bỏ cấu trúc 01-04 template dựng sẵn -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-6 border-t border-stone-200">
                            <div class="space-y-1">
                                <div class="text-xl sm:text-2xl font-black text-[#C25E00]">Từ 2021</div>
                                <p class="text-xs text-stone-600 leading-relaxed font-medium">
                                    Hơn 5 năm đồng hành cùng các bữa cơm gia đình và tiệc sum họp tại Phúc Thọ.
                                </p>
                            </div>

                            <div class="space-y-1">
                                <div class="text-xl sm:text-2xl font-black text-[#C25E00]">100% Tươi mới</div>
                                <p class="text-xs text-stone-600 leading-relaxed font-medium">
                                    Rau sạch sông Đáy, bò tươi trong ngày, cá lăng và hải sản bơi tươi tại bể.
                                </p>
                            </div>

                            <div class="space-y-1">
                                <div class="text-xl sm:text-2xl font-black text-[#C25E00]">80+ Món ăn</div>
                                <p class="text-xs text-stone-600 leading-relaxed font-medium">
                                    Đa dạng từ cơm canh dân dã, lẩu nhúng dấm đến các món nướng chảo gang.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Ảnh Bếp Trưởng tràn khung cân đối, không để khoảng trống thừa -->
                    <div class="lg:col-span-5 flex flex-col justify-center">
                        <div class="w-full">
                            <div class="rounded-none sm:rounded-sm overflow-hidden shadow-2xs border border-stone-200 bg-stone-100 aspect-[4/5] w-full">
                                <img src="{{ asset('images/gallery/chef_real.png') }}" 
                                     alt="Bếp trưởng Nguyễn Văn Hải tại CIVILIZE Restaurant" 
                                     class="w-full h-full object-cover object-top">
                            </div>
                            <p class="text-xs text-stone-500 text-center mt-3 font-medium">
                                Bếp trưởng Nguyễn Văn Hải — Hơn 12 năm tận tâm với ẩm thực truyền thống
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section 2: Không gian quán thực tế & Đón tiếp tiệc tùng -->
        <section class="py-10 sm:py-14 bg-[#FAF7F2] border-b border-stone-200/80">
            <div class="max-w-[1200px] mx-auto px-4 sm:px-6 w-full">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                    <!-- Left: Ảnh chụp thực tế không gian quán (Ảnh thật dự án) -->
                    <div class="lg:col-span-5 order-2 lg:order-1">
                        <div class="w-full">
                            <div class="rounded-none sm:rounded-sm overflow-hidden shadow-2xs border border-stone-200 bg-stone-100 aspect-[4/3] w-full">
                                <img src="{{ asset('images/gallery/about2.png') }}" 
                                     alt="Không gian ẩm thực tại nhà hàng CIVILIZE Phúc Thọ" 
                                     class="w-full h-full object-cover">
                            </div>
                            <p class="text-xs text-stone-500 text-center mt-3 font-medium">
                                Không gian sảnh tiệc rộng rãi, ấm cúng tại số 120 đường Phúc Hòa, Phúc Thọ
                            </p>
                        </div>
                    </div>

                    <!-- Right: Thông tin không gian & Nút điều hướng đồng bộ màu cam -->
                    <div class="lg:col-span-7 order-1 lg:order-2">
                        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-stone-900 tracking-tight leading-tight mb-5">
                            Không gian mộc mạc cho những buổi sum họp
                        </h2>

                        <div class="space-y-4 text-stone-600 text-sm sm:text-[15px] leading-relaxed mb-8">
                            <p>
                                Tại CIVILIZE, các dãy bàn gỗ được bố trí rộng rãi, ánh đèn vàng ấm áp tạo cảm giác gần gũi, thoải mái như đang dùng bữa tại nhà. Quán có sảnh tiệc lớn phục vụ cùng lúc hơn 150 khách, cùng 3 phòng riêng ấm cúng (sức chứa từ 10 đến 30 khách trang bị điều hòa riêng), rất thích hợp cho những buổi tiệc sinh nhật, họp lớp hay liên hoan công ty.
                            </p>
                            <p>
                                Quán có khoảng sân và vỉa hè rộng rãi ngay mặt đường lớn, đỗ ô tô và xe máy thuận tiện, an toàn và hoàn toàn miễn phí cho thực khách.
                            </p>
                        </div>

                        <!-- 2 Nút hành động thống nhất ngôn ngữ: CTA chính cam #F47716, phụ viền xám -->
                        <div class="flex flex-wrap items-center gap-3">
                            <a href="{{ route('site.product.gridview') }}" 
                               class="px-6 py-2.5 rounded-none sm:rounded-sm bg-[#F47716] hover:bg-[#EA580C] text-white font-bold text-xs uppercase tracking-wider shadow-2xs transition-all cursor-pointer">
                                Xem thực đơn
                            </a>
                            <a href="{{ route('site.image') }}" 
                               class="px-6 py-2.5 rounded-none sm:rounded-sm bg-white hover:bg-stone-50 text-stone-700 font-bold text-xs uppercase tracking-wider border border-stone-300 shadow-2xs transition-all cursor-pointer">
                                Hình ảnh không gian
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-layout-frontend>
