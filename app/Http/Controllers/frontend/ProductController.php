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

        $removeAccents = function ($str) {
            if (!$str) return '';
            $str = mb_strtolower($str, 'UTF-8');
            $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/u", 'a', $str);
            $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/u", 'e', $str);
            $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/u", 'i', $str);
            $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/u", 'o', $str);
            $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/u", 'u', $str);
            $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/u", 'y', $str);
            $str = preg_replace("/(đ)/u", 'd', $str);
            return trim($str);
        };

        $rawQ = mb_strtolower($q, 'UTF-8');
        $asciiQ = $removeAccents($rawQ);

        $products = Product::where('product.status', 1)
            ->join('category', 'product.category_id', '=', 'category.id')
            ->where('category.status', 1)
            ->select('product.*', 'category.name as category_name')
            ->get()
            ->filter(function ($item) use ($rawQ, $asciiQ, $removeAccents) {
                $nameRaw = mb_strtolower($item->name, 'UTF-8');
                $nameAscii = $removeAccents($item->name);
                $catRaw = mb_strtolower($item->category_name ?? '', 'UTF-8');
                $catAscii = $removeAccents($item->category_name ?? '');

                return str_contains($nameRaw, $rawQ)
                    || str_contains($nameAscii, $asciiQ)
                    || str_contains($catRaw, $rawQ)
                    || str_contains($catAscii, $asciiQ);
            })
            ->sortByDesc(function ($item) use ($rawQ, $asciiQ, $removeAccents) {
                $nameRaw = mb_strtolower($item->name, 'UTF-8');
                $nameAscii = $removeAccents($item->name);
                if (str_starts_with($nameRaw, $rawQ) || str_starts_with($nameAscii, $asciiQ)) {
                    return 300;
                }
                if (str_contains($nameRaw, $rawQ) || str_contains($nameAscii, $asciiQ)) {
                    return 200;
                }
                return 100;
            })
            ->values();

        $items = $products->take(15)->map(function ($item) {
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
            'total' => $products->count(),
            'items' => $items,
        ]);
    }
}
