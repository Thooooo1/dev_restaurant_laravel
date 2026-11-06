<section class="blogs-area py-12 sm:py-16 bg-white border-t border-stone-200/60">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 w-full">
        <!-- Section Title -->
        <div class="text-center max-w-2xl mx-auto mb-8 sm:mb-10">
            <span class="inline-block text-[#F47716] font-bold uppercase text-xs tracking-wider mb-1">
                Góc chia sẻ
            </span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-stone-900 tracking-tight mb-2.5">
                Cẩm nang ẩm thực & tin tức mới nhất
            </h2>
            <p class="text-stone-600 text-xs sm:text-sm">
                Những câu chuyện ẩm thực, kinh nghiệm chọn món ngon và các chương trình ưu đãi tri ân khách hàng tại nhà hàng CIVILIZE.
            </p>
        </div>

        <!-- Blog Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
            @foreach ($posts as $item)
                @php
                    $imgSrc = !empty($item->image) && file_exists(public_path('images/post/' . $item->image))
                        ? asset('images/post/' . $item->image)
                        : asset('images/blog/single_blog_1.png');
                @endphp
                <article class="group bg-white rounded-none sm:rounded-sm overflow-hidden border border-stone-200 shadow-2xs hover:shadow-md transition-all duration-300 flex flex-col justify-between">
                    <div>
                        <!-- Image Container -->
                        <div class="relative aspect-[16/10] overflow-hidden bg-stone-100">
                            <a href="{{ route('site.blog.detail', ['slug' => $item->slug]) }}" class="block w-full h-full">
                                <img src="{{ $imgSrc }}" 
                                     alt="{{ $item->title }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out">
                            </a>
                            <div class="absolute top-2.5 left-2.5 bg-stone-900/90 text-white text-[11px] font-bold px-2.5 py-0.5 rounded-none sm:rounded-xs shadow-xs">
                                <i class="far fa-calendar-alt text-amber-400 mr-1"></i>
                                {{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') }}
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-5 sm:p-6">
                            <h3 class="font-bold text-base sm:text-lg text-stone-900 group-hover:text-[#F47716] transition-colors line-clamp-2 leading-snug mb-2.5">
                                <a href="{{ route('site.blog.detail', ['slug' => $item->slug]) }}">
                                    {{ $item->title }}
                                </a>
                            </h3>
                            @if (!empty($item->description))
                                <p class="text-stone-600 text-xs sm:text-sm line-clamp-2 leading-relaxed mb-3">
                                    {{ $item->description }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="px-5 sm:px-6 py-3.5 border-t border-stone-100 flex items-center justify-between mt-auto bg-stone-50/70">
                        <div class="flex items-center gap-2">
                            <img src="{{ asset('images/logo/logo.png') }}" alt="CIVILIZE Restaurant" class="h-5 w-auto object-contain">
                            <span class="text-stone-700 font-bold text-xs">CIVILIZE Restaurant</span>
                        </div>
                        <a href="{{ route('site.blog.detail', ['slug' => $item->slug]) }}" 
                           class="inline-flex items-center gap-1.5 text-xs font-bold text-[#F47716] group-hover:translate-x-0.5 transition-transform uppercase tracking-wider">
                            <span>Chi tiết</span>
                            <i class="fas fa-chevron-right text-[10px]"></i>
                        </a>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-10 sm:mt-12 text-center">
            <a href="{{ route('site.blog') }}" 
               class="inline-flex items-center justify-center px-8 py-3 rounded-none sm:rounded-sm bg-white hover:bg-stone-50 text-stone-700 font-bold text-xs uppercase tracking-wider border border-stone-300 shadow-2xs transition-colors cursor-pointer">
                <span>Khám phá tất cả bài viết</span>
                <i class="fas fa-arrow-right text-[11px] ml-2"></i>
            </a>
        </div>
    </div>
</section>
