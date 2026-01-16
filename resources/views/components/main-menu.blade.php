<nav class="w-full">
    <ul class="flex flex-col md:flex-row md:justify-center md:items-center gap-2 md:gap-4">
        @foreach ($menus as $menuitem )
            <x-sub-main-menu :menuitem="$menuitem" />
        @endforeach
    </ul>
    {{-- <div class="md:hidden visible text-black">
        <i class="fa-solid fa-bars-staggered"></i>
    </div> --}}
</nav>
