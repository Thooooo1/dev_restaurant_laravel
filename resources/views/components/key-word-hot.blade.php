<div class="inline-flex items-center flex-wrap justify-center gap-x-1.5 gap-y-1 text-xs text-stone-500">
    <span class="text-orange-600 font-bold">
        Gợi ý:
    </span>
    @foreach ($keywords->take(5) as $item)
        <a href="{{ route('site.product.gridview', ['search' => $item->title]) }}" 
           class="hot-keyword-item text-stone-600 hover:text-orange-600 transition-colors hover:underline font-medium cursor-pointer"
           data-keyword="{{ $item->title }}">
            {{ $item->title }}
        </a>{{ !$loop->last ? ',' : '' }}
    @endforeach
</div>
