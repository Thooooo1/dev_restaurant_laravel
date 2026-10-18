@if (count($menus)>0)
    <li class="relative group w-full md:w-auto" x-data="{ open: false }">
        <div class="flex items-center justify-between md:block w-full md:w-auto">
            <a class="text-gray-700 font-bold inline-block px-4 py-3 md:py-2.5 whitespace-nowrap rounded-lg hover:bg-gradient-to-r hover:from-orange-100 hover:to-yellow-100 hover:text-orange-600 transition-all duration-300 relative text-lg" href="{{url($menu->link)}}">
                {{$menu->name}}
                <span class="absolute bottom-0 left-1/2 -translate-x-1/2 w-0 h-0.5 bg-gradient-to-r from-orange-500 to-yellow-500 group-hover:w-4/5 transition-all duration-300"></span>
            </a>
            <button @click="open = !open" class="md:hidden px-3 py-3 focus:outline-none text-orange-600">
                <i class="fa fa-chevron-down transition-transform duration-300" :class="{'rotate-180': open}"></i>
            </button>
        </div>
        <ul :class="open ? 'block relative opacity-100 visible' : 'hidden md:block md:absolute md:invisible md:opacity-0 md:group-hover:visible md:group-hover:opacity-100'" 
            class="transition-all duration-300 ease-in-out bg-white w-full md:w-56 z-50 left-0 top-full md:mt-2 md:rounded-xl md:shadow-2xl md:border-2 md:border-orange-200 overflow-hidden">
            @foreach ($menus as $item )
                <li class="group">
                    <a class="text-gray-700 block px-5 py-3.5 hover:bg-gradient-to-r from-orange-50 to-yellow-50 hover:text-orange-600 hover:pl-6 transition-all duration-300 whitespace-nowrap font-semibold border-b border-orange-50 last:border-b-0 text-base" href="{{url($item->link)}}">
                        <i class="fas fa-angle-right mr-2 text-orange-400 group-hover:translate-x-1 transition-transform duration-300"></i>
                        {{$item->name}}
                    </a>
                </li>
            @endforeach
        </ul>
    </li>
    @else
    <li class="relative group w-full md:w-auto">
        <a class="text-gray-700 font-bold inline-block px-4 py-3 md:py-2.5 w-full md:w-auto whitespace-nowrap rounded-lg hover:bg-gradient-to-r hover:from-orange-100 hover:to-yellow-100 hover:text-orange-600 transition-all duration-300 relative text-lg" href="{{url($menu->link)}}">
            {{$menu->name}}
            <span class="absolute bottom-0 left-1/2 -translate-x-1/2 w-0 h-0.5 bg-gradient-to-r from-orange-500 to-yellow-500 group-hover:w-4/5 transition-all duration-300"></span>
        </a>
    </li>
@endif
