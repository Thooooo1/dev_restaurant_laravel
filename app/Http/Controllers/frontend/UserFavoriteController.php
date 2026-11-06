<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Favorite;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class UserFavoriteController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $isGuest = !Auth::check();

        if ($isGuest) {
            $products = new \Illuminate\Pagination\LengthAwarePaginator(
                collect(), 0, 9, 1, ['path' => $request->url(), 'query' => $request->query()]
            );
            $categories = collect();
            $categoryitem = 'all';
            $favoriteCount = 0;
            $totalAllFavorites = 0;

            return view('frontend.favorite', compact(
                'products',
                'categories',
                'categoryitem',
                'favoriteCount',
                'totalAllFavorites',
                'isGuest'
            ));
        }

        // Lấy tất cả ID sản phẩm đã yêu thích của user
        $favoriteProductIds = Favorite::where('user_id', $userId)->pluck('product_id')->toArray();
        $totalAllFavorites = count($favoriteProductIds);

        // Lọc danh mục chứa các món yêu thích
        $categories = Category::where('status', 1)
            ->withCount(['products' => function ($q) use ($favoriteProductIds) {
                $q->where('status', 1)->whereIn('id', $favoriteProductIds);
            }])
            ->get();

        $categoryitem = $request->input('category', 'all');
        $search = $request->input('search');
        $sort = $request->input('sort', 'default');

        if (empty($favoriteProductIds)) {
            $products = new \Illuminate\Pagination\LengthAwarePaginator(
                collect(), 0, 9, 1, ['path' => $request->url(), 'query' => $request->query()]
            );
            $favoriteCount = 0;

            return view('frontend.favorite', compact(
                'products',
                'categories',
                'categoryitem',
                'favoriteCount',
                'totalAllFavorites',
                'isGuest'
            ));
        }

        $query = Product::where('status', 1)
            ->whereIn('id', $favoriteProductIds)
            ->with('category')
            ->withCount('reviews');

        if ($categoryitem && $categoryitem !== 'all') {
            $query->where('category_id', $categoryitem);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        switch ($sort) {
            case 'priceAsc':
                $query->orderByRaw('COALESCE(NULLIF(price_sale, 0), price_buy) ASC');
                break;
            case 'priceDesc':
                $query->orderByRaw('COALESCE(NULLIF(price_sale, 0), price_buy) DESC');
                break;
            case 'alphabet':
                $query->orderBy('name', 'asc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            default:
                $reversedIds = array_reverse($favoriteProductIds);
                $idsOrder = implode(',', $reversedIds);
                $query->orderByRaw("FIELD(id, {$idsOrder}) DESC");
                break;
        }

        $products = $query->paginate(9)->withQueryString();
        $favoriteCount = $products->total();

        return view('frontend.favorite', compact(
            'products',
            'categories',
            'categoryitem',
            'favoriteCount',
            'totalAllFavorites',
            'isGuest'
        ));
    }

    public function store(Request $request, $productId)
    {
        if (!Auth::check()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Bạn cần đăng nhập để thêm vào yêu thích.',
                    'redirect' => route('site.login')
                ], 401);
            }
            return redirect()->route('site.login')->with('error', 'Bạn cần đăng nhập để thêm vào yêu thích.');
        }

        $userId = Auth::id();
        $exists = Favorite::where('user_id', $userId)->where('product_id', $productId)->exists();

        if (!$exists) {
            Favorite::create([
                'user_id' => $userId,
                'product_id' => $productId,
            ]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            $newCount = Favorite::where('user_id', $userId)->count();
            return response()->json([
                'status' => 'success',
                'message' => 'Đã thêm món vào danh sách yêu thích!',
                'isFavorited' => true,
                'count' => $newCount
            ]);
        }

        return redirect()->back()->with('success', 'Thêm sản phẩm vào danh sách yêu thích thành công!');
    }

    public function destroy(Request $request, $productId)
    {
        if (!Auth::check()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Bạn cần đăng nhập để thực hiện thao tác này.',
                    'redirect' => route('site.login')
                ], 401);
            }
            return redirect()->route('site.login')->with('error', 'Bạn cần đăng nhập để thực hiện thao tác này.');
        }

        $userId = Auth::id();
        Favorite::where('user_id', $userId)->where('product_id', $productId)->delete();

        if ($request->ajax() || $request->wantsJson()) {
            $newCount = Favorite::where('user_id', $userId)->count();
            return response()->json([
                'status' => 'success',
                'message' => 'Đã bỏ món khỏi danh sách yêu thích.',
                'isFavorited' => false,
                'count' => $newCount
            ]);
        }

        return redirect()->back()->with('success', 'Đã xóa sản phẩm khỏi danh sách yêu thích.');
    }
}
