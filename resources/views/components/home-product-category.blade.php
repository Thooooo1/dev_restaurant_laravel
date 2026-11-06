@forelse ($products as $productitem)
    <x-product-card :productitem="$productitem"/>
@empty
    <div class="col-span-full py-12 text-center">
        <div class="w-16 h-16 rounded-none sm:rounded-sm bg-stone-100 text-stone-400 flex items-center justify-center mx-auto mb-3">
            <i class="fas fa-utensils text-2xl"></i>
        </div>
        <p class="text-stone-500 text-sm">Các món trong danh mục này đang được cập nhật. Quý khách vui lòng chọn danh mục khác nhé!</p>
    </div>
@endforelse
