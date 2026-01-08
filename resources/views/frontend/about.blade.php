<x-layout-frontend>
    <main>  
        <section class="bg-gray-200 ml-5">
            <div class="breadcrumb flex items-center text-gray-600 text-sm  ">
                <span class="mr-4">Bạn đang ở đây:</span>
                <a href="{{url('/')}}" class="hover:text-orange-500"> Quay lại Trang chủ</a>
                <span class="mx-2">></span>
                <span class="font-semibold text-gray-800">Giới thiệu</span>
            </div>
        </section>
        
        <section class="section_about bg-white py-4 mr-4 px-8 ">
            <div class="container mx-auto flex flex-wrap items-center">
                <div class="w-full lg:w-6/12 px-6">
                    <p class="text-orange-500 font-semibold text-lg mb-2">CIVILIZE Restaurant</p>
                    <h2 style="font-size: 35px;font-weight: bold;" class="font-bold">
                        Thưởng thức ẩm thực đỉnh cao tại nhà hàng của chúng tôi!
                    </h2>
                    <p class="text-gray-600 mb-8 leading-relaxed">
                        Luôn đặt cái tâm trong công việc, chúng tôi luôn sẵn sàng phục vụ các bạn. Tiêu chí nhà hàng
                        luôn được chúng tôi để tâm
                    </p>
                    <div class="grid grid-cols-2 gap-6">
                        <div class="flex items-start">
                            <i class="fa-solid fa-user text-orange-500 text-3xl mr-4"></i>
                            <p class="text-gray-600">Đầu bếp chuyên nghiệp.</p>
                        </div>
                        <div class="flex items-start">
                            <i class="fa-solid fa-utensils text-orange-500 text-3xl mr-4"></i>
                            <p class="text-gray-600">An toàn vệ sinh thực phẩm.</p>
                        </div>
                        <div class="flex items-start">
                            <i class="fa-solid fa-utensils text-orange-500 text-3xl mr-4"></i>
                            <p class="text-gray-600">Thực đơn phong phú.</p>
                        </div>
                        <div class="flex items-start">
                            <i class="fa-solid fa-user text-orange-500 text-3xl mr-4"></i>
                            <p class="text-gray-600">Khách hàng là thượng đế.</p>
                        </div>
                    </div>
                </div>
                <div class="w-full lg:w-6/12 px-6">
                    <img src="../images/gallery/about.png" alt="Chef" class="rounded-lg shadow-lg w-full">
                </div>
            </div>
        </section>
        <section>
            <div class="about-area2 py-10 bg-gray-100">
                <div class="container mx-auto px-4">
                    <div class="flex flex-wrap items-center">
                        <div class="w-full lg:w-1/2 mb-8 lg:mb-0 right-0">
                            <div class="about-img">
                                <img src="../images/gallery/about2.png" alt="About Our Restaurant"
                                    class="w-300 rounded-lg shadow-lg">
                            </div>
                        </div>
                        <div class="w-full lg:w-1/2 ">
                            <div class="about-caption">
                                <div class="section-tittle mb-6">
                                    <span class="text-orange-500 font-semibold uppercase text-sm tracking-wide">Về nhà
                                        hàng</span>
                                    <h2 class="text-3xl font-bold text-gray-800 leading-snug mt-2">Thực đơn phổ biến,
                                        phong phú !</h2>
                                </div>
    
                                <p class="text-gray-600 mb-8">
                                    Nhà hàng ẩm thực hiện đại kết hợp với truyền thống, tạo nên tính mới lạ cho thực
                                    khách. Được ra đời vào năm 2021 với tiêu chí "Khách hàng là trên hết" nên chúng tôi
                                    luôn tự hào về cách phục vụ cũng như các món ăn mà chúng tôi làm ra. Nhà hàng chúng
                                    tôi luôn luôn đặt khách hàng lên hàng đầu, tận tâm phục vụ, mang lại cho khách hàng
                                    những trãi nghiệm tuyệt với nhất. Các món ăn với công thức độc quyền sẽ mang lại
                                    hương vị mới mẻ cho thực khách. CIVILIZE Restaurant xin chân thành cảm ơn.
                                </p>
                                <p class="text-gray-600 mb-6">
                                    HÃY ĐẾN CIVILIZE RESTAURANT ĐỂ THƯỞNG THỨC NGAY BẠN NHÉ!
                                </p>
                                <a href="{{url('/danh-sach')}}"
                                    class="inline-block px-6 py-3 border-2 border-orange-500 text-orange-500 font-semibold rounded-lg hover:bg-orange-500 hover:text-white transition">
                                    Thực đơn
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section>
            <div class="our-services py-10 ">
                <div class="container mx-auto px-4">
                    <div class="text-center mb-16">
                        <span class="text-orange-500 font-semibold uppercase text-sm tracking-wide">Về dịch vụ</span>
                        <h2 class="text-3xl font-bold text-gray-800 mt-2">Dịch vụ tốt nhất của chúng tôi</h2>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                        <div
                            class="single-service text-center bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition">
                            <div class="services-icon text-orange-500 text-5xl mb-4">
                                <i class="flaticon-restaurant"></i>
                            </div>
                            <div class="services-caption">
                                <h5 class="text-xl font-semibold mb-2">
                                    <a href="#" class="text-gray-800 hover:text-orange-500 transition">Đầu bếp hàng
                                        đầu</a>
                                </h5>
                                <p class="text-gray-600">
                                    Đi đầu về lĩnh vực đầu bếp, các đầu bếp được trao truốt kĩ năng đem đến cho thực
                                    khách những món ăn chất lượng.
                                </p>
                            </div>
                        </div>
                        <div
                            class="single-service text-center bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition border-2 border-gray-500">
                            <div class="services-icon text-orange-500 text-5xl mb-4">
                                <i class="flaticon-tools-and-utensils-1"></i>
                            </div>
                            <div class="services-caption ">
                                <h5 class="text-xl font-semibold mb-2">
                                    <a href="#" class="text-gray-800 hover:text-orange-500 transition">Chất lượng thực
                                        phẩm</a>
                                </h5>
                                <p class="text-gray-600">
                                    Tất cả các loại thực phẩm chế biến điều được nhà hàng chọn lọc, phân loại kĩ càng để
                                    đảm bảo sự tươi mới và chất lượng bậc nhất !
                                </p>
                            </div>
                        </div>
                        <div
                            class="single-service text-center bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition">
                            <div class="services-icon text-orange-500 text-5xl mb-4">
                                <i class="flaticon-restaurant"></i>
                            </div>
                            <div class="services-caption">
                                <h5 class="text-xl font-semibold mb-2">
                                    <a href="#" class="text-gray-800 hover:text-orange-500 transition">Không gian nhà
                                        hàng</a>
                                </h5>
                                <p class="text-gray-600">
                                    Không chỉ đặt bàn ăn mà có là điểm dừng chân lí tưởng để tổ chức tiệc, CIVILIZE
                                    Restaurant sẽ luôn là sự lựa chọn hàng đầu của bạn.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</x-layout-frontend>