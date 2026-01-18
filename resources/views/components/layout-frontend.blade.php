<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CIVILIZE Restaurant</title>
    <link rel="shortcut icon" type="image/x-icon" href="../images/logo/logo.png">
    <!-- jQuery -->
    {{--
    <script src="/Scripts/js/jquery-2.0.0.min.js" type="text/javascript"></script> --}}

    <!-- Bootstrap4 files-->
    <script src="/Scripts/js/bootstrap.bundle.min.js" type="text/javascript"></script>
    <link href="/Content/css/bootstrap.css" rel="stylesheet" type="text/css" />

    <!-- Font awesome 5 -->
    <link href="/fonts/fonts/fontawesome/css/all.min.css" type="text/css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.0/css/all.min.css"
        integrity="sha512-9xKTRVabjVeZmc+GUW8GgSmcREDunMM+Dt/GrzchfN8tkwHizc5RP4Ok/MXFFy5rIjJjzhndFScTceq5e6GvVQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="../output.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    {{ $header ?? '' }}
</head>

<body class="flex flex-col min-h-screen">
    @include('components.alert')
    <header class="bg-white border-b py-6 sticky top-0 left-0 w-full z-50" x-data="{ openMenu: false }">

        <div class="container mx-auto px-4">
            <div class="flex flex-wrap items-center justify-between">
                <!-- Logo -->
                <div class="w-2/12">
                    <a href="{{ url('/') }}">
                        <img src="{{ asset('images/logo/logo.png') }}" alt="All food" class="h-12">
                        {{-- ../images/logo/logo.png --}}
                    </a>
                </div>

                <!-- Mobile Actions & Hamburger -->
                <div class="w-9/12 md:hidden flex justify-end items-center gap-3">
                    @if (session('table_name'))
                        <span class="only-mobile items-center px-4 py-2 rounded-full bg-green-500 text-white font-semibold">
                            {{ session('table_name') }}
                        </span>
                    @endif
                    <a href="{{ route('site.booking') }}" class="text-gray-800 hover:text-orange-500 flex flex-col items-center">
                        <i class="fa-solid fa-calendar-check text-xl"></i>
                        <span class="text-[10px] font-medium leading-none mt-1">Đặt bàn</span>
                    </a>
                    <a href="{{ url('/gio-hang') }}" class="text-gray-800 hover:text-orange-500 flex flex-col items-center">
                        <i class="fa-solid fa-cart-shopping text-xl"></i>
                        <span class="text-[10px] font-medium leading-none mt-1">Giỏ hàng</span>
                    </a>
                    <button @click="openMenu = !openMenu" class="text-gray-800 focus:outline-none ml-2 flex flex-col items-center">
                        <i class="fas fa-bars text-2xl"></i>
                        {{-- <span class="text-[10px] font-medium leading-none mt-1">Menu</span> --}}
                    </button>
                </div>

                <!-- Menu -->
                <div :class="openMenu ? 'block' : 'hidden'" class="w-full md:!block md:w-auto lg:basis-6/12 mt-4 md:mt-0 order-3 md:order-2">
                    <x-main-menu />
                </div>

                <div :class="openMenu ? 'block' : 'hidden'" class="w-full md:!block md:w-4/12 lg:basis-4/12 mt-4 md:mt-0 order-4 md:order-3">
                    <div class="flex flex-col md:flex-row justify-end gap-4 items-center">
                        <a style="margin-right: 10px;" href="{{ route('site.booking') }}"
                            class="text-gray-800 hover:text-blue-500">
                            <i class="fa-solid fa-calendar-check" style="margin-right: 5px;"></i>Đặt bàn
                        </a>

                        @if (!auth()->check())
                            <a href="{{ route('site.login') }}" class="text-gray-800 hover:text-blue-500"
                                style="margin-right: 10px;">
                                <i class="fa-solid fa-user" style="margin-right: 5px;"></i> Đăng nhập
                            </a>
                        @else
                            @php
                                if (!session()->has('table_id')) {
                                    app(\App\Http\Controllers\frontend\AuthController::class)
                                        ->syncTableSessionForUser(auth()->user());
                                }
                            @endphp
                            <a style="margin-right: 10px;" href="{{ url('/gio-hang') }}"
                                class="text-gray-800 hover:text-blue-500">
                                <i class="fa-solid fa-cart-shopping" style="margin-right: 5px;"></i>Giỏ hàng
                            </a>

                            <div x-data="{ open: false }" class="relative inline-block text-left">
                                <a @click="open = !open" class="cursor-pointer text-gray-800 hover:text-blue-500"
                                    style="margin-right: 10px;">
                                    <i class="fa-solid fa-user" style="margin-right: 5px;"></i> Tài khoản
                                </a>
                                <!-- Dropdown menu -->
                                <div x-show="open" @click.away="open = false" x-transition
                                    class="absolute mt-2 bg-white border rounded shadow-md z-50 w-52 right-0 md:left-0" style="left: auto;">
                                    <a href="{{ route('site.profile') }}"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fa-solid fa-user-circle mr-[5px]"></i> Hồ sơ
                                    </a>
                                    <a href="{{ route('site.booking') }}"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fa-solid fa-calendar-check mr-[5px]"></i> Đặt bàn
                                    </a>
                                    <a href="{{ route('site.orders') }}"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fa-solid fa-clipboard-list mr-[5px]"></i> Đơn hàng
                                    </a>
                                    <a href="{{ route('site.favorites') }}"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fa-solid fa-heart mr-[5px]"></i> Yêu thích
                                    </a>
                                    <a href="{{ route('site.logout') }}"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fa-solid fa-right-from-bracket mr-[5px]"></i> Đăng xuất
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>


            </div>
        </div>

        <div class="fixed bottom-6 right-6 flex flex-col gap-4 z-50">
            <button id="scrollToTop"
                class="scroll-to-top bg-orange-100 hover:bg-orange-200 text-orange-600 rounded-full w-12 h-12 flex items-center justify-center shadow-lg">
                <i class="fas fa-arrow-up"></i>
            </button>

            <div id="slider"
                class="slider-container bg-gray-50 rounded-full shadow-lg w-14 h-14 flex items-center justify-center overflow-hidden">
                {{-- <a href="https://www.facebook.com" target="_blank"
                    class="messenger-button bg-blue-500 hover:bg-blue-600 text-white rounded-full w-14 h-14 flex items-center justify-center">
                    <i class="fab fa-facebook-messenger text-2xl"></i>
                </a>
                <a href="tel:0981487674"
                    class="contact-button bg-green-100 hover:bg-green-200 text-green-600 rounded-full w-14 h-14 flex items-center justify-center">
                    <i class="fas fa-phone text-xl"></i>
                </a> --}}
                <a href="https://zalo.me/0336216546" target="_blank"
                    class="zalo-button bg-blue-100 hover:bg-blue-200 text-blue-600 rounded-full w-14 h-14 flex items-center justify-center">
                    <img src="{{ asset('images/logo/zaloicon.png') }}" alt="Zalo" class="w-8 h-8">
                </a>
            </div>
        </div>

        <script>
            const scrollToTopButton = document.getElementById("scrollToTop");
            window.addEventListener("scroll", () => {
                if (window.scrollY > 200) {
                    scrollToTopButton.style.display = "flex";
                } else {
                    scrollToTopButton.style.display = "none";
                }
            });

            scrollToTopButton.addEventListener("click", () => {
                window.scrollTo({
                    top: 0,
                    behavior: "smooth"
                });
            });

            const slider = document.getElementById("slider");
            const buttons = slider.querySelectorAll("a");
            let currentIndex = 0;

            function showNextButton() {
                buttons.forEach(button => (button.style.display = "none"));

                buttons[currentIndex].style.display = "flex";

                currentIndex = (currentIndex + 1) % buttons.length;
            }

            setInterval(showNextButton, 3000);

            showNextButton();
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
        <div class="fixed bottom-6 left-6 flex items-center gap-4 z-50">
            <button id="chatButton"
                class="bg-red-500 hover:bg-red-600 text-white rounded-full w-14 h-14 flex items-center justify-center shadow-lg">
                <i class="fas fa-comment-dots text-xl"></i>
            </button>

            <div id="notification"
                class="notification bg-red-500 text-white rounded-lg px-4 py-2 shadow-lg flex items-center gap-2">
                <span class="dot bg-white rounded-full w-3 h-3"></span>
                <span>Chuyên Viên Tư Vấn đang sẵn sàng để hỗ trợ bạn!</span>
            </div>
        </div>

        <div id="chatForm" class="hidden fixed bottom-20 left-6 bg-white rounded-lg shadow-lg p-4 w-72">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Chuyên Viên Tư Vấn</h3>
                <button id="closeChatForm" class="text-gray-500 hover:text-red-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <p class="text-sm text-gray-600 mb-4">Hỏi chúng tôi bất cứ điều gì. Chúng tôi sẽ trả lời ngay lập tức.</p>
            <x-list-admin />

            {{-- <form action="../config/process_form.php" method="POST">
                <div class="mb-3">
                    <input type="text" name="name" placeholder="Tên" class="w-full border rounded px-3 py-2 text-sm"
                        required>
                </div>
                <div class="mb-3">
                    <input type="text" name="phone" placeholder="Số Điện Thoại"
                        class="w-full border rounded px-3 py-2 text-sm" required>
                </div>
                <div class="mb-3">
                    <textarea name="message" placeholder="Bạn cần tư vấn gì"
                        class="w-full border rounded px-3 py-2 text-sm" required></textarea>
                </div>
                <button type="submit"
                    class="bg-red-500 hover:bg-red-600 text-white rounded px-4 py-2 w-full">Gửi</button>
            </form> --}}
            <x-inquiry-form />

        </div>


        <style>
            .user-message {
                text-align: right;
                background-color: #d1e7ff;
                margin-bottom: 5px;
                padding: 5px 10px;
                border-radius: 10px;
                display: inline-block;
            }

            .admin-message {
                text-align: left;
                background-color: #e2e2e2;
                margin-bottom: 5px;
                padding: 5px 10px;
                border-radius: 10px;
                display: inline-block;
            }
        </style>

        <style>
            .hidden {
                display: none;
            }

            .fixed {
                position: fixed;
            }

            .notification {
                animation: fadeInOut 8s infinite;
                opacity: 0;
            }

            @keyframes fadeInOut {

                0%,
                100% {
                    opacity: 0;
                }

                10%,
                90% {
                    opacity: 1;
                }
            }

            .fixed {
                position: fixed;
                z-index: 9999;
            }

            .notification {
                animation: fadeInOut 8s infinite;
                opacity: 0;
                z-index: 9999;
            }

            #chatForm {
                z-index: 9999;
            }

            #chatForm img {
                border: 2px solid #ddd;
                padding: 4px;
                transition: transform 0.2s;
            }

            #chatForm img:hover {
                transform: scale(1.1);
                border-color: #ff0000;
            }

            .relative {
                position: relative;
            }

            .status-indicator {
                position: absolute;
                top: 0;
                right: 3;
                width: 0.75rem;
                height: 0.75rem;
                border-radius: 50%;
                border: 2px solid white;
            }
        </style>
        <script>
            document.getElementById('chatButton').addEventListener('click', function() {
                document.getElementById('chatForm').classList.remove('hidden');
            });

            document.getElementById('closeChatForm').addEventListener('click', function() {
                document.getElementById('chatForm').classList.add('hidden');
            });
        </script>


    </header>
    <main class="flex-grow">
        {{ $slot }}
    </main>
    <footer class="bg-cover bg-center py-4" style="background-image: url('../images/gallery/section_bg02.png');">
        <div class="container mx-auto text-white px-4">
            <div class="flex flex-col md:flex-row gap-6">
                <div class="w-full md:w-3/12">
                    <h3 class="title-footer py-2">Về chúng tôi</h3>
                    <p>CIVILIZE Restaurant luôn bảo đảm về chất lượng cũng như an toàn thực phẩm.</p>
                    <ul class="list-icon">
                        <li> <i class="icon fa fa-map-marker" style="margin-right: 10px;"> </i>Hẻm 30, An Khánh, Ninh
                            Kiều, Cần Thơ</li>
                        <li> <i class="icon fa fa-envelope" style="margin-right: 10px;"> </i> devrestaurant@gmail.com
                        </li>
                        <li> <i class="icon fa fa-phone" style="margin-right: 10px;"> </i> (84) 012-345-6789</li>
                        <li> <i class="icon fa fa-clock" style="margin-right: 10px;"> </i>Các ngày trong tuần 7:00am -
                            22:00pm</li>
                    </ul>

                </div>
                <div class="w-full md:w-3/12">
                    <h3 class="title-footer ">Chính sách</h3>
                    {{-- <ul class="*:text-white">
                        <a href="{{url('/dieu-khoan-su-dung')}}">
                            <li>- Điều khoản sử dụng</li>
                        </a>
                        <a href="{{url('/chinh-sach-bao-mat')}}">
                            <li>- Chính sách bảo mật thông tin</li>
                        </a>
                        <a href="{{url('/chinh-sach-an-toan-thuc-pham')}}">
                            <li>- Chính sách an toàn thực phẩm</li>
                        </a>
                        <a href="{{url('/co-so-vat-chat')}}">
                            <li>- Cơ sở vật chất</li>
                        </a>
                        <a href="{{url('/chinh-sach-lien-he')}}">
                            <li>- Chính sách liên hệ</li>
                        </a>

                    </ul> --}}
                    <x-footer-menu />
                </div>
                <div class="w-full md:w-3/12">
                    <h3 class="title-footer py-2">Thư viện ảnh</h3>
                    <div class="grid grid-cols-3 gap-2">
                        <img src="../images//gallery/instagram1.png" alt=""
                            style="width: 70px;height: 70px;">
                        <img src="../images//gallery/instagram2.png" alt=""
                            style="width: 70px;height: 70px;">
                        <img src="../images//gallery/instagram3.png" alt=""
                            style="width: 70px;height: 70px;">
                        <img src="../images//gallery/instagram4.png" alt=""
                            style="width: 70px;height: 70px;">
                        <img src="../images//gallery/instagram5.png" alt=""
                            style="width: 70px;height: 70px;">
                        <img src="../images//gallery/instagram6.png" alt=""
                            style="width: 70px;height: 70px;">
                    </div>

                </div>
                <div class="w-full md:w-3/12">
                    <img src="../images//logo/logo.png" alt="" class="py-2">
                    Theo dõi nhà hàng qua
                    <div class="footer-social f-right">
                        <span>Follow Us __</span>
                        <a href="#"><i class="fab fa-twitter" style="margin-right: 10px;"></i></a>
                        <a href="https://www.facebook.com/"><i class="fab fa-facebook-f"
                                style="margin-right: 10px;"></i></a>
                        <a href="#"><i class="fas fa-globe" style="margin-right: 10px;"></i></a>
                        <a href="#"><i class="fab fa-instagram" style="margin-right: 10px;"></i></a>
                    </div>
                </div>

            </div>
            <div style="margin-top: 55px;text-align: center;" class="py-6 ">
                <p>-- Copyright &copy;
                    <script>
                        document.write(new Date().getFullYear());
                    </script> Website | Dev Coder --</a>
                </p>
            </div>

        </div>
    </footer>
    {{ $footer ?? '' }}


    <script src="{{ asset('plugins/bootstrap/js/bootstrap.js') }}"></script>
    <script src="{{ asset('plugins/bootstrap/js/boot.js') }}"></script>
</body>

</html>
