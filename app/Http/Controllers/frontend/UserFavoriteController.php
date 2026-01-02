<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Favorite;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Support\Facades\Auth;

class UserFavoriteController extends Controller
{
    public function index(Request $request)
    {
        $query = Favorite::where('user_id', Auth::id())->with('product');
        $favorites = Favorite::where('user_id', Auth::id())->with('product')->get();
        $favoriteCount = $favorites->count();
        if ($request->has('category') && $request->category !== 'all') {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('category_id', $request->category);
            });
        }

        if ($request->has('brand') && $request->brand !== 'all') {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('brand_id', $request->brand);
            });
        }

        $favorites = $query->orderBy('created_at', 'desc')->paginate(6);;

        $categories = Category::where('status', 1)->get();
        $brands = Brand::where('status', 1)->get();

        return view('frontend.favorite', compact('favorites', 'categories', 'brands','favoriteCount'));
    }


    public function store($productId)
    {
        if (!Auth::check()) {
            return redirect()->route('site.login')->with('error', 'Bạn cần đăng nhập để thêm vào yêu thích.');
        }

        $exists = Favorite::where('user_id', Auth::id())->where('product_id', $productId)->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Sản phẩm đã có trong danh sách yêu thích.');
        }

        Favorite::create([
            'user_id' => Auth::id(),
            'product_id' => $productId,
        ]);

        return redirect()->back()->with('success', 'Thêm sản phẩm vào danh sách yêu thích thành công!');
    }

    public function destroy($productId)
    {
        if (!Auth::check()) {
            return redirect()->route('site.login')->with('error', 'Bạn cần đăng nhập để thực hiện thao tác này.');
        }

        Favorite::where('user_id', Auth::id())->where('product_id', $productId)->delete();

        return redirect()->back()->with('success', 'Đã xóa sản phẩm khỏi danh sách yêu thích.');
    }
}
