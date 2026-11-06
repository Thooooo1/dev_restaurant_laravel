<x-layout-frontend>
    <main class="bg-stone-50/60 min-h-screen">
        <!-- Page Hero Header with Breadcrumbs -->
        <x-page-hero 
            title="Hình Ảnh Không Gian Quán" 
            subtitle="Hình ảnh thực tế không gian nhà hàng, bàn tiệc gia đình và các buổi liên hoan tại CIVILIZE Restaurant." 
            badge="HÌNH ẢNH" 
            :breadcrumbs="[['name' => 'Hình ảnh', 'url' => route('site.image')]]" 
        />

        <section class="py-12 md:py-16">
            <div class="container mx-auto px-4 max-w-7xl">
                <!-- Section Intro: Bỏ overline khuôn mẫu, tiêu đề trang trọng mộc mạc -->
                <div class="text-center max-w-2xl mx-auto mb-10">
                    <h2 class="text-2xl sm:text-3xl md:text-4xl font-black text-stone-900 tracking-tight mb-3 font-serif">
                        Không Gian Nhà Hàng CIVILIZE
                    </h2>
                    <p class="text-stone-600 text-sm sm:text-base leading-relaxed">
                        Hình ảnh thực tế không gian sảnh tiệc, bàn ăn gia đình và các phòng tiệc riêng ấm cúng tại Phúc Thọ, Hà Nội.
                    </p>
                </div>

                <!-- Gallery Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    @forelse ($images as $item)
                        @php
                            $imgSrc = !empty($item->image) && file_exists(public_path('images/library/' . $item->image))
                                ? asset('images/library/' . $item->image)
                                : asset('images/gallery/space_family.jpg');

                            $areaTags = [
                                1 => 'Sảnh tiệc chính tầng 1',
                                2 => 'Phòng VIP riêng tư cao cấp',
                                3 => 'Khu sự kiện & Hội tiệc',
                                4 => 'Khu tiệc lẩu nướng sum vầy',
                                5 => 'Khu bàn tiệc gia đình ấm cúng',
                                6 => 'Sảnh đón tiếp & Quầy mộc',
                                7 => 'Sân vườn thoáng mát',
                                8 => 'Khu bàn tiệc liên hoan',
                            ];
                            $tag = $areaTags[$item->id] ?? 'Không gian ẩm thực CIVILIZE';
                        @endphp
                        <div class="group relative rounded-none sm:rounded-sm overflow-hidden shadow-2xs hover:shadow-md transition-all duration-300 bg-stone-900 aspect-[4/3] border border-stone-200/80">
                            <img src="{{ $imgSrc }}" 
                                 alt="{{ $item->title ?? 'Không gian nhà hàng' }}" 
                                 class="w-full h-full object-cover transform transition-transform duration-700 ease-out group-hover:scale-110 group-hover:brightness-90">
                            
                            <!-- Overlay Caption on Hover -->
                            <div class="absolute inset-0 bg-gradient-to-t from-stone-950/95 via-stone-950/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-end p-5 text-white">
                                <h4 class="font-bold text-sm sm:text-base leading-snug drop-shadow-md">
                                    {{ $item->title ?? 'Không gian CIVILIZE' }}
                                </h4>
                                <span class="text-xs text-amber-300 font-medium flex items-center gap-1.5 mt-1.5">
                                    <i class="fas fa-location-dot text-[11px]"></i> {{ $tag }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-12 text-stone-500">
                            <i class="fas fa-images text-4xl text-stone-300 mb-3"></i>
                            <p>Đang cập nhật hình ảnh không gian nhà hàng...</p>
                        </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                @if ($images->hasPages())
                    <div class="mt-12 flex justify-center">
                        {{ $images->links() }}
                    </div>
                @endif
            </div>
        </section>
    </main>
</x-layout-frontend>
