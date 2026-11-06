<nav class="shrink-0">
    <ul class="flex flex-col md:flex-row md:justify-center md:items-center md:flex-nowrap whitespace-nowrap gap-2 md:gap-5 lg:gap-7 xl:gap-8">
        @foreach ($menus as $menuitem )
            <x-sub-main-menu :menuitem="$menuitem" />
        @endforeach
    </ul>
    {{-- <div class="md:hidden visible text-black">
        <i class="fa-solid fa-bars-staggered"></i>
    </div> --}}
</nav>
