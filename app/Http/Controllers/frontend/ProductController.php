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
        return view("frontend.product");
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
}
