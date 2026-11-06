<x-layout-frontend>
    <main class="bg-[#FAF8F5] min-h-screen pb-16">
        <x-page-hero 
            title="Xác Nhận Mã OTP" 
            :breadcrumbs="[
                ['name' => 'Đăng nhập', 'url' => route('site.login')],
                ['name' => 'Xác nhận OTP', 'url' => '']
            ]" 
        />

        <div class="container mx-auto px-4 max-w-md pt-8 sm:pt-12">
            <div class="bg-white p-7 sm:p-9 rounded-none sm:rounded-sm border-t-4 border-t-[#C25E00] border-x border-b border-stone-200/90 shadow-md">
                <div class="text-center mb-6">
                    <span class="text-xs uppercase font-bold tracking-[0.2em] text-[#C25E00] block mb-1">CIVILIZE VERIFICATION</span>
                    <h2 class="text-2xl sm:text-3xl font-black font-serif text-stone-900 tracking-tight">Nhập Mã OTP</h2>
                </div>

                {{-- HIỂN THỊ OTP TO RÕ, ĐẬM CHẤT ARCHITECTURAL --}}
                @if (isset($otp_display))
                    <div class="bg-amber-50/80 border border-amber-300 text-stone-900 p-4 rounded-none sm:rounded-sm mb-6 text-center shadow-2xs">
                        <div class="text-[10px] font-bold uppercase tracking-widest text-amber-800 bg-amber-200/60 inline-block px-2.5 py-1 mb-2 rounded-xs">[Môi Trường Thử Nghiệm - Mã OTP Mô Phỏng Qua SMS]</div>
                        <div class="text-xs font-bold uppercase tracking-widest text-[#C25E00] mb-1">Mã OTP Của Quý Khách</div>
                        <div class="text-3xl sm:text-4xl font-mono font-black tracking-[0.25em] text-stone-900 my-1">{{ $otp_display }}</div>
                        <div class="text-[11px] text-stone-500 font-medium">Mã có hiệu lực trong vòng 10 phút</div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-700 px-4 py-3 rounded-none sm:rounded-sm text-xs mb-5">
                        {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('site.verify_otp') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="phone" value="{{ old('phone', $phone) }}">
                    <div>
                        <label for="otp_code" class="block text-xs sm:text-sm font-bold text-stone-700 mb-1.5 text-center">
                            Mã xác thực gồm 6 chữ số
                        </label>
                        <input type="text" 
                               name="otp_code" 
                               id="otp_code"
                               class="w-full px-4 py-3 bg-stone-50/60 border border-stone-300 rounded-none sm:rounded-sm text-center text-2xl font-mono font-bold tracking-[0.3em] text-stone-900 focus:outline-none focus:bg-white focus:border-[#C25E00] focus:ring-0 transition-colors" 
                               placeholder="••••••" 
                               maxlength="6" 
                               value="{{ old('otp_code') }}"
                               autofocus 
                               required>
                        @error('otp_code')
                            <p class="text-rose-600 text-xs mt-1.5 font-medium text-center">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-2">
                        <button type="submit" 
                                class="w-full bg-[#C25E00] hover:bg-[#A34E00] text-white py-3.5 px-4 rounded-none sm:rounded-sm font-bold text-xs uppercase tracking-[0.2em] transition-all cursor-pointer shadow-xs hover:shadow-md">
                            Xác nhận OTP
                        </button>
                    </div>

                    <div class="pt-4 border-t border-stone-100 text-center">
                        <a href="{{ route('site.forgot_password') }}" class="text-xs text-stone-500 hover:text-[#C25E00] font-bold uppercase tracking-wider inline-flex items-center gap-1.5">
                            <i class="fa-solid fa-arrow-left text-[10px]"></i>
                            <span>Nhập lại số điện thoại</span>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</x-layout-frontend>
