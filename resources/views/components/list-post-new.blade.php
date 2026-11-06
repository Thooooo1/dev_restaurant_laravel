<div class="space-y-4">
    @foreach ($posts as $index => $item)
        @php
            $imgSrc = !empty($item->image) && file_exists(public_path('images/post/' . $item->image))
                ? asset('images/post/' . $item->image)
                : asset('images/blog/single_blog_1.png');
            
            $rankColors = [
                0 => 'bg-orange-600 text-white',
                1 => 'bg-amber-600 text-white',
                2 => 'bg-stone-700 text-white',
                3 => 'bg-stone-500 text-white',
            ];
            $badgeColor = $rankColors[$index] ?? 'bg-stone-300 text-stone-700';
        @endphp
        <a href="{{ route('site.blog.detail', ['slug' => $item->slug]) }}" 
           class="group flex items-center gap-4 p-2 rounded-none sm:rounded-sm hover:bg-stone-50 transition-colors">
            <div class="relative w-20 h-20 sm:w-[84px] sm:h-[84px] rounded-none sm:rounded-sm overflow-hidden shrink-0 bg-stone-100 border border-stone-200/80 shadow-2xs">
                <img src="{{ $imgSrc }}" 
                     alt="{{ $item->title }}" 
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                <span class="absolute top-1 left-1 w-6 h-6 rounded-none sm:rounded-sm flex items-center justify-center text-xs font-black {{ $badgeColor }} shadow-xs">
                    {{ $index + 1 }}
                </span>
            </div>
            <div class="flex-1 min-w-0">
                <h4 class="text-sm sm:text-[15px] font-bold text-stone-900 group-hover:text-[#C25E00] transition-colors line-clamp-2 leading-snug mb-1.5">
                    {{ $item->title }}
                </h4>
                <div class="flex items-center gap-2.5 text-xs sm:text-[13px] text-stone-500 font-medium">
                    <span class="flex items-center gap-1.5 text-[#C25E00] font-bold">
                        <i class="far fa-eye text-xs"></i>
                        <span>{{ number_format($item->view, 0, ',', '.') }}</span>
                    </span>
                    <span class="text-stone-300">•</span>
                    <span>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') }}</span>
                </div>
            </div>
        </a>
    @endforeach
</div>
