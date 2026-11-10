<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Quản trị - CIVILIZE</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('images/logo/logo.png') }}">
    
    <!-- Google Fonts: Plus Jakarta Sans & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome 6 Icons -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('plugins/toastr/toastr.min.css') }}">
    <script src="{{ asset('plugins/toastr/toastr.min.js') }}"></script>

    <style>
        html, body,
        h1, h2, h3, h4, h5, h6,
        p, span, a, div, label, input, button {
            font-family: 'Plus Jakarta Sans', 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
            -webkit-font-smoothing: antialiased !important;
            -moz-osx-font-smoothing: grayscale !important;
        }
        .fa, .fas, .fa-solid {
            font-family: "Font Awesome 6 Free" !important;
            font-weight: 900 !important;
        }
    </style>
</head>
<body class="bg-[#FAF8F5] min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white p-7 sm:p-9 rounded-2xl sm:rounded-3xl border border-stone-200/90 shadow-xl">
        @include("components.alert")
        <div class="text-center mb-6">
            <img src="{{ asset('images/logo/logo.png') }}" alt="CIVILIZE" class="h-14 w-auto mx-auto mb-2.5 object-contain">
            <span class="text-xs uppercase font-bold tracking-[0.2em] text-[#C25E00] block mb-1">CIVILIZE RESTAURANT</span>
            <h1 class="text-2xl sm:text-3xl font-black font-serif text-stone-900 tracking-tight">Hệ Thống Quản Trị</h1>
        </div>

        <form action="{{ route('admin.dologin') }}" method="POST" class="space-y-4" id="adminLoginForm">
            @csrf
            <div>
                <input type="text" 
                       name="phone" 
                       id="adminLoginInput"
                       value="{{ old('phone') }}"
                       required
                       class="w-full px-5 py-3.5 bg-white border border-stone-300 rounded-xl sm:rounded-2xl text-stone-900 text-sm sm:text-base placeholder-stone-400 focus:outline-none focus:border-[#C25E00] focus:ring-2 focus:ring-[#C25E00]/20 transition-all shadow-2xs" 
                       placeholder="Email, số điện thoại hoặc tên đăng nhập"
                       autofocus>
            </div>

            <div>
                <input type="password" 
                       name="password" 
                       id="adminPasswordInput"
                       required
                       class="w-full px-5 py-3.5 bg-white border border-stone-300 rounded-xl sm:rounded-2xl text-stone-900 text-sm sm:text-base placeholder-stone-400 focus:outline-none focus:border-[#C25E00] focus:ring-2 focus:ring-[#C25E00]/20 transition-all shadow-2xs" 
                       placeholder="Mật khẩu">
            </div>

            <div class="pt-2">
                <button type="submit" 
                        id="adminLoginBtn"
                        class="w-full bg-[#C25E00] hover:bg-[#a85000] text-white py-3.5 px-4 rounded-xl sm:rounded-2xl font-bold text-xs uppercase tracking-[0.2em] transition-all cursor-pointer shadow-md hover:shadow-lg active:scale-[0.99]">
                    Đăng nhập hệ thống
                </button>
            </div>        
        </form>
    </div>

    <script>
        function refreshCsrfToken() {
            return fetch('{{ route("refresh-csrf") }}', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                if (data && data.token) {
                    document.querySelectorAll('input[name="_token"]').forEach(el => el.value = data.token);
                    return data.token;
                }
            })
            .catch(() => {});
        }

        // 1. Khi tab duoc phuc hoi sau khi bat may hoac back/forward cache (pageshow)
        window.addEventListener('pageshow', function(event) {
            refreshCsrfToken();
        });

        // 2. Khi nguoi dung chuyen ve tab hoac click vao man hinh (focus / visibilitychange)
        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'visible') {
                refreshCsrfToken();
            }
        });
        window.addEventListener('focus', refreshCsrfToken);

        // 3. Heartbeat giu session luon song moi 10 phut
        setInterval(refreshCsrfToken, 10 * 60 * 1000);

        // 4. Khi nguoi dung focus vao o nhap mat khau
        document.getElementById('adminPasswordInput')?.addEventListener('focus', function() {
            refreshCsrfToken();
        }, { once: true });

        // 5. Dam bao CSRF token luon moi tinh ngay truoc khi submit form dang nhap
        let isSubmitting = false;
        document.getElementById('adminLoginForm')?.addEventListener('submit', async function(e) {
            if (isSubmitting) return;
            e.preventDefault();
            isSubmitting = true;
            const btn = document.getElementById('adminLoginBtn');
            if (btn) {
                btn.disabled = true;
                btn.innerText = 'Đang kiểm tra đăng nhập...';
            }
            try {
                await refreshCsrfToken();
            } catch (err) {}
            this.submit();
        });
    </script>
</body>
</html>
