@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-center my-4">
        <div class="inline-flex items-center gap-1.5 flex-wrap justify-center bg-stone-50 p-2 rounded-lg border border-stone-200 shadow-2xs">
            
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="px-3.5 py-2 text-xs font-bold text-stone-400 bg-white rounded-md border border-stone-200 cursor-not-allowed inline-flex items-center gap-1" style="color: #9ca3af !important;">
                    <i class="fas fa-chevron-left text-[10px]" style="color: #9ca3af !important;"></i>
                    <span>Trước</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="px-3.5 py-2 text-xs font-bold text-stone-900 bg-white hover:bg-stone-100 border border-stone-200 rounded-md transition-colors shadow-2xs inline-flex items-center gap-1 cursor-pointer" style="color: #111827 !important; text-decoration: none;">
                    <i class="fas fa-chevron-left text-[10px]" style="color: #111827 !important;"></i>
                    <span>Trước</span>
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="w-8 text-center text-xs text-stone-400 font-bold select-none" style="color: #9ca3af !important;">{{ $element }}</span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="w-9 h-9 inline-flex items-center justify-center text-xs font-black bg-stone-900 text-white border border-stone-900 rounded-md shadow-xs" style="color: #ffffff !important; background-color: #111827 !important; border-color: #111827 !important;">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" class="w-9 h-9 inline-flex items-center justify-center text-xs font-bold text-stone-900 bg-white hover:bg-stone-100 border border-stone-200 rounded-md transition-colors shadow-2xs" style="color: #111827 !important; text-decoration: none;">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="px-3.5 py-2 text-xs font-bold text-stone-900 bg-white hover:bg-stone-100 border border-stone-200 rounded-md transition-colors shadow-2xs inline-flex items-center gap-1 cursor-pointer" style="color: #111827 !important; text-decoration: none;">
                    <span>Sau</span>
                    <i class="fas fa-chevron-right text-[10px]" style="color: #111827 !important;"></i>
                </a>
            @else
                <span class="px-3.5 py-2 text-xs font-bold text-stone-400 bg-white rounded-md border border-stone-200 cursor-not-allowed inline-flex items-center gap-1" style="color: #9ca3af !important;">
                    <span>Sau</span>
                    <i class="fas fa-chevron-right text-[10px]" style="color: #9ca3af !important;"></i>
                </span>
            @endif

        </div>
    </nav>
@endif
