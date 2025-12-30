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

        $review = new Review();
        $review->product_id = $request->product_id;
        $review->user_id = Auth::id();
        $review->rating = $request->rating;
        $review->comment = $request->comment;
        $review->created_at = now();
        $review->save();
        $product = Product::find($request->product_id);

        if ($product) {
            return redirect()->route('site.product.detail', ['slug' => $product->slug])
                ->with('success', 'Cảm ơn bạn đã đánh giá!');
        }

        return redirect()->route('site.home')->with('error', 'Sản phẩm không tồn tại.');
    }

    public function delete(string $review_id)
    {
        $review = Review::with('product')->find($review_id);
        if ($review) {
            $product_slug = $review->product->slug;
            $review->delete();
            return redirect()->route('site.product.detail', ['slug' => $product_slug])
                ->with('success', 'Xóa đánh giá thành công!');
        }
        return redirect()->route('site.home')->with('error', 'Lỗi xóa!');
    }


}
