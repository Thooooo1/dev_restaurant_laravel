<div id="reviews" class="tab-pane hidden">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">Đánh giá sản phẩm</h2>

    <!-- Lọc đánh giá -->
    <div class="filter-options mb-4">
        <span class="font-semibold text-gray-700">Lọc đánh giá:</span>
        @foreach(range(5, 1) as $rating)
            <button class="filter-btn px-2 py-1 bg-gray-200 rounded-lg hover:bg-gray-300 text-yellow ml-2" data-rating="{{ $rating }}">
                {{ str_repeat('★', $rating) }}
            </button>
        @endforeach
        <button id="show-all-btn" class="px-2 py-1 bg-gray-200 rounded-lg hover:bg-gray-300 text-yellow ml-2" data-rating="all">Tất cả</button>
    </div>

    <!-- Hiển thị tổng quan đánh giá -->
    <div class="rating-summary mb-4">
        <div class="rating-overview bg-white p-4 rounded-lg shadow-md">
            <h3 class="text-xl font-bold mb-4">Tổng quan đánh giá</h3>
            <div class="text-4xl font-bold text-yellow-500">
                {{ round($reviews->avg('rating'), 1) }}/5
            </div>
        </div>
    </div>

    <!-- Hiển thị danh sách đánh giá -->
    <div class="reviews-list">
        @foreach ($reviews as $review)
            <div class="review-item mb-4 p-4 border border-gray-300 rounded-lg" data-rating="{{ $review->rating }}">
                <div class="flex items-center mb-2">
                    <span class="font-semibold text-gray-800">{{ $review->user->name }}</span>
                    <span class="text-yellow-500 ml-2">{{ str_repeat('★', $review->rating) }}</span>
                </div>
                <p class="text-gray-700">{{ $review->comment }}</p>
                <span class="text-sm text-gray-500">Đăng vào lúc: {{ $review->created_at->format('d/m/Y H:i') }}</span>
                <div class="flex items-center mt-2">
                    <button class="like-btn text-green-500">👍 {{ $review->likes }}</button>
                    <button class="dislike-btn text-red-500 ml-4">👎 {{ $review->dislikes }}</button>
                </div>

                @if ($review->user_id == Auth::id())
                    <form method="POST" action="{{ route('reviews.delete', $review->review_id) }}" class="mt-4">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-500">Xóa đánh giá</button>
                    </form>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Form đánh giá -->
    <form method="POST" action="{{ route('reviews.store') }}" class="mt-6">
        @csrf
        <input type="hidden" name="product_id" value="{{ $product->id }}">
        <label for="rating" class="block text-gray-700">Đánh giá chất lượng sản phẩm:</label>
        <div class="flex space-x-2 items-center py-2">
            @foreach(range(1, 5) as $rating)
                <label class="cursor-pointer text-yellow-500">
                    <input type="radio" name="rating" value="{{ $rating }}" class="hidden">
                    <span>{{ str_repeat('★', $rating) }}</span>
                </label>
            @endforeach
        </div>
        <label for="comment" class="block text-gray-700 py-2">Góp ý của bạn:</label>
        <textarea id="comment" name="comment" rows="4" class="border border-gray-300 rounded-lg w-full p-2 mb-4" required></textarea>
        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">Gửi đánh giá</button>
    </form>
</div>

<script>
    document.querySelectorAll('.filter-btn').forEach(button => {
        button.addEventListener('click', () => {
            const rating = button.dataset.rating;
            const reviews = document.querySelectorAll('.review-item');
            let hasMatch = false;

            reviews.forEach(review => {
                if (rating === 'all' || review.dataset.rating === rating) {
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
</script>
