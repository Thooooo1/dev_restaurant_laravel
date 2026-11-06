<section class="py-16 md:py-24 bg-white relative">
    <div class="container mx-auto px-4 max-w-7xl">
        <!-- Section Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 pb-4 border-b border-stone-100">
            <div>
                <span class="inline-block text-orange-600 font-extrabold uppercase text-xs tracking-widest mb-2">
                    Món Mới Đặc Sắc
                </span>
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-black text-stone-900 tracking-tight">
                    Sáng Tạo Mới Từ Bếp Trưởng
                </h2>
            </div>
            <a href="{{ route('site.product.gridview') }}" 
               class="text-orange-600 hover:text-orange-700 font-bold text-xs sm:text-sm inline-flex items-center gap-1.5 mt-3 md:mt-0 transition-colors group">
                <span>Khám phá thêm</span>
                <i class="fas fa-arrow-right text-xs transform group-hover:translate-x-1 transition-transform"></i>
            </a>
        </div>

        <!-- Dishes Grid (8 món mới) -->
        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
            @foreach ($products as $item)
                <x-product-card :productitem="$item" />
            @endforeach
        </div>
    </div>
</section>
