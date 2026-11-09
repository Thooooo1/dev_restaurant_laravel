<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Post;
use App\Models\Review;

class ProductController extends Controller
{
    public function index()
    {
        return view("frontend.product_gridview");
    }
    public function product_gridview()
    {
        return view("frontend.product_gridview");
    }

    public function product_detail($slug)
    {

        $product = Product::with(['brand', 'category'])->where('slug', $slug)->firstOrFail();
        $reviews = Review::with(['product', 'user'])
            ->where('product_id', $product->id)
            ->orderBy('created_at', 'DESC')
            ->get();

        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->limit(5)
            ->get();

        $relatedBrandProducts = Product::where('brand_id', $product->brand_id)
            ->where('id', '!=', $product->id)
            ->limit(4)
            ->get();

        $blogs = Post::where('new', 1)->limit(3)->get();
        return view("frontend.product-detail", compact('product', 'relatedProducts', 'relatedBrandProducts', 'blogs', 'reviews'));
    }

    public function quickSearch(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        if (empty($q)) {
            return response()->json([
                'total' => 0,
                'items' => [],
            ]);
        }

        // Tối ưu truy vấn SQL trực tiếp thay vì nạp toàn bộ sản phẩm vào bộ nhớ
        $products = Product::where('product.status', 1)
            ->join('category', 'product.category_id', '=', 'category.id')
            ->where('category.status', 1)
            ->where(function ($query) use ($q) {
                $query->where('product.name', 'like', "%{$q}%")
                      ->orWhere('category.name', 'like', "%{$q}%");
            })
            ->select('product.*', 'category.name as category_name')
            ->orderByRaw("CASE WHEN product.name LIKE ? THEN 1 WHEN product.name LIKE ? THEN 2 ELSE 3 END", ["{$q}%", "%{$q}%"])
            ->take(15)
            ->get();

        $items = $products->map(function ($item) {
            $hasDiscount = !empty($item->price_sale) && !empty($item->price_buy) && ($item->price_sale < $item->price_buy);
            $imgSrc = !empty($item->image) && file_exists(public_path('images/product/' . $item->image))
                ? asset('images/product/' . $item->image)
                : asset('images/default-product.png');

            return [
                'id' => $item->id,
                'name' => $item->name,
                'slug' => $item->slug,
                'category_name' => $item->category_name,
                'price_formatted' => number_format($hasDiscount ? $item->price_sale : $item->price_buy, 0, ',', '.') . '₫',
                'old_price_formatted' => $hasDiscount ? number_format($item->price_buy, 0, ',', '.') . '₫' : null,
                'has_discount' => $hasDiscount,
                'image' => $imgSrc,
                'url' => route('site.product.detail', ['slug' => $item->slug]),
            ];
        });

        return response()->json([
            'total' => $items->count(),
            'items' => $items,
        ]);
    }
}
