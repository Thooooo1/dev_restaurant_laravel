<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 ">

    <!-- Bài viết Blog -->
    <div class="col-span-2 mb-5 lg:mb-0">
        <div class="blog_left_sidebar">
            <x-list-post :topicitem="$topicitem" />

            <!-- Phân Trang -->

        </div>
    </div>

    <!-- Thanh bên -->
    <div class="col-span-1">
        <div class="blog_right_sidebar">
            <!-- Widget Tìm kiếm -->
            <aside class="single_sidebar_widget search_widget mb-8">
                <form action="{{ route('site.blog') }}" method="get">
                    <div class="form-group">
                        <div class="input-group">
                            <input type="text" class="form-control py-2 px-4 w-full border" name="search"
                                placeholder="Tìm kiếm từ khóa" value="{{ request()->get('search') }}">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button">
                                    <i class="ti-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <button class="button rounded-0 primary bg-orange-600 hover:bg-orange-700  w-full py-2 px-4"
                        type="submit">
                        Tìm kiếm
                    </button>
                </form>

            </aside>

            <!-- Widget Danh mục -->
            <aside class="single_sidebar_widget post_category_widget mb-8">
                <h4 class="widget_title text-gray-800 text-xl mb-4 font-bold">Danh mục</h4>
                <ul class="list cat-list space-y-4">
                    @foreach ($topics as $item)
                        <li>
                            <a href="{{ route('site.blog', ['topic' => $item->id]) }}"
                                class="flex justify-between hover:bg-gray-200 ">
                                <p>{{ $item->name }}</p>
                                <p>({{ $item->posts_count }})</p>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </aside>


            <!--  Bài viết gần đây -->
            <aside class="single_sidebar_widget popular_post_widget mb-8">
                <h3 class="widget_title text-gray-800 text-xl mb-4 font-bold">Bài viết gần đây</h3>
                <x-list-post-new />

            </aside>

            <!-- Widget Tag Clouds -->
            <x-keyword-blog />

            <!-- Widget Instagram Feeds -->
            <aside class="single_sidebar_widget instagram_feeds">
                <h4 class="widget_title text-gray-800 text-xl mb-4 font-bold">Instagram Feeds</h4>
                <ul class="instagram_row space-x-2 flex">
                    <li><a href="#"><img class="w-20 h-20 object-cover rounded-md"
                                src="../images/gallery/instagram1.png" alt=""></a></li>
                    <li><a href="#"><img class="w-20 h-20 object-cover rounded-md"
                                src="../images/gallery/instagram2.png" alt=""></a></li>
                    <li><a href="#"><img class="w-20 h-20 object-cover rounded-md"
                                src="../images/gallery/instagram3.png" alt=""></a></li>
                    <li><a href="#"><img class="w-20 h-20 object-cover rounded-md"
                                src="../images/gallery/instagram4.png" alt=""></a></li>
                    <li><a href="#"><img class="w-20 h-20 object-cover rounded-md"
                                src="../images/gallery/instagram5.png" alt=""></a></li>
                    <li><a href="#"><img class="w-20 h-20 object-cover rounded-md"
                                src="../images/gallery/instagram6.png" alt=""></a></li>
                </ul>
            </aside>
        </div>
    </div>
</div>
