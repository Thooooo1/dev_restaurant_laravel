<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, viewport-fit=cover, interactive-widget=resizes-content">
    <meta name="theme-color" content="#1890FF" media="(prefers-color-scheme: light)">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="CIVILIZE Admin">
    <meta name="application-name" content="CIVILIZE Admin">
    <meta name="format-detection" content="telephone=no">
    <title>Quản lý Nhà hàng - CIVILIZE (Ant Design Pro)</title>
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

    <!-- Google Fonts: Plus Jakarta Sans & Inter (Chuẩn nét, sắc sảo cho tiếng Việt) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,500;1,700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome 6 Icons CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    
    <!-- Tailwind CSS (utility helper) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- AdminLTE & DataTables styles -->
    <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/DataTables/datatables.css') }}" />
    <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js"></script>
    
    <style>
        /* ==========================================================================
           ANT DESIGN PRO DESIGN SYSTEM (CHUẨN DOANH NGHIỆP PRO.ANT.DESIGN)
           - Phân cấp thông tin rõ ràng, tối giản, không trang trí thừa
           - Màu canvas: #F0F2F5, Thẻ: #FFFFFF bo góc 4px, viền 1px #F0F0F0
           - Text: rgba(0, 0, 0, 0.85), Subtext: rgba(0, 0, 0, 0.45)
           ========================================================================== */
        :root {
            --ant-primary: #F47716;
            --ant-primary-hover: #ea580c;
            --ant-primary-bg: #FFF2E8;
            --ant-primary-border: #FFBB96;
            --ant-success: #52C41A;
            --ant-processing: #1890FF;
            --ant-warning: #FAAD14;
            --ant-error: #FF4D4F;
            --ant-bg-layout: #F0F2F5;
            --ant-bg-container: #FFFFFF;
            --ant-border-color: #F0F0F0;
            --ant-text-heading: rgba(0, 0, 0, 0.85);
            --ant-text-primary: rgba(0, 0, 0, 0.85);
            --ant-text-secondary: rgba(0, 0, 0, 0.45);
        }

        /* CROSS-PLATFORM SUPPORT: WINDOWS, MAC, IOS & ANDROID */
        html, body {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
            -webkit-tap-highlight-color: transparent;
        }

        button, a, input, select, textarea, [role="button"] {
            touch-action: manipulation;
        }

        @media screen and (max-width: 768px) {
            input, select, textarea {
                font-size: 16px !important;
            }
        }

        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.15);
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 0, 0, 0.3);
        }
        * {
            scrollbar-width: thin;
            scrollbar-color: rgba(0, 0, 0, 0.15) transparent;
        }

        /* 1. BẢO VỆ TUYỆT ĐỐI FONT AWESOME ICONS */
        i.fa, i.fas, i.fa-solid, i.far, i.fa-regular, i.fab, i.fa-brands,
        span.fa, span.fas, span.fa-solid, span.far, span.fa-regular, span.fab, span.fa-brands,
        [class^="fa-"], [class*=" fa-"] {
            display: inline-block !important;
            font-style: normal !important;
            font-variant: normal !important;
            text-rendering: auto !important;
            line-height: 1 !important;
        }

        .fa, .fas, .fa-solid,
        .fa::before, .fas::before, .fa-solid::before,
        [class*="fa-solid"]::before, [class*="fas"]::before {
            font-family: "Font Awesome 6 Free" !important;
            font-weight: 900 !important;
        }

        .far, .fa-regular,
        .far::before, .fa-regular::before,
        [class*="fa-regular"]::before, [class*="far"]::before {
            font-family: "Font Awesome 6 Free" !important;
            font-weight: 400 !important;
        }

        .fab, .fa-brands,
        .fab::before, .fa-brands::before,
        [class*="fa-brands"]::before, [class*="fab"]::before {
            font-family: "Font Awesome 6 Brands" !important;
            font-weight: 400 !important;
        }

        /* 2. TYPOGRAPHY CHUẨN ANT DESIGN PRO */
        html, body,
        h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6,
        p, span:not([class*="fa"]):not([class*="fas"]):not([class*="far"]):not([class*="fab"]),
        a:not([class*="fa"]),
        div:not([class*="fa"]),
        li, ul, ol, label, small, strong, b,
        input, button, select, textarea, optgroup,
        table, th, td, thead, tbody, tr,
        .btn, .badge, .form-control, .form-select, .card, .card-title, .card-header, .card-body,
        .nav, .nav-link, .dropdown, .dropdown-item, .dropdown-menu,
        .sidebar, .navbar, .content-wrapper, .modal, .modal-title, .modal-body,
        .dataTables_wrapper, .dataTable {
            font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif !important;
            -webkit-font-smoothing: antialiased !important;
            -moz-osx-font-smoothing: grayscale !important;
            text-rendering: optimizeLegibility !important;
        }

        body {
            font-size: 14px;
            line-height: 1.5715;
            color: rgba(0, 0, 0, 0.85);
            background-color: #F0F2F5;
        }

        /* 3. ANT DESIGN PRO BUTTONS */
        .ant-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 500;
            height: 32px;
            padding: 4px 15px;
            border-radius: 4px;
            border: 1px solid #D9D9D9;
            background-color: #FFFFFF;
            color: rgba(0, 0, 0, 0.85);
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.645, 0.045, 0.355, 1);
            user-select: none;
            text-decoration: none;
            gap: 6px;
        }
        .ant-btn:hover {
            color: #F47716;
            border-color: #F47716;
        }
        .ant-btn-primary {
            color: #FFFFFF !important;
            background-color: #F47716 !important;
            border-color: #F47716 !important;
            box-shadow: 0 2px 0 rgba(0, 0, 0, 0.043);
        }
        .ant-btn-primary:hover {
            background-color: #ea580c !important;
            border-color: #ea580c !important;
        }
        .ant-btn-danger {
            color: #FF4D4F !important;
            background-color: #FFFFFF !important;
            border-color: #FFA39E !important;
        }
        .ant-btn-danger:hover {
            color: #FFFFFF !important;
            background-color: #FF4D4F !important;
            border-color: #FF4D4F !important;
        }
        .ant-btn-sm {
            height: 28px !important;
            padding: 2px 10px !important;
            font-size: 12.5px !important;
            border-radius: 4px !important;
        }
        .ant-btn-pos {
            color: #F47716 !important;
            background-color: #FFF2E8 !important;
            border-color: #FFBB96 !important;
        }
        .ant-btn-pos:hover {
            background-color: #ffe7d6 !important;
            border-color: #F47716 !important;
        }

        /* 3.1. SHOPIFY POLARIS & ANT DESIGN BADGES - Chuẩn văn bản đen tối giản, không màu sắc */
        .polaris-badge,
        .polaris-badge--subdued,
        .polaris-badge--info,
        .polaris-badge--success,
        .polaris-badge--warning,
        .polaris-badge--critical,
        .polaris-badge--attention,
        .polaris-badge-success,
        .polaris-badge-warning,
        .polaris-badge-processing,
        .polaris-badge-error,
        .polaris-badge-default,
        .badge {
            display: inline-block !important;
            padding: 0 !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            line-height: 1.4 !important;
            border: none !important;
            border-radius: 0 !important;
            background: transparent !important;
            background-color: transparent !important;
            color: rgba(0, 0, 0, 0.85) !important;
            white-space: nowrap !important;
            box-shadow: none !important;
        }

        /* 3.2. SHOPIFY POLARIS FORM INPUTS */
        .polaris-input {
            width: 100%;
            height: 32px;
            padding: 4px 11px;
            font-size: 13.5px;
            color: rgba(0, 0, 0, 0.85);
            background-color: #FFFFFF;
            border: 1px solid #D9D9D9;
            border-radius: 4px;
            transition: all 0.2s cubic-bezier(0.645, 0.045, 0.355, 1);
            outline: none;
        }
        .polaris-input:hover {
            border-color: #F47716;
        }
        .polaris-input:focus {
            border-color: #F47716;
            box-shadow: 0 0 0 2px rgba(244, 119, 22, 0.15);
        }
        .polaris-select {
            height: 32px;
            padding: 4px 11px;
            font-size: 13px;
            color: rgba(0, 0, 0, 0.85);
            background-color: #FFFFFF;
            border: 1px solid #D9D9D9;
            border-radius: 4px;
            transition: all 0.2s;
            outline: none;
            cursor: pointer;
        }
        .polaris-select:hover, .polaris-select:focus {
            border-color: #F47716;
        }

        /* 4. ANT DESIGN SEGMENTED CONTROLS (RADIO GROUP BUTTON STYLE) */
        .ant-segmented {
            display: inline-flex;
            background-color: #F0F2F5;
            padding: 2px;
            border-radius: 4px;
            border: 1px solid #E2E8F0;
        }
        .ant-segmented-item {
            padding: 4px 12px;
            font-size: 13px;
            font-weight: 500;
            color: rgba(0, 0, 0, 0.65);
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            line-height: 1.2;
        }
        .ant-segmented-item:hover {
            color: #F47716;
        }
        .ant-segmented-item-active {
            background-color: #FFFFFF;
            color: #F47716;
            font-weight: 600;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        /* 5. ANT DESIGN PRO CARDS */
        .ant-card {
            background: #FFFFFF;
            border-radius: 4px;
            border: 1px solid #F0F0F0;
            transition: all 0.2s;
        }
        .ant-card:hover {
            border-color: #E2E8F0;
        }

        /* 6. ANT DESIGN STATUS BADGES (STATUS DOT) */
        .ant-badge-status-dot {
            position: relative;
            top: -1px;
            display: inline-block;
            width: 6px;
            height: 6px;
            vertical-align: middle;
            border-radius: 50%;
            margin-right: 6px;
        }
        .ant-badge-status-success { background-color: #52C41A; }
        .ant-badge-status-processing { background-color: #1890FF; }
        .ant-badge-status-warning { background-color: #FAAD14; }
        .ant-badge-status-error { background-color: #FF4D4F; }
        .ant-badge-status-default { background-color: #D9D9D9; }

        /* 7. ANT DESIGN PRO TABLES */
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 14px;
        }
        table thead tr th {
            background: #FAFAFA !important;
            color: rgba(0, 0, 0, 0.85) !important;
            font-weight: 600 !important;
            font-size: 13px !important;
            padding: 12px 16px !important;
            border-bottom: 1px solid #F0F0F0 !important;
            white-space: nowrap !important;
            letter-spacing: normal !important;
            text-transform: none !important;
        }
        table tbody tr td {
            padding: 12px 16px !important;
            font-size: 13.5px !important;
            color: rgba(0, 0, 0, 0.75) !important;
            border-bottom: 1px solid #F0F0F0 !important;
            background: #FFFFFF;
            vertical-align: middle !important;
            transition: background 0.2s;
        }
        table tbody tr:hover td {
            background: #FAFAFA !important;
        }

        /* 8. SCROLLBAR */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f0f2f5;
        }
        ::-webkit-scrollbar-thumb {
            background: #d9d9d9;
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #bfbfbf;
        }
        /* Sidebar Nav Links: Chặn link xanh mặc định, unselected màu chữ chuẩn nét, selected nền #FFF2E8 chữ và icon cam #F47716 */
        #sidebar a {
            text-decoration: none !important;
            color: rgba(0, 0, 0, 0.85) !important;
        }
        #sidebar a span {
            text-decoration: none !important;
            color: rgba(0, 0, 0, 0.85) !important;
        }
        #sidebar a i {
            color: rgba(0, 0, 0, 0.65) !important;
        }
        #sidebar a:hover {
            color: #111827 !important;
            background-color: #F5F5F5 !important;
            text-decoration: none !important;
        }
        #sidebar a:hover span {
            color: #111827 !important;
        }
        #sidebar a.active,
        #sidebar a[class*="bg-[#FFF2E8]"],
        #sidebar a[class*="text-[#F47716]"] {
            background-color: #FFF2E8 !important;
            color: #F47716 !important;
        }
        #sidebar a.active span,
        #sidebar a[class*="bg-[#FFF2E8]"] span,
        #sidebar a[class*="text-[#F47716]"] span {
            color: #F47716 !important;
            font-weight: 600 !important;
        }
        #sidebar a.active i,
        #sidebar a[class*="bg-[#FFF2E8]"] i,
        #sidebar a[class*="text-[#F47716]"] i {
            color: #F47716 !important;
        }
    </style>
</head>
<body class="bg-[#F0F2F5] text-slate-800 min-h-screen">
    @include('components.alert')
    
    <div class="flex min-h-screen">
        <!-- Ant Design Pro Sider (Sidebar 256px, nền trắng, viền #F0F0F0, menu chuẩn AntD) -->
        <aside id="sidebar" class="fixed left-0 top-0 z-40 flex flex-col w-[256px] h-full bg-white border-r border-[#F0F0F0] transition-transform duration-200 ease-in-out">
            <!-- Brand Logo Header (Ant Design Pro Sider Header 48px) -->
            <div class="h-14 px-4 border-b border-[#F0F0F0] bg-white flex items-center justify-center">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center justify-center w-full" title="CIVILIZE Restaurant">
                    <img src="{{ asset('images/logo/logo.png') }}" alt="CIVILIZE Restaurant" class="h-10 max-h-10 w-auto object-contain">
                </a>
            </div>
            
            <!-- Ant Design Pro SiderMenu -->
            <nav class="flex-1 px-2.5 py-3 overflow-y-auto space-y-4">
                <div>
                    <div class="px-3 mb-1 text-[11px] font-medium text-[rgba(0,0,0,0.45)] uppercase tracking-wider">
                        Vận hành & Bán hàng
                    </div>
                    <div class="space-y-0.5">
                        <!-- 1. Tổng quan -->
                        @if(Auth::user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" 
                               class="flex items-center gap-3 px-3 py-2 rounded text-[13.5px] transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-[#FFF2E8] text-[#F47716] font-semibold' : 'text-[rgba(0,0,0,0.85)] hover:bg-[#F5F5F5] font-medium' }}">
                                <i class="fa-solid fa-chart-pie w-4 text-center text-sm {{ request()->routeIs('admin.dashboard') ? 'text-[#F47716]' : 'text-[rgba(0,0,0,0.65)]' }}"></i>
                                <span>Tổng quan</span>
                            </a>
                        @endif

                        <!-- 2. Bán hàng (POS) -->
                        <a href="{{ route('admin.table-order.index') }}" 
                           class="flex items-center gap-3 px-3 py-2 rounded text-[13.5px] transition-colors {{ (request()->routeIs('admin.table-order.*') || request()->routeIs('admin.table.*')) ? 'bg-[#FFF2E8] text-[#F47716] font-semibold' : 'text-[rgba(0,0,0,0.85)] hover:bg-[#F5F5F5] font-medium' }}">
                            <i class="fa-solid fa-cash-register w-4 text-center text-sm {{ (request()->routeIs('admin.table-order.*') || request()->routeIs('admin.table.*')) ? 'text-[#F47716]' : 'text-[rgba(0,0,0,0.65)]' }}"></i>
                            <span>Bán hàng (POS)</span>
                        </a>

                        <!-- 3. Đơn hàng giao -->
                        <a href="{{ url('admin/order/') }}" 
                           class="flex items-center gap-3 px-3 py-2 rounded text-[13.5px] transition-colors {{ (request()->is('admin/order*') && !request()->is('admin/order-history*')) ? 'bg-[#FFF2E8] text-[#F47716] font-semibold' : 'text-[rgba(0,0,0,0.85)] hover:bg-[#F5F5F5] font-medium' }}">
                            <i class="fa-solid fa-truck-ramp-box w-4 text-center text-sm {{ (request()->is('admin/order*') && !request()->is('admin/order-history*')) ? 'text-[#F47716]' : 'text-[rgba(0,0,0,0.65)]' }}"></i>
                            <span>Đơn hàng giao</span>
                        </a>

                        <!-- 4. Đặt bàn -->
                        <a href="{{ url('admin/booking/') }}" 
                           class="flex items-center gap-3 px-3 py-2 rounded text-[13.5px] transition-colors {{ request()->is('admin/booking*') ? 'bg-[#FFF2E8] text-[#F47716] font-semibold' : 'text-[rgba(0,0,0,0.85)] hover:bg-[#F5F5F5] font-medium' }}">
                            <i class="fa-solid fa-calendar-check w-4 text-center text-sm {{ request()->is('admin/booking*') ? 'text-[#F47716]' : 'text-[rgba(0,0,0,0.65)]' }}"></i>
                            <span>Đặt bàn</span>
                        </a>

                        @if(Auth::user()->isAdmin())
                            <!-- 5. Thực đơn & giá -->
                            <a href="{{ url('admin/product/') }}" 
                               class="flex items-center gap-3 px-3 py-2 rounded text-[13.5px] transition-colors {{ request()->is('admin/product*') ? 'bg-[#FFF2E8] text-[#F47716] font-semibold' : 'text-[rgba(0,0,0,0.85)] hover:bg-[#F5F5F5] font-medium' }}">
                                <i class="fa-solid fa-utensils w-4 text-center text-sm {{ request()->is('admin/product*') ? 'text-[#F47716]' : 'text-[rgba(0,0,0,0.65)]' }}"></i>
                                <span>Thực đơn & giá</span>
                            </a>

                            <!-- 6. Quản lý nhập kho -->
                            <a href="{{ route('admin.import-goods.index') }}" 
                               class="flex items-center gap-3 px-3 py-2 rounded text-[13.5px] transition-colors {{ request()->routeIs('admin.import-goods.*') ? 'bg-[#FFF2E8] text-[#F47716] font-semibold' : 'text-[rgba(0,0,0,0.85)] hover:bg-[#F5F5F5] font-medium' }}">
                                <i class="fa-solid fa-boxes-packing w-4 text-center text-sm {{ request()->routeIs('admin.import-goods.*') ? 'text-[#F47716]' : 'text-[rgba(0,0,0,0.65)]' }}"></i>
                                <span>Quản lý nhập kho</span>
                            </a>

                            <!-- 7. Báo cáo & Doanh thu -->
                            <a href="{{ route('admin.order-history.index') }}" 
                               class="flex items-center gap-3 px-3 py-2 rounded text-[13.5px] transition-colors {{ request()->routeIs('admin.order-history.*') ? 'bg-[#FFF2E8] text-[#F47716] font-semibold' : 'text-[rgba(0,0,0,0.85)] hover:bg-[#F5F5F5] font-medium' }}">
                                <i class="fa-solid fa-file-invoice-dollar w-4 text-center text-sm {{ request()->routeIs('admin.order-history.*') ? 'text-[#F47716]' : 'text-[rgba(0,0,0,0.65)]' }}"></i>
                                <span>Báo cáo & Doanh thu</span>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- QUẢN TRỊ & HỆ THỐNG -->
                @if(Auth::user()->isAdmin())
                <div>
                    <div class="px-3 mb-1 text-[11px] font-medium text-[rgba(0,0,0,0.45)] uppercase tracking-wider">
                        Quản trị & Hệ thống
                    </div>
                    <div class="space-y-0.5">
                        <a href="{{ route('admin.user.employees') }}" 
                           class="flex items-center gap-3 px-3 py-2 rounded text-[13.5px] transition-colors {{ request()->routeIs('admin.user.employees') ? 'bg-[#FFF2E8] text-[#F47716] font-semibold' : 'text-[rgba(0,0,0,0.85)] hover:bg-[#F5F5F5] font-medium' }}">
                            <i class="fa-solid fa-users-gear w-4 text-center text-sm {{ request()->routeIs('admin.user.employees') ? 'text-[#F47716]' : 'text-[rgba(0,0,0,0.65)]' }}"></i>
                            <span>Quản lý nhân viên</span>
                        </a>

                        <a href="{{ route('admin.user.customers') }}" 
                           class="flex items-center gap-3 px-3 py-2 rounded text-[13.5px] transition-colors {{ request()->routeIs('admin.user.customers') ? 'bg-[#FFF2E8] text-[#F47716] font-semibold' : 'text-[rgba(0,0,0,0.85)] hover:bg-[#F5F5F5] font-medium' }}">
                            <i class="fa-solid fa-users w-4 text-center text-sm {{ request()->routeIs('admin.user.customers') ? 'text-[#F47716]' : 'text-[rgba(0,0,0,0.65)]' }}"></i>
                            <span>Quản lý khách hàng</span>
                        </a>

                        <!-- Cài đặt hệ thống (Dành riêng cho Admin) -->
                        <a href="{{ route('admin.setting.index') }}" 
                           class="flex items-center gap-3 px-3 py-2 rounded text-[13.5px] transition-colors {{ request()->routeIs('admin.setting.*') ? 'bg-[#FFF2E8] text-[#F47716] font-semibold' : 'text-[rgba(0,0,0,0.85)] hover:bg-[#F5F5F5] font-medium' }}">
                            <i class="fa-solid fa-gear w-4 text-center text-sm {{ request()->routeIs('admin.setting.*') ? 'text-[#F47716]' : 'text-[rgba(0,0,0,0.65)]' }}"></i>
                            <span>Cài đặt hệ thống</span>
                        </a>
                    </div>
                </div>
                @endif

                <!-- KHU VỰC DỮ LIỆU & BỔ TRỢ -->
                @if(Auth::user()->isAdmin())
                <div>
                    <div class="px-3 mb-1 text-[11px] font-medium text-[rgba(0,0,0,0.45)] uppercase tracking-wider">
                        Dữ liệu & Nội dung
                    </div>
                    <div class="space-y-0.5">
                        <a href="{{ url('admin/category/') }}" 
                           class="flex items-center gap-3 px-3 py-2 rounded text-[13.5px] transition-colors {{ request()->is('admin/category*') ? 'bg-[#FFF2E8] text-[#F47716] font-semibold' : 'text-[rgba(0,0,0,0.85)] hover:bg-[#F5F5F5] font-medium' }}">
                            <i class="fa-solid fa-list-ul w-4 text-center text-sm {{ request()->is('admin/category*') ? 'text-[#F47716]' : 'text-[rgba(0,0,0,0.65)]' }}"></i>
                            <span>Danh mục món</span>
                        </a>

                        <a href="{{ url('admin/brand/') }}" 
                           class="flex items-center gap-3 px-3 py-2 rounded text-[13.5px] transition-colors {{ request()->is('admin/brand*') ? 'bg-[#FFF2E8] text-[#F47716] font-semibold' : 'text-[rgba(0,0,0,0.85)] hover:bg-[#F5F5F5] font-medium' }}">
                            <i class="fa-solid fa-tags w-4 text-center text-sm {{ request()->is('admin/brand*') ? 'text-[#F47716]' : 'text-[rgba(0,0,0,0.65)]' }}"></i>
                            <span>Thương hiệu & Ẩm thực</span>
                        </a>

                        <a href="{{ url('admin/banner/') }}" 
                           class="flex items-center gap-3 px-3 py-2 rounded text-[13.5px] transition-colors {{ request()->is('admin/banner*') ? 'bg-[#FFF2E8] text-[#F47716] font-semibold' : 'text-[rgba(0,0,0,0.85)] hover:bg-[#F5F5F5] font-medium' }}">
                            <i class="fa-solid fa-images w-4 text-center text-sm {{ request()->is('admin/banner*') ? 'text-[#F47716]' : 'text-[rgba(0,0,0,0.65)]' }}"></i>
                            <span>Banner</span>
                        </a>

                        <a href="{{ url('admin/contact/') }}" 
                           class="flex items-center gap-3 px-3 py-2 rounded text-[13.5px] transition-colors {{ request()->is('admin/contact*') ? 'bg-[#FFF2E8] text-[#F47716] font-semibold' : 'text-[rgba(0,0,0,0.85)] hover:bg-[#F5F5F5] font-medium' }}">
                            <i class="fa-solid fa-envelope w-4 text-center text-sm {{ request()->is('admin/contact*') ? 'text-[#F47716]' : 'text-[rgba(0,0,0,0.65)]' }}"></i>
                            <span>Hộp thư liên hệ</span>
                        </a>

                        <a href="{{ url('admin/inquiries/') }}" 
                           class="flex items-center gap-3 px-3 py-2 rounded text-[13.5px] transition-colors {{ request()->is('admin/inquiries*') ? 'bg-[#FFF2E8] text-[#F47716] font-semibold' : 'text-[rgba(0,0,0,0.85)] hover:bg-[#F5F5F5] font-medium' }}">
                            <i class="fa-solid fa-comments w-4 text-center text-sm {{ request()->is('admin/inquiries*') ? 'text-[#F47716]' : 'text-[rgba(0,0,0,0.65)]' }}"></i>
                            <span>Khách tư vấn</span>
                        </a>
                    </div>
                </div>
                @endif
            </nav>
            
            <!-- Ant Design Pro Sider Footer -->
            <div class="px-4 py-3 border-t border-[#F0F0F0] bg-[#FAFAFA]">
                <div class="flex items-center justify-between text-xs text-[rgba(0,0,0,0.65)] font-medium">
                    <span class="flex items-center gap-1.5">
                        <span class="ant-badge-status-dot ant-badge-status-success"></span>
                        Hệ thống sẵn sàng
                    </span>
                    <span class="font-mono text-[11px] text-[rgba(0,0,0,0.45)]">v2.6.10</span>
                </div>
            </div>
        </aside>
        
        <!-- Main Area (Offset by Ant Design Pro sider width: 256px) -->
        <div class="flex-1 ml-[256px] min-h-screen flex flex-col bg-[#F0F2F5]">
            <!-- Ant Design Pro Header (Height 48px, pure white, border-bottom #F0F0F0) -->
            <header class="h-12 bg-white border-b border-[#F0F0F0] sticky top-0 z-30 px-6 sm:px-8 flex items-center justify-between">
                <!-- Left: Mobile Menu Toggle -->
                <div class="flex items-center gap-3">
                    <button onclick="toggleSidebar()" class="lg:hidden p-1.5 text-gray-600 hover:bg-gray-100 rounded transition-colors" title="Menu">
                        <i class="fa-solid fa-bars text-sm"></i>
                    </button>
                </div>

                <!-- Right Controls: Bell, POS Button, Profile Dropdown -->
                <div class="flex items-center gap-3">
                    <!-- Chuông thông báo Ant Design có Dropdown & Realtime Polling -->
                    <div class="relative" id="systemNotificationRoot">
                        <button type="button" 
                                id="systemNotificationBtn" 
                                class="relative p-1.5 text-[rgba(0,0,0,0.65)] hover:text-[#F47716] transition-colors focus:outline-none cursor-pointer" 
                                title="Thông báo hệ thống & Nhắc nhở gọi phục vụ">
                            <i class="fa-regular fa-bell text-base transition-transform" id="bellIcon"></i>
                            <!-- Badge số lượng thông báo -->
                            <span id="notificationBadge" 
                                  class="hidden absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 bg-[#FF4D4F] text-white text-[10px] font-bold rounded-full flex items-center justify-center shadow-xs">
                                0
                            </span>
                        </button>

                        <!-- Panel Dropdown Thông Báo -->
                        <div id="systemNotificationDropdown" 
                             class="hidden absolute right-0 mt-2 w-[440px] max-w-[94vw] bg-white rounded-lg shadow-2xl border border-[#F0F0F0] z-50 overflow-hidden text-xs">
                            <!-- Header -->
                            <div class="px-4 py-3 bg-gradient-to-r from-orange-50/90 to-amber-50/90 border-b border-[#F0F0F0] flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-[#F47716] text-white flex items-center justify-center text-xs shadow-xs">
                                        <i class="fa-solid fa-bell"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider">Thông Báo & Nhắc Nhở</h3>
                                        <p class="text-[10px] text-gray-500" id="notificationSubtext">Đang kết nối hệ thống...</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <button type="button" 
                                            id="markAllBackendReadBtn" 
                                            class="text-[11px] font-semibold text-[#F47716] hover:underline px-2 py-1 rounded hover:bg-orange-100/50 transition-colors cursor-pointer" 
                                            title="Đánh dấu tất cả thông báo là đã xem">
                                        Đã xem tất cả
                                    </button>
                                    <button type="button" 
                                            id="soundToggleBtn" 
                                            class="text-gray-400 hover:text-[#F47716] p-1.5 text-xs rounded hover:bg-white/80 transition-colors cursor-pointer" 
                                            title="Bật/Tắt âm chuông nhắc nhở">
                                        <i class="fa-solid fa-volume-high" id="soundIcon"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Tabs: Thanh toán | Gọi phục vụ | Món mới | Đặt bàn -->
                            <div class="flex border-b border-[#F0F0F0] bg-gray-50 text-[11px]">
                                <button type="button" data-tab="payments" class="noti-tab-btn flex-1 py-2 text-center font-bold text-[#F47716] border-b-2 border-[#F47716] bg-white transition-colors cursor-pointer">
                                    Thanh toán (<span id="tabPaymentsCount">0</span>)
                                </button>
                                <button type="button" data-tab="calls" class="noti-tab-btn flex-1 py-2 text-center text-gray-500 hover:text-gray-800 transition-colors cursor-pointer">
                                    Gọi phục vụ (<span id="tabCallsCount">0</span>)
                                </button>
                                <button type="button" data-tab="orders" class="noti-tab-btn flex-1 py-2 text-center text-gray-500 hover:text-gray-800 transition-colors cursor-pointer">
                                    Món mới (<span id="tabOrdersCount">0</span>)
                                </button>
                                <button type="button" data-tab="bookings" class="noti-tab-btn flex-1 py-2 text-center text-gray-500 hover:text-gray-800 transition-colors cursor-pointer">
                                    Đặt bàn (<span id="tabBookingsCount">0</span>)
                                </button>
                            </div>

                            <!-- Container Danh Sách -->
                            <div class="max-h-[350px] overflow-y-auto divide-y divide-gray-100" id="notificationListContainer">
                                <div class="p-6 text-center text-gray-400 text-xs">
                                    <i class="fa-solid fa-spinner fa-spin text-lg text-orange-500 mb-2"></i>
                                    <p>Đang tải thông báo...</p>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="p-2.5 bg-gray-50 border-t border-[#F0F0F0] flex items-center justify-between text-[11px]">
                                <a href="{{ route('admin.table-order.index') }}" class="text-[#F47716] hover:underline font-semibold flex items-center gap-1.5">
                                    <i class="fa-solid fa-cash-register"></i> Mở sơ đồ bàn (POS)
                                </a>
                                <span class="text-gray-400 text-[10px] flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span> Real-time
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Nút Mở POS (Ant Design Pro Ghost Button) -->
                    <a href="{{ route('admin.table-order.index') }}" class="ant-btn ant-btn-pos text-xs">
                        <i class="fa-solid fa-cash-register text-xs"></i>
                        <span>Mở màn hình POS</span>
                    </a>

                    <!-- Profile Dropdown (Ant Design Header Avatar) -->
                    <div class="relative" id="profileDropdownRoot">
                        <button type="button" 
                                id="profileDropdownBtn" 
                                class="flex items-center gap-2 px-2 py-1 rounded hover:bg-gray-50 transition-colors focus:outline-none">
                            <div class="w-6 h-6 rounded-full bg-[#F47716] text-white font-bold text-xs flex items-center justify-center flex-shrink-0">
                                {{ mb_substr(Auth::user()->fullname ?? 'A', 0, 1) }}
                            </div>
                            <span class="hidden sm:inline text-xs font-medium text-[rgba(0,0,0,0.85)]">{{ trim(str_ireplace(['Hệ Thống', 'Hệ thống', 'hệ thống', 'Vận Hành', 'Vận hành', 'vận hành'], '', Auth::user()->fullname)) ?: (Auth::user()->roles === 'admin' ? 'Quản trị viên' : 'Nhân viên') }}</span>
                            <i class="fa-solid fa-chevron-down text-[9px] text-[rgba(0,0,0,0.45)]"></i>
                        </button>

                        <!-- Ant Design Menu Dropdown -->
                        <div id="profileDropdownMenu" 
                             class="hidden absolute right-0 mt-2 w-52 bg-white rounded shadow-lg border border-[#F0F0F0] py-1 z-50 text-xs">
                            <div class="px-4 py-2 border-b border-[#F0F0F0]">
                                <div class="font-semibold text-[rgba(0,0,0,0.85)] flex items-center justify-between">
                                    <span class="truncate">{{ trim(str_ireplace(['Hệ Thống', 'Hệ thống', 'hệ thống', 'Vận Hành', 'Vận hành', 'vận hành'], '', Auth::user()->fullname)) ?: (Auth::user()->roles === 'admin' ? 'Quản trị viên' : 'Nhân viên') }}</span>
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded {{ Auth::user()->roles === 'admin' ? 'bg-orange-100 text-[#F47716]' : 'bg-blue-100 text-blue-700' }}">
                                        {{ Auth::user()->roles === 'admin' ? 'Quản trị viên' : 'Nhân viên' }}
                                    </span>
                                </div>
                                <div class="text-[rgba(0,0,0,0.45)] text-[11px] truncate">{{ Auth::user()->email ?? Auth::user()->phone }}</div>
                            </div>
                            
                            <a href="{{ url('/') }}" target="_blank" class="flex items-center gap-2 px-4 py-2 text-[rgba(0,0,0,0.85)] hover:bg-[#F5F5F5] hover:text-[#F47716] transition-colors">
                                <i class="fa-solid fa-arrow-up-right-from-square text-xs text-[rgba(0,0,0,0.45)] w-4"></i>
                                <span>Xem trang chủ</span>
                            </a>

                            @if(Auth::user()->isAdmin())
                            <a href="{{ route('admin.user.employees') }}" class="flex items-center gap-2 px-4 py-2 text-[rgba(0,0,0,0.85)] hover:bg-[#F5F5F5] hover:text-[#F47716] transition-colors">
                                <i class="fa-solid fa-users-gear text-xs text-[rgba(0,0,0,0.45)] w-4"></i>
                                <span>Quản lý nhân viên</span>
                            </a>
                            @endif

                            <div class="border-t border-[#F0F0F0] my-1"></div>

                            <form action="{{ route('admin.logout') }}" method="POST" class="m-0 p-0">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-[#FF4D4F] hover:bg-[#FFF1F0] transition-colors font-medium bg-transparent border-0 text-left cursor-pointer text-sm">
                                    <i class="fa-solid fa-arrow-right-from-bracket text-xs w-4"></i>
                                    <span>Đăng xuất</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Page Content Body (Padding 24px-32px chuẩn Ant Design Pro cho tất cả các trang) -->
            <main class="flex-1 {{ ($noPadding || ($attributes['no-padding'] ?? false) || ($attributes['noPadding'] ?? false)) ? '' : 'p-6 sm:p-8' }}">
                {{ $slot }}
            </main>
        </div>
    </div>
    
    <!-- Mobile sidebar overlay -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-black/30 z-30 hidden lg:hidden" onclick="toggleSidebar()"></div>
    
    <!-- Live Floating Toast Container (Nhắc nhở trực tiếp nổi góc phải) -->
    <div id="liveToastContainer" class="fixed top-14 right-5 z-[9999] flex flex-col gap-2 pointer-events-none max-w-sm w-full"></div>

    <script>
        const profileBtn = document.getElementById('profileDropdownBtn');
        const profileMenu = document.getElementById('profileDropdownMenu');

        if (profileBtn && profileMenu) {
            profileBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                profileMenu.classList.toggle('hidden');
                // Đóng dropdown thông báo nếu đang mở
                document.getElementById('systemNotificationDropdown')?.classList.add('hidden');
            });

            document.addEventListener('click', function(e) {
                if (!profileMenu.contains(e.target) && !profileBtn.contains(e.target)) {
                    profileMenu.classList.add('hidden');
                }
            });
        }

        // ========================================================
        // HỆ THỐNG THÔNG BÁO THỜI GIAN THỰC & GỌI NHÂN VIÊN PHỤC VỤ
        // ========================================================
        const notiBtn = document.getElementById('systemNotificationBtn');
        const notiDropdown = document.getElementById('systemNotificationDropdown');
        const notiBadge = document.getElementById('notificationBadge');
        const bellIcon = document.getElementById('bellIcon');
        const notiSubtext = document.getElementById('notificationSubtext');
        const notiListContainer = document.getElementById('notificationListContainer');
        const tabPaymentsCount = document.getElementById('tabPaymentsCount');
        const tabCallsCount = document.getElementById('tabCallsCount');
        const tabOrdersCount = document.getElementById('tabOrdersCount');
        const tabBookingsCount = document.getElementById('tabBookingsCount');
        const soundToggleBtn = document.getElementById('soundToggleBtn');
        const soundIcon = document.getElementById('soundIcon');
        const liveToastContainer = document.getElementById('liveToastContainer');

        let currentTab = 'payments';
        let latestData = { payments: [], service_calls: [], new_orders: [], new_bookings: [] };
        let knownCallIds = new Set();
        let knownPaymentIds = new Set();
        let isInitialLoad = true;
        let soundMuted = localStorage.getItem('civilize_noti_sound_muted') === 'true';

        function updateSoundIcon() {
            if (soundMuted) {
                soundIcon.className = 'fa-solid fa-volume-xmark text-gray-400';
                soundToggleBtn.title = 'Bật âm chuông thông báo';
            } else {
                soundIcon.className = 'fa-solid fa-volume-high text-[#F47716]';
                soundToggleBtn.title = 'Tắt âm chuông thông báo';
            }
        }
        updateSoundIcon();

        if (soundToggleBtn) {
            soundToggleBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                soundMuted = !soundMuted;
                localStorage.setItem('civilize_noti_sound_muted', soundMuted ? 'true' : 'false');
                updateSoundIcon();
                if (!soundMuted) playWaiterChime();
            });
        }

        // Phát âm thanh chuông gọi phục vụ chuyên dụng bằng Web Audio API
        function playWaiterChime() {
            if (soundMuted) return;
            try {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                if (!AudioContext) return;
                const ctx = new AudioContext();

                const now = ctx.currentTime;
                // Chuông 1 (F5 / 698.46 Hz)
                const osc1 = ctx.createOscillator();
                const gain1 = ctx.createGain();
                osc1.type = 'sine';
                osc1.frequency.setValueAtTime(698.46, now);
                gain1.gain.setValueAtTime(0.3, now);
                gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.6);
                osc1.connect(gain1);
                gain1.connect(ctx.destination);
                osc1.start(now);
                osc1.stop(now + 0.6);

                // Chuông 2 (A5 / 880 Hz)
                const osc2 = ctx.createOscillator();
                const gain2 = ctx.createGain();
                osc2.type = 'sine';
                osc2.frequency.setValueAtTime(880, now + 0.15);
                gain2.gain.setValueAtTime(0.35, now + 0.15);
                gain2.gain.exponentialRampToValueAtTime(0.001, now + 1.2);
                osc2.connect(gain2);
                gain2.connect(ctx.destination);
                osc2.start(now + 0.15);
                osc2.stop(now + 1.2);
            } catch (err) {
                console.log('AudioContext play error:', err);
            }
        }

        // Hiển thị toast thông báo thanh toán / chuyển khoản thành công
        function showPaymentLiveToast(payment) {
            if (!liveToastContainer) return;
            const toast = document.createElement('div');
            toast.className = 'pointer-events-auto bg-white rounded-xl shadow-2xl border-l-4 border-emerald-500 border-y border-r border-emerald-100 p-3.5 transform transition-all duration-300 translate-x-full opacity-0 flex items-start gap-3';
            toast.id = 'live-payment-toast-' + payment.id;

            const badgeText = payment.badge_label || (payment.is_vietqr ? 'Chuyển khoản VietQR' : 'Thanh toán thành công');

            toast.innerHTML = `
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-bold text-gray-900 truncate">${payment.title}</span>
                        <span class="text-[10px] text-gray-400 whitespace-nowrap">${payment.time_ago}</span>
                    </div>
                    <p class="text-xs font-semibold text-emerald-600 mt-0.5">${badgeText}</p>
                    <p class="text-[11px] text-gray-600 mt-1 line-clamp-2">${payment.message}</p>
                    <div class="flex items-center gap-2 mt-2">
                        <a href="${payment.url}" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold rounded shadow-xs transition-colors">
                            Xem chi tiết
                        </a>
                    </div>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-gray-400 hover:text-gray-600 text-xs p-1 cursor-pointer">
                    ✕
                </button>
            `;

            liveToastContainer.appendChild(toast);

            setTimeout(() => {
                toast.classList.remove('translate-x-full', 'opacity-0');
            }, 10);

            setTimeout(() => {
                toast.classList.add('translate-x-full', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 10000);
        }

        // Hiển thị toast thông báo nhắc nhở nổi trực tiếp góc phải
        function showLiveToast(call) {
            if (!liveToastContainer) return;
            const toast = document.createElement('div');
            toast.className = 'pointer-events-auto bg-white rounded-xl shadow-2xl border-l-4 border-[#F47716] border-y border-r border-orange-100 p-3.5 transform transition-all duration-300 translate-x-full opacity-0 flex items-start gap-3';
            toast.id = 'live-toast-' + call.id;

            toast.innerHTML = `
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5 min-w-0">
                            <span class="inline-block w-2 h-2 rounded-full bg-[#F47716] animate-pulse shrink-0"></span>
                            <span class="text-xs font-bold text-gray-900 truncate">${call.full_table_name}</span>
                        </div>
                        <span class="text-[10px] text-gray-400 whitespace-nowrap">${call.time_ago}</span>
                    </div>
                    <p class="text-xs font-semibold text-[#F47716] mt-0.5">${call.type_label}</p>
                    ${call.note ? `<p class="text-[11px] text-gray-600 mt-1 italic line-clamp-2 bg-orange-50/50 p-1 rounded">"${call.note}"</p>` : ''}
                    <div class="flex items-center gap-2 mt-2">
                        <button type="button" onclick="completeCall(${call.id}, this)" class="px-2.5 py-1 bg-[#52C41A] hover:bg-[#389E0D] text-white text-[11px] font-bold rounded shadow-xs transition-colors cursor-pointer">
                            ✓ Đã phục vụ
                        </button>
                        <a href="${call.pos_url}" class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-[11px] font-medium rounded transition-colors">
                            Xem bàn
                        </a>
                    </div>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-gray-400 hover:text-gray-600 text-xs p-1">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            `;

            liveToastContainer.appendChild(toast);

            setTimeout(() => {
                toast.classList.remove('translate-x-full', 'opacity-0');
            }, 10);

            // Tự đóng sau 10 giây
            setTimeout(() => {
                toast.classList.add('translate-x-full', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 10000);
        }

        // Toggle dropdown thông báo & tự động xóa badge khi người dùng xem
        const markAllBackendReadBtn = document.getElementById('markAllBackendReadBtn');
        function markBackendNotificationsRead() {
            if (notiBadge) notiBadge.classList.add('hidden');
            if (bellIcon) bellIcon.classList.remove('text-[#F47716]');
            if (notiSubtext) notiSubtext.textContent = 'Đã xem các thông báo mới';
            fetch('{{ route("admin.notifications.markAllRead") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            }).then(() => {
                if (typeof window.pollSystemNotifications === 'function') {
                    window.pollSystemNotifications();
                }
            }).catch(() => {});
        }

        if (markAllBackendReadBtn) {
            markAllBackendReadBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                markBackendNotificationsRead();
            });
        }

        if (notiBtn && notiDropdown) {
            notiBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                const willOpen = notiDropdown.classList.contains('hidden');
                notiDropdown.classList.toggle('hidden');
                profileMenu?.classList.add('hidden');
                if (willOpen) {
                    markBackendNotificationsRead();
                    renderNotificationList();
                }
            });

            document.addEventListener('click', function(e) {
                if (!notiDropdown.contains(e.target) && !notiBtn.contains(e.target)) {
                    notiDropdown.classList.add('hidden');
                }
            });
        }

        // Chuyển Tabs trong dropdown
        document.querySelectorAll('.noti-tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.noti-tab-btn').forEach(b => {
                    b.classList.remove('font-bold', 'text-[#F47716]', 'border-b-2', 'border-[#F47716]', 'bg-white');
                    b.classList.add('text-gray-500');
                });
                this.classList.add('font-bold', 'text-[#F47716]', 'border-b-2', 'border-[#F47716]', 'bg-white');
                this.classList.remove('text-gray-500');
                currentTab = this.dataset.tab;
                renderNotificationList();
            });
        });

        // Render nội dung danh sách theo tab hiện tại
        function renderNotificationList() {
            if (!notiListContainer) return;

            if (currentTab === 'payments') {
                const payments = latestData.payments || [];
                if (payments.length === 0) {
                    notiListContainer.innerHTML = `
                        <div class="py-10 text-center text-gray-400">
                            <p class="font-bold text-gray-700 text-xs uppercase tracking-wider">Chưa có giao dịch thanh toán</p>
                            <span class="text-[11px] text-gray-400 mt-0.5 block">Các giao dịch chuyển khoản VietQR và tiền mặt sẽ hiển thị tại đây</span>
                        </div>
                    `;
                    return;
                }

                let html = '';
                payments.forEach(p => {
                    const badgeClass = p.is_vietqr ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800';
                    const badgeLabel = p.badge_label || (p.is_vietqr ? 'Chuyển khoản VietQR' : 'Thanh toán thành công');
                    html += `
                        <a href="${p.url}" class="p-3 hover:bg-emerald-50/40 transition-colors flex items-start gap-3 block border-b border-gray-100 last:border-b-0 ${!p.is_read ? 'bg-emerald-50/20' : ''}">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-1 mb-1">
                                    <span class="font-bold text-gray-900 text-xs truncate">${p.title}</span>
                                    <span class="text-[10px] text-gray-400 shrink-0">${p.time_ago}</span>
                                </div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded ${badgeClass} uppercase">${badgeLabel}</span>
                                    <span class="text-[10px] text-gray-400">${p.created_at}</span>
                                </div>
                                <p class="text-[11px] text-gray-600 line-clamp-2 leading-relaxed">${p.message}</p>
                            </div>
                        </a>
                    `;
                });
                notiListContainer.innerHTML = html;
            } else if (currentTab === 'calls') {
                const calls = latestData.service_calls || [];
                if (calls.length === 0) {
                    notiListContainer.innerHTML = `
                        <div class="py-10 text-center text-gray-400">
                            <p class="font-bold text-gray-700 text-xs uppercase tracking-wider">Không có bàn nào đang gọi</p>
                            <span class="text-[11px] text-gray-400 mt-0.5 block">Tất cả bàn đang được phục vụ chu đáo</span>
                        </div>
                    `;
                    return;
                }

                let html = '';
                calls.forEach(call => {
                    html += `
                        <div class="p-3 hover:bg-orange-50/40 transition-colors flex items-start gap-3 group border-b border-gray-100 last:border-b-0" id="noti-call-item-${call.id}">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-1 mb-1">
                                    <span class="font-bold text-gray-900 text-xs truncate">${call.full_table_name}</span>
                                    <span class="text-[10px] text-gray-400 shrink-0">${call.time_ago}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 uppercase">${call.type_label}</span>
                                    ${call.note ? `<span class="text-[11px] text-gray-600 italic line-clamp-1">"${call.note}"</span>` : ''}
                                </div>
                                <div class="flex items-center gap-2 mt-2">
                                    <button type="button" onclick="completeCall(${call.id}, this)" class="px-2.5 py-1 bg-[#52C41A] hover:bg-[#389E0D] text-white text-[10px] font-bold rounded shadow-xs transition-colors cursor-pointer">
                                        Đã phục vụ
                                    </button>
                                    <a href="${call.pos_url}" class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-[10px] font-medium rounded transition-colors">
                                        Mở bàn
                                    </a>
                                </div>
                            </div>
                        </div>
                    `;
                });
                notiListContainer.innerHTML = html;
            } else if (currentTab === 'orders') {
                const orders = latestData.new_orders || [];
                if (orders.length === 0) {
                    notiListContainer.innerHTML = `
                        <div class="py-10 text-center text-gray-400">
                            <p class="font-bold text-gray-700 text-xs uppercase tracking-wider">Không có món mới</p>
                            <span class="text-[11px] text-gray-400 mt-0.5 block">Chưa có món mới cần xác nhận</span>
                        </div>
                    `;
                    return;
                }

                let html = '';
                orders.forEach(ord => {
                    html += `
                        <a href="${ord.url}" class="p-3 hover:bg-blue-50/40 transition-colors flex items-start gap-3 block border-b border-gray-100 last:border-b-0">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-1 mb-1">
                                    <span class="font-bold text-gray-900 text-xs truncate">${ord.title}</span>
                                    <span class="text-[10px] text-gray-400">${ord.time_ago}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-blue-100 text-blue-800 uppercase">Món mới</span>
                                    <span class="text-[11px] text-gray-500">${ord.subtitle}</span>
                                </div>
                                <div class="text-[11px] font-bold text-blue-600 mt-1">Tổng: ${ord.total_price}</div>
                            </div>
                        </a>
                    `;
                });
                notiListContainer.innerHTML = html;
            } else if (currentTab === 'bookings') {
                const bookings = latestData.new_bookings || [];
                if (bookings.length === 0) {
                    notiListContainer.innerHTML = `
                        <div class="py-10 text-center text-gray-400">
                            <p class="font-bold text-gray-700 text-xs uppercase tracking-wider">Không có đặt bàn mới</p>
                            <span class="text-[11px] text-gray-400 mt-0.5 block">Không có yêu cầu đặt bàn đang chờ</span>
                        </div>
                    `;
                    return;
                }

                let html = '';
                bookings.forEach(b => {
                    html += `
                        <a href="${b.url}" class="p-3 hover:bg-purple-50/40 transition-colors flex items-start gap-3 block border-b border-gray-100 last:border-b-0">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-1 mb-1">
                                    <span class="font-bold text-gray-900 text-xs truncate">${b.customer_name} (${b.people} khách)</span>
                                    <span class="text-[10px] text-gray-400">${b.time_ago}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-purple-100 text-purple-800 uppercase">Đặt bàn</span>
                                    <span class="text-[11px] text-gray-600">SĐT: ${b.phone}</span>
                                </div>
                                <div class="text-[11px] font-semibold text-purple-700 mt-0.5">Giờ đến: ${b.booking_time} - ${b.booking_date}</div>
                            </div>
                        </a>
                    `;
                });
                notiListContainer.innerHTML = html;
            }
        }

        // Hàm hoàn thành yêu cầu phục vụ nhanh từ popup
        window.completeCall = function(callId, btnElement) {
            if (btnElement) {
                btnElement.disabled = true;
                btnElement.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang lưu...';
            }

            fetch('{{ url("admin/notifications/service-call") }}/' + callId + '/complete', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Xóa toast nếu có
                    document.getElementById('live-toast-' + callId)?.remove();

                    // Cập nhật giao diện mục trong list
                    const item = document.getElementById('noti-call-item-' + callId);
                    if (item) {
                        item.classList.add('bg-emerald-50', 'opacity-50');
                        setTimeout(() => item.remove(), 300);
                    }

                    // Tải lại dữ liệu polling ngay
                    pollNotifications();
                }
            })
            .catch(err => {
                console.error('Lỗi khi hoàn thành gọi phục vụ:', err);
                if (btnElement) {
                    btnElement.disabled = false;
                    btnElement.innerHTML = 'Thử lại';
                }
            });
        };

        // Hàm Polling thời gian thực cập nhật thông báo
        window.pollSystemNotifications = function() {
            fetch('{{ route("admin.notifications.latest") }}', {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) return;

                latestData = data;

                // 1. Cập nhật số đếm các tabs
                if (tabPaymentsCount) tabPaymentsCount.textContent = data.payment_count || 0;
                if (tabCallsCount) tabCallsCount.textContent = data.service_call_count;
                if (tabOrdersCount) tabOrdersCount.textContent = data.new_order_count;
                if (tabBookingsCount) tabBookingsCount.textContent = data.new_booking_count;

                // 2. Cập nhật Badge trên chuông (nếu đang mở xem thì không hiện badge)
                const isViewing = notiDropdown && !notiDropdown.classList.contains('hidden');
                if (data.total_count > 0 && !isViewing) {
                    notiBadge.textContent = data.total_count > 99 ? '99+' : data.total_count;
                    notiBadge.classList.remove('hidden');
                    bellIcon.classList.add('text-[#F47716]');
                    if (data.payment_count > 0) {
                        notiSubtext.textContent = `Có ${data.payment_count} thông báo thanh toán mới`;
                    } else if (data.service_call_count > 0) {
                        notiSubtext.textContent = `Có ${data.service_call_count} bàn đang gọi phục vụ`;
                    } else {
                        notiSubtext.textContent = `Có ${data.total_count} thông báo mới`;
                    }
                } else if (!isViewing) {
                    notiBadge.classList.add('hidden');
                    bellIcon.classList.remove('text-[#F47716]');
                    notiSubtext.textContent = 'Hệ thống đang hoạt động ổn định';
                }

                // 3. Kiểm tra thông báo thanh toán mới xuất hiện
                const currentPayments = data.payments || [];
                let hasNewPayment = false;

                currentPayments.forEach(payment => {
                    if (!knownPaymentIds.has(payment.id)) {
                        knownPaymentIds.add(payment.id);
                        if (!isInitialLoad && !payment.is_read) {
                            hasNewPayment = true;
                            showPaymentLiveToast(payment);
                        }
                    }
                });

                // 4. Kiểm tra các cuộc gọi phục vụ mới xuất hiện
                const currentCalls = data.service_calls || [];
                let hasNewCall = false;

                currentCalls.forEach(call => {
                    if (!knownCallIds.has(call.id)) {
                        knownCallIds.add(call.id);
                        if (!isInitialLoad) {
                            hasNewCall = true;
                            showLiveToast(call);
                        }
                    }
                });

                if ((hasNewCall || hasNewPayment) && !isInitialLoad) {
                    playWaiterChime();
                    // Hiệu ứng lắc chuông nhắc nhở
                    bellIcon.classList.add('rotate-12');
                    setTimeout(() => bellIcon.classList.remove('rotate-12'), 300);
                }

                isInitialLoad = false;

                // Nếu dropdown đang mở -> render lại list
                if (notiDropdown && !notiDropdown.classList.contains('hidden')) {
                    renderNotificationList();
                }
            })
            .catch(err => {
                console.log('Notification polling:', err);
            });
        };

        // Khởi động polling ngay và định kỳ mỗi 5 giây
        pollNotifications = window.pollSystemNotifications;
        pollNotifications();
        setInterval(pollNotifications, 5000);

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            if (sidebar && overlay) {
                sidebar.classList.toggle('-translate-x-full');
                overlay.classList.toggle('hidden');
            }
        }
        
        function checkScreenSize() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            if (sidebar && overlay) {
                if (window.innerWidth < 1024) {
                    sidebar.classList.add('-translate-x-full');
                } else {
                    sidebar.classList.remove('-translate-x-full');
                    overlay.classList.add('hidden');
                }
            }
        }
        
        window.addEventListener('resize', checkScreenSize);
        checkScreenSize();

        // Global CSRF Protection: Tự động chuyển toàn bộ link trạng thái, xóa, khôi phục sang POST kèm CSRF token
        window.submitAdminAction = function(url) {
            let form = document.getElementById('globalAdminPostForm');
            if (!form) {
                form = document.createElement('form');
                form.id = 'globalAdminPostForm';
                form.method = 'POST';
                form.style.display = 'none';
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                form.innerHTML = `<input type="hidden" name="_token" value="${token}">`;
                document.body.appendChild(form);
            }
            form.action = url;
            form.submit();
        };

        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (!link) return;
            const href = link.getAttribute('href');
            if (!href || href === '#' || href.startsWith('javascript:')) return;

            // Kiểm tra các thao tác thay đổi dữ liệu trong trang quản trị
            if (/\/admin\/(.*?\/)?(status|delete|restore|destroy|logout)/.test(href)) {
                e.preventDefault();
                e.stopPropagation();

                // Kiểm tra hộp thoại xác nhận trước khi thực hiện
                const isDelete = href.includes('/delete') || href.includes('/destroy');
                const onclickAttr = link.getAttribute('onclick') || '';
                let confirmMsg = link.getAttribute('data-confirm');

                if (!confirmMsg && onclickAttr.includes('confirm(')) {
                    const match = onclickAttr.match(/confirm\s*\(\s*['"](.*?)['"]\s*\)/);
                    if (match && match[1]) {
                        confirmMsg = match[1];
                    }
                }

                if (!confirmMsg && isDelete) {
                    confirmMsg = 'Bạn có chắc chắn muốn xóa bản ghi này vào thùng rác không?';
                }

                if (confirmMsg && !window.confirm(confirmMsg)) {
                    return false;
                }

                window.submitAdminAction(href);
            }
        }, true);
    </script>
</body>
</html>
