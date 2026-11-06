<x-layout-frontend>
    <main class="bg-[#FAF8F5] min-h-screen pb-16">
        <x-page-hero 
            title="Chính Sách Vận Chuyển & Giao Món" 
            subtitle="Quy trình giao nhận món ăn nóng hổi, bảo quản kỹ lưỡng đến tận tay thực khách." 
            :breadcrumbs="[
                ['name' => 'Chính sách', 'url' => ''],
                ['name' => 'Vận chuyển & giao hàng', 'url' => '']
            ]" 
        />

        <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-4xl pt-8 sm:pt-12">
            <div class="bg-white shadow-2xs border border-stone-200/90 rounded-none sm:rounded-sm p-8 sm:p-12">
                <h2 class="text-xl sm:text-2xl font-black font-serif text-stone-900 mb-4">Các Phương Thức Giao Món</h2>
                <p class="text-stone-700 leading-relaxed mb-6">
                    Chúng tôi áp dụng các tiêu chuẩn vận chuyển ẩm thực khắt khe để đảm bảo món ăn giữ trọn hương vị tươi nóng khi đến tay quý khách:
                </p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                    <div class="p-5 bg-stone-50 border border-stone-200 rounded-none sm:rounded-sm">
                        <i class="fa-solid fa-clock text-[#C25E00] text-lg mb-2 block"></i>
                        <h4 class="font-bold text-stone-900 text-sm mb-1">Giao hỏa tốc</h4>
                        <p class="text-xs text-stone-600 leading-relaxed">Giao trong vòng 30 - 45 phút cho khu vực nội thành Phúc Thọ.</p>
                    </div>
                    <div class="p-5 bg-stone-50 border border-stone-200 rounded-none sm:rounded-sm">
                        <i class="fa-solid fa-box text-[#C25E00] text-lg mb-2 block"></i>
                        <h4 class="font-bold text-stone-900 text-sm mb-1">Đóng gói chuyên dụng</h4>
                        <p class="text-xs text-stone-600 leading-relaxed">Hộp giữ nhiệt cao cấp, seal niêm phong an toàn vệ sinh 100%.</p>
                    </div>
                    <div class="p-5 bg-stone-50 border border-stone-200 rounded-none sm:rounded-sm">
                        <i class="fa-solid fa-shield-halved text-[#C25E00] text-lg mb-2 block"></i>
                        <h4 class="font-bold text-stone-900 text-sm mb-1">Cam kết chất lượng</h4>
                        <p class="text-xs text-stone-600 leading-relaxed">Đổi món mới hoặc hoàn tiền ngay nếu món ăn bị nguội hoặc hư hại.</p>
                    </div>
                </div>
            
                <h3 class="text-lg sm:text-xl font-bold font-serif text-stone-900 mb-4 pb-2 border-b border-stone-200">Thời Gian & Khu Vực Phục Vụ</h3>
                <ul class="list-disc pl-6 text-stone-700 leading-relaxed mb-8 space-y-2">
                    <li>Khu vực huyện Phúc Thọ: Giao hàng nhanh từ 30 - 45 phút kể từ lúc hoàn tất chế biến.</li>
                    <li>Khu vực lân cận (Sơn Tây, Đan Phượng): Giao hàng từ 45 - 60 phút trong bán kính 15km.</li>
                    <li>Thời gian nhận đơn trực tuyến: Từ 09:00 đến 21:30 hàng ngày.</li>
                </ul>
            
                <h3 class="text-lg sm:text-xl font-bold font-serif text-stone-900 mb-4 pb-2 border-b border-stone-200">Biểu Phí Giao Nhận</h3>
                <p class="text-stone-700 leading-relaxed mb-4">
                    Biểu phí được tính toán minh bạch dựa trên khoảng cách di chuyển từ cơ sở nhà hàng:
                </p>
                <ul class="list-disc pl-6 text-stone-700 leading-relaxed mb-8 space-y-2">
                    <li>Miễn phí giao hàng cho hóa đơn từ 500.000đ trong bán kính 5km.</li>
                    <li>Dưới 5km: Phí giao hàng tiêu chuẩn 20.000đ/đơn.</li>
                    <li>Từ 5km - 15km: Phí giao hàng 35.000đ - 50.000đ tùy khoảng cách.</li>
                </ul>
            
                <div class="p-6 bg-stone-50 border-l-4 border-l-[#C25E00] text-stone-700 leading-relaxed">
                    <p class="font-medium">Mọi thắc mắc về đơn hàng đang giao, quý khách vui lòng liên hệ trực tiếp hotline <span class="font-bold text-[#C25E00]">0981 487 674</span> để được điều phối viên hỗ trợ ngay lập tức.</p>
                </div>
            </div>
        </div>
    </main>
</x-layout-frontend>