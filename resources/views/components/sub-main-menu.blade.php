@if (count($menus)>0)
    <li class="relative group w-full md:w-auto" x-data="{ open: false }">
        <div class="flex items-center justify-between md:block w-full md:w-auto">
            <a class="text-black inline-block px-3 py-3 md:py-2 whitespace-nowrap" href="{{url($menu->link)}}">{{$menu->name}}</a>
            <button @click="open = !open" class="md:hidden px-3 py-3 focus:outline-none">
                <i class="fa fa-chevron-down transition-transform duration-300" :class="{'rotate-180': open}"></i>
            </button>
        </div>
        <ul :class="open ? 'block relative opacity-100 visible' : 'hidden md:block md:absolute md:invisible md:opacity-0 md:group-hover:visible md:group-hover:opacity-100'" 
            class="transition-all duration-300 ease-in-out bg-gray-100 w-full md:w-48 z-50 left-0 top-full md:shadow-lg">
            @foreach ($menus as $item )
                <li class="group border-b md:border-none">
                    <a class="text-black block p-3 hover:bg-orange-100 whitespace-nowrap" href="{{url($item->link)}}">{{$item->name}}</a>
                </li>
            @endforeach
        </ul>
    </li>
    @else
    <li class="relative group w-full md:w-auto border-b md:border-none">
        <a class="text-black inline-block px-3 py-3 md:py-2 w-full md:w-auto whitespace-nowrap" href="{{url($menu->link)}}">{{$menu->name}}</a>
    </li>
@endif
