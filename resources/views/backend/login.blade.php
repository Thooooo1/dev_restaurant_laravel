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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    
    <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>

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

        <form action="{{ route('admin.dologin') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <input type="text" 
                       name="phone" 
                       id="adminLoginInput"
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

            <!-- GỢI Ý TÀI KHOẢN DEMO 1-CLICK -->
            <div class="p-3.5 bg-amber-50/90 border border-amber-200 rounded-xl text-xs space-y-2">
                <div class="flex items-center justify-between mb-1">
                    <span class="font-bold text-amber-900 text-xs flex items-center gap-1.5">
                        <i class="fa-solid fa-key text-[#C25E00]"></i>
                        <span>Tài khoản Demo Quản Trị:</span>
                    </span>
                    <span class="text-[11px] font-semibold text-stone-500">Mật khẩu: <strong class="text-stone-800">123456</strong></span>
                </div>
                <div class="space-y-1.5">
                    <button type="button" 
                            onclick="fillAdminLogin('admin1', '123456')" 
                            class="w-full py-2 px-3 bg-white hover:bg-stone-50 border border-amber-200/80 rounded-lg text-left flex items-center justify-between transition cursor-pointer">
                        <span class="font-bold text-[#C25E00] text-xs">
                            admin1 <span class="text-stone-500 font-medium text-[11px] ml-1">(Quản trị viên)</span>
                        </span>
                        <span class="text-[11px] text-stone-500 underline font-medium">Bấm để điền nhanh</span>
                    </button>
                    <button type="button" 
                            onclick="fillAdminLogin('nhanvien1', '123456')" 
                            class="w-full py-2 px-3 bg-white hover:bg-stone-50 border border-amber-200/80 rounded-lg text-left flex items-center justify-between transition cursor-pointer">
                        <span class="font-bold text-[#C25E00] text-xs">
                            nhanvien1 <span class="text-stone-500 font-medium text-[11px] ml-1">(Nhân viên POS)</span>
                        </span>
                        <span class="text-[11px] text-stone-500 underline font-medium">Bấm để điền nhanh</span>
                    </button>
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" 
                        class="w-full bg-[#C25E00] hover:bg-[#a85000] text-white py-3.5 px-4 rounded-xl sm:rounded-2xl font-bold text-xs uppercase tracking-[0.2em] transition-all cursor-pointer shadow-md hover:shadow-lg active:scale-[0.99]">
                    Đăng nhập hệ thống
                </button>
            </div>        
        </form>

        <script>
            function fillAdminLogin(username, password) {
                const inputUser = document.getElementById('adminLoginInput');
                const inputPass = document.getElementById('adminPasswordInput');
                if (inputUser) inputUser.value = username;
                if (inputPass) inputPass.value = password;
            }
        </script>
    </div>
</body>
</html>