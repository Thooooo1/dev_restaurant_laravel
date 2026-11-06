<x-layout-frontend>
    <main class="bg-[#FAF8F5] min-h-screen pb-16">
        <x-page-hero 
            title="Thiết Lập Mật Khẩu Mới" 
            :breadcrumbs="[
                ['name' => 'Đăng nhập', 'url' => route('site.login')],
                ['name' => 'Đặt lại mật khẩu', 'url' => '']
            ]" 
        />

        <div class="container mx-auto px-4 max-w-md pt-8 sm:pt-12">
            <div class="bg-white p-7 sm:p-9 rounded-none sm:rounded-sm border-t-4 border-t-[#C25E00] border-x border-b border-stone-200/90 shadow-md">
                <div class="text-center mb-6">
                    <span class="text-xs uppercase font-bold tracking-[0.2em] text-[#C25E00] block mb-1">CIVILIZE SECURITY</span>
                    <h2 class="text-2xl sm:text-3xl font-black font-serif text-stone-900 tracking-tight">Mật Khẩu Mới</h2>
                </div>

                @if (session('error'))
                    <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-700 px-4 py-3 rounded-none sm:rounded-sm text-xs mb-5">
                        {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('site.reset_password') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="password" class="block text-xs sm:text-sm font-bold text-stone-700 mb-1.5">Mật khẩu mới</label>
                        <input type="password" 
                               name="password" 
                               id="password" 
                               class="w-full px-4 py-3 bg-stone-50/60 border border-stone-300 rounded-none sm:rounded-sm text-stone-900 text-sm sm:text-base focus:outline-none focus:bg-white focus:border-[#C25E00] focus:ring-0 transition-colors" 
                               placeholder="••••••••" 
                               required 
                               autofocus>
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs sm:text-sm font-bold text-stone-700 mb-1.5">Xác nhận mật khẩu</label>
                        <input type="password" 
                               name="password_confirmation" 
                               id="password_confirmation" 
                               class="w-full px-4 py-3 bg-stone-50/60 border border-stone-300 rounded-none sm:rounded-sm text-stone-900 text-sm sm:text-base focus:outline-none focus:bg-white focus:border-[#C25E00] focus:ring-0 transition-colors" 
                               placeholder="••••••••" 
                               required>
                    </div>

                    <div class="pt-2">
                        <button type="submit" 
                                class="w-full bg-[#C25E00] hover:bg-[#A34E00] text-white py-3.5 px-4 rounded-none sm:rounded-sm font-bold text-xs uppercase tracking-[0.2em] transition-all cursor-pointer shadow-xs hover:shadow-md">
                            Cập nhật mật khẩu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</x-layout-frontend>
