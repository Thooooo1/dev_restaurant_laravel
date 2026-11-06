<x-layout-frontend>
    <main class="bg-[#FAF8F5] min-h-[calc(100vh-200px)] flex items-center justify-center py-10 sm:py-16 px-4">
        <div class="w-full max-w-md">
            <div class="bg-white p-7 sm:p-9 rounded-none sm:rounded-sm border-t-4 border-t-[#C25E00] border-x border-b border-stone-200/90 shadow-md">
                <div class="text-center mb-6">
                    <span class="text-xs uppercase font-bold tracking-[0.2em] text-[#C25E00] block mb-1">CIVILIZE SECURITY</span>
                    <h2 class="text-2xl sm:text-3xl font-black font-serif text-stone-900 tracking-tight">Khôi Phục Mật Khẩu</h2>
                </div>

                @if (session('success'))
                    <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 px-4 py-3 rounded-none sm:rounded-sm text-xs mb-5">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-700 px-4 py-3 rounded-none sm:rounded-sm text-xs mb-5">
                        {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('site.send_otp') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="phone" class="block text-xs sm:text-sm font-bold text-stone-700 mb-1.5">Số điện thoại</label>
                        <input 
                            type="tel" 
                            name="phone" 
                            id="phone"
                            class="w-full px-4 py-3 bg-stone-50/60 border border-stone-300 rounded-none sm:rounded-sm text-stone-900 text-sm sm:text-base font-medium focus:outline-none focus:bg-white focus:border-[#C25E00] focus:ring-0 transition-colors" 
                            placeholder="Ví dụ: 0912 345 678" 
                            value="{{ old('phone') }}"
                            required
                            autofocus>
                        @error('phone')
                            <p class="text-rose-600 text-xs mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-2">
                        <button 
                            id="send-otp-btn"
                            type="submit" 
                            class="w-full bg-[#C25E00] hover:bg-[#A34E00] text-white py-3.5 px-4 rounded-none sm:rounded-sm font-bold text-xs uppercase tracking-[0.2em] transition-all cursor-pointer shadow-xs hover:shadow-md">
                            Gửi mã OTP xác nhận
                        </button>
                    </div>

                    <div class="pt-4 border-t border-stone-100 text-center">
                        <a href="{{ route('site.login') }}" class="text-xs text-stone-500 hover:text-[#C25E00] font-bold uppercase tracking-wider inline-flex items-center gap-1.5">
                            <i class="fa-solid fa-arrow-left text-[10px]"></i>
                            <span>Quay lại đăng nhập</span>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</x-layout-frontend>
