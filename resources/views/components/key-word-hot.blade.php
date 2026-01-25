<ul class="flex flex-wrap gap-3 *:px-4 *:py-2 *:rounded-x1 *:border *:bg-white">
    @foreach ($keywords as $item)
        <li class="hover:bg-gray-200"><a href="{{ route('site.product.detail', ['slug' => $item->slug]) }}">{{$item->title}}</a></li>
    @endforeach
   
</ul>