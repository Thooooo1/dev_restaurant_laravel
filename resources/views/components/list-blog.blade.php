<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-start">

    <!-- ======================================================== -->
    <!-- CỘT TRÁI: DANH SÁCH BÀI VIẾT (8 CỘT)                     -->
    <!-- ======================================================== -->
    <div class="lg:col-span-8 space-y-6">
        
        <!-- Active Filter Indicator (Gọn gàng, góc sắc nét, không bo tròn AI) -->
        @if ($topicitem !== 'all' || request('search'))
            <div class="bg-white rounded-none sm:rounded-sm border border-stone-200 p-3 sm:p-3.5 flex items-center justify-between gap-3 text-xs shadow-2xs">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-stone-500 font-medium">Đang xem:</span>
                    @if ($topicitem !== 'all')
                        @php $curTopic = $topics->firstWhere('id', $topicitem); @endphp
                        @if ($curTopic)
                            <span class="px-2.5 py-1 rounded-none sm:rounded-sm bg-orange-50 text-[#F47716] font-bold flex items-center gap-1.5 border border-orange-200">
                                <i class="fas fa-tag text-[10px]"></i>
                                <span>{{ $curTopic->name }}</span>
                            </span>
                        @endif
                    @endif
                    @if (request('search'))
                        <span class="px-2.5 py-1 rounded-none sm:rounded-sm bg-orange-50 text-[#F47716] font-bold flex items-center gap-1.5 border border-orange-200">
                            <i class="fas fa-search text-[10px]"></i>
                            <span>"{{ request('search') }}"</span>
                        </span>
                    @endif
                </div>
                <a href="{{ route('site.blog') }}" 
                   class="text-stone-600 hover:text-[#F47716] font-semibold underline shrink-0 transition-colors">
                    Xem tất cả bài viết
                </a>
            </div>
        @endif

        <!-- List Post Component -->
        <x-list-post :topicitem="$topicitem" />

    </div>

    <!-- ======================================================== -->
    <!-- CỘT PHẢI: THANH BÊN BÁO CHÍ & TIỆC TINH HOA (4 CỘT)       -->
    <!-- ======================================================== -->
    <aside class="lg:col-span-4 space-y-6">
        
        <div class="bg-white rounded-none sm:rounded-sm border border-stone-200 p-6 space-y-6 shadow-2xs">
            
            <!-- 1. Tìm kiếm bài viết -->
            <div>
                <h3 class="font-bold text-stone-900 text-xs sm:text-sm uppercase tracking-wider mb-3 flex items-center justify-between">
                    <span>Tìm kiếm bài viết</span>
                    <i class="fas fa-search text-stone-300 text-xs"></i>
                </h3>
                <form action="{{ route('site.blog') }}" method="GET" class="relative">
                    @if ($topicitem !== 'all')
                        <input type="hidden" name="topic" value="{{ $topicitem }}">
                    @endif
                    <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400 text-xs"></i>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Nhập từ khóa tìm kiếm..." 
                           class="w-full pl-9 pr-16 py-2.5 text-xs sm:text-sm bg-stone-50 hover:bg-white focus:bg-white border border-stone-200 rounded-none sm:rounded-sm focus:outline-none focus:border-[#F47716] transition">
                    <button type="submit" 
                            class="absolute right-1 top-1/2 -translate-y-1/2 px-3 py-1.5 bg-[#F47716] hover:bg-[#EA580C] text-white font-bold text-xs rounded-none sm:rounded-sm transition-colors cursor-pointer">
                        Tìm
                    </button>
                </form>
            </div>

            <!-- 2. Chủ đề bài viết -->
            <div class="pt-5 border-t border-stone-100">
                <h3 class="font-bold text-stone-900 text-xs sm:text-sm uppercase tracking-wider mb-3">
                    Chủ đề bài viết
                </h3>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('site.blog') }}" 
                           class="flex items-center justify-between px-3 py-2 border-l-2 transition-all text-xs sm:text-sm {{ $topicitem === 'all' ? 'border-[#F47716] bg-orange-50/70 text-[#F47716] font-bold' : 'border-transparent text-stone-700 hover:text-[#F47716] hover:border-stone-300 hover:bg-stone-50 font-medium' }}">
                            <span>Tất cả bài viết</span>
                            <span class="text-xs font-semibold {{ $topicitem === 'all' ? 'text-[#F47716]' : 'text-stone-400' }}">({{ $totalAllPosts }})</span>
                        </a>
                    </li>
                    @foreach ($topics as $item)
                        @if ($item->posts_count > 0)
                            <li>
                                <a href="{{ route('site.blog', ['topic' => $item->id]) }}" 
                                   class="flex items-center justify-between px-3 py-2 border-l-2 transition-all text-xs sm:text-sm {{ $topicitem == $item->id ? 'border-[#F47716] bg-orange-50/70 text-[#F47716] font-bold' : 'border-transparent text-stone-700 hover:text-[#F47716] hover:border-stone-300 hover:bg-stone-50 font-medium' }}">
                                    <span class="truncate pr-2">{{ $item->name }}</span>
                                    <span class="text-xs font-semibold {{ $topicitem == $item->id ? 'text-[#F47716]' : 'text-stone-400' }}">({{ $item->posts_count }})</span>
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>

            <!-- 3. Bài viết mới nhất (Recent Posts) -->
            @if (isset($recentPosts) && $recentPosts->count() > 0)
                <div class="pt-5 border-t border-stone-100">
                    <h3 class="font-bold text-stone-900 text-xs sm:text-sm uppercase tracking-wider mb-3.5 flex items-center justify-between">
                        <span>Bài viết mới nhất</span>
                        <span class="w-1.5 h-1.5 rounded-full bg-[#F47716]"></span>
                    </h3>
                    <div class="space-y-3.5">
                        @foreach ($recentPosts as $rPost)
                            @php
                                $rImg = !empty($rPost->image) && file_exists(public_path('images/post/' . $rPost->image))
                                    ? asset('images/post/' . $rPost->image)
                                    : asset('images/banner/vietnamese_banquet_feast.jpg');
                            @endphp
                            <a href="{{ route('site.blog.detail', ['slug' => $rPost->slug]) }}" 
                               class="group flex items-start gap-3 p-1.5 -mx-1.5 rounded-none sm:rounded-sm hover:bg-stone-50 transition-colors">
                                <div class="w-16 h-16 shrink-0 rounded-none sm:rounded-sm overflow-hidden bg-stone-100 border border-stone-200">
                                    <img src="{{ $rImg }}" 
                                         alt="{{ $rPost->title }}" 
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-1.5 text-[10px] text-stone-400 mb-1">
                                        <i class="far fa-calendar-alt text-[9px]"></i>
                                        <span>{{ $rPost->created_at ? $rPost->created_at->format('d/m/Y') : 'Mới cập nhật' }}</span>
                                    </div>
                                    <h4 class="text-xs font-bold text-stone-800 group-hover:text-[#F47716] transition-colors line-clamp-2 leading-snug">
                                        {{ $rPost->title }}
                                    </h4>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- 4. Khoảnh khắc quán (6 ảnh gốc) -->
            <div class="pt-5 border-t border-stone-100">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-bold text-stone-900 text-xs sm:text-sm uppercase tracking-wider">
                        Khoảnh khắc quán
                    </h3>
                    <a href="{{ route('site.image') }}" class="text-xs font-bold text-[#F47716] hover:underline">
                        Xem tất cả
                    </a>
                </div>
                @php
                    $sidebarMoments = [
                        ['img' => 'images/gallery/instagram1.png', 'title' => 'Khoảnh khắc ẩm thực CIVILIZE 1'],
                        ['img' => 'images/gallery/instagram2.png', 'title' => 'Khoảnh khắc ẩm thực CIVILIZE 2'],
                        ['img' => 'images/gallery/instagram3.png', 'title' => 'Khoảnh khắc ẩm thực CIVILIZE 3'],
                        ['img' => 'images/gallery/instagram4.png', 'title' => 'Khoảnh khắc ẩm thực CIVILIZE 4'],
                        ['img' => 'images/gallery/instagram5.png', 'title' => 'Khoảnh khắc ẩm thực CIVILIZE 5'],
                        ['img' => 'images/gallery/instagram6.png', 'title' => 'Khoảnh khắc ẩm thực CIVILIZE 6'],
                    ];
                @endphp
                <div class="grid grid-cols-3 gap-2">
                    @foreach ($sidebarMoments as $moment)
                        <a href="{{ route('site.image') }}" 
                           class="group relative aspect-square rounded-none sm:rounded-sm overflow-hidden bg-stone-100 block border border-stone-200"
                           title="{{ $moment['title'] }}">
                            <img src="{{ asset($moment['img']) }}" 
                                 alt="{{ $moment['title'] }}" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            <div class="absolute inset-0 bg-stone-900/0 group-hover:bg-stone-900/20 transition-colors duration-200 flex items-center justify-center">
                                <i class="fas fa-eye text-white opacity-0 group-hover:opacity-100 transition-opacity duration-200 text-xs"></i>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- 5. Thẻ từ khóa thịnh hành (Popular Tags) -->
            @if (isset($hotTags) && count($hotTags) > 0)
                <div class="pt-5 border-t border-stone-100">
                    <h3 class="font-bold text-stone-900 text-xs sm:text-sm uppercase tracking-wider mb-3">
                        Từ khóa thịnh hành
                    </h3>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($hotTags as $tag)
                            <a href="{{ route('site.blog', ['search' => $tag]) }}" 
                               class="text-[11px] px-2.5 py-1 bg-stone-50 hover:bg-orange-50 text-stone-600 hover:text-[#F47716] border border-stone-200 hover:border-orange-200 rounded-none sm:rounded-sm transition-colors font-medium">
                                #{{ $tag }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>

    </aside>

</div>
