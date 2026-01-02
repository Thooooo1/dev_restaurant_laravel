@if (count($menus)>0)
    <li class="relative group ">
        <a class="text-black inline-block px-3 p-3" href="{{url($menu->link)}}">{{$menu->name}}</a>
        <ul class="transition-all duration-700 ease-in-out absolute invisible opacity-0 group-hover:visible group-hover:opacity-100 bg-gray-100 ">
            @foreach ($menus as $item )
                <li class="group">
                    <a class="text-black block p-3" href="{{url($item->link)}}">{{$item->name}}</a>
                </li>
            @endforeach
        </ul>
    </li>
    @else
    <li class="relative group">
        <a class="text-black inline-block px-3 p-3" href="{{url($menu->link)}}">{{$menu->name}}</a>
    </li>
@endif
