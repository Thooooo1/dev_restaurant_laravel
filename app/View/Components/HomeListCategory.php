<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\Component;
use App\Models\Category;
use App\Models\Product;

class HomeListCategory extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $data = Cache::remember('frontend_home_list_category_v2', 300, function () {
            // 5 danh mục món ăn cốt lõi cho trang chủ (Lẩu, Nướng, Xào, Chiên, Khai vị)
            $coreCategoryIds = [8, 12, 9, 11, 1];
            $dbCategories = Category::where('status', 1)
                ->whereIn('id', $coreCategoryIds)
                ->get()
                ->keyBy('id');

            $categories = collect();
            foreach ($coreCategoryIds as $cid) {
                if (isset($dbCategories[$cid])) {
                    $categories->push($dbCategories[$cid]);
                }
            }

            // Tab gộp Đồ uống (thay vì dàn trải riêng lẻ 5 tab)
            $drinksCategory = (object) [
                'id' => 'drinks',
                'name' => 'Đồ uống',
                'category_ids' => [13, 15, 16, 18, 19],
            ];
            $categories->push($drinksCategory);

            // Tải toàn bộ món ăn 1 lần duy nhất kèm category để tránh N+1 query
            $allProducts = Product::with('category')
                ->where('status', 1)
                ->orderBy('id', 'desc')
                ->get();

            // 8 món chính đặc sắc / Signature bán chạy nhất
            $featuredIds = [52, 53, 90, 85, 93, 86, 87, 56];
            $featuredProducts = $allProducts->whereIn('id', $featuredIds)->values();

            if ($featuredProducts->isEmpty()) {
                $featuredProducts = $allProducts->whereIn('category_id', [8, 12])->take(8)->values();
            }

            // Map sẵn danh sách món cho từng tab danh mục trong bộ nhớ (0 query bổ sung)
            $childMap = Category::select('id', 'parent_id')
                ->where('status', 1)
                ->whereIn('parent_id', $coreCategoryIds)
                ->get()
                ->groupBy('parent_id');

            $categoryProductsMap = [];
            foreach ($categories as $cat) {
                if (isset($cat->category_ids) && is_array($cat->category_ids)) {
                    $catIds = $cat->category_ids;
                } else {
                    $catIds = array_merge([$cat->id], $childMap->get($cat->id, collect())->pluck('id')->all());
                }
                $categoryProductsMap[$cat->id] = $allProducts->whereIn('category_id', $catIds)->take(8)->values();
            }

            // Chuẩn bị dữ liệu JSON gọn nhẹ cho tính năng tìm kiếm tức thì trên trang chủ
            $searchCatalog = $allProducts->map(function ($product) {
                $hasDiscount = !empty($product->price_sale) && !empty($product->price_buy) && ($product->price_sale < $product->price_buy);
                $discountPercent = $hasDiscount ? (int) round((($product->price_buy - $product->price_sale) / $product->price_buy) * 100) : 0;
                $effectivePrice = $product->price_sale > 0 ? $product->price_sale : $product->price_buy;
                $imgSrc = !empty($product->image) && file_exists(public_path('images/product/' . $product->image))
                    ? asset('images/product/' . $product->image)
                    : asset('images/default-product.png');
                $catName = $product->category ? $product->category->name : '';

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'raw' => mb_strtolower($product->name, 'UTF-8'),
                    'ascii' => Str::lower(Str::ascii($product->name)),
                    'cat' => $catName,
                    'cat_raw' => mb_strtolower($catName, 'UTF-8'),
                    'cat_ascii' => Str::lower(Str::ascii($catName)),
                    'url' => route('site.product.detail', ['slug' => $product->slug]),
                    'img' => $imgSrc,
                    'new_p' => (int) $product->new_p,
                    'has_discount' => $hasDiscount,
                    'discount_pct' => $discountPercent,
                    'price_fmt' => ($effectivePrice > 0) ? (number_format($effectivePrice, 0, ',', '.') . '₫') : 'Liên hệ giá',
                    'old_price_fmt' => $hasDiscount ? (number_format($product->price_buy, 0, ',', '.') . '₫') : '',
                    'has_price' => ($effectivePrice > 0),
                ];
            })->values()->all();

            return compact('categories', 'featuredProducts', 'categoryProductsMap', 'searchCatalog');
        });

        return view('components.home-list-category', $data);
    }
}
