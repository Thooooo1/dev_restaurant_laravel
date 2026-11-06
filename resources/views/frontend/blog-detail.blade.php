<x-layout-frontend>
    <main class="bg-stone-50/60 min-h-screen">
        <!-- Page Hero Header with Breadcrumbs -->
        <x-page-hero 
            :title="$post->title" 
            :breadcrumbs="[
                ['name' => 'Bài viết', 'url' => route('site.blog')],
                ['name' => $post->title, 'url' => '']
            ]" 
        />

        <article class="py-10 sm:py-14">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 w-full">
                <!-- Meta Info Bar (Góc sắc nét, tinh tế) -->
                <div class="flex flex-wrap items-center justify-between gap-4 py-3.5 px-6 bg-white rounded-none sm:rounded-sm border border-stone-200 shadow-2xs mb-8 text-xs sm:text-sm text-stone-600">
                    <div class="flex items-center gap-4 flex-wrap">
                        <span class="flex items-center gap-1.5 font-bold text-stone-800">
                            <i class="far fa-calendar-alt text-[#F47716]"></i>
                            {{ \Carbon\Carbon::parse($post->created_at)->format('d/m/Y') }}
                        </span>
                        @if ($post->topic)
                            <span class="flex items-center gap-1.5 font-semibold text-[#F47716] border-l border-stone-200 pl-4">
                                {{ $post->topic->name }}
                            </span>
                        @endif
                        <span class="flex items-center gap-1.5 font-medium text-stone-500 border-l border-stone-200 pl-4">
                            <i class="far fa-eye text-stone-400 text-xs"></i>
                            {{ number_format($post->view, 0, ',', '.') }} lượt xem
                        </span>
                        <span class="flex items-center gap-1.5 font-medium text-stone-700 border-l border-stone-200 pl-4">
                            <img src="{{ asset('images/logo/logo.png') }}" alt="CIVILIZE Restaurant" class="h-4 w-auto object-contain">
                            <span>CIVILIZE Restaurant</span>
                        </span>
                    </div>
                    <a href="{{ route('site.blog') }}" class="text-[#F47716] hover:text-[#EA580C] font-bold flex items-center gap-1.5 transition-colors text-xs uppercase tracking-wider">
                        <i class="fas fa-arrow-left text-[10px]"></i>
                        <span>Quay lại danh sách</span>
                    </a>
                </div>

                <!-- Featured Image -->
                @php
                    $detailImg = !empty($post->image) && file_exists(public_path('images/post/' . $post->image))
                        ? asset('images/post/' . $post->image)
                        : (file_exists(public_path('images/gallery/' . $post->image))
                            ? asset('images/gallery/' . $post->image)
                            : asset('images/blog/single_blog_1.png'));
                @endphp
                @if (!empty($post->image))
                    <div class="mb-8 rounded-none sm:rounded-sm overflow-hidden shadow-2xs border border-stone-200 bg-stone-100">
                        <img class="w-full h-auto object-cover max-h-[500px]" 
                             src="{{ $detailImg }}" 
                             alt="{{ $post->title }}">
                    </div>
                @endif

                <!-- Article Content -->
                <div class="bg-white rounded-none sm:rounded-sm p-6 sm:p-10 shadow-2xs border border-stone-200 mb-10 prose max-w-none text-stone-800 leading-relaxed text-sm sm:text-base">
                    {!! $post->content !!}
                </div>

                <!-- Back link & CTA -->
                <div class="flex flex-wrap items-center justify-center gap-4">
                    <a href="{{ route('site.booking') }}" 
                       class="inline-flex items-center justify-center px-6 py-3 rounded-none sm:rounded-sm bg-[#F47716] hover:bg-[#EA580C] text-white font-bold text-xs uppercase tracking-wider transition-colors shadow-2xs">
                        <span>Đặt bàn tại nhà hàng</span>
                    </a>
                    <a href="{{ route('site.blog') }}" 
                       class="inline-flex items-center justify-center px-6 py-3 rounded-none sm:rounded-sm bg-white hover:bg-stone-50 text-stone-700 font-bold text-xs uppercase tracking-wider border border-stone-300 transition-colors shadow-2xs">
                        <span>Xem tất cả bài viết khác</span>
                    </a>
                </div>
            </div>
        </article>
    </main>
</x-layout-frontend>
