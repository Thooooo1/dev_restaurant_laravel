<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dev Restaurant Admin</title>
    <link rel="shortcut icon" type="image/x-icon" href="../images/logo/logo.png">
    {{-- <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}"> --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/DataTables/datatables.css') }}" />
    <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.css">
    <!-- Thêm Quill CSS -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <!-- Thêm Quill JS -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js"></script>
    {{-- <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet"> --}}

    {{ $header ?? '' }}
</head>

<body class="hold-transition sidebar-mini">
    @include('components.alert')
    <div class="wrapper">
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i
                            class="fas fa-bars"></i></a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="{{ url('admin/') }}" class="nav-link">Trang chủ</a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="#" class="nav-link">Liên hệ</a>
                </li>
            </ul>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="far fa-user"></i> {{ Auth::user()->fullname }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/') }}">
                        <i class="fas fa-power-off"></i> Thoát
                    </a>
                </li>
            </ul>
        </nav>
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <a href="#" class="brand-link">

                <img src=" {{ asset('dist/img/AdminLTELogo.png') }}" alt="AdminLTE Logo"
                    class="brand-image img-circle elevation-3" style="opacity: .8">
                <span class="brand-text font-weight-light">Trang quản trị</span>
            </a>
            <div class="sidebar">
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <img src="{{ asset('images/logo/logo.png') }}" class="img-circle elevation-2" alt="User Image">
                    </div>
                    <div class="info">
                        <a href="{{ url('admin/') }}" class="d-block">Dev Restaurant</a>
                    </div>
                </div>
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">


                        <li class="nav-item pl-1">
                            <a href="{{ url('admin/order/') }}" class="nav-link">
                                <i class="fas fa-shopping-bag"></i>
                                <p>Đơn hàng</p>
                            </a>
                        </li>
                        <li class="nav-item pl-1">
                            <a href="{{ url('admin/booking/') }}" class="nav-link">
                                <i class="fa-solid fa-clipboard"></i>
                                <p>Đặt bàn</p>
                            </a>
                        </li>
                        <li class="nav-item pl-1">
                            <a href="{{ url('admin/table/') }}" class="nav-link">
                                <i class="fa-solid fa-chair"></i>
                                <p>Bàn ăn</p>
                            </a>
                        </li>
                        <li class="nav-item pl-1">
                            <a href="{{ route('admin.table-order.index') }}" class="nav-link">
                                <i class="fa-solid fa-cash-register"></i>
                                <p>Order tại bàn</p>
                            </a>
                        </li>
                        <li class="nav-item pl-1">
                            <a href="{{ url('admin/inquiries/') }}" class="nav-link">
                                <i class="fa-solid fa-comment"></i>
                                <p>Tư vấn</p>
                            </a>
                        </li>

                        <li class="nav-item pl-1">
                            <a href="{{ url('admin/contact/') }}" class="nav-link">
                                <i class="fas fa-id-card"></i>
                                <p>Liên hệ</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="fa-solid fa-utensils ml-2"></i>
                                <p class="ml-2">
                                    Món ăn
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ url('admin/product/') }}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Tất cả món ăn</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ url('admin/category/') }}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Danh mục</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ url('admin/brand/') }}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Thương hiệu</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-copy"></i>
                                <p>
                                    Bài viết
                                    <i class="fas fa-angle-left right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ url('admin/blog/') }}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Tất cả bài viết</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ url('admin/topic/') }}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Chủ đề</p>
                                    </a>
                                </li>
                            </ul>
                        </li>


                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="fa-solid fa-warehouse ml-1"></i>
                                <p class="ml-2">
                                    Quản lý kho
                                    <i class="fas fa-angle-left right "></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="#" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Thực phẩm đông lạnh</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Đồ tươi</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Pha chế</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ url('admin/keyword/') }}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Đồ khô</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-edit"></i>
                                <p>
                                    Giao diện
                                    <i class="fas fa-angle-left right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ url('admin/menu/') }}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Menu</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ url('admin/banner/') }}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Banner</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ url('admin/image/') }}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Thư viện ảnh</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ url('admin/keyword/') }}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Từ khóa</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-users"></i>
                                <p>
                                    Quản lý người dùng
                                    <i class="fas fa-angle-left right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('admin.user.customers') }}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Khách hàng</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.user.employees') }}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>QL người làm</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.user.employees') }}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Nhân viên</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>
        <div class="content-wrapper">
            {{ $slot }}
        </div>
        <footer class="main-footer">
            <div class="float-right d-none d-sm-block">
                <b>Version</b> 3.2.0
            </div>
            <strong>Dev pro vip</strong> All rights reserved.
        </footer>
        <aside class="control-sidebar control-sidebar-dark">
        </aside>
    </div>

    <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('plugins/bootstrap/js/boot.js') }}"></script>
    <script src="{{ asset('dist/js/adminlte.min.js') }}"></script>

    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script> --}}

</body>

</html>
