<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;

class ListFilterGrid extends Component
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
        $args = [
            ['status', '=', 1]
        ];
        $brands = Brand::where($args)->get();
        $categories = Category::where('status', 1)
            ->where('name', 'not like', '%Thuốc lá%')
            ->orderBy('sort_order', 'asc')
            ->withCount(['products' => function ($q) {
                $q->where('status', 1);
            }])
            ->get();

        $productQuery = Product::join('category', 'product.category_id', '=', 'category.id')
            ->where('product.status', 1)
            ->where('category.status', 1);
        $categoryitem = request()->get('category', 'all');
        $branditem = request()->get('brand', 'all');

        if ($categoryitem && $categoryitem !== 'all') {
            $productQuery->where('product.category_id', $categoryitem);
        }
        if ($branditem && $branditem !== 'all') {
            $productQuery->where('product.brand_id', $branditem);
        }
        $search = request()->get('search', '');
        if (!empty($search)) {
            $productQuery->where('product.name', 'like', '%' . $search . '%');
        }
        $priceMin = request()->get('price_min');
        $priceMax = request()->get('price_max');
        if (!empty($priceMin)) {
            $productQuery->where('product.price_sale', '>=', (float) $priceMin);
        }
        if (!empty($priceMax)) {
            $productQuery->where('product.price_sale', '<=', (float) $priceMax);
        }

        $productCount = $productQuery->count();
        $totalAllProducts = Product::where('status', 1)->count();
        $viewMode = request()->get('view', 'grid');
        
        return view('components.list-filter-grid', compact(
            'brands', 
            'categories', 
            'categoryitem', 
            'branditem', 
            'productCount',
            'totalAllProducts',
            'viewMode'
        ));
    }
}
