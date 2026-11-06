<x-layout-frontend>
    <main class="bg-[#FAF8F5] min-h-[calc(100vh-200px)] flex items-center justify-center py-10 sm:py-16 px-4">
        <div class="w-full max-w-md">
            <div class="bg-white p-7 sm:p-9 rounded-2xl sm:rounded-3xl border border-stone-200/90 shadow-xl">
                <!-- Header -->
                <div class="text-center mb-6">
                    <span class="text-xs uppercase font-bold tracking-[0.2em] text-[#C25E00] block mb-1">CIVILIZE RESTAURANT</span>
                    <h2 class="text-2xl sm:text-3xl font-black font-serif text-stone-900 tracking-tight">Đăng Ký</h2>
                </div>

                <!-- FORM ĐĂNG KÝ -->
                <form action="{{ route('site.doregister') }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <!-- 1. HỌ VÀ TÊN -->
                    <div>
                        <label class="block text-xs sm:text-sm font-bold text-stone-700 mb-1.5">Họ và tên <span class="text-rose-500">*</span></label>
                        <input name="fullname" 
                               type="text" 
                               value="{{ old('fullname') }}"
                               required
                               autofocus
                               class="w-full px-5 py-3.5 bg-white border border-stone-300 rounded-xl sm:rounded-2xl text-stone-900 text-sm sm:text-base placeholder-stone-400 focus:outline-none focus:border-[#C25E00] focus:ring-2 focus:ring-[#C25E00]/20 transition-all shadow-2xs" 
                               placeholder="Ví dụ: Nguyễn Văn A" />
                        @error('fullname')
                            <p class="text-rose-600 text-xs mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 2. TÊN NGƯỜI DÙNG (USERNAME) -->
                    <div>
                        <label class="block text-xs sm:text-sm font-bold text-stone-700 mb-1.5">Tên người dùng <span class="text-rose-500">*</span></label>
                        <input name="username" 
                               type="text" 
                               value="{{ old('username') }}"
                               required
                               class="w-full px-5 py-3.5 bg-white border border-stone-300 rounded-xl sm:rounded-2xl text-stone-900 text-sm sm:text-base placeholder-stone-400 focus:outline-none focus:border-[#C25E00] focus:ring-2 focus:ring-[#C25E00]/20 transition-all shadow-2xs" 
                               placeholder="Ví dụ: nguyenvana hoặc vanhaicoder" />
                        @error('username')
                            <p class="text-rose-600 text-xs mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 3. SỐ ĐIỆN THOẠI -->
                    <div>
                        <label class="block text-xs sm:text-sm font-bold text-stone-700 mb-1.5">Số điện thoại <span class="text-rose-500">*</span></label>
                        <input name="phone" 
                               type="tel" 
                               value="{{ old('phone', request('phone')) }}"
                               required
                               class="w-full px-5 py-3.5 bg-white border border-stone-300 rounded-xl sm:rounded-2xl text-stone-900 text-sm sm:text-base placeholder-stone-400 focus:outline-none focus:border-[#C25E00] focus:ring-2 focus:ring-[#C25E00]/20 transition-all shadow-2xs" 
                               placeholder="Ví dụ: 0912 345 678" />
                        @error('phone')
                            <p class="text-rose-600 text-xs mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 4. EMAIL (TÙY CHỌN) -->
                    <div>
                        <label class="block text-xs sm:text-sm font-bold text-stone-700 mb-1.5">Email <span class="text-stone-400 font-normal text-xs">(Không bắt buộc)</span></label>
                        <input name="email" 
                               type="email" 
                               value="{{ old('email') }}"
                               class="w-full px-5 py-3.5 bg-white border border-stone-300 rounded-xl sm:rounded-2xl text-stone-900 text-sm sm:text-base placeholder-stone-400 focus:outline-none focus:border-[#C25E00] focus:ring-2 focus:ring-[#C25E00]/20 transition-all shadow-2xs" 
                               placeholder="email@example.com" />
                        @error('email')
                            <p class="text-rose-600 text-xs mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 5. MẬT KHẨU -->
                    <div x-data="{ showPass: false }">
                        <label class="block text-xs sm:text-sm font-bold text-stone-700 mb-1.5">Mật khẩu <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <input name="password" 
                                   :type="showPass ? 'text' : 'password'" 
                                   required
                                   class="w-full px-5 py-3.5 pr-12 bg-white border border-stone-300 rounded-xl sm:rounded-2xl text-stone-900 text-sm sm:text-base placeholder-stone-400 focus:outline-none focus:border-[#C25E00] focus:ring-2 focus:ring-[#C25E00]/20 transition-all shadow-2xs" 
                                   placeholder="Tối thiểu 6 ký tự" />
                            <button type="button" 
                                    @click="showPass = !showPass" 
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-700 p-1 cursor-pointer focus:outline-none transition-colors"
                                    title="Ẩn/Hiện mật khẩu">
                                <i class="fa-solid" :class="showPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-rose-600 text-xs mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 6. NÚT ĐĂNG KÝ -->
                    <div class="pt-2">
                        <button type="submit" 
                                class="w-full bg-[#C25E00] hover:bg-[#a85000] text-white py-3.5 px-4 rounded-xl sm:rounded-2xl font-bold text-xs uppercase tracking-[0.2em] transition-all cursor-pointer shadow-md hover:shadow-lg active:scale-[0.99]">
                            Đăng ký
                        </button>
                    </div>
                </form>

                <!-- Chuyển sang đăng nhập nếu đã có tài khoản -->
                <div class="mt-6 pt-5 border-t border-stone-100 text-center text-xs sm:text-sm text-stone-600">
                    <span>Đã có tài khoản CIVILIZE? </span>
                    <a href="{{ route('site.login') }}" class="font-bold text-[#C25E00] hover:underline">
                        Đăng nhập ngay
                    </a>
                </div>
            </div>
        </div>
    </main>
</x-layout-frontend>
