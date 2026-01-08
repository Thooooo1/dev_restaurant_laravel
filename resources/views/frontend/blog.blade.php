<x-layout-frontend>
    <main>  
        <section class="bg-gray-200 ml-5">
            <div class="breadcrumb flex items-center text-gray-600 text-sm  ">
                <span class="mr-4">Bạn đang ở đây:</span>
                <a href="{{url('/')}}" class="hover:text-orange-500"> Quay lại Trang chủ</a>
                <span class="mx-2">></span>
                <span class="font-semibold text-gray-800">Bài viết</span>
            </div>
        </section>
        <!-- Khu vực Blog -->
        <section class="blog_area py-10 px-8 ">
            <div class="container">
                <h1 class="font-bold mb-2 text-2xl ml-2"> Bài viết mới </h1>
                {{-- <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 ">
                    
                    <!-- Bài viết Blog -->
                    <div class="col-span-2 mb-5 lg:mb-0">
                        <div class="blog_left_sidebar">
                            <!-- Mục Blog Đơn -->
                            <article class="blog_item mb-8">
                                <div class="blog_item_img relative">
                                    <img class="rounded-0 w-full" src="../images/post/1.jpg" alt="">
                                    <a href="#" class="blog_item_date absolute top-0 left-0 bg-gray-800 text-white p-4">
                                        <h3 class="text-2xl">15</h3>
                                        <p>Tháng 1</p>
                                    </a>
                                </div>
                                <div class="blog_details mt-4">
                                    <a class="d-inline-block" href="blog_details.html">
                                        <h2 class="text-gray-800 text-xl font-bold">Mách bạn công thức làm canh cá nấu mẻ thơm ngon đậm vị</h2>
                                    </a>
                                    <p class="mt-2">Canh cá nấu mẻ là món ăn dân dã, quen thuộc trong mỗi mâm cơm gia đình Việt. Mùa hè nắng nóng mà có bát canh cá chua chua, ngọt ngọt thì còn gì bằng. </p>
                                    <ul class="blog-info-link flex mt-4 space-x-4">
                                        <li><a href="#" class="text-blue-600"><i class="fa fa-user"></i> Công thức nấu ăn, Cảm hứng</a></li>
                                        <li><a href="#" class="text-blue-600"><i class="fa fa-comments"></i> 03 Bình luận</a></li>
                                    </ul>
                                </div>
                            </article>
                            <article class="blog_item mb-8">
                                <div class="blog_item_img relative">
                                    <img class="rounded-0 w-full" src="../images/post/2.jpg" alt="">
                                    <a href="#" class="blog_item_date absolute top-0 left-0 bg-gray-800 text-white p-4">
                                        <h3 class="text-2xl">15</h3>
                                        <p>Tháng 1</p>
                                    </a>
                                </div>
                                <div class="blog_details mt-4">
                                    <a class="d-inline-block" href="blog_details.html">
                                        <h2 class="text-gray-800 text-xl font-bold">Tuyển tập 8 món xào đơn giản, tiết kiệm thời gian cho chị em</h2>
                                    </a>
                                    <p class="mt-2">Cùng Bếp nhà CIVILIZE khám phá công thức làm 8 món xào đơn giản, nhanh gọn trong bài viết dưới đây để làm phong phú, đa dạng thêm cho bữa cơm của gia đình mình nhé..</p>
                                    <ul class="blog-info-link flex mt-4 space-x-4">
                                        <li><a href="#" class="text-blue-600"><i class="fa fa-user"></i> Công thức nấu ăn, Món ăn nhà hàng</a></li>
                                        <li><a href="#" class="text-blue-600"><i class="fa fa-comments"></i> 02 Bình luận</a></li>
                                    </ul>
                                </div>
                            </article>                              
                            <!-- Phân Trang -->
                            <nav class="blog-pagination justify-center flex mt-8">
                                <ul class="pagination flex space-x-4">
                                    <li class="page-item">
                                        <a href="#" class="page-link text-blue-600" aria-label="Trước">
                                            <i class="ti-angle-left"></i>
                                        </a>
                                    </li>
                                    <li class="page-item">
                                        <a href="#" class="page-link text-blue-600">1</a>
                                    </li>
                                    <li class="page-item active">
                                        <a href="#" class="page-link text-blue-600">2</a>
                                    </li>
                                    <li class="page-item">
                                        <a href="#" class="page-link text-blue-600" aria-label="Sau">
                                            <i class="ti-angle-right"></i>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
    
                    <!-- Thanh bên -->
                    <div class="col-span-1">
                        <div class="blog_right_sidebar">
                            <!-- Widget Tìm kiếm -->
                            <aside class="single_sidebar_widget search_widget mb-8">
                                <form action="#">
                                    <div class="form-group">
                                        <div class="input-group ">
                                            <input type="text" class="form-control py-2 px-4 w-full border" placeholder="Tìm kiếm từ khóa">
                                            <div class="input-group-append">
                                                <button class="btn btn-primary" type="button">
                                                    <i class="ti-search"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <button class="button rounded-0 primary bg-orange-600 hover:bg-orange-700  w-full py-2 px-4" type="submit">
                                        Tìm kiếm
                                    </button>
                                </form>
                            </aside>
    
                            <!-- Widget Danh mục -->
                            <aside class="single_sidebar_widget post_category_widget mb-8">
                                <h4 class="widget_title text-gray-800 text-xl mb-4 font-bold">Danh mục</h4>
                                <ul class="list cat-list space-y-4">
                                    <li><a href="#" class="flex justify-between hover:bg-gray-200"><p>Thực phẩm nhà hàng</p><p>(37)</p></a></li>
                                    <li><a href="#" class="flex justify-between hover:bg-gray-200"><p>Công thức nấu ăn</p><p>(10)</p></a></li>
                                    <li><a href="#" class="flex justify-between hover:bg-gray-200"><p>Công nghệ hiện đại</p><p>(03)</p></a></li>
                                    <li><a href="#" class="flex justify-between hover:bg-gray-200"><p>Sản phẩm</p><p>(11)</p></a></li>
                                    <li><a href="#" class="flex justify-between hover:bg-gray-200"><p>Cảm hứng</p><p>21</p></a></li>
                                    <li><a href="#" class="flex justify-between hover:bg-gray-200"><p>Chăm sóc sức khỏe</p><p>09</p></a></li>
                                </ul>
                            </aside>
    
                            <!--  Bài viết gần đây -->
                            <aside class="single_sidebar_widget popular_post_widget mb-8">
                                <h3 class="widget_title text-gray-800 text-xl mb-4 font-bold">Bài viết gần đây</h3>
                                <div class="media post_item mb-4 flex  bg-gray-100 hover:bg-gray-200">
                                    <img class="w-20 h-20 object-cover rounded-md" src="../images/doan/1.webp" alt="bài viết">
                                    <div class="media-body ml-4">
                                        <a href="blog_details.html">
                                            <h3 class="text-gray-800 text-lg">Mách bạn công thức làm canh cá nấu mẻ thơm ngon đậm vị</h3>
                                        </a>
                                        <p class="text-gray-600">12 tháng 1, 2024</p>
                                    </div>
                                </div>
                                <div class="media post_item mb-4 flex bg-gray-100 hover:bg-gray-200">
                                    <img class="w-20 h-20 object-cover rounded-md" src="../images/doan/2.webp" alt="bài viết">
                                    <div class="media-body ml-4">
                                        <a href="blog_details.html">
                                            <h3 class="text-gray-800 text-lg">Tuyển tập 8 món xào đơn giản, tiết kiệm thời gian cho chị em</h3>
                                        </a>
                                        <p class="text-gray-600">12 tháng 1, 2024</p>
                                    </div>
                                </div>
                                <div class="media post_item mb-4 flex bg-gray-100 hover:bg-gray-200">
                                    <img class="w-20 h-20 object-cover rounded-md" src="../images/doan/3.webp" alt="bài viết">
                                    <div class="media-body ml-4">
                                        <a href="blog_details.html">
                                            <h3 class="text-gray-800 text-lg">Hé lộ chìa khóa vàng giúp thiết lập được công thức nấu ăn ngon</h3>
                                        </a>
                                        <p class="text-gray-600">12 tháng 1, 2024</p>
                                    </div>
                                </div>
                                <div class="media post_item mb-4 flex bg-gray-100 hover:bg-gray-200">
                                    <img class="w-20 h-20 object-cover rounded-md" src="../images/doan/4.webp" alt="bài viết">
                                    <div class="media-body ml-4">
                                        <a href="blog_details.html">
                                            <h3 class="text-gray-800 text-lg">
                                                1001 món ngon mỗi ngày - Giải quyết vấn đề “Hôm nay ăn gì?”</h3>
                                        </a>
                                        <p class="text-gray-600">12 tháng 1, 2024</p>
                                    </div>
                                </div>
                            </aside>
    
                            <!-- Widget Tag Clouds -->
                            <aside class="single_sidebar_widget tag_cloud_widget mb-8">
                                <h4 class="widget_title text-gray-800 text-xl mb-4 font-bold">Tìm kiếm gần đây</h4>
                                <ul class="list space-x-4 flex flex-wrap">
                                    <li><a href="#" class="text-blue-600 border bg-gray-100 hover:bg-gray-200 ">Công thức nấu lẩu</a></li>
                                    <li><a href="#" class="text-blue-600 border bg-gray-100 hover:bg-gray-200">Bảo quản đồ ăn</a></li>
                                    <li><a href="#" class="text-blue-600 border bg-gray-100 hover:bg-gray-200">Cá kho khế</a></li>
                                    <li><a href="#" class="text-blue-600 border bg-gray-100 hover:bg-gray-200">Gà chiên mắm</a></li>
                                    
                                </ul>
                            </aside>
    
                            <!-- Widget Instagram Feeds -->
                            <aside class="single_sidebar_widget instagram_feeds">
                                <h4 class="widget_title text-gray-800 text-xl mb-4 font-bold">Instagram Feeds</h4>
                                <ul class="instagram_row space-x-2 flex">
                                    <li><a href="#"><img class="w-20 h-20 object-cover rounded-md" src="../images/gallery/instagram1.png" alt=""></a></li>
                                    <li><a href="#"><img class="w-20 h-20 object-cover rounded-md" src="../images/gallery/instagram2.png" alt=""></a></li>
                                    <li><a href="#"><img class="w-20 h-20 object-cover rounded-md" src="../images/gallery/instagram3.png" alt=""></a></li>
                                    <li><a href="#"><img class="w-20 h-20 object-cover rounded-md" src="../images/gallery/instagram4.png" alt=""></a></li>
                                    <li><a href="#"><img class="w-20 h-20 object-cover rounded-md" src="../images/gallery/instagram5.png" alt=""></a></li>
                                    <li><a href="#"><img class="w-20 h-20 object-cover rounded-md" src="../images/gallery/instagram6.png" alt=""></a></li>
                                </ul>
                            </aside>
                        </div>
                    </div>
                </div> --}}
                <x-list-blog/>
            </div>
        </section>
    </main>
</x-layout-frontend>
