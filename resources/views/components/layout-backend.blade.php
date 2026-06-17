<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản lý Nhà hàng</title>
    <link rel="shortcut icon" type="image/x-icon" href="../images/logo/logo.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/DataTables/datatables.css') }}" />
    <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
    <!-- Bootstrap 4 JS (for existing modals and collapse) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js"></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #1e293b;
        }
        ::-webkit-scrollbar-thumb {
            background: #475569;
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }
        
        /* Table styles */
        table {
            font-size: 1.1rem;
        }
        th, td {
            color: #1e293b;
            font-weight: 600;
        }
        td {
            font-size: 1.15rem !important;
            color: #000000 !important;
        }
        th {
            font-weight: 900 !important;
            font-size: 1.9rem !important;
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%) !important;
            color: #000000 !important;
            text-transform: none !important;
            padding-top: 1.9rem !important;
            padding-bottom: 1.9rem !important;
        }
        
        /* Action buttons and icons */
        .btn {
            font-size: 1rem;
            font-weight: 600;
        }
        .btn i {
            font-size: 1.2rem;
            font-weight: 900;
        }
        table td .btn {
            padding: 0.5rem 0.75rem;
        }
        
        /* Small square action buttons */
        table td a.inline-flex {
            width: 40px !important;
            height: 40px !important;
        }
        table td a.inline-flex i {
            font-size: 1.3rem;
            font-weight: 900;
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">
    @include('components.alert')
    
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside id="sidebar" class="fixed left-0 top-0 z-40 flex flex-col w-64 h-full bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900 border-r border-slate-700/50 sidebar-transition">
            <!-- Logo -->
            <div class="px-6 py-4 border-b border-slate-700/50">
                <a href="{{ route('admin.table-order.index') }}" class="flex items-center gap-3">
                    <div class="w-11 h-11 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl flex items-center justify-center shadow-lg shadow-orange-500/30">
                        <i class="fas fa-utensils text-white text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-white font-bold text-lg">Restaurant</h1>
                        <p class="text-slate-400 text-xs">Quản lý chuyên nghiệp</p>
                    </div>
                </a>
            </div>
            
            <!-- Menu -->
            <nav class="flex-1 p-4 overflow-y-auto">
                <div class="space-y-1">
                    <!-- Quản lý Order -->
                    <a href="{{ route('admin.table-order.index') }}" class="flex items-center gap-3 px-4 py-3.5 rounded-xl {{ request()->routeIs('admin.table-order.*') ? 'bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-lg shadow-orange-500/25' : 'text-slate-400 hover:bg-slate-700/50 hover:text-white' }} transition-all duration-200">
                        <i class="fas fa-cash-register w-5 text-center"></i>
                        <span class="font-medium">Quản lý Order</span>
                    </a>
                    
                    <!-- Lịch sử order -->
                    <a href="{{ route('admin.order-history.index') }}" class="flex items-center gap-3 px-4 py-3.5 rounded-xl {{ request()->routeIs('admin.order-history.*') ? 'bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-lg shadow-orange-500/25' : 'text-slate-400 hover:bg-slate-700/50 hover:text-white' }} transition-all duration-200">
                        <i class="fas fa-history w-5 text-center"></i>
                        <span class="font-medium">Lịch sử Order</span>
                    </a>
                    
                    <!-- Đặt bàn -->
                    <a href="{{ url('admin/booking/') }}" class="flex items-center gap-3 px-4 py-3.5 rounded-xl {{ request()->is('admin/booking*') ? 'bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-lg shadow-orange-500/25' : 'text-slate-400 hover:bg-slate-700/50 hover:text-white' }} transition-all duration-200">
                        <i class="fas fa-clipboard-list w-5 text-center"></i>
                        <span class="font-medium">Đặt bàn</span>
                    </a>
                    
                    <!-- Đơn hàng -->
                    <a href="{{ url('admin/order/') }}" class="flex items-center gap-3 px-4 py-3.5 rounded-xl {{ (request()->is('admin/order*') && !request()->is('admin/order-history*')) ? 'bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-lg shadow-orange-500/25' : 'text-slate-400 hover:bg-slate-700/50 hover:text-white' }} transition-all duration-200">
                        <i class="fas fa-shopping-bag w-5 text-center"></i>
                        <span class="font-medium">Đơn hàng</span>
                    </a>
                    
                    @if(Auth::user()->isAdmin())
                        <!-- Quản lý nhập hàng -->
                        <a href="{{ route('admin.import-goods.index') }}" class="flex items-center gap-3 px-4 py-3.5 rounded-xl {{ request()->routeIs('admin.import-goods.*') ? 'bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-lg shadow-orange-500/25' : 'text-slate-400 hover:bg-slate-700/50 hover:text-white' }} transition-all duration-200">
                            <i class="fas fa-file-import w-5 text-center"></i>
                            <span class="font-medium">Nhập hàng</span>
                        </a>
                        
                        <!-- Bàn ăn -->
                        <a href="{{ url('admin/table/') }}" class="flex items-center gap-3 px-4 py-3.5 rounded-xl {{ request()->routeIs('admin.table.*') ? 'bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-lg shadow-orange-500/25' : 'text-slate-400 hover:bg-slate-700/50 hover:text-white' }} transition-all duration-200">
                            <i class="fas fa-chair w-5 text-center"></i>
                            <span class="font-medium">Bàn ăn</span>
                        </a>
                        
                        <!-- Món ăn -->
                        <a href="{{ url('admin/product/') }}" class="flex items-center gap-3 px-4 py-3.5 rounded-xl {{ request()->is('admin/product*') ? 'bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-lg shadow-orange-500/25' : 'text-slate-400 hover:bg-slate-700/50 hover:text-white' }} transition-all duration-200">
                            <i class="fas fa-utensils w-5 text-center"></i>
                            <span class="font-medium">Món ăn</span>
                        </a>
                        
                        <!-- Danh mục -->
                        <a href="{{ url('admin/category/') }}" class="flex items-center gap-3 px-4 py-3.5 rounded-xl {{ request()->is('admin/category*') ? 'bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-lg shadow-orange-500/25' : 'text-slate-400 hover:bg-slate-700/50 hover:text-white' }} transition-all duration-200">
                            <i class="fas fa-list w-5 text-center"></i>
                            <span class="font-medium">Danh mục</span>
                        </a>
                        
                        <!-- Thương hiệu -->
                        <a href="{{ url('admin/brand/') }}" class="flex items-center gap-3 px-4 py-3.5 rounded-xl {{ request()->is('admin/brand*') ? 'bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-lg shadow-orange-500/25' : 'text-slate-400 hover:bg-slate-700/50 hover:text-white' }} transition-all duration-200">
                            <i class="fas fa-tag w-5 text-center"></i>
                            <span class="font-medium">Thương hiệu</span>
                        </a>
                        
                        <!-- Banner -->
                        <a href="{{ url('admin/banner/') }}" class="flex items-center gap-3 px-4 py-3.5 rounded-xl {{ request()->is('admin/banner*') ? 'bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-lg shadow-orange-500/25' : 'text-slate-400 hover:bg-slate-700/50 hover:text-white' }} transition-all duration-200">
                            <i class="fas fa-image w-5 text-center"></i>
                            <span class="font-medium">Banner</span>
                        </a>
                        
                        <!-- Người dùng -->
                        <div class="menu-item-wrapper">
                            <button onclick="toggleSubmenu('user-submenu')" class="w-full flex items-center justify-between gap-3 px-4 py-3.5 rounded-xl {{ request()->routeIs('admin.user.*') ? 'bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-lg shadow-orange-500/25' : 'text-slate-400 hover:bg-slate-700/50 hover:text-white' }} transition-all duration-200">
                                <div class="flex items-center gap-3">
                                    <i class="fas fa-users w-5 text-center"></i>
                                    <span class="font-medium">Quản lý người dùng</span>
                                </div>
                                <i class="fas fa-chevron-down text-sm transition-transform" id="user-submenu-icon"></i>
                            </button>
                            <div id="user-submenu" class="ml-4 mt-1 space-y-1 {{ request()->routeIs('admin.user.*') ? 'block' : 'hidden' }}">
                                <a href="{{ route('admin.user.customers') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm {{ request()->routeIs('admin.user.customers') ? 'bg-orange-500/20 text-orange-400 font-medium' : 'text-slate-400 hover:bg-slate-700/30 hover:text-slate-200' }} transition-all">
                                    <i class="fas fa-circle text-[6px]"></i>
                                    Khách hàng
                                </a>
                                <a href="{{ route('admin.user.employees') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm {{ request()->routeIs('admin.user.employees') ? 'bg-orange-500/20 text-orange-400 font-medium' : 'text-slate-400 hover:bg-slate-700/30 hover:text-slate-200' }} transition-all">
                                    <i class="fas fa-circle text-[6px]"></i>
                                    Nhân viên
                                </a>
                            </div>
                        </div>
                        
                        <!-- Liên lạc -->
                        <a href="{{ url('admin/contact/') }}" class="flex items-center gap-3 px-4 py-3.5 rounded-xl {{ request()->is('admin/contact*') ? 'bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-lg shadow-orange-500/25' : 'text-slate-400 hover:bg-slate-700/50 hover:text-white' }} transition-all duration-200">
                            <i class="fas fa-id-card w-5 text-center"></i>
                            <span class="font-medium">Liên lạc</span>
                        </a>
                        
                        <!-- Tư vấn -->
                        <a href="{{ url('admin/inquiries/') }}" class="flex items-center gap-3 px-4 py-3.5 rounded-xl {{ request()->is('admin/inquiries*') ? 'bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-lg shadow-orange-500/25' : 'text-slate-400 hover:bg-slate-700/50 hover:text-white' }} transition-all duration-200">
                            <i class="fas fa-comment-dots w-5 text-center"></i>
                            <span class="font-medium">Tư vấn</span>
                        </a>
                    @endif
                </div>
            </nav>
            
            <!-- User info -->
            <div class="p-4 border-t border-slate-700/50 bg-slate-800/30">
                <div class="flex items-center gap-3 px-2">
                    <div class="w-11 h-11 bg-gradient-to-br from-orange-500 to-orange-600 rounded-full flex items-center justify-center text-white font-semibold shadow-md">
                        {{ mb_substr(Auth::user()->fullname, 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-white font-medium truncate text-sm">{{ Auth::user()->fullname }}</p>
                        <p class="text-slate-400 text-xs truncate">
                            @if(Auth::user()->isAdmin())
                                Quản trị viên
                            @else
                                Nhân viên
                            @endif
                        </p>
                    </div>
                    <a href="{{ route('admin.logout') }}" class="w-9 h-9 flex items-center justify-center rounded-full hover:bg-red-500/20 text-slate-400 hover:text-red-400 transition-colors" title="Đăng xuất">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>
        </aside>
        
        <!-- Main content -->
        <div class="flex-1 ml-64 min-h-screen flex flex-col">
            <!-- Header -->
            <header class="bg-white border-b border-slate-200 sticky top-0 z-30">
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <button onclick="toggleSidebar()" class="lg:hidden w-10 h-10 flex items-center justify-center rounded-xl hover:bg-slate-100 transition-colors">
                                <i class="fas fa-bars text-slate-600"></i>
                            </button>
                            <div>
                                <p class="text-slate-500 text-sm">Xin chào,</p>
                                <h2 class="text-slate-800 font-semibold text-lg">{{ Auth::user()->fullname }} 👋</h2>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="{{ url('/') }}" class="flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition-colors text-sm font-medium">
                                <i class="fas fa-home"></i>
                                <span class="hidden sm:inline">Trang chủ</span>
                            </a>
                            <a href="{{ route('admin.logout') }}" class="flex items-center gap-2 px-4 py-2.5 bg-orange-50 hover:bg-orange-100 text-orange-600 rounded-xl transition-colors text-sm font-medium">
                                <i class="fas fa-sign-out-alt"></i>
                                <span class="hidden sm:inline">Đăng xuất</span>
                            </a>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Page content -->
            <main class="flex-1 p-6">
                {{ $slot }}
            </main>
        </div>
    </div>
    
    <!-- Mobile sidebar overlay -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-30 hidden lg:hidden" onclick="toggleSidebar()"></div>
    
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }
        
        function toggleSubmenu(id) {
            const submenu = document.getElementById(id);
            const icon = document.getElementById(id + '-icon');
            
            submenu.classList.toggle('hidden');
            icon.classList.toggle('rotate-180');
        }
        
        // Responsive sidebar
        function checkScreenSize() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            if (window.innerWidth < 1024) {
                sidebar.classList.add('-translate-x-full');
            } else {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.add('hidden');
            }
        }
        
        window.addEventListener('resize', checkScreenSize);
        checkScreenSize();
    </script>
</body>
</html>
