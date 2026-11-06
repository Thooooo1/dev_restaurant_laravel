@props([
    'title' => null,
    'subtitle' => null,
    'badge' => null,
    'breadcrumbs' => []
])

<!-- Clean, Elegant Page Header (Gom chung về một phía cân đối, tiêu đề H1 to rõ ràng, không bị lệch lơ lửng) -->
<section class="bg-stone-50/80 border-b border-stone-200/70 py-5 sm:py-6">
    <div class="container mx-auto px-4 max-w-7xl">
        <div class="flex flex-col gap-1.5">
            <!-- Page Title H1: Đóng vai trò tiêu đề chính rõ ràng, font đậm sắc nét -->
            @if ($title)
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-stone-900 tracking-tight">
                    {{ $title }}
                </h1>
            @endif
        </div>
    </div>
</section>
