<ul class="flex flex-wrap gap-5 *:px-4 *:py-2 *:rounded-x1 *:border *:bg-white">
    @foreach ($keywords as $item)
        <li class="hover:bg-gray-200"><a href="#">{{$item->title}}</a></li>
    @endforeach
   
</ul>