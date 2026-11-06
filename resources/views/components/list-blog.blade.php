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
    <!-- CỘT PHẢI: THANH BÊN BÁO CHÍ TINH GỌN (4 CỘT)              -->
    <!-- ======================================================== -->
    <aside class="lg:col-span-4 sticky top-24">
        
        <div class="bg-white rounded-none sm:rounded-sm border border-stone-200 p-6 space-y-6 shadow-2xs">
            
            <!-- 1. Tìm kiếm bài viết -->
            <div>
                <h3 class="font-bold text-stone-900 text-xs sm:text-sm uppercase tracking-wider mb-3">
                    Tìm kiếm bài viết
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

            <!-- 2. Chủ đề bài viết (Phong cách chuyên mục báo chí phẳng, không bo tròn viên thuốc) -->
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

            <!-- 3. Khoảnh khắc quán (Khôi phục toàn bộ 6 ảnh gốc của dự án theo đúng yêu cầu) -->
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

        </div>

    </aside>

</div>
