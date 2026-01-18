<x-layout-frontend>
    <x-slot:footer>
        <script>
            document.querySelectorAll('.tab-item').forEach((tab) => {
                tab.addEventListener('click', (e) => {
                    document.querySelectorAll('.tab-item').forEach((t) => t.classList.remove('text-blue-600',
                        'border-blue-600'));
                    document.querySelectorAll('.tab-pane').forEach((pane) => pane.classList.add('hidden'));

                    tab.classList.add('text-blue-600', 'border-blue-600');
                    const tabId = tab.getAttribute('data-tab');
                    document.getElementById(tabId).classList.remove('hidden');
                });
            });
        </script>
    </x-slot:footer>
    <main>
        <section class="bg-gray-200 py-2">
            <div class="container mx-auto px-4 breadcrumb flex items-center text-gray-600 text-sm">
                <span class="mr-4">Bạn đang ở đây:</span>
                <a href="{{ url('/') }}" class="hover:text-orange-500"> Quay lại Trang chủ</a>
                <span class="mx-2">></span>
                <a href="{{ url('/thuc-don') }}" class="hover:text-orange-500">Chi tiết món ăn</a>
                <span class="mx-2">></span>
                <span class="font-semibold text-gray-800">{{ $product->name }}</span>
            </div>
        </section>
        <div class="py-10 bg-gray-100 flex">
            <div class="container mx-auto px-4 ">
                <div class="flex flex-wrap gap-8">
                    <!-- Bố cục sản phẩm -->
                    <div class="flex flex-col md:flex-row gap-8 w-full lg:w-8/12">
                        <!-- Hình ảnh sản phẩm -->
                        <div class="w-full md:w-5/12 ">
                            <div class="relative rounded-lg shadow-lg overflow-hidden">
                                <img src="{{ asset('/images/product/' . $product->image) }}" alt="{{ $product->name }}"
                                    class="w-full h-64 object-cover transition-all duration-300 group-hover:opacity-60">

                            </div>
                            <form action="{{ route('site.favorites.add', $product->id) }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="bg-gray-200 text-black py-2 px-4 rounded hover:bg-gray-300 my-2  ">
                                    <i class="fa-regular fa-heart"></i> Thêm vào yêu thích
                                </button>
                            </form>
                        </div>

                        <!-- Thông tin sản phẩm -->
                        <div class="w-full md:w-7/12">
                            <div class="flex  items-center  ">
                                <h1 class="text-3xl font-bold text-gray-800 mb-4">{{ $product->name }}</h1>
                                @if ($product->new_p == 1)
                                    <span
                                        class=" bg-red-500 text-white text-xs px-2 py-1 rounded-tl-md rounded-br-md ml-3 mb-3">Mới</span>
                                @endif
                            </div>

                            <span class="text-red-500 font-semibold">-3%</span>

                            <span
                                class="text-orange-500 font-semibold text-lg">{{ $product->price_sale ? number_format($product->price_sale, 0, ',', '.') . ' VND' : 'Liên hệ' }}</span>
                            <span
                                class="line-through text-gray-400 ml-2">{{ number_format($product->price_buy, 0, ',', '.') . ' VND' }}</span>
                            <p class="text-gray-600 mb-6">
                                {{ $product->description }}
                            </p>

                            <form action="{{ route('site.addcart', ['id' => $product->id]) }}" method="POST" data-save-scroll="true">
                                @csrf
                                <div class="flex items-center gap-4 mb-6">
                                    <span class="text-gray-700">Số lượng:</span>
                                    <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden">
                                        <button type="button" class="px-4 py-2 bg-gray-200 hover:bg-gray-300"
                                            onclick="updateQuantity(-1)">-</button>
                                        <input type="number" name="qty" value="1" min="1"
                                            class="w-16 text-center border-0" id="quantity">
                                        <button type="button" class="px-4 py-2 bg-gray-200 hover:bg-gray-300"
                                            onclick="updateQuantity(1)">+</button>

                                    </div>

                                </div>

                                <button type="submit"
                                    class="w-full md:w-auto px-8 py-3 bg-orange-500 text-white font-semibold rounded-lg hover:bg-orange-600 transition">
                                    Thêm vào giỏ hàng
                                </button>
                                <button type="button" onclick="window.location.href='{{ route('site.booking') }}';"
                                    class="w-full md:w-auto px-8 py-3 bg-blue-500 text-white font-semibold rounded-lg hover:bg-blue-600 transition">
                                    Đặt bàn tại đây
                                </button>

                            </form>



                            <script>
                                function updateQuantity(value) {
                                    const qtyInput = document.getElementById('quantity');
                                    let currentQty = parseInt(qtyInput.value) || 1;
                                    qtyInput.value = Math.max(currentQty + value, 1);
                                }
                            </script>


                        </div>
                    </div>
                </div>
                <!-- Tabs thông tin chi tiết -->
                <div class="mt-10">
                    <div class="tabs border-b pb-2 flex flex-wrap gap-4 md:gap-8">
                        <button class="tab-item text-blue-600 border-b-2 border-blue-600 px-4 py-2 active"
                            data-tab="description">Mô tả món ăn</button>

                        <button class="tab-item text-gray-600 hover:text-blue-600 px-4 py-2" data-tab="reviews">Đánh
                            giá</button>
                        <button class="tab-item text-gray-600 hover:text-blue-600 px-4 py-2" data-tab="csach">Chính
                            sách</button>
                    </div>

                    <!-- Nội dung tab -->
                    <div class="tab-content mt-6">
                        <div id="description" class="tab-pane active">
                            <h2 class="text-2xl font-bold text-gray-800 mb-4">Sơ lược món ăn : </h2>
                            <p class="text-gray-700 leading-relaxed">
                                {!! $product->content !!}
                            </p>

                        </div>
                        <div id="reviews" class="tab-pane hidden">
                            <h2 class="text-2xl font-bold text-gray-800 mb-4">Đánh giá sản phẩm</h2>
                            <div class="filter-options mb-4">
                                <span class="font-semibold text-gray-700">Lọc đánh giá:</span>
                                @foreach (range(5, 1) as $rating)
                                    <button
                                        class="filter-btn px-2 py-1 bg-gray-200 rounded-lg hover:bg-gray-300 text-yellow ml-2"
                                        data-rating="{{ $rating }}">
                                        {{ str_repeat('★', $rating) }}
                                    </button>
                                @endforeach
                                <button id="show-all-btn"
                                    class="px-2 py-1 bg-gray-200 rounded-lg hover:bg-gray-300 text-yellow ml-2"
                                    data-rating="all">Tất cả</button>
                            </div>

                            <div class="rating-summary mb-4">
                                <div class="rating-overview bg-white p-4 rounded-lg shadow-md">
                                    <h3 class="text-xl font-bold mb-4">Tổng quan đánh giá</h3>
                                    <div class="text-4xl font-bold text-yellow-500">
                                        {{ round($reviews->avg('rating'), 1) }}/5
                                    </div>
                                    {{-- < for ($i=5; $i>= 1; $i--): ?>
                                        <div class="flex items-center mb-2">
                                            <div class="flex items-center w-20 text-yellow">
                                                < echo str_repeat('★', $i) . str_repeat('☆', 5 - $i); ?>
                                            </div>
                                            <div class="flex-1 h-2 bg-gray-200 rounded-lg mx-4 relative">
                                                <div class="h-2 bg-yellow-400 rounded-lg"
                                                    style="width: < echo isset($rating_counts[$i]) ? ($rating_counts[$i] / $total_reviews) * 100 : 0; ?>%;">
                                                </div>
                                            </div>
                                            <div class="w-12 text-right text-gray-600">
                                                < echo isset($rating_counts[$i]) ? $rating_counts[$i] : 0; ?>
                                            </div>
                                        </div>
                                        < endfor; ?> --}}
                                </div>
                            </div>

                            @if ($reviews->isEmpty())
                                <p class="text-gray-600">Chưa có đánh giá nào. Hãy là người đầu tiên đánh giá!</p>
                            @else
                                @foreach ($reviews as $item)
                                    <div class="review-item mb-4 p-4 border border-gray-300 rounded-lg"
                                        data-rating="{{ $item->rating }}">
                                        <div class="flex items-center mb-2">
                                            <span class="font-semibold text-gray-800">{{ $item->user->fullname }}</span>
                                            <span class="text-yellow-500 ml-2">{{ str_repeat('★', $item->rating) }}</span>
                                        </div>
                                        <p class="text-gray-700">{{ $item->comment }}</p>
                                        <span class="text-sm text-gray-500">Đăng vào lúc:
                                            {{ $item->created_at }}</span>
                                        <div class="flex items-center mt-2">
                                            <button class="like-btn text-green-500" data-id="{{ $item->review_id }}">👍
                                                {{ $item->likes }}</button>
                                            <button class="dislike-btn text-red-500 ml-4" data-id="{{ $item->review_id }}">👎
                                                {{ $item->dislikes }}</button>
                                        </div>
                                        @if (!empty($item->response))

                                            <div class="mt-2 bg-gray-100 p-2 rounded">
                                                <span class="font-semibold text-gray-800">CIVILIZE Restaurant: </span>
                                                <p class="text-gray-700 ">{{ $item->response }}</p>
                                            </div>
                                        @endif
                                        @if ($item->user_id == Auth::id())
                                            <form method="POST"
                                                action="{{ route('reviews.delete', ['review_id' => $item->review_id]) }}"
                                                class="mt-4">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-500">Xóa đánh giá</button>
                                            </form>
                                        @endif
                                    </div>
                                @endforeach
                            @endif

                            <form method="POST" action="{{ route('reviews.store') }}" class="mt-6">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <label for="rating" class="block text-gray-700">Đánh giá chất lượng sản
                                    phẩm:</label>
                                <div class="flex space-x-2 items-center py-2">
                                    @foreach (range(1, 5) as $rating)
                                        <label class="cursor-pointer text-gray-500 star-label" data-rating="{{ $rating }}">
                                            <input type="radio" name="rating" value="{{ $rating }}" class="hidden">
                                            <span class="star text-2xl">{{ str_repeat('★', $rating) }}</span>
                                        </label>
                                    @endforeach
                                </div>


                                <label for="comment" class="block text-gray-700 py-2">Góp ý của bạn:</label>
                                <textarea id="comment" name="comment" rows="4"
                                    class="border border-gray-300 rounded-lg w-full p-2 mb-4" required></textarea>
                                <button type="submit"
                                    class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">Gửi đánh
                                    giá</button>
                            </form>
                        </div>

                        <script>
                            document.querySelectorAll('.filter-btn').forEach(button => {
                                button.addEventListener('click', () => {
                                    const rating = button.dataset.rating;
                                    const reviews = document.querySelectorAll('.review-item');
                                    let hasMatch = false;

                                    reviews.forEach(review => {
                                        if (rating === 'all' || parseInt(review.dataset.rating) === parseInt(rating)) {

                                            review.style.display = 'block';
                                            hasMatch = true;
                                        } else {
                                            review.style.display = 'none';
                                        }
                                    });

                                    const noReviewsMessage = document.getElementById('no-reviews-message');
                                    if (!hasMatch && !noReviewsMessage) {
                                        const message = document.createElement('p');
                                        message.id = 'no-reviews-message';
                                        message.textContent = 'Không có đánh giá nào khớp với bộ lọc.';
                                        message.className = 'text-gray-600';
                                        document.getElementById('reviews').appendChild(message);
                                    } else if (hasMatch && noReviewsMessage) {
                                        noReviewsMessage.remove();
                                    }
                                });
                            });

                            document.getElementById('show-all-btn').addEventListener('click', () => {
                                document.querySelectorAll('.review-item').forEach(review => review.style.display = 'block');
                                const noReviewsMessage = document.getElementById('no-reviews-message');
                                if (noReviewsMessage) noReviewsMessage.remove();
                            });
                            document.querySelectorAll('.star-label').forEach(label => {
                                label.addEventListener('click', (event) => {
                                    event.preventDefault(); 
                                    const selectedRating = parseInt(label.dataset.rating);
                                    const radioInput = label.querySelector('input[type="radio"]');

                                    if (radioInput.checked) {
                                       
                                        radioInput.checked = false;
                                        updateStars(0); 
                                    } else {
                                        radioInput.checked = true;
                                        updateStars(selectedRating); 
                                    }
                                });
                            });

                            function updateStars(selectedRating) {
                                document.querySelectorAll('.star-label .star').forEach(star => {
                                    star.classList.remove('text-yellow-500'); 
                                    star.classList.add('text-gray-500'); 
                                });

                                document.querySelectorAll('.star-label').forEach(starLabel => {
                                    if (parseInt(starLabel.dataset.rating) <= selectedRating) {
                                        starLabel.querySelector('.star').classList.remove('text-gray-500');
                                        starLabel.querySelector('.star').classList.add('text-yellow-500'); 
                                    }
                                });
                            }


                        </script>

                        <div id="csach" class="tab-pane hidden">
                            <h2 class="text-2xl font-bold text-gray-800 mb-4 ">Chính sách sản phẩm</h2>
                            {{-- --}}
                            <div class="w-full md:w-3/12">
                                <ul class="text-black">
                                    <x-footer-menu />
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Sản phẩm cùng loại -->
                <div class="mt-10">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Sản phẩm cùng loại</h3>
                    <div class="flex overflow-x-auto gap-4 scroll-smooth">
                        @foreach ($relatedProducts as $relatedProduct)
                            <div
                                class="gallery-box relative bg-white rounded-lg shadow-md overflow-hidden group w-48 flex-shrink-0">
                                <div class="relative">
                                    <img src="{{ asset('/images/product/' . $relatedProduct->image) }}"
                                        alt="{{ $relatedProduct->name }}"
                                        class="w-full h-64 object-cover transition-all duration-300 group-hover:opacity-60">
                                    <div
                                        class="absolute inset-0 flex justify-center items-center bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                        <div class="flex space-x-4">
                                            <a href="{{ route('site.product.detail', ['slug' => $product->slug]) }}"
                                                class="flex justify-center items-center w-12 h-12 bg-white bg-opacity-20 text-white rounded-full hover:bg-opacity-40 transition transform hover:scale-110">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            <form action="{{ route('site.addcart', ['id' => $product->id]) }}"
                                                method="POST">
                                                @csrf
                                                <button type="submit"
                                                    class="flex justify-center items-center w-12 h-12 bg-white bg-opacity-20 text-white rounded-full hover:bg-opacity-40 transition transform hover:scale-110">
                                                    <i class="fas fa-cart-plus"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-4">
                                    <span class="text-red-500 font-semibold">-3%</span>
                                    <span
                                        class="text-orange-500 font-semibold text-lg">{{ $relatedProduct->price_sale ? number_format($relatedProduct->price_sale, 0, ',', '.') . ' VND' : 'Liên hệ' }}</span>
                                    <span
                                        class="line-through text-gray-400 ml-2">{{ number_format($relatedProduct->price_buy, 0, ',', '.') . ' VND' }}</span>
                                    <h4 class="font-bold text-lg mt-2 mb-3">{{ $relatedProduct->name }}</h4>
                                    <a href="{{ route('site.product.detail', ['slug' => $relatedProduct->slug]) }}"
                                        class="btn mt-2 bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600">Xem
                                        chi tiết</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
            <!-- Có thể bạn tìm kiếm -->
            <div class="w-full lg:w-4/12  ">
                <aside class="mb-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Có thể bạn đang tìm</h3>
                    <ul class="space-y-4">
                        @foreach ($relatedBrandProducts as $relatedBrandProduct)
                            <li class="flex items-center gap-4">
                                <img src="{{ asset('/images/product/' . $relatedBrandProduct->image) }}"
                                    alt="{{ $relatedBrandProduct->name }}" class="w-20 h-20 object-cover rounded-lg">
                                <div>
                                    <p class="text-lg font-semibold text-gray-700">
                                        {{ $relatedBrandProduct->name }}
                                    </p>
                                    <p class="text-orange-500 font-semibold text-sm mb-2">
                                        {{ $relatedBrandProduct->price_sale ? number_format($relatedBrandProduct->price_sale, 0, ',', '.') . ' VND' : 'Liên hệ' }}
                                    </p>

                                    </p>

                                    <a href="{{ route('site.product.detail', ['slug' => $relatedBrandProduct->slug]) }}"
                                        class="btn mt-2 bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600">Xem
                                        chi tiết</a>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </aside>
                <aside class="single_sidebar_widget popular_post_widget mb-8">
                    <h3 class="widget_title text-gray-800 text-xl mb-4 font-bold">Bài viết gần đây</h3>
                    {{-- @foreach ($blogs as $blog)
                    <div class="media post_item mb-4 flex  bg-gray-100 hover:bg-gray-200">
                        <img class="w-20 h-20 object-cover rounded-md" src="{{ asset('/images/post/' . $blog->image) }}"
                            alt="{{$blog->title}}">
                        <div class="media-body ml-4">
                            <a href="">
                                <h3 class="text-gray-800 text-lg">{{$blog->title}}</h3>
                            </a>
                            <p class="text-gray-600">{{$blog->created_at ->format('d/m/Y')}}</p>
                        </div>
                    </div>
                    @endforeach --}}
                    <x-list-post-new />
                </aside>
            </div>
        </div>
    </main>
</x-layout-frontend>
