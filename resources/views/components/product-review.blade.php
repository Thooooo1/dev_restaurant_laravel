<div id="reviews" class="tab-pane hidden">
    <h2 class="text-xl font-bold font-serif text-stone-900 mb-4">Đánh giá món ăn từ thực khách</h2>

    <!-- Lọc đánh giá -->
    <div class="filter-options mb-6 flex flex-wrap items-center gap-2">
        <span class="text-xs uppercase font-bold tracking-wider text-stone-600 mr-1">Lọc theo sao:</span>
        <button id="show-all-btn" class="px-3 py-1.5 bg-stone-100 hover:bg-stone-200 text-stone-800 text-xs font-bold rounded-none sm:rounded-sm transition-colors cursor-pointer" data-rating="all">Tất cả</button>
        @foreach(range(5, 1) as $rating)
            <button class="filter-btn px-3 py-1.5 bg-stone-100 hover:bg-amber-50 text-amber-500 text-xs font-bold rounded-none sm:rounded-sm transition-colors cursor-pointer" data-rating="{{ $rating }}">
                {{ str_repeat('★', $rating) }} ({{ $rating }} sao)
            </button>
        @endforeach
    </div>

    <!-- Hiển thị tổng quan đánh giá -->
    <div class="rating-summary mb-6">
        <div class="rating-overview bg-stone-50/80 p-5 rounded-none sm:rounded-sm border border-stone-200 flex items-center gap-5">
            <div class="text-3xl sm:text-4xl font-serif font-black text-amber-500">
                {{ round($reviews->avg('rating'), 1) }}<span class="text-sm font-sans font-medium text-stone-500">/5</span>
            </div>
            <div class="border-l border-stone-200 pl-4 text-xs text-stone-600">
                <span class="font-bold text-stone-900 block text-sm">{{ $reviews->count() }} lượt nhận xét</span>
                <span>Từ khách hàng đã thưởng thức món ăn tại nhà hàng</span>
            </div>
        </div>
    </div>

    <!-- Hiển thị danh sách đánh giá -->
    <div class="reviews-list space-y-4">
        @foreach ($reviews as $review)
            <div class="review-item p-4 sm:p-5 border border-stone-200 rounded-none sm:rounded-sm bg-white shadow-2xs" data-rating="{{ $review->rating }}">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-none sm:rounded-sm bg-stone-900 text-amber-400 font-serif font-black flex items-center justify-center text-xs">
                            {{ mb_strtoupper(mb_substr($review->user->fullname ?? $review->user->name ?? 'K', 0, 1)) }}
                        </span>
                        <span class="font-bold text-xs sm:text-sm text-stone-900">{{ $review->user->fullname ?? $review->user->name }}</span>
                    </div>
                    <span class="text-amber-500 text-xs font-bold">{{ str_repeat('★', $review->rating) }}</span>
                </div>
                <p class="text-xs sm:text-sm text-stone-700 leading-relaxed mb-2">{{ $review->comment }}</p>
                <div class="flex items-center justify-between text-[11px] text-stone-400 border-t border-stone-100 pt-2">
                    <span>Đăng lúc: {{ $review->created_at->format('d/m/Y H:i') }}</span>
                    <div class="flex items-center gap-3">
                        <button class="like-btn text-stone-500 hover:text-emerald-600 cursor-pointer flex items-center gap-1"><i class="fa-regular fa-thumbs-up"></i> {{ $review->likes }}</button>
                        <button class="dislike-btn text-stone-500 hover:text-rose-600 cursor-pointer flex items-center gap-1"><i class="fa-regular fa-thumbs-down"></i> {{ $review->dislikes }}</button>
                    </div>
                </div>

                @if ($review->user_id == Auth::id())
                    <form method="POST" action="{{ route('reviews.delete', $review->review_id) }}" class="mt-2 text-right">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs text-rose-600 hover:underline font-semibold cursor-pointer">Xóa đánh giá</button>
                    </form>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Form đánh giá -->
    <form method="POST" action="{{ route('reviews.store') }}" class="mt-8 pt-6 border-t border-stone-200">
        @csrf
        <input type="hidden" name="product_id" value="{{ $product->id }}">
        <h4 class="text-sm font-bold uppercase tracking-wider text-stone-900 mb-2">Viết đánh giá của bạn</h4>
        <div class="mb-3">
            <span class="block text-xs text-stone-600 mb-1">Chất lượng món ăn:</span>
            <div class="flex space-x-3 items-center py-1">
                @foreach(range(1, 5) as $rating)
                    <label class="cursor-pointer text-amber-500 text-base">
                        <input type="radio" name="rating" value="{{ $rating }}" class="hidden" {{ $rating == 5 ? 'checked' : '' }}>
                        <span class="hover:scale-110 inline-block transition-transform">{{ str_repeat('★', $rating) }}</span>
                    </label>
                @endforeach
            </div>
        </div>
        <div class="mb-4">
            <label for="comment" class="block text-xs font-bold text-stone-700 mb-1.5">Cảm nhận của quý khách:</label>
            <textarea id="comment" name="comment" rows="3" class="border border-stone-300 rounded-none sm:rounded-sm w-full p-3 text-xs sm:text-sm text-stone-900 focus:outline-none focus:border-[#C25E00]" placeholder="Hương vị món ăn, cách trình bày, độ nóng sốt..." required></textarea>
        </div>
        <button type="submit" class="px-6 py-2.5 bg-[#C25E00] hover:bg-[#A34E00] text-white text-xs font-bold uppercase tracking-widest rounded-none sm:rounded-sm transition-colors cursor-pointer shadow-xs">
            Gửi đánh giá
        </button>
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
