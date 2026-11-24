<x-layout-frontend>
    <div class="min-h-[65vh] flex items-center justify-center bg-[#FAF6EE] py-16 px-4">
        <div class="max-w-md w-full text-center">
            <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-rose-100 text-rose-600 mb-6 shadow-inner">
                <span class="text-4xl font-black">500</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-stone-900 tracking-tight mb-3">
                Hệ Thống Đang Xử Lý
            </h1>
            <p class="text-stone-600 text-sm sm:text-base leading-relaxed mb-8">
                Đã xảy ra sự cố tạm thời trong quá trình xử lý yêu cầu. Đội ngũ kỹ thuật đã ghi nhận và đang kiểm tra. Vui lòng thử lại sau giây lát.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ route('site.home') }}" 
                   class="w-full sm:w-auto px-6 py-3 rounded-sm bg-[#C25E00] hover:bg-[#A34E00] text-white font-bold text-xs uppercase tracking-wider transition-all shadow-md">
                    <i class="fas fa-home mr-1.5"></i> Về Trang Chủ
                </a>
                <a href="{{ route('site.booking') }}" 
                   class="w-full sm:w-auto px-6 py-3 rounded-sm bg-white hover:bg-stone-50 text-stone-800 border border-stone-300 font-bold text-xs uppercase tracking-wider transition-all shadow-xs">
                    <i class="fas fa-calendar-alt mr-1.5"></i> Đặt Bàn Trước
                </a>
            </div>
        </div>
    </div>
</x-layout-frontend>
