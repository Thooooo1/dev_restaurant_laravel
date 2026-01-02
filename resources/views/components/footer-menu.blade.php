@if (count($menus)>0)
    <div class="relative group ">
        <ul class=" py-2">
            @foreach ($menus as $item )             
                    <a href="{{url($item->link)}}">
                        <li>- {{$item->name}}</li>
                    </a>               
            @endforeach
        </ul>
    </div>
    @else
    <li class="relative group">
        <a class="text-black inline-block px-3 p-3" href="{{url($menu->link)}}">{{$menu->name}}</a>
    </li>
@endif


