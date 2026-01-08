<x-layout-frontend>
    <section class="bg-gray-200 ml-5">
        <div class="breadcrumb flex items-center text-gray-600 text-sm  ">
            <span class="mr-4">Bạn đang ở đây:</span>
            <a href="{{url('/')}}" class="hover:text-orange-500"> Quay lại Trang chủ</a>
            <span class="mx-2">></span>
            <span class="font-semibold text-gray-800">Liên hệ</span>
        </div>
    </section>
    {{-- <main class="container mx-auto px-4 py-10  ">
        <section>
            <div class="slider-area bg-cover bg-center text-black" style="background-image: url('../images/gallery/section_bg01.png');">
                <div class="slider-height2 flex items-center py-16">
                    <div class="container mx-auto">
                        <div class="flex justify-center">
                            <div class="w-full">
                                <div class="hero-cap text-center ">
                                    <h1 class="text-3xl font-bold text-orange-500"> CIVILIZE RESTAURANT</h1>
                                    <p class="text-gray-400">Chúng tôi luôn sẵn sàng lắng nghe ý kiến của bạn</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>    
    
    
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Contact Information -->
            <div class="p-6 bg-gray-800 rounded-lg shadow-lg">
                <h2 class="text-2xl font-bold text-orange-500 mb-4">Cửa hàng chính</h2>
                <p class="text-gray-300 mb-6">
                    CIVILIZE Restaurant luôn bảo đảm về chất lượng cũng như an toàn thực phẩm.
                </p>
                <ul class="space-y-4 text-gray-300">
                    <li><i class="fas fa-map-marker-alt text-orange-500 mr-3"></i> 212 Tăng Nhơn Phú, Phước Long B, Quận
                        9, Thành phố Thủ Đức</li>
                    <li><i class="fas fa-envelope text-orange-500 mr-3"></i> civilizerestaurant@gmail.com</li>
                    <li><i class="fas fa-phone text-orange-500 mr-3"></i> (84) 098-148-7674</li>
                    <li><i class="fas fa-clock text-orange-500 mr-3"></i> Các ngày trong tuần 7:00am - 22:00pm</li>
                </ul>
            </div>
    
            <!-- Contact Form -->
            <div class="bg-white text-gray-800 p-6 rounded-lg shadow-lg">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Liên hệ với chúng tôi</h2>
                <form>
                    <div class="mb-4">
                        <label for="name" class="block text-gray-700 font-medium">Họ và tên</label>
                        <input type="text" id="name" name="name" placeholder="Nhập họ và tên"
                            class="w-full mt-2 p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                    <div class="mb-4">
                        <label for="email" class="block text-gray-700 font-medium">Email</label>
                        <input type="email" id="email" name="email" placeholder="Nhập email của bạn"
                            class="w-full mt-2 p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                    <div class="mb-4">
                        <label for="phone" class="block text-gray-700 font-medium">Điện thoại</label>
                        <input type="tel" id="phone" name="phone" placeholder="Nhập số điện thoại của bạn"
                            class="w-full mt-2 p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                    <div class="mb-4">
                        <label for="message" class="block text-gray-700 font-medium">Nội dung</label>
                        <textarea id="message" name="message" placeholder="Nhập nội dung"
                            class="w-full mt-2 p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                            rows="5"></textarea>
                    </div>
                    <button type="submit"
                        class="w-full bg-orange-500 text-white py-3 rounded-lg hover:bg-orange-600 transition">Gửi thông
                        tin</button>
                </form>
            </div>
        </div>
    
        <div class="mt-10 h-full rounded-lg overflow-hidden">
            <iframe
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3918.4817582262575!2d106.80081757458554!3d10.850288559282847!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752702c89b467f%3A0x74dbb56a7764b8b7!2zMjEyIFTDom5nIE5ow7RuIFBo4buNLCBQaMO6Y2ggTG9uZyBCLCBUaOG7jW5oIHBo4buRIEThu6VjLCBUaOG7jW5oIHBo4buRIFRo4bupYyBEdXjDsmM!5e0!3m2!1svi!2s!4v1698227611238!5m2!1svi!2s"
                width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy"
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
    </main> --}}
    <x-contact-h/>
    
</x-layout-frontend>