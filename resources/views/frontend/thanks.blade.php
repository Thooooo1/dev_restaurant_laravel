<x-layout-frontend>
    <main class="bg-[#FAF8F5] min-h-screen pb-16">
        <x-page-hero 
            title="Thanh Toán Thành Công" 
            :breadcrumbs="[
                ['name' => 'Thanh toán thành công', 'url' => '']
            ]" 
        />

        <div class="container mx-auto px-4 max-w-lg pt-10 sm:pt-14">
            <div class="bg-white rounded-none sm:rounded-sm border-t-4 border-t-[#C25E00] border-x border-b border-stone-200/90 shadow-md p-8 sm:p-10 text-center">
                <div class="w-16 h-16 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center mx-auto mb-4 text-2xl">
                    <i class="fa-solid fa-check"></i>
                </div>
                <h2 class="text-2xl font-black font-serif text-stone-900 mb-2">Đặt Hàng Hoàn Tất!</h2>
                <p class="text-xs sm:text-sm text-stone-600 leading-relaxed mb-6">
                    Món ăn của bạn đang được các đầu bếp chuẩn bị nóng sốt. Nhân viên nhà hàng sẽ liên hệ xác nhận trong giây lát.
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                    <a href="{{ route('site.orders') }}" class="w-full sm:w-auto px-6 py-3 bg-[#C25E00] hover:bg-[#A34E00] text-white font-bold text-xs uppercase tracking-widest rounded-none sm:rounded-sm transition-colors shadow-xs">
                        Xem đơn hàng
                    </a>
                    <a href="{{ route('site.home') }}" class="w-full sm:w-auto px-6 py-3 bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold text-xs uppercase tracking-widest rounded-none sm:rounded-sm transition-colors">
                        Về trang chủ
                    </a>
                </div>
            </div>
        </div>
    </main>
</x-layout-frontend>