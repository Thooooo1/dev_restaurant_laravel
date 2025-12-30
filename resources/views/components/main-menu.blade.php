<nav class = "flex justify-between items-center">
    <ul class="flex">
        @foreach ($menus as $menuitem )
            <x-sub-main-menu :menuitem="$menuitem" />
        @endforeach
    </ul>
    <div class="md::hidden visible text-black">
        {{-- <i class="fa-solid fa-bars-staggered"></i> --}}
    </div>
</nav>