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
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white p-8 rounded-none sm:rounded-sm shadow-md border-t-4 border-t-[#C25E00] border-x border-b border-slate-200">
        @include("components.alert")
        <div class="text-center mb-6">
            <img src="{{ asset('images/logo/logo.png') }}" alt="CIVILIZE" class="h-16 w-auto mx-auto mb-3 object-contain">
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight font-serif">Hệ Thống Quản Trị CIVILIZE</h1>
        </div>
        <form action="{{ route('admin.dologin') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Số điện thoại</label>
                <input type="text" 
                       name="phone" 
                       required
                       class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-none sm:rounded-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-[#C25E00] focus:bg-white transition-all" 
                       placeholder="Ví dụ: 0912345678">
            </div>

            <div>
                <div class="flex justify-between items-center mb-1.5">
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">Mật khẩu</label>
                </div>
                <input type="password" 
                       name="password" 
                       required
                       class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-none sm:rounded-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-[#C25E00] focus:bg-white transition-all" 
                       placeholder="••••••••">
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full bg-stone-900 hover:bg-[#C25E00] text-white py-3 px-4 rounded-none sm:rounded-sm font-bold text-xs uppercase tracking-[0.2em] shadow-xs transition-all flex items-center justify-center cursor-pointer">
                    <span>Đăng nhập hệ thống</span>
                </button>
            </div>        
        </form>
    </div>
</body>
</html>
