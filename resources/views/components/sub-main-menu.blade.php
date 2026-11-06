@php
    $linkPath = trim($menu->link, '/');
    $isActive = false;
    if ($linkPath === '' || $linkPath === '/') {
        $isActive = request()->is('/');
    } else {
        $isActive = request()->is($linkPath) || request()->is($linkPath . '/*');
        if ($linkPath === 'danh-sach' && (request()->is('thuc-don*') || request()->is('chi-tiet-san-pham*'))) {
            $isActive = true;
        }
        if ($linkPath === 'bai-viet' && (request()->is('tin-tuc*') || request()->is('chi-tiet-bai-viet*'))) {
            $isActive = true;
        }
    }
@endphp

@if (count($menus)>0)
    <li class="relative group w-full md:w-auto shrink-0" x-data="{ open: false }">
        <div class="flex items-center justify-between md:block w-full md:w-auto">
            <a class="{{ $isActive ? 'text-[#C25E00] font-bold' : 'text-stone-800 font-semibold hover:text-[#C25E00]' }} inline-flex items-center gap-1.5 px-3 py-2 text-[15px] sm:text-[15.5px] transition-colors relative whitespace-nowrap shrink-0" href="{{url($menu->link)}}">
                <span class="whitespace-nowrap">{{$menu->name}}</span>
                <i class="fas fa-chevron-down text-xs text-stone-400 group-hover:text-stone-600 transition-transform duration-200 group-hover:rotate-180"></i>
                @if ($isActive)
                    <span class="absolute bottom-0 left-3 right-3 h-[2.5px] bg-[#C25E00] rounded-full"></span>
                @endif
            </a>
            <button @click="open = !open" class="md:hidden px-3 py-2 text-stone-500 focus:outline-none">
                <i class="fa fa-chevron-down transition-transform duration-200" :class="{'rotate-180': open}"></i>
            </button>
        </div>
        <ul :class="open ? 'block relative opacity-100 visible' : 'hidden md:block md:absolute md:invisible md:opacity-0 md:group-hover:visible md:group-hover:opacity-100'" 
            class="transition-all duration-200 ease-out bg-white w-full md:w-56 z-50 left-0 top-full md:mt-1 md:rounded-sm md:shadow-lg md:border md:border-stone-200/90 overflow-hidden py-2">
            @foreach ($menus as $item )
                @php
                    $subPath = trim($item->link, '/');
                    $isSubActive = request()->is($subPath) || request()->is($subPath . '/*');
                @endphp
                <li>
                    <a class="{{ $isSubActive ? 'text-[#C25E00] bg-stone-50 font-bold' : 'text-stone-700 hover:bg-stone-50 hover:text-stone-900 font-medium' }} block px-4 py-2.5 text-[14.5px] transition-colors whitespace-nowrap" href="{{url($item->link)}}">
                        {{$item->name}}
                    </a>
                </li>
            @endforeach
        </ul>
    </li>
@else
    <li class="relative w-full md:w-auto shrink-0">
        <a class="{{ $isActive ? 'text-[#C25E00] font-bold' : 'text-stone-800 font-semibold hover:text-[#C25E00]' }} inline-block px-3 py-2 text-[15px] sm:text-[15.5px] transition-colors relative whitespace-nowrap shrink-0" href="{{url($menu->link)}}">
            <span class="whitespace-nowrap">{{$menu->name}}</span>
            @if ($isActive)
                <span class="absolute bottom-0 left-3 right-3 h-[2.5px] bg-[#C25E00] rounded-full"></span>
            @endif
        </a>
    </li>
@endif
