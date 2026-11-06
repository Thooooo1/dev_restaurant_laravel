<x-layout-frontend>
    <main class="bg-[#FAF8F5] min-h-screen pb-16">
        <x-page-hero 
            title="Chính Sách Liên Hệ & Hỗ Trợ" 
            subtitle="Đội ngũ chăm sóc khách hàng CIVILIZE Restaurant luôn sẵn sàng lắng nghe và phục vụ quý khách." 
            :breadcrumbs="[
                ['name' => 'Chính sách', 'url' => ''],
                ['name' => 'Chính sách liên hệ', 'url' => '']
            ]" 
        />

        <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-4xl pt-8 sm:pt-12">
            <div class="bg-white shadow-2xs border border-stone-200/90 rounded-none sm:rounded-sm p-8 sm:p-12">
                <h2 class="text-xl sm:text-2xl font-black font-serif text-stone-900 mb-4">Thông Tin Liên Hệ Trực Tiếp</h2>
                <p class="text-stone-700 leading-relaxed mb-6">
                    Quý khách có thể kết nối với chúng tôi qua các kênh tiếp đón chính thức:
                </p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                    <div class="p-5 bg-stone-50 border border-stone-200 rounded-none sm:rounded-sm">
                        <i class="fa-solid fa-location-dot text-[#C25E00] text-lg mb-2 block"></i>
                        <h4 class="font-bold text-stone-900 text-sm mb-1">Địa chỉ nhà hàng</h4>
                        <p class="text-xs text-stone-600 leading-relaxed">4H45+988, Phúc Thọ, Hà Nội, Việt Nam</p>
                    </div>
                    <div class="p-5 bg-stone-50 border border-stone-200 rounded-none sm:rounded-sm">
                        <i class="fa-solid fa-phone text-[#C25E00] text-lg mb-2 block"></i>
                        <h4 class="font-bold text-stone-900 text-sm mb-1">Hotline đặt bàn</h4>
                        <p class="text-xs text-stone-600 leading-relaxed">0981 487 674<br>(Hỗ trợ 24/7)</p>
                    </div>
                    <div class="p-5 bg-stone-50 border border-stone-200 rounded-none sm:rounded-sm">
                        <i class="fa-solid fa-envelope text-[#C25E00] text-lg mb-2 block"></i>
                        <h4 class="font-bold text-stone-900 text-sm mb-1">Thư điện tử</h4>
                        <p class="text-xs text-stone-600 leading-relaxed">civilizerestaurant@gmail.com</p>
                    </div>
                </div>
            
                <h3 class="text-lg sm:text-xl font-bold font-serif text-stone-900 mb-4 pb-2 border-b border-stone-200">Gửi Tin Nhắn Cho Nhà Hàng</h3>
                <form action="{{ route('site.contact.submit') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="name" class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1.5">Họ và tên</label>
                            <input type="text" id="name" name="name" required class="w-full px-4 py-3 bg-stone-50/60 border border-stone-300 rounded-none sm:rounded-sm text-stone-900 text-sm focus:outline-none focus:bg-white focus:border-[#C25E00]" placeholder="Nguyễn Văn A">
                        </div>
                        <div>
                            <label for="email" class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1.5">Email liên hệ</label>
                            <input type="email" id="email" name="email" required class="w-full px-4 py-3 bg-stone-50/60 border border-stone-300 rounded-none sm:rounded-sm text-stone-900 text-sm focus:outline-none focus:bg-white focus:border-[#C25E00]" placeholder="email@domain.com">
                        </div>
                    </div>
            
                    <div>
                        <label for="message" class="block text-xs font-bold uppercase tracking-wider text-stone-700 mb-1.5">Nội dung yêu cầu</label>
                        <textarea id="message" name="content" rows="4" required class="w-full px-4 py-3 bg-stone-50/60 border border-stone-300 rounded-none sm:rounded-sm text-stone-900 text-sm focus:outline-none focus:bg-white focus:border-[#C25E00]" placeholder="Nhập câu hỏi hoặc yêu cầu hỗ trợ của quý khách..."></textarea>
                    </div>
            
                    <div class="pt-2">
                        <button type="submit" class="w-full bg-[#C25E00] hover:bg-[#A34E00] text-white py-3.5 px-4 rounded-none sm:rounded-sm font-bold text-xs uppercase tracking-[0.2em] transition-all cursor-pointer shadow-xs hover:shadow-md">
                            Gửi yêu cầu liên hệ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</x-layout-frontend>