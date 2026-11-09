@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-center my-2 max-w-full">
        <div class="flex items-center gap-1 sm:gap-1.5 flex-nowrap overflow-x-auto py-1 px-1 max-w-full select-none" style="scrollbar-width: none; -ms-overflow-style: none;">
            
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="h-8 px-2.5 sm:px-3 text-xs font-semibold text-gray-400 bg-white rounded-md border border-gray-200 cursor-not-allowed inline-flex items-center gap-1 shrink-0 whitespace-nowrap" style="color: #9ca3af !important;">
                    <i class="fas fa-chevron-left text-[10px]" style="color: #9ca3af !important;"></i>
                    <span>Trước</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="h-8 px-2.5 sm:px-3 text-xs font-semibold text-gray-800 hover:text-[#F47716] bg-white hover:bg-orange-50/50 border border-gray-200 hover:border-orange-200 rounded-md transition-colors shadow-2xs inline-flex items-center gap-1 cursor-pointer shrink-0 whitespace-nowrap" style="text-decoration: none;">
                    <i class="fas fa-chevron-left text-[10px]"></i>
                    <span>Trước</span>
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="h-8 min-w-[28px] sm:min-w-[32px] inline-flex items-center justify-center text-xs text-gray-400 font-bold select-none shrink-0" style="color: #9ca3af !important;">{{ $element }}</span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="h-8 min-w-[28px] sm:min-w-[32px] px-2 inline-flex items-center justify-center text-xs font-black bg-stone-900 text-white border border-stone-900 rounded-md shadow-xs shrink-0 whitespace-nowrap" style="color: #ffffff !important; background-color: #111827 !important; border-color: #111827 !important;">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" class="h-8 min-w-[28px] sm:min-w-[32px] px-2 inline-flex items-center justify-center text-xs font-medium text-gray-700 hover:text-[#F47716] bg-white hover:bg-orange-50/50 border border-gray-200 hover:border-orange-200 rounded-md transition-colors shadow-2xs shrink-0 whitespace-nowrap" style="text-decoration: none;">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="h-8 px-2.5 sm:px-3 text-xs font-semibold text-gray-800 hover:text-[#F47716] bg-white hover:bg-orange-50/50 border border-gray-200 hover:border-orange-200 rounded-md transition-colors shadow-2xs inline-flex items-center gap-1 cursor-pointer shrink-0 whitespace-nowrap" style="text-decoration: none;">
                    <span>Sau</span>
                    <i class="fas fa-chevron-right text-[10px]"></i>
                </a>
            @else
                <span class="h-8 px-2.5 sm:px-3 text-xs font-semibold text-gray-400 bg-white rounded-md border border-gray-200 cursor-not-allowed inline-flex items-center gap-1 shrink-0 whitespace-nowrap" style="color: #9ca3af !important;">
                    <span>Sau</span>
                    <i class="fas fa-chevron-right text-[10px]" style="color: #9ca3af !important;"></i>
                </span>
            @endif

        </div>
    </nav>
@endif
