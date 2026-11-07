<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('site.login')->with('error', 'Bạn cần đăng nhập để đánh giá sản phẩm.');
        }

        $request->validate([
            'product_id' => 'required|integer',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ], [
            'rating.required' => 'Vui lòng chọn số sao đánh giá (1-5 sao).',
            'rating.min' => 'Số sao đánh giá tối thiểu là 1 sao.',
            'rating.max' => 'Số sao đánh giá tối đa là 5 sao.',
            'comment.max' => 'Nội dung đánh giá không được vượt quá 1.000 ký tự.',
        ]);

        $product = Product::find($request->product_id);
        if (!$product) {
            return redirect()->route('site.home')->with('error', 'Món ăn không tồn tại.');
        }

        // Chống spam: Mỗi khách hàng chỉ được lưu 1 đánh giá cho mỗi món ăn (nếu đánh giá lại sẽ cập nhật)
        $existingReview = Review::where('product_id', $product->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($existingReview) {
            $existingReview->rating = (int)$request->rating;
            $existingReview->comment = strip_tags($request->comment ?? '');
            $existingReview->updated_at = now();
            $existingReview->save();

            return redirect()->route('site.product.detail', ['slug' => $product->slug])
                ->with('success', 'Đã cập nhật đánh giá của bạn thành công!');
        }

        $review = new Review();
        $review->product_id = $product->id;
        $review->user_id = Auth::id();
        $review->rating = (int)$request->rating;
        $review->comment = strip_tags($request->comment ?? '');
        $review->created_at = now();
        $review->save();

        return redirect()->route('site.product.detail', ['slug' => $product->slug])
            ->with('success', 'Cảm ơn bạn đã đánh giá món ăn!');
    }

    public function delete(string $review_id)
    {
        if (!Auth::check()) {
            return redirect()->route('site.login')->with('error', 'Bạn cần đăng nhập để thực hiện thao tác này.');
        }

        $review = Review::with('product')->find($review_id);
        if (!$review) {
            return redirect()->back()->with('error', 'Đánh giá không tồn tại hoặc đã bị xóa.');
        }

        // Kiểm tra phân quyền: Chỉ chủ sở hữu đánh giá hoặc Quản trị viên / Nhân viên mới có quyền xóa
        $currentUser = Auth::user();
        $isOwner = (int)$review->user_id === (int)$currentUser->id;
        $isAdmin = in_array($currentUser->roles, ['admin', 'employee']);

        if (!$isOwner && !$isAdmin) {
            abort(403, 'Bạn không có quyền xóa đánh giá của người khác.');
        }

        $product_slug = $review->product ? $review->product->slug : null;
        $review->delete();

        if ($product_slug) {
            return redirect()->route('site.product.detail', ['slug' => $product_slug])
                ->with('success', 'Xóa đánh giá thành công!');
        }

        return redirect()->back()->with('success', 'Xóa đánh giá thành công!');
    }


}
