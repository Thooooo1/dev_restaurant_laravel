<!DOCTYPE html>
<html lang="vi" class="scroll-smooth notranslate" translate="no">

<head>
    <meta charset="UTF-8">
    <meta name="google" content="notranslate">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover, interactive-widget=resizes-content">
    <meta name="theme-color" content="#FAF8F5" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#1c1917" media="(prefers-color-scheme: dark)">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="CIVILIZE">
    <meta name="application-name" content="CIVILIZE">
    <meta name="format-detection" content="telephone=no">
    <title>CIVILIZE Restaurant</title>
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo/logo.png') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('images/logo/logo.png') }}">
    
    <!-- PWA Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js').catch(function() {});
            });
        }
    </script>
    <!-- jQuery -->
    {{--
    <script src="/Scripts/js/jquery-2.0.0.min.js" type="text/javascript"></script> --}}

    <!-- Bootstrap4 files-->
    <script src="/Scripts/js/bootstrap.bundle.min.js" type="text/javascript"></script>
    <link href="/Content/css/bootstrap.css" rel="stylesheet" type="text/css" />

    <!-- Font Awesome 6.7.0 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.0/css/all.min.css"
        integrity="sha512-9xKTRVabjVeZmc+GUW8GgSmcREDunMM+Dt/GrzchfN8tkwHizc5RP4Ok/MXFFy5rIjJjzhndFScTceq5e6GvVQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Slick CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
    <link rel="stylesheet" type="text/css"
        href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css" />

    <!-- Slick JS -->
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>

    {{--
    <script src="{{asset('plugins/jquery/jquery.min.js')}}"></script> --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.css">
    {{-- <!-- Thêm Quill CSS -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <!-- Thêm Quill JS -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script> --}}


    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js"></script>

    <!-- Nhúng Alpine.js nếu chưa có -->
    <script src="{{ asset('assets/alpinejs@3.x.x/dist/cdn.min.js') }}" defer></script>

    <!-- Style chống FOUC cho Alpine.js và Hiệu ứng màu thương hiệu Social Media chuẩn xác -->
    <style>
        [x-cloak] { display: none !important; }

        /* Social Icons: Chuyển đổi màu y hệt app khi chỉ đến (hover) */
        .hover-social-fb:hover {
            background-color: #1877F2 !important;
            border-color: #1877F2 !important;
            color: #ffffff !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(24, 119, 242, 0.35);
        }
        .hover-social-insta:hover {
            background: radial-gradient(circle at 30% 107%, #fdf497 0%, #fdf497 5%, #fd5949 45%, #d6249f 60%, #285AEB 90%) !important;
            border-color: transparent !important;
            color: #ffffff !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(214, 36, 159, 0.4);
        }
        .hover-social-tiktok:hover {
            background-color: #000000 !important;
            border-color: #25F4EE !important;
            color: #ffffff !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.6);
        }
        .hover-social-tiktok:hover i {
            text-shadow: -1.5px -1.5px 0 #25F4EE, 1.5px 1.5px 0 #FE2C55;
        }
        .hover-social-yt:hover {
            background-color: #FF0000 !important;
            border-color: #FF0000 !important;
            color: #ffffff !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 0, 0, 0.4);
        }
    </style>

    <!-- Google Fonts: Inter (Cực kỳ sắc nét, rõ ràng, dễ nhìn nhất trên mọi màn hình máy tính & điện thoại) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Inter"', 'system-ui', '-apple-system', 'sans-serif'],
                        serif: ['"Inter"', 'system-ui', '-apple-system', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#FFF7ED',
                            100: '#FFEDD5',
                            200: '#FED7AA',
                            500: '#F47716',
                            600: '#EA580C',
                            700: '#C25E00',
                            800: '#9A3412',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        /* ========================================================
           CROSS-PLATFORM DESIGN: WINDOWS, MAC, IOS & ANDROID
           ======================================================== */
        :root {
            --sat: env(safe-area-inset-top, 0px);
            --sab: env(safe-area-inset-bottom, 0px);
            --sal: env(safe-area-inset-left, 0px);
            --sar: env(safe-area-inset-right, 0px);
        }

        html, body {
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif !important;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
            -webkit-tap-highlight-color: transparent;
            background-color: #FAF8F5;
            color: #0F172A;
            font-weight: 500;
        }

        /* Tối ưu tốc độ chạm trên màn hình cảm ứng iPhone & Android, bỏ độ trễ 300ms */
        button, a, input, select, textarea, [role="button"] {
            touch-action: manipulation;
        }

        /* Chống lỗi phóng to tự động (Auto-Zoom) phiền toái trên iPhone / iPad khi gõ vào ô nhập */
        @media screen and (max-width: 768px) {
            input[type="text"],
            input[type="search"],
            input[type="number"],
            input[type="tel"],
            input[type="email"],
            input[type="password"],
            select,
            textarea {
                font-size: 16px !important;
            }
        }

        /* Thanh cuộn hiện đại, thanh mảnh, đồng bộ trên cả Windows (Chrome/Edge/Firefox) & Mac */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(120, 113, 108, 0.25);
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(194, 94, 0, 0.6);
        }
        * {
            scrollbar-width: thin;
            scrollbar-color: rgba(120, 113, 108, 0.25) transparent;
        }

        /* Lớp hỗ trợ vùng an toàn màn hình tai thỏ / Dynamic Island & thanh vuốt đáy iPhone */
        .pb-safe {
            padding-bottom: calc(1rem + env(safe-area-inset-bottom, 0px)) !important;
        }
        .pt-safe {
            padding-top: calc(0.5rem + env(safe-area-inset-top, 0px)) !important;
        }
        .safe-area-bottom {
            padding-bottom: env(safe-area-inset-bottom, 0px) !important;
        }

        h1, h2, h3, h4, h5, h6, .font-serif, .font-serif-luxury {
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
            font-weight: 800 !important;
            color: #0F172A;
            letter-spacing: -0.025em;
        }
        input, button, select, textarea {
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        }
        /* Bulletproof Font Awesome 6 rendering with exact weights */
        .fa, .fas, .fa-solid {
            font-family: "Font Awesome 6 Free", "Font Awesome 5 Free", FontAwesome !important;
            font-weight: 900 !important;
            font-style: normal !important;
        }
        .far, .fa-regular {
            font-family: "Font Awesome 6 Free", "Font Awesome 5 Free", FontAwesome !important;
            font-weight: 400 !important;
            font-style: normal !important;
        }
        .fab, .fa-brands {
            font-family: "Font Awesome 6 Brands", "Font Awesome 5 Brands", FontAwesome !important;
            font-weight: 400 !important;
            font-style: normal !important;
        }

        /* Clean Slick Slider Controls (suppress default pseudo-arrows) */
        .slick-prev:before, .slick-next:before {
            display: none !important;
            content: '' !important;
        }
        @media (max-width: 639px) {
            .home-hero-slider .slick-prev,
            .home-hero-slider .slick-next {
                display: none !important;
            }
        }
        .home-hero-slider .slick-prev {
            left: 16px !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            z-index: 30 !important;
            width: 46px !important;
            height: 46px !important;
        }
        .home-hero-slider .slick-next {
            right: 16px !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            z-index: 30 !important;
            width: 46px !important;
            height: 46px !important;
        }
        @media (min-width: 640px) {
            .home-hero-slider .slick-prev {
                left: 36px !important;
                width: 52px !important;
                height: 52px !important;
            }
            .home-hero-slider .slick-next {
                right: 36px !important;
                width: 52px !important;
                height: 52px !important;
            }
        }
        .slick-dots {
            bottom: 24px !important;
            z-index: 25 !important;
        }
        .slick-dots li {
            margin: 0 4px !important;
        }
        .slick-dots li button:before {
            font-size: 10px !important;
            color: #ffffff !important;
            opacity: 0.4 !important;
            transition: all 0.3s ease;
        }
        .slick-dots li.slick-active button:before {
            color: #f97316 !important;
            opacity: 1 !important;
            font-size: 13px !important;
        }

        html {
            scroll-behavior: smooth;
        }

        /* Hero Slider: Chiếm trọn 100% màn hình đầu tiên (Full viewport height cả 1 trang) */
        .hero-slider-section {
            position: relative;
            width: 100%;
            height: calc(100vh - 80px);
            height: calc(100dvh - 80px);
            min-height: 500px;
            background-color: #0c0a09;
            overflow: hidden;
        }
        @media (min-width: 640px) {
            .hero-slider-section {
                height: calc(100vh - 96px);
                height: calc(100dvh - 96px);
                min-height: 580px;
            }
        }
        .home-hero-slider,
        .home-hero-slider .slick-list,
        .home-hero-slider .slick-track,
        .home-hero-slider .slick-slide,
        .home-hero-slider .slick-slide > div {
            height: 100% !important;
        }
        .hero-slide-item {
            position: relative;
            width: 100%;
            height: 100% !important;
            min-height: 100% !important;
            display: flex !important;
            align-items: center !important;
            outline: none;
        }
    </style>

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    {{ $header ?? '' }}
</head>

<body class="flex flex-col min-h-screen pb-16 md:pb-0">
    @include('components.alert')
    <header class="bg-white border-b border-stone-200 sticky top-0 left-0 w-full z-50 shadow-2xs" x-data="{ openMenu: false }">

        <div class="max-w-[1200px] mx-auto px-4 sm:px-6 w-full">
            <div class="flex items-center justify-between h-20 sm:h-24 lg:h-[96px]">
                <!-- Logo & Brand Identity (Chuẩn logo chính thức, tinh gọn, không lặp lại tên thương hiệu) -->
                <div class="flex items-center shrink-0">
                    <a href="{{ url('/') }}" class="flex items-center group py-1" title="CIVILIZE Restaurant - Phúc Thọ, Hà Nội">
                        <img src="{{ asset('images/logo/logo.png') }}" alt="CIVILIZE Restaurant" class="h-16 sm:h-20 lg:h-[82px] w-auto object-contain transition-transform duration-200 group-hover:scale-105">
                    </a>
                </div>

                <!-- Desktop Navigation Menu (Thoáng đãng, không bao giờ bị rớt dòng chữ) -->
                <div class="hidden lg:flex items-center justify-center px-4 xl:px-8 shrink-0">
                    <x-main-menu />
                </div>

                <!-- Right Actions: Đồng bộ chiều cao, thiết kế khoa học & đẹp mắt -->
                <div class="hidden lg:flex items-center gap-3 shrink-0">
                    @php
                        $userCartKey = Auth::check() ? 'cart_' . Auth::id() : 'cart_guest_' . session()->getId();
                        $userCart = session()->get($userCartKey, []);
                        $userCartCount = is_array($userCart) ? array_sum(array_column($userCart, 'qty')) : 0;
                    @endphp

                    @if (!auth()->check())
                        <!-- KHÁCH CHƯA ĐĂNG NHẬP: Phong cách Luxury Editorial - Thuần typography, không icon thừa -->
                        <div class="flex items-center gap-1.5">
                            <a href="{{ url('/gio-hang') }}" 
                               class="inline-flex items-center gap-1 text-stone-700 hover:text-[#C25E00] font-bold text-xs uppercase tracking-wider px-2 py-2 transition-colors whitespace-nowrap">
                                <span>Giỏ hàng</span>
                                <span class="global-cart-badge px-1.5 py-0.2 rounded-full text-[10px] font-black {{ $userCartCount > 0 ? 'bg-[#C25E00] text-white' : 'bg-stone-100 text-stone-500' }}">
                                    {{ $userCartCount }}
                                </span>
                            </a>
                            <span class="text-stone-300 text-xs select-none">|</span>
                            <a href="{{ route('site.login') }}" 
                               class="inline-flex items-center text-stone-800 hover:text-[#C25E00] font-bold text-xs uppercase tracking-[0.15em] px-2 py-2 transition-colors whitespace-nowrap">
                                Đăng nhập
                            </a>
                            <span class="text-stone-300 text-xs select-none">/</span>
                            <a href="{{ route('site.register') }}" 
                               class="inline-flex items-center text-stone-600 hover:text-[#C25E00] font-medium text-xs uppercase tracking-[0.15em] px-2 py-2 transition-colors whitespace-nowrap">
                                Đăng ký
                            </a>
                        </div>

                        <div class="h-4 w-px bg-stone-300 hidden sm:block mx-1"></div>

                        <!-- Primary Booking CTA: Nút góc vuông nhẹ tinh tế, không bo tròn dạng viên thuốc -->
                        <a href="{{ route('site.booking') }}"
                           class="inline-flex items-center justify-center px-6 py-2.5 rounded-sm bg-[#C25E00] hover:bg-[#A34E00] text-white font-bold text-xs uppercase tracking-[0.15em] shadow-xs hover:shadow-md transition-all cursor-pointer whitespace-nowrap">
                            Đặt Bàn
                        </a>
                    @else
                        @php
                            $isStaffUser = Auth::check() && (Auth::user()->isAdmin() || Auth::user()->isEmployee());
                        @endphp

                        @if (!$isStaffUser)
                            <!-- 1. Giỏ hàng: Typography thanh lịch kèm số lượng (Chỉ dành cho Khách hàng) -->
                            <a href="{{ url('/gio-hang') }}" 
                               class="group inline-flex items-center gap-1.5 px-3 py-2 text-stone-700 hover:text-[#C25E00] transition-colors"
                               title="Giỏ hàng món ăn của bạn">
                                <span class="text-xs sm:text-[13px] font-bold tracking-wide uppercase">Giỏ hàng</span>
                                <span class="global-cart-badge px-1.5 py-0.5 rounded-full text-[10px] font-black {{ $userCartCount > 0 ? 'bg-[#C25E00] text-white' : 'bg-stone-100 text-stone-500' }}">
                                    {{ $userCartCount }}
                                </span>
                            </a>
                        @else
                            <!-- Phím tắt Màn hình POS dành riêng cho Nhân viên & Quản trị viên -->
                            <a href="{{ route('admin.table-order.index') }}" 
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-white bg-[#C25E00] hover:bg-[#A34E00] rounded-sm transition-colors uppercase tracking-wider shadow-2xs">
                                <span>Màn hình POS</span>
                            </a>
                        @endif

                        <!-- 1.5 Chuông Thông Báo Facebook / Messenger Style -->
                        <x-frontend-notification-bell />

                        <!-- Vách ngăn dọc mờ phân tách nhóm mua sắm và tài khoản -->
                        <div class="h-8 w-px bg-stone-200 hidden sm:block mx-1"></div>

                        <!-- 2. Menu Tài Khoản (Động theo vai trò Admin, Nhân viên, Khách hàng) -->
                        @php
                            $userRole = Auth::user()->roles ?? 'customer';
                            $roleLabel = 'Thành viên';
                            $roleColor = 'text-stone-400';
                            $dropdownTitle = 'Tài khoản thành viên';

                            if ($userRole === 'admin') {
                                $roleLabel = 'Quản trị viên';
                                $roleColor = 'text-[#C25E00] font-semibold';
                                $dropdownTitle = 'Tài khoản Quản trị viên';
                            } elseif ($userRole === 'employee') {
                                $roleLabel = 'Nhân viên';
                                $roleColor = 'text-blue-600 font-semibold';
                                $dropdownTitle = 'Tài khoản Nhân viên';
                            }
                        @endphp
                        <div x-data="{ open: false }" class="relative inline-block text-left">
                            <button @click="open = !open" 
                                    type="button"
                                    class="flex items-center gap-2 py-1 text-stone-800 hover:text-[#C25E00] transition-colors cursor-pointer group text-left">
                                <span class="w-8 h-8 rounded-full bg-stone-100 text-stone-800 font-bold flex items-center justify-center text-xs border border-stone-200 group-hover:border-[#C25E00]/40 transition-colors shrink-0">
                                    {{ mb_strtoupper(mb_substr(Auth::user()->fullname ?? Auth::user()->name ?? 'U', 0, 1)) }}
                                </span>
                                <span class="font-bold text-xs sm:text-sm text-stone-900 group-hover:text-[#C25E00] transition-colors whitespace-nowrap flex items-center gap-1">
                                    {{ Auth::user()->fullname ?? Auth::user()->name }}
                                    <i class="fas fa-chevron-down text-[9px] text-stone-400 group-hover:text-[#C25E00] transition-colors"></i>
                                </span>
                            </button>

                            <!-- Dropdown Menu: Kích thước chữ rõ ràng, dễ nhìn, khoảng cách bấm thoải mái -->
                            <div x-cloak x-show="open" @click.away="open = false" @keydown.escape.window="open = false" x-transition style="display: none;"
                                 class="absolute right-0 mt-2 bg-white border border-stone-200 shadow-xl z-50 w-72 py-2 overflow-hidden rounded-sm">
                                <div class="px-5 py-3.5 border-b border-stone-100 bg-stone-50/70">
                                    <div class="flex items-center justify-between">
                                        <p class="text-xs text-stone-400 font-semibold uppercase tracking-wider">Tài khoản</p>
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded {{ $userRole === 'admin' ? 'bg-orange-100 text-[#C25E00]' : ($userRole === 'employee' ? 'bg-blue-100 text-blue-700' : 'bg-stone-200 text-stone-600') }}">
                                            {{ $roleLabel }}
                                        </span>
                                    </div>
                                    <p class="text-base font-bold text-stone-900 truncate mt-1">{{ Auth::user()->fullname ?? Auth::user()->name }}</p>
                                </div>

                                @if($userRole === 'admin')
                                    <a href="{{ route('admin.dashboard') }}"
                                       class="flex items-center px-5 py-3 text-sm font-bold text-[#C25E00] hover:bg-orange-50/70 transition-colors">
                                        <span>Vào trang quản trị</span>
                                    </a>
                                @elseif($userRole === 'employee')
                                    <a href="{{ route('admin.table-order.index') }}"
                                       class="flex items-center px-5 py-3 text-sm font-bold text-[#C25E00] hover:bg-orange-50/70 transition-colors">
                                        <span>Màn hình Bán hàng (POS)</span>
                                    </a>
                                @endif

                                <a href="{{ route('site.profile') }}"
                                   class="flex items-center px-5 py-3 text-sm font-medium text-stone-700 hover:text-[#C25E00] hover:bg-stone-50 transition-colors">
                                    <span>Hồ sơ cá nhân</span>
                                </a>
                                @if(!$isStaffUser)
                                    <a href="{{ url('/gio-hang') }}"
                                       class="flex items-center justify-between px-5 py-3 text-sm font-medium text-stone-700 hover:text-[#C25E00] hover:bg-stone-50 transition-colors">
                                        <span>Giỏ hàng món ăn</span>
                                        <span class="global-cart-badge-dropdown {{ $userCartCount > 0 ? '' : 'hidden' }} bg-[#C25E00] text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $userCartCount }}</span>
                                    </a>
                                @endif
                                <a href="{{ route('site.orders') }}"
                                   class="flex items-center px-5 py-3 text-sm font-medium text-stone-700 hover:text-[#C25E00] hover:bg-stone-50 transition-colors">
                                    <span>Đơn hàng đã đặt</span>
                                </a>
                                <a href="{{ route('site.favorites') }}"
                                   class="flex items-center px-5 py-3 text-sm font-medium text-stone-700 hover:text-[#C25E00] hover:bg-stone-50 transition-colors">
                                    <span>Món ăn yêu thích</span>
                                </a>

                                <div class="border-t border-stone-100 my-1.5"></div>
                                <form action="{{ route('site.logout') }}" method="POST" class="m-0 p-0">
                                    @csrf
                                    <button type="submit"
                                            class="w-full flex items-center px-5 py-3 text-sm font-semibold text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer bg-transparent border-0 text-left">
                                        <span>Đăng xuất</span>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- 3. Primary Booking CTA: Nút ĐẶT BÀN nổi bật -->
                        <a href="{{ route('site.booking') }}"
                           class="inline-flex items-center justify-center px-6 py-2.5 rounded-sm bg-[#C25E00] hover:bg-[#A34E00] text-white font-bold text-xs uppercase tracking-[0.15em] shadow-xs hover:shadow-md transition-all cursor-pointer whitespace-nowrap ml-1">
                            ĐẶT BÀN
                        </a>
                    @endif
                </div>

                <!-- Mobile & Tablet Hamburger Button & Quick Actions -->
                <div class="flex items-center gap-2 lg:hidden">
                    <!-- Chuông thông báo trên Mobile / Tablet -->
                    <x-frontend-notification-bell />

                    @php
                        $isStaffUserMobile = Auth::check() && (Auth::user()->isAdmin() || Auth::user()->isEmployee());
                    @endphp
                    @if (!$isStaffUserMobile)
                        <a href="{{ url('/gio-hang') }}" class="relative px-2.5 py-1 text-xs font-bold text-stone-700 hover:text-[#C25E00] uppercase tracking-wider border border-stone-200 rounded">
                            <span>Giỏ hàng</span>
                            <span class="global-cart-badge-mobile {{ $userCartCount > 0 ? '' : 'hidden' }} ml-1 bg-[#C25E00] text-white text-[10px] font-bold px-1.5 py-0.2 rounded-full">
                                {{ $userCartCount }}
                            </span>
                        </a>
                    @else
                        <a href="{{ route('admin.table-order.index') }}" class="px-2.5 py-1 text-xs font-bold text-white bg-[#C25E00] uppercase tracking-wider rounded">
                            <span>POS</span>
                        </a>
                    @endif
                    <button @click="openMenu = !openMenu" 
                            class="p-2 text-stone-700 hover:text-stone-900 rounded-none sm:rounded-sm focus:outline-none"
                            aria-label="Toggle navigation">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- Mobile Drawer Menu -->
            <div x-cloak x-show="openMenu" 
                 x-transition
                 style="display: none;"
                 class="lg:hidden border-t border-stone-200 py-4 px-2 space-y-3 bg-white">
                <x-main-menu />
                <div class="pt-3 border-t border-stone-100 flex flex-col gap-2">
                    <a href="{{ route('site.booking') }}" 
                       class="w-full text-center py-2.5 rounded-none sm:rounded-sm bg-[#C25E00] text-white font-bold text-xs uppercase tracking-[0.15em] shadow-xs">
                        Đặt Bàn Giữ Chỗ
                    </a>
                    @if (!auth()->check())
                        <div class="pt-2 text-center text-xs text-stone-600">
                            <span>Khách hàng thành viên? </span>
                            <a href="{{ route('site.login') }}" class="font-bold text-[#C25E00] hover:underline">
                                Đăng nhập tài khoản
                            </a>
                        </div>
                    @else
                        <a href="{{ route('site.profile') }}" class="text-xs text-stone-600 px-3 py-1.5">Tài khoản của tôi</a>
                        <a href="{{ route('site.orders') }}" class="text-xs text-stone-600 px-3 py-1.5">Đơn hàng của tôi</a>
                        <form action="{{ route('site.logout') }}" method="POST" class="m-0 p-0 w-full">
                            @csrf
                            <button type="submit" class="w-full text-left text-xs text-rose-600 px-3 py-1.5 cursor-pointer bg-transparent border-0 font-medium">
                                Đăng xuất
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Subtle Scroll to Top Button (Đặt phía trên nút chuông gọi bàn, không đè lấn) -->
        <button id="scrollToTop" 
                class="fixed bottom-36 md:bottom-20 left-4 sm:left-6 z-40 bg-stone-900/90 hover:bg-[#C25E00] text-white rounded-none sm:rounded-sm w-10 h-10 hidden items-center justify-center shadow-md transition-all duration-300 cursor-pointer"
                title="Lên đầu trang">
            <i class="fas fa-chevron-up text-xs"></i>
        </button>

        <script>
            const scrollToTopButton = document.getElementById("scrollToTop");
            if (scrollToTopButton) {
                window.addEventListener("scroll", () => {
                    if (window.scrollY > 300) {
                        scrollToTopButton.classList.remove("hidden");
                        scrollToTopButton.classList.add("flex");
                    } else {
                        scrollToTopButton.classList.remove("flex");
                        scrollToTopButton.classList.add("hidden");
                    }
                });

                scrollToTopButton.addEventListener("click", () => {
                    window.scrollTo({
                        top: 0,
                        behavior: "smooth"
                    });
                });
            }
        </script>

        <style>
            .only-mobile {
                display: block;
            }

            @media (min-width: 768px) {
                .only-mobile {
                    display: none;
                }
            }

            .slider-container {
                position: relative;
                width: 3.5rem;
                height: 3.5rem;
            }

            .slider-container a {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                display: none;
                justify-content: center;
                align-items: center;
                transition: all 0.3s ease-in-out;
            }
        </style>
    </header>
    <main class="flex-grow">
        {{ $slot }}
    </main>
    <!-- ======================================================== -->
    <!-- FOOTER: CIVILIZE LUXURY RESTAURANT (PROMAX VIP DESIGN)    -->
    <!-- ======================================================== -->
    <footer class="relative bg-gradient-to-b from-stone-900 via-stone-950 to-black text-white overflow-hidden">
        <!-- Top Signature Orange Border Accent (Viền màu cam nhận diện thương hiệu) -->
        <div class="w-full h-1 bg-gradient-to-r from-orange-500 via-amber-400 to-orange-500 shadow-md shadow-orange-500/30"></div>

        <!-- Subtle Ambient Background Glow -->
        <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[800px] h-60 bg-amber-500/5 rounded-full blur-3xl pointer-events-none"></div>

        <div class="container mx-auto px-4 sm:px-6 lg:px-8 pt-12 pb-8 relative z-10">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-8 lg:gap-10 pb-12 border-b border-stone-800/80">
                
                <!-- CỘT 1: THƯƠNG HIỆU & LOGO VỀ TRANG CHỦ (3 CỘT) -->
                <div class="lg:col-span-3 flex flex-col justify-between">
                    <div>
                        <!-- Logo Clickable Back to Homepage (To & Cân đối) -->
                        <a href="{{ url('/') }}" 
                           class="inline-block group transition-transform duration-300 hover:scale-105 mb-4" 
                           title="Trở về Trang chủ CIVILIZE Restaurant">
                            <img src="{{ asset('images/logo/logo.png') }}" 
                                 alt="CIVILIZE Restaurant" 
                                 class="h-24 sm:h-28 w-auto max-w-[240px] drop-shadow-2xl object-contain brightness-105">
                        </a>

                        <p class="text-stone-400 text-xs sm:text-sm leading-relaxed mb-6 pr-2">
                            <span class="font-bold text-stone-200">CIVILIZE Restaurant</span> — Nhà hàng ẩm thực tại Phúc Thọ, Hà Nội. Chuyên phục vụ mâm cơm gia đình truyền thống, lẩu nướng và hải sản tươi sạch trong không gian ấm cúng, thoáng đãng.
                        </p>
                    </div>

                    <!-- Mạng xã hội / Social Links -->
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-400/90 block mb-3">Kết nối cùng CIVILIZE</span>
                        <div class="flex items-center gap-2.5">
                            <a href="https://www.facebook.com/" target="_blank" rel="noopener noreferrer" 
                               class="hover-social-fb w-9 h-9 rounded-none sm:rounded-sm bg-stone-800/90 text-stone-300 flex items-center justify-center transition-all duration-300 shadow-xs border border-stone-700/60" 
                               title="Facebook">
                                <i class="fab fa-facebook-f text-sm"></i>
                            </a>
                            <a href="https://www.instagram.com/" target="_blank" rel="noopener noreferrer" 
                               class="hover-social-insta w-9 h-9 rounded-none sm:rounded-sm bg-stone-800/90 text-stone-300 flex items-center justify-center transition-all duration-300 shadow-xs border border-stone-700/60" 
                               title="Instagram">
                                <i class="fab fa-instagram text-sm"></i>
                            </a>
                            <a href="https://www.tiktok.com/" target="_blank" rel="noopener noreferrer" 
                               class="hover-social-tiktok w-9 h-9 rounded-none sm:rounded-sm bg-stone-800/90 text-stone-300 flex items-center justify-center transition-all duration-300 shadow-xs border border-stone-700/60" 
                               title="TikTok">
                                <i class="fab fa-tiktok text-sm"></i>
                            </a>
                            <a href="https://www.youtube.com/" target="_blank" rel="noopener noreferrer" 
                               class="hover-social-yt w-9 h-9 rounded-none sm:rounded-sm bg-stone-800/90 text-stone-300 flex items-center justify-center transition-all duration-300 shadow-xs border border-stone-700/60" 
                               title="YouTube">
                                <i class="fab fa-youtube text-sm"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- CỘT 2: THÔNG TIN LIÊN HỆ (4 CỘT - Rộng rãi, không bị rớt chữ xuống dòng) -->
                <div class="lg:col-span-4">
                    <h3 class="text-white font-bold text-sm sm:text-base uppercase tracking-wider mb-5">
                        Thông Tin Liên Hệ
                    </h3>

                    <ul class="space-y-3.5 text-xs sm:text-sm text-stone-300">
                        <li class="flex items-start gap-2.5">
                            <i class="fas fa-location-dot text-amber-500 mt-1 shrink-0 text-sm"></i>
                            <div class="leading-relaxed">
                                <span class="text-stone-400 text-xs font-medium">Địa chỉ:</span>
                                <a href="https://maps.google.com/?q=21.1097,105.5620" target="_blank" rel="noopener noreferrer" class="text-stone-300 hover:text-amber-400 transition-colors ml-1">
                                    {{ $restaurantSettings['address'] ?? 'Cụm 1, TT. Phúc Thọ, Huyện Phúc Thọ, TP. Hà Nội.' }}
                                </a>
                            </div>
                        </li>

                        <li class="flex items-center gap-2.5">
                            <i class="fas fa-phone text-amber-500 shrink-0 text-sm"></i>
                            <div>
                                <span class="text-stone-400 text-xs font-medium">Hotline:</span>
                                <a href="tel:{{ preg_replace('/[^0-9]/', '', $restaurantSettings['hotline'] ?? '0338990538') }}" class="text-white hover:text-amber-400 font-semibold ml-1 transition-colors">
                                    {{ $restaurantSettings['hotline'] ?? '0338 990 538' }}
                                </a>
                            </div>
                        </li>

                        <li class="flex items-center gap-2.5">
                            <i class="fas fa-envelope text-amber-500 shrink-0 text-sm"></i>
                            <div>
                                <span class="text-stone-400 text-xs font-medium">Email:</span>
                                <a href="mailto:{{ $restaurantSettings['email'] ?? 'tnguyenanh189@gmail.com' }}" class="text-stone-300 hover:text-amber-400 ml-1 transition-colors">
                                    {{ $restaurantSettings['email'] ?? 'tnguyenanh189@gmail.com' }}
                                </a>
                            </div>
                        </li>

                        <li class="flex items-center gap-2.5">
                            <i class="fas fa-clock text-amber-500 shrink-0 text-sm"></i>
                            <div>
                                <span class="text-stone-400 text-xs font-medium">Giờ mở cửa:</span>
                                <span class="text-stone-300 ml-1">{{ $restaurantSettings['opening_hours'] ?? '09:00 – 23:00 tất cả các ngày trong tuần' }}</span>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- CỘT 3: CHÍNH SÁCH & ĐIỀU HƯỚNG (2 CỘT) -->
                <div class="lg:col-span-2">
                    <h3 class="text-white font-bold text-sm sm:text-base uppercase tracking-wider mb-5">
                        Chính Sách
                    </h3>

                    <x-footer-menu />
                </div>

                <!-- CỘT 4: THƯ VIỆN ẢNH & KHOẢNH KHẮC (3 CỘT - Bỏ nút Đặt Bàn dư thừa) -->
                <div class="lg:col-span-3">
                    <h3 class="text-white font-bold text-sm sm:text-base uppercase tracking-wider mb-5">
                        Khoảnh Khắc Ẩm Thực
                    </h3>

                    <!-- 6 Real Food & Space Thumbnail Grid -->
                    @php
                        $realFooterPhotos = [
                            ['img' => 'images/product/20260106145148.jpg', 'title' => 'Lẩu thái hải sản', 'url' => route('site.product.gridview')],
                            ['img' => 'images/product/20260106145629.jpg', 'title' => 'Lẩu bắp bò nhúng dấm', 'url' => route('site.product.gridview')],
                            ['img' => 'images/product/20260112133550.jpg', 'title' => 'Bò xào ngồng tỏi', 'url' => route('site.product.gridview')],
                            ['img' => 'images/product/20260112140331.webp', 'title' => 'Salad rau trộn', 'url' => route('site.product.gridview')],
                            ['img' => 'images/product/20260112141146.jpg', 'title' => 'Khoai chiên lắc phô mai', 'url' => route('site.product.gridview')],
                            ['img' => 'images/gallery/space_family.jpg', 'title' => 'Không gian tiệc gia đình', 'url' => route('site.image')],
                        ];
                    @endphp
                    <div class="grid grid-cols-3 gap-2">
                        @foreach ($realFooterPhotos as $item)
                            <a href="{{ $item['url'] }}" 
                               class="group relative aspect-square rounded-none sm:rounded-sm overflow-hidden bg-stone-900 border border-stone-800 shadow-2xs block"
                               title="{{ $item['title'] }}">
                                <img src="{{ asset($item['img']) }}" 
                                     alt="{{ $item['title'] }}" 
                                     class="w-full h-full object-cover group-hover:scale-115 transition-transform duration-500">
                                <div class="absolute inset-0 bg-orange-600/0 group-hover:bg-orange-600/40 transition-colors duration-300 flex items-center justify-center">
                                    <i class="fas fa-utensils text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300 text-xs"></i>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    <p class="text-[11px] text-stone-400 mt-2.5 leading-relaxed">
                        Khám phá trọn vẹn hương vị tinh tế và không gian đầm ấm tại CIVILIZE.
                    </p>
                </div>

            </div>

            <!-- COPYRIGHT BOTTOM BAR -->
            <div class="mt-8 flex flex-col sm:flex-row items-center justify-between text-xs text-stone-500 gap-3">
                <p>© {{ date('Y') }} <span class="font-bold text-stone-300">CIVILIZE Restaurant</span>. Tất cả các quyền được bảo lưu.</p>
                
                <div class="flex flex-wrap items-center justify-center gap-4 text-stone-400">
                    <a href="{{ url('/') }}" class="hover:text-amber-400 transition-colors">Trang chủ</a>
                    <span class="text-stone-700">•</span>
                    <a href="{{ route('site.product.gridview') }}" class="hover:text-amber-400 transition-colors">Thực đơn</a>
                    <span class="text-stone-700">•</span>
                    <a href="{{ route('site.about') }}" class="hover:text-amber-400 transition-colors">Giới thiệu</a>
                    <span class="text-stone-700">•</span>
                    <a href="{{ route('site.contact') }}" class="hover:text-amber-400 transition-colors">Liên hệ</a>
                </div>
            </div>
        </div>
    </footer>
    {{ $footer ?? '' }}

    <script>
        // Global Toast Notification (Phong cách tối giản, tuyệt đối không có icon hay emoji theo yêu cầu)
        window.showGlobalCartToast = function(message, type = 'success') {
            const existing = document.getElementById('globalCartToastNotification');
            if (existing) existing.remove();

            const toast = document.createElement('div');
            toast.id = 'globalCartToastNotification';
            toast.className = `fixed bottom-20 right-5 z-[999999] px-4 py-2.5 rounded shadow-xl text-xs font-bold flex items-center transform transition-all duration-300 ease-out translate-y-2 opacity-0 ${
                type === 'success' ? 'bg-stone-900 text-white border border-[#C25E00]' :
                (type === 'error' ? 'bg-red-900 text-white border border-red-700' : 'bg-stone-900 text-white border border-stone-700')
            }`;
            toast.style.zIndex = '999999';
            toast.textContent = message;
            document.body.appendChild(toast);

            setTimeout(() => toast.classList.remove('translate-y-2', 'opacity-0'), 10);
            setTimeout(() => {
                toast.classList.add('translate-y-2', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 2200);
        };

        // Global Cập nhật Badge giỏ hàng trên toàn trang
        window.updateGlobalCartBadges = function(count) {
            count = parseInt(count, 10) || 0;
            document.querySelectorAll('.global-cart-badge').forEach(badge => {
                badge.textContent = count;
                if (count > 0) {
                    badge.classList.remove('bg-stone-100', 'text-stone-500');
                    badge.classList.add('bg-[#C25E00]', 'text-white');
                } else {
                    badge.classList.add('bg-stone-100', 'text-stone-500');
                    badge.classList.remove('bg-[#C25E00]', 'text-white');
                }
            });
            document.querySelectorAll('.global-cart-badge-dropdown').forEach(badge => {
                badge.textContent = count;
                if (count > 0) badge.classList.remove('hidden');
                else badge.classList.add('hidden');
            });
            document.querySelectorAll('.global-cart-badge-mobile').forEach(badge => {
                badge.textContent = count;
                if (count > 0) badge.classList.remove('hidden');
                else badge.classList.add('hidden');
            });
        };

        // Global Add to Cart AJAX (Không bao giờ reload trang)
        window.addToCartGlobal = function(productId, btnElement, qty = 1) {
            if (!productId) return;

            let originalHtml = '';
            if (btnElement) {
                originalHtml = btnElement.innerHTML;
                btnElement.disabled = true;
                btnElement.textContent = 'Đang thêm...';
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                || '{{ csrf_token() }}';

            fetch('{{ url("/addcart") }}/' + productId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ ajax: 1, qty: qty })
            })
            .then(res => res.json())
            .then(data => {
                if (btnElement) {
                    btnElement.textContent = 'Đã chọn';
                    setTimeout(() => {
                        btnElement.innerHTML = originalHtml;
                        btnElement.disabled = false;
                    }, 1000);
                }

                if (data && data.success) {
                    window.updateGlobalCartBadges(data.count);
                    if (typeof window.renderLiveCartFromData === 'function') {
                        window.renderLiveCartFromData(data);
                    }
                    window.showGlobalCartToast(data.message || 'Đã thêm món vào giỏ hàng', 'success');
                } else {
                    window.showGlobalCartToast((data && data.message) ? data.message : 'Không thể thêm món vào giỏ', 'error');
                }
            })
            .catch(err => {
                console.error('Lỗi thêm món:', err);
                if (btnElement) {
                    btnElement.innerHTML = originalHtml;
                    btnElement.disabled = false;
                }
                window.showGlobalCartToast('Không thể kết nối đến máy chủ', 'error');
            });
        };

        // Đồng bộ alias để tương thích với tất cả trang gọi addToCartAjax
        window.addToCartAjax = window.addToCartGlobal;

        // Chặn triệt để mọi form submit có action /addcart/ để không bao giờ bị load lại trang
        document.addEventListener('DOMContentLoaded', function () {
            var savedScrollY = sessionStorage.getItem('savedScrollY');
            if (savedScrollY !== null) {
                window.scrollTo(0, parseInt(savedScrollY, 10) || 0);
                sessionStorage.removeItem('savedScrollY');
            }

            document.addEventListener('submit', function (e) {
                const form = e.target;
                if (form && form.action && form.action.includes('/addcart/')) {
                    e.preventDefault();
                    e.stopPropagation();
                    const match = form.action.match(/\/addcart\/(\d+)/);
                    if (match && match[1]) {
                        const btn = form.querySelector('button[type="submit"]') || form.querySelector('button');
                        const qtyInput = form.querySelector('input[name="qty"]');
                        const qty = qtyInput ? (parseInt(qtyInput.value, 10) || 1) : 1;
                        window.addToCartGlobal(match[1], btn, qty);
                    }
                    return false;
                }
            }, true);
        });
    </script>

    <!-- Service Call Widget (Gọi nhân viên phục vụ tại bàn) -->
    <x-service-call-button />

    <!-- Chatbot Widget -->
    <x-chatbot />

    <!-- Mobile Bottom Navigation Bar (Màn hình điện thoại < 768px) -->
    <x-mobile-bottom-nav />

    <!-- PWA Install Banner (Máy tính & Điện thoại) -->
    <x-pwa-install-banner />

    <script src="{{ asset('plugins/bootstrap/js/bootstrap.js') }}"></script>
    <script src="{{ asset('plugins/bootstrap/js/boot.js') }}"></script>
</body>

</html>
