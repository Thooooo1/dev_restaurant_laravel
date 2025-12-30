<x-layout-backend>
    <div class="container">
        <h2 class="my-4 text-center"> Đánh Giá Sản Phẩm</h2>

        <div class="card mb-4 shadow-sm">
            <div class="row g-0">
                {{-- Hình ảnh sản phẩm --}}
                <div class="col-md-4 text-center p-3">
                    <img src="{{ asset('/images/product/' . $product->image) }}" class="img-fluid rounded shadow"
                        alt="{{ $product->image }}" style="max-height: 250px; object-fit: cover;">
                </div>

                {{-- Thông tin sản phẩm --}}
                <div class="col-md-8">
                    <div class="card-body">
                        <h3 class="card-title mr-3">{{ $product->name }} </h3>
                        <p class="text-muted"><strong></strong>
                            <del>{{ number_format($product->price_buy, 0, ',', '.') }} VND</del></p>
                        <p class="text-danger"><strong>Giá:</strong>
                            {{ number_format($product->price_sale, 0, ',', '.') }} VND</p>
                        <p><strong>Mô tả:</strong> {{ $product->description }}</p>
                        <a href="{{ route('admin.product.index') }}" class="btn btn-secondary mt-3">Quay lại danh
                            sách</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card p-3 mb-4">
            <form method="GET">
                <div class="row">
                    <div class="col-md-4">
                        <label for="rating">Lọc theo số sao:</label>
                        <select name="rating" class="form-control">
                            <option value="">Tất cả</option>
                            @for ($i = 5; $i >= 1; $i--)
                                <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>{{ $i }} ⭐</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="unresponded">Tùy chọn:</label>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="unresponded" value="1" {{ request('unresponded') ? 'checked' : '' }}>
                            <label class="form-check-label">Chưa phản hồi</label>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Lọc</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="card p-3">
            <h4 class="mb-3">Danh sách đánh giá</h4>
            <table class="table table-bordered">
                <thead>
                    <tr class="text-center">
                        <th>Người dùng</th>
                        <th>Đánh giá</th>
                        <th>Bình luận</th>
                        <th>Phản hồi</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reviews as $review)
                        <tr>
                            <td class="text-center">{{ $review->user->username }}</td>
                            <td class="text-center">{{ $review->rating }} ⭐</td>
                            <td>{{ $review->comment }}</td>
                            <td>{{ $review->response ?? 'Chưa phản hồi' }}</td>
                            <td class="text-center">
                                <form
                                    action="{{ route('admin.review.respond', ['product' => $review->product->id, 'review' => $review->review_id]) }}"
                                    method="POST">
                                    @csrf
                                    <input type="text" name="response" class="form-control mb-1"
                                        placeholder="Nhập phản hồi...">
                                    <button type="submit" class="btn btn-primary btn-sm">Gửi</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">Không có đánh giá nào</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-3">
                {{ $reviews->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</x-layout-backend>