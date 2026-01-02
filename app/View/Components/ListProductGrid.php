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
        $query = Product::where('status', '=', 1);

    if ($this->category_item && $this->category_item !== 'all') {
        $query->where('category_id', $this->category_item);
    }

    if ($this->brand_item && $this->brand_item !== 'all') {
        $query->where('brand_id', $this->brand_item);
    }

    $search = request()->get('search', '');
    if (!empty($search)) {
        $query->where('name', 'like', '%' . $search . '%');
    }
    $priceMin = request()->get('price_min');
    $priceMax = request()->get('price_max');
    if (!empty($priceMin)) {
        $query->where('price_sale', '>=', (float) $priceMin);
    }
    if (!empty($priceMax)) {
        $query->where('price_sale', '<=', (float) $priceMax);
    }

    $sort = request()->get('sort', 'default');
    if ($sort === 'priceAsc') {
        $query->orderBy('price_sale', 'asc');
    } elseif ($sort === 'priceDesc') {
        $query->orderBy('price_sale', 'desc');
    } elseif ($sort === 'alphabet') {
        $query->orderBy('name', 'asc');
    } elseif ($sort === 'newest') {
        $query->orderBy('created_at', 'desc');
    } elseif ($sort === 'oldest') {
        $query->orderBy('created_at', 'asc');
    }
    
    $products = $query->withCount('reviews')->orderBy('created_at', 'desc')->paginate(8);
    $products->appends(request()->all());
    return view('components.list-product-grid', compact('products'));
    }
}