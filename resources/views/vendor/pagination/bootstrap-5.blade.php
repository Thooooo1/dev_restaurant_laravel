@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-center my-4">
        <div class="inline-flex items-center gap-1.5 flex-wrap justify-center bg-stone-50 p-2 rounded-none sm:rounded-sm border border-stone-200 shadow-2xs">
            
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="px-3.5 py-2 text-xs font-bold text-stone-300 bg-white rounded-none sm:rounded-sm border border-stone-200 cursor-not-allowed inline-flex items-center gap-1">
                    <i class="fas fa-chevron-left text-[10px]"></i>
                    <span>Trước</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="px-3.5 py-2 text-xs font-bold text-stone-700 bg-white hover:bg-[#F47716] hover:text-white border border-stone-200 rounded-none sm:rounded-sm transition-colors shadow-2xs inline-flex items-center gap-1 cursor-pointer">
                    <i class="fas fa-chevron-left text-[10px]"></i>
                    <span>Trước</span>
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="w-8 text-center text-xs text-stone-400 font-bold select-none">{{ $element }}</span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="w-9 h-9 inline-flex items-center justify-center text-xs font-black bg-[#F47716] text-white rounded-none sm:rounded-sm shadow-2xs">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" class="w-9 h-9 inline-flex items-center justify-center text-xs font-bold text-stone-700 bg-white hover:bg-stone-100 hover:text-[#F47716] border border-stone-200 rounded-none sm:rounded-sm transition-colors shadow-2xs">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="px-3.5 py-2 text-xs font-bold text-stone-700 bg-white hover:bg-[#F47716] hover:text-white border border-stone-200 rounded-none sm:rounded-sm transition-colors shadow-2xs inline-flex items-center gap-1 cursor-pointer">
                    <span>Sau</span>
                    <i class="fas fa-chevron-right text-[10px]"></i>
                </a>
            @else
                <span class="px-3.5 py-2 text-xs font-bold text-stone-300 bg-white rounded-none sm:rounded-sm border border-stone-200 cursor-not-allowed inline-flex items-center gap-1">
                    <span>Sau</span>
                    <i class="fas fa-chevron-right text-[10px]"></i>
                </span>
            @endif

        </div>
    </nav>
@endif
