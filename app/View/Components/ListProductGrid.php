<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;

class ListProductGrid extends Component
{
    public $category_item;
    public $brand_item;

    public function __construct($categoryitem = null, $branditem = null)
    {
        $this->category_item = $categoryitem;
        $this->brand_item = $branditem;
    }

    public function render(): View|Closure|string
    {
        $query = Product::select('product.*')
            ->join('category', 'product.category_id', '=', 'category.id')
            ->where('product.status', 1)
            ->where('category.status', 1);

        if ($this->category_item && $this->category_item !== 'all') {
            $query->where('product.category_id', $this->category_item);
        }

        if ($this->brand_item && $this->brand_item !== 'all') {
            $query->where('product.brand_id', $this->brand_item);
        }

        $search = request()->get('search', '');
        if (!empty($search)) {
            $query->where('product.name', 'like', '%' . $search . '%');
        }
        $priceMin = request()->get('price_min');
        $priceMax = request()->get('price_max');
        if (!empty($priceMin)) {
            $query->where('product.price_sale', '>=', (float) $priceMin);
        }
        if (!empty($priceMax)) {
            $query->where('product.price_sale', '<=', (float) $priceMax);
        }

        $sort = request()->get('sort', 'default');
        if ($sort === 'priceAsc') {
            $query->orderBy('product.price_sale', 'asc');
        } elseif ($sort === 'priceDesc') {
            $query->orderBy('product.price_sale', 'desc');
        } elseif ($sort === 'alphabet') {
            $query->orderBy('product.name', 'asc');
        } elseif ($sort === 'newest') {
            $query->orderBy('product.created_at', 'desc');
        } elseif ($sort === 'oldest') {
            $query->orderBy('product.created_at', 'asc');
        } else {
            // Mặc định: Ưu tiên các món ăn chính lên trang 1 (Lẩu, Nướng, Xào, Chiên...)
            // Món đặc sắc / bán chạy (new_p = 1) nổi lên đầu, đồ uống/cafe xuống trang cuối
            $query->orderBy('category.sort_order', 'asc')
                  ->orderBy('product.new_p', 'desc')
                  ->orderBy('product.id', 'asc');
        }
        
        $totalCount = (clone $query)->count();
        // Chia toàn bộ thực đơn tối đa 2 trang (Trang 1 và Trang 2) cho khách hàng dễ theo dõi
        $perPage = max(16, (int) ceil($totalCount / 2));
        $products = $query->with(['category', 'brand'])->withCount('reviews')->paginate($perPage);
        $products->appends(request()->all());
        $viewMode = request()->get('view', 'grid');
        return view('components.list-product-grid', compact('products', 'viewMode'));
    }
}
