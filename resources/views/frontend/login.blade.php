<x-layout-frontend>
    <main class="bg-[#FAF8F5] min-h-[calc(100vh-200px)] flex items-center justify-center py-10 sm:py-16 px-4">
        <div class="w-full max-w-md">
            <div class="bg-white p-7 sm:p-9 rounded-2xl sm:rounded-3xl border border-stone-200/90 shadow-xl">
                <!-- Header -->
                <div class="text-center mb-6">
                    <span class="text-xs uppercase font-bold tracking-[0.2em] text-[#C25E00] block mb-1">CIVILIZE RESTAURANT</span>
                    <h2 class="text-2xl sm:text-3xl font-black font-serif text-stone-900 tracking-tight">Đăng Nhập</h2>
                </div>

                <!-- FORM ĐĂNG NHẬP TRỰC TIẾP -->
                <form action="{{ route('site.dologin') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="redirect" value="{{ $redirect ?? request()->query('redirect') ?? '' }}">
                    
                    <!-- 1. EMAIL, SỐ ĐIỆN THOẠI HOẶC TÊN ĐĂNG NHẬP -->
                    <div>
                        <input type="text" 
                               name="phone" 
                               id="phone" 
                               value="{{ old('phone') }}" 
                               class="w-full px-5 py-3.5 bg-white border border-stone-300 rounded-xl sm:rounded-2xl text-stone-900 text-sm sm:text-base placeholder-stone-400 focus:outline-none focus:border-[#C25E00] focus:ring-2 focus:ring-[#C25E00]/20 transition-all shadow-2xs" 
                               placeholder="Email, số điện thoại hoặc tên đăng nhập" 
                               required 
                               autofocus>
                    </div>
                    
                    <!-- 2. MẬT KHẨU & QUÊN MẬT KHẨU -->
                    <div>
                        <div class="flex justify-end items-center mb-1.5">
                            <a href="{{ route('site.forgot_password') }}" class="text-xs sm:text-sm text-[#C25E00] hover:underline font-semibold">
                                Quên mật khẩu?
                            </a>
                        </div>
                        <input type="password" 
                               name="password" 
                               id="password" 
                               class="w-full px-5 py-3.5 bg-white border border-stone-300 rounded-xl sm:rounded-2xl text-stone-900 text-sm sm:text-base placeholder-stone-400 focus:outline-none focus:border-[#C25E00] focus:ring-2 focus:ring-[#C25E00]/20 transition-all shadow-2xs" 
                               placeholder="Mật khẩu" 
                               required>
                    </div>


                    <!-- 3. NÚT ĐĂNG NHẬP -->
                    <div class="pt-2">
                        <button type="submit" 
                                class="w-full bg-[#C25E00] hover:bg-[#a85000] text-white py-3.5 px-4 rounded-xl sm:rounded-2xl font-bold text-xs uppercase tracking-[0.2em] transition-all cursor-pointer shadow-md hover:shadow-lg active:scale-[0.99]">
                            Đăng nhập
                        </button>
                    </div>
                </form>

                <!-- 4. CHUYỂN SANG ĐĂNG KÝ -->
                <div class="mt-6 pt-5 border-t border-stone-100 text-center text-xs sm:text-sm text-stone-600">
                    <span>Chưa có tài khoản CIVILIZE? </span>
                    <a href="{{ route('site.register', ['redirect' => $redirect ?? request()->query('redirect') ?? '']) }}" class="font-bold text-[#C25E00] hover:underline">
                        Đăng ký ngay
                    </a>
                </div>
            </div>
        </div>
    </main>

    <script>
        function refreshCsrfToken() {
            fetch('{{ route("refresh-csrf") }}', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                if (data && data.token) {
                    document.querySelectorAll('input[name="_token"]').forEach(el => el.value = data.token);
                }
            })
            .catch(() => {});
        }
        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'visible') {
                refreshCsrfToken();
            }
        });
        window.addEventListener('focus', refreshCsrfToken);
        setInterval(refreshCsrfToken, 10 * 60 * 1000);
    </script>
</x-layout-frontend>