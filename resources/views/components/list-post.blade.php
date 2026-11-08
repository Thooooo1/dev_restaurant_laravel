@if ($posts->isEmpty())
    <div class="bg-white rounded-none sm:rounded-sm border border-stone-200 p-12 text-center my-4 shadow-2xs">
        <div class="w-14 h-14 rounded-none sm:rounded-sm bg-orange-50 text-[#F47716] flex items-center justify-center mx-auto text-2xl mb-4 border border-orange-100">
            <i class="far fa-newspaper"></i>
        </div>
        <h3 class="text-base sm:text-lg font-bold text-stone-900 mb-1.5">Chưa có bài viết nào phù hợp</h3>
        <p class="text-stone-500 text-xs sm:text-sm max-w-md mx-auto mb-6 leading-relaxed">
            Quý khách vui lòng thử tìm kiếm bằng từ khóa khác hoặc quay lại danh sách toàn bộ bài viết của nhà hàng.
        </p>
        <a href="{{ route('site.blog') }}" 
           class="inline-flex items-center justify-center px-6 py-2.5 rounded-none sm:rounded-sm bg-[#F47716] hover:bg-[#EA580C] text-white font-bold text-xs uppercase tracking-wider transition-colors shadow-2xs">
            <span>Xem tất cả bài viết</span>
        </a>
    </div>
@else
    <div class="space-y-6">
        @foreach ($posts as $post)
            @php
                $imgSrc = !empty($post->image) && file_exists(public_path('images/post/' . $post->image))
                    ? asset('images/post/' . $post->image)
                    : (file_exists(public_path('images/gallery/' . $post->image))
                        ? asset('images/gallery/' . $post->image)
                        : asset('images/blog/single_blog_1.png'));
            @endphp
            <article class="group bg-white rounded-none sm:rounded-sm border border-stone-200 hover:border-stone-400 shadow-2xs transition-all duration-200 overflow-hidden flex flex-col sm:flex-row">
                
                <!-- Ảnh bài viết -->
                <div class="relative w-full sm:w-[42%] md:w-[38%] shrink-0 overflow-hidden bg-stone-100 aspect-[16/10] sm:aspect-auto">
                    <a href="{{ route('site.blog.detail', ['slug' => $post->slug]) }}" class="block w-full h-full min-h-[180px] sm:min-h-[220px]">
                        <img src="{{ $imgSrc }}" 
                             alt="{{ $post->title }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out">
                    </a>
                </div>

                <!-- Nội dung bài viết -->
                <div class="p-5 sm:p-6 flex flex-col justify-between flex-1 min-w-0">
                    <div>
                        <!-- Meta Tag & Ngày đăng -->
                        <div class="flex items-center gap-2.5 text-xs mb-2.5">
                            @if ($post->topic)
                                <span class="text-[#F47716] font-bold uppercase tracking-wider text-[11px]">
                                    {{ $post->topic->name }}
                                </span>
                                <span class="text-stone-300">•</span>
                            @endif
                            <span class="text-stone-400 font-medium text-xs flex items-center gap-1.5">
                                <i class="far fa-calendar-alt text-xs"></i>
                                {{ \Carbon\Carbon::parse($post->created_at)->format('d/m/Y') }}
                            </span>
                        </div>

                        <!-- Tiêu đề câu tự nhiên, rõ ràng -->
                        <h2 class="text-base sm:text-lg font-bold text-stone-900 group-hover:text-[#F47716] transition-colors leading-snug line-clamp-2 mb-2.5">
                            <a href="{{ route('site.blog.detail', ['slug' => $post->slug]) }}">
                                {{ $post->title }}
                            </a>
                        </h2>

                        <!-- Đoạn trích -->
                        @if (!empty($post->description))
                            <p class="text-stone-600 text-xs sm:text-sm leading-relaxed line-clamp-3 mb-4">
                                {{ $post->description }}
                            </p>
                        @endif
                    </div>

                    <!-- Footer thẻ bài viết -->
                    <div class="pt-3.5 border-t border-stone-100 flex items-center justify-between mt-auto">
                        <div class="flex items-center gap-2 text-xs text-stone-600 font-medium">
                            <img src="{{ asset('images/logo/logo.png') }}" alt="CIVILIZE Restaurant" class="h-5 w-auto object-contain">
                            <span class="text-stone-800 font-bold text-xs">CIVILIZE Restaurant</span>
                        </div>

                        <a href="{{ route('site.blog.detail', ['slug' => $post->slug]) }}" 
                           class="inline-flex items-center gap-1.5 text-xs font-bold text-[#F47716] group-hover:translate-x-1 transition-transform duration-200 uppercase tracking-wider">
                            <span>Đọc tiếp</span>
                            <i class="fas fa-chevron-right text-[10px]"></i>
                        </a>
                    </div>
                </div>

            </article>
        @endforeach
    </div>

    <!-- Phân trang thanh lịch, góc sắc nét chuẩn editorial -->
    @if ($posts->hasPages())
        <div class="flex justify-center items-center mt-10 mb-4">
            <nav class="flex items-center gap-1.5 flex-wrap">
                
                {{-- Previous Page Link --}}
                @if ($posts->onFirstPage())
                    <span class="px-3.5 py-2 text-xs font-bold text-stone-400 bg-stone-100 rounded-none sm:rounded-sm border border-stone-200 cursor-not-allowed" style="color: #9ca3af !important;">
                        <i class="fas fa-chevron-left mr-1" style="color: #9ca3af !important;"></i> Trước
                    </span>
                @else
                    <a href="{{ $posts->previousPageUrl() }}" 
                       class="px-3.5 py-2 text-xs font-bold text-stone-900 bg-white hover:bg-stone-100 border border-stone-200 rounded-none sm:rounded-sm transition-colors shadow-2xs"
                       style="color: #111827 !important; text-decoration: none;">
                        <i class="fas fa-chevron-left mr-1" style="color: #111827 !important;"></i> Trước
                    </a>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($posts->getUrlRange(1, $posts->lastPage()) as $page => $url)
                    @if ($page == $posts->currentPage())
                        <span class="w-9 h-9 flex items-center justify-center text-xs font-black bg-stone-900 text-white rounded-none sm:rounded-sm shadow-2xs"
                              style="color: #ffffff !important; background-color: #111827 !important;">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}" 
                           class="w-9 h-9 flex items-center justify-center text-xs font-bold text-stone-900 bg-white hover:bg-stone-100 border border-stone-200 rounded-none sm:rounded-sm transition-colors shadow-2xs"
                           style="color: #111827 !important; text-decoration: none;">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($posts->hasMorePages())
                    <a href="{{ $posts->nextPageUrl() }}" 
                       class="px-3.5 py-2 text-xs font-bold text-stone-900 bg-white hover:bg-stone-100 border border-stone-200 rounded-none sm:rounded-sm transition-colors shadow-2xs"
                       style="color: #111827 !important; text-decoration: none;">
                        Sau <i class="fas fa-chevron-right ml-1" style="color: #111827 !important;"></i>
                    </a>
                @else
                    <span class="px-3.5 py-2 text-xs font-bold text-stone-400 bg-stone-100 rounded-none sm:rounded-sm border border-stone-200 cursor-not-allowed" style="color: #9ca3af !important;">
                        Sau <i class="fas fa-chevron-right ml-1" style="color: #9ca3af !important;"></i>
                    </span>
                @endif
            </nav>
        </div>
    @endif
@endif
