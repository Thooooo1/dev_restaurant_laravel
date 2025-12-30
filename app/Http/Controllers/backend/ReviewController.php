<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function respond(Request $request, $productId, $reviewId)
    {
        $review = Review::where('review_id', $reviewId)->where('product_id', $productId)->first();

        if (!$review) {
            return back()->with('error', 'Đánh giá không tồn tại hoặc không thuộc sản phẩm này.');
        }

        $review->response = $request->response;
        $review->save();

        return back()->with('success', 'Đã phản hồi đánh giá');
    }



}