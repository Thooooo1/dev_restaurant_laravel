<x-layout-frontend>
    <main class="bg-stone-50/60 min-h-screen">
        
        <section class="py-10 sm:py-14">
            <div class="max-w-[1200px] mx-auto px-4 sm:px-6 w-full">

                <!-- 1. Tiêu đề lớn chuẩn xác như Ảnh 2 -->
                <div class="mb-8 sm:mb-10">
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-stone-900 tracking-tight">
                        Liên Hệ Với Nhà Hàng
                    </h1>
                </div>

                <!-- 2. Thanh Tab Phân Loại Bộ Phận: Gạch chân phẳng chuẩn Ảnh 2 -->
                <div class="border-b border-stone-200 mb-8 sm:mb-10">
                    <div class="flex items-center gap-6 sm:gap-10 overflow-x-auto text-xs sm:text-sm uppercase font-bold scrollbar-none -mb-px">
                        <button type="button" 
                                class="dept-tab active-dept border-b-2 border-orange-600 text-orange-600 pb-3 font-extrabold transition-colors whitespace-nowrap cursor-pointer"
                                data-dept="ketoan">
                            KẾ TOÁN – TÀI CHÍNH
                        </button>
                        <button type="button" 
                                class="dept-tab border-b-2 border-transparent text-stone-500 hover:text-stone-800 pb-3 font-bold transition-colors whitespace-nowrap cursor-pointer"
                                data-dept="truyenthong">
                            HỢP TÁC TRUYỀN THÔNG
                        </button>
                        <button type="button" 
                                class="dept-tab border-b-2 border-transparent text-stone-500 hover:text-stone-800 pb-3 font-bold transition-colors whitespace-nowrap cursor-pointer"
                                data-dept="khieunai">
                            PHẢN ÁNH KHIẾU NẠI
                        </button>
                    </div>
                </div>

                <!-- Alert Thông Báo Gửi Thành Công -->
                @if (session('success'))
                    <div class="mb-6 p-4 rounded-none sm:rounded-sm bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-semibold flex items-center gap-3">
                        <i class="fas fa-check-circle text-emerald-500 text-lg shrink-0"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                <!-- 3. Thẻ Trắng Trung Tâm 2 Cột chuẩn Ảnh 2 -->
                <div class="bg-white rounded-none sm:rounded-sm shadow-2xs border border-stone-200 p-6 sm:p-10 md:p-12 mb-10 sm:mb-12">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-8 md:gap-12 items-start">
                        
                        <!-- Cột Trái: Thông tin bộ phận đại diện -->
                        <div class="md:col-span-5 flex flex-col justify-between h-full space-y-6">
                            <div>
                                <span class="inline-block text-[#C25E00] font-bold uppercase text-[11px] tracking-widest mb-1.5">
                                    CIVILIZE RESTAURANT
                                </span>
                                
                                <h2 class="text-2xl sm:text-3xl font-extrabold text-stone-900 tracking-tight mb-1">
                                    CIVILIZE Group
                                </h2>
                                
                                <div class="text-stone-500 text-sm font-semibold mb-6" id="dept-name-display">
                                    Kế toán – Tài chính
                                </div>

                                <div class="space-y-4 text-xs sm:text-sm text-stone-700">
                                    <!-- Địa chỉ -->
                                    <div class="flex items-start gap-3.5">
                                        <i class="fas fa-house text-[#C25E00] text-sm mt-1 shrink-0"></i>
                                        <span class="leading-relaxed font-medium">120 Phúc Hòa, Huyện Phúc Thọ, TP. Hà Nội</span>
                                    </div>

                                    <!-- Email -->
                                    <div class="flex items-center gap-3.5">
                                        <i class="fas fa-envelope text-[#C25E00] text-sm shrink-0"></i>
                                        <a href="mailto:civilize.ketoan@gmail.com" class="font-medium hover:text-[#C25E00] transition-colors" id="dept-email-display">
                                            civilize.ketoan@gmail.com
                                        </a>
                                    </div>

                                    <!-- Hotline -->
                                    <div class="flex items-center gap-3.5">
                                        <i class="fas fa-phone text-[#C25E00] text-sm shrink-0"></i>
                                        <a href="tel:0338990538" class="font-bold text-stone-900 hover:text-[#C25E00] transition-colors" id="dept-phone-display">
                                            0338 990 538 nhánh 1
                                        </a>
                                    </div>

                                    <!-- Thời gian phục vụ -->
                                    <div class="flex items-center gap-3.5">
                                        <i class="fas fa-clock text-[#C25E00] text-sm shrink-0"></i>
                                        <span class="font-medium text-stone-600">08:00 – 23:00 hàng ngày (kể cả Lễ, Tết)</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Social Icons: Vuông bo nhẹ chuẩn Ảnh 2 -->
                            <div class="pt-6 border-t border-stone-100 flex items-center gap-2.5">
                                <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" 
                                   class="w-9 h-9 rounded-none sm:rounded-sm border border-stone-200 hover:border-[#1877F2] hover:bg-[#1877F2]/10 text-stone-600 hover:text-[#1877F2] flex items-center justify-center transition-colors text-xs" 
                                   title="Facebook">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                                <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" 
                                   class="w-9 h-9 rounded-none sm:rounded-sm border border-stone-200 hover:border-[#E4405F] hover:bg-[#E4405F]/10 text-stone-600 hover:text-[#E4405F] flex items-center justify-center transition-colors text-xs" 
                                   title="Instagram">
                                    <i class="fab fa-instagram"></i>
                                </a>
                                <a href="https://tiktok.com" target="_blank" rel="noopener noreferrer" 
                                   class="w-9 h-9 rounded-none sm:rounded-sm border border-stone-200 hover:border-black hover:bg-black/10 text-stone-600 hover:text-black flex items-center justify-center transition-colors text-xs" 
                                   title="TikTok">
                                    <i class="fab fa-tiktok"></i>
                                </a>
                                <a href="https://youtube.com" target="_blank" rel="noopener noreferrer" 
                                   class="w-9 h-9 rounded-none sm:rounded-sm border border-stone-200 hover:border-[#FF0000] hover:bg-[#FF0000]/10 text-stone-600 hover:text-[#FF0000] flex items-center justify-center transition-colors text-xs" 
                                   title="YouTube">
                                    <i class="fab fa-youtube"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Cột Phải: Form gửi thông tin chuẩn Ảnh 2 -->
                        <div class="md:col-span-7">
                            <form action="{{ route('inquiries.store') }}" method="POST" class="space-y-4">
                                @csrf
                                <input type="hidden" name="department" id="input-department" value="Kế toán – Tài chính">

                                <div>
                                    <input type="text" 
                                           name="name" 
                                           value="{{ old('name') }}" 
                                           placeholder="Tên của bạn*" 
                                           required 
                                           class="w-full px-3.5 py-2.5 rounded-none sm:rounded-sm border border-stone-300 text-stone-900 text-xs sm:text-sm bg-white focus:outline-none focus:border-stone-700 transition">
                                </div>

                                <div>
                                    <input type="email" 
                                           name="email" 
                                           value="{{ old('email') }}" 
                                           placeholder="Email*" 
                                           class="w-full px-3.5 py-2.5 rounded-none sm:rounded-sm border border-stone-300 text-stone-900 text-xs sm:text-sm bg-white focus:outline-none focus:border-stone-700 transition">
                                </div>

                                <div>
                                    <input type="tel" 
                                           name="phone" 
                                           value="{{ old('phone') }}" 
                                           placeholder="Số điện thoại*" 
                                           required 
                                           class="w-full px-3.5 py-2.5 rounded-none sm:rounded-sm border border-stone-300 text-stone-900 text-xs sm:text-sm bg-white focus:outline-none focus:border-stone-700 transition">
                                </div>

                                <div>
                                    <input type="text" 
                                           name="title" 
                                           value="{{ old('title') }}" 
                                           placeholder="Tiêu đề*" 
                                           class="w-full px-3.5 py-2.5 rounded-none sm:rounded-sm border border-stone-300 text-stone-900 text-xs sm:text-sm bg-white focus:outline-none focus:border-stone-700 transition">
                                </div>

                                <div>
                                    <textarea name="message" 
                                              rows="4" 
                                              placeholder="Nhập nội dung cần tư vấn hoặc góp ý..." 
                                              required 
                                              class="w-full px-3.5 py-2.5 rounded-none sm:rounded-sm border border-stone-300 text-stone-900 text-xs sm:text-sm bg-white focus:outline-none focus:border-stone-700 transition">{{ old('message') }}</textarea>
                                </div>

                                <div class="flex justify-end pt-2">
                                    <button type="submit" 
                                            class="px-8 py-3 rounded-none sm:rounded-sm bg-[#1c1917] hover:bg-black text-white font-bold text-xs uppercase tracking-wider transition-colors cursor-pointer shadow-xs">
                                        GỬI THÔNG TIN
                                    </button>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>

                <!-- 4. Google Maps Hoàn Chỉnh: Ghim chính xác 120 Phúc Hòa, Phúc Thọ -->
                <div class="w-full">
                    <div class="mb-3.5 flex items-center justify-between">
                        <div class="text-stone-800 font-bold text-xs sm:text-sm">
                            <span>Bản đồ vị trí nhà hàng CIVILIZE Phúc Thọ</span>
                        </div>
                        <a href="https://maps.google.com/?q=21.1097,105.5620" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="text-xs font-semibold text-[#C25E00] hover:underline flex items-center gap-1">
                            <span>Mở chỉ đường trên Google Maps</span>
                            <i class="fas fa-arrow-up-right-from-square text-[10px]"></i>
                        </a>
                    </div>
                    <div class="w-full h-[400px] sm:h-[480px] rounded-none sm:rounded-sm overflow-hidden border border-stone-200 shadow-2xs bg-stone-100">
                        <iframe
                            src="https://maps.google.com/maps?q=120+Ph%C3%BAc+H%C3%B2a,+Ph%C3%BAc+Th%E1%BB%8D,+H%C3%A0+N%E1%BB%99i&amp;t=&amp;z=16&amp;ie=UTF8&amp;iwloc=&amp;output=embed"
                            width="100%" 
                            height="100%" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade"
                            class="w-full h-full">
                        </iframe>
                    </div>
                </div>

            </div>
        </section>

    </main>

    <!-- JavaScript chuyển đổi tab bộ phận tương tác mượt mà -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tabs = document.querySelectorAll('.dept-tab');
            const deptName = document.getElementById('dept-name-display');
            const deptEmail = document.getElementById('dept-email-display');
            const deptPhone = document.getElementById('dept-phone-display');
            const inputDept = document.getElementById('input-department');

            const deptData = {
                ketoan: {
                    name: 'Kế toán – Tài chính',
                    email: 'civilize.ketoan@gmail.com',
                    phone: '0338 990 538 nhánh 1'
                },
                truyenthong: {
                    name: 'Hợp tác truyền thông & Đối tác',
                    email: 'civilize.truyenthong@gmail.com',
                    phone: '0338 990 538 nhánh 2'
                },
                khieunai: {
                    name: 'Phản ánh khiếu nại & Dịch vụ',
                    email: 'civilize.cskh@gmail.com',
                    phone: '0338 990 538 nhánh 3'
                }
            };

            tabs.forEach(tab => {
                tab.addEventListener('click', function () {
                    const deptKey = this.getAttribute('data-dept');
                    const data = deptData[deptKey];

                    if (!data) return;

                    // Cập nhật tab active dạng gạch chân phẳng
                    tabs.forEach(t => {
                        t.classList.remove('active-dept', 'border-orange-600', 'text-orange-600', 'font-extrabold');
                        t.classList.add('border-transparent', 'text-stone-500', 'font-bold');
                    });
                    this.classList.remove('border-transparent', 'text-stone-500', 'font-bold');
                    this.classList.add('active-dept', 'border-orange-600', 'text-orange-600', 'font-extrabold');

                    // Cập nhật thông tin cột trái
                    if (deptName) deptName.textContent = data.name;
                    if (deptEmail) {
                        deptEmail.textContent = data.email;
                        deptEmail.setAttribute('href', 'mailto:' + data.email);
                    }
                    if (deptPhone) {
                        deptPhone.textContent = data.phone;
                    }
                    if (inputDept) {
                        inputDept.value = data.name;
                    }
                });
            });
        });
    </script>
</x-layout-frontend>
