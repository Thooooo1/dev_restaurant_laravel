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
        $categories = Category::where($args)->get();
        $productQuery = Product::query();
        $categoryitem = request()->get('category', 'all');
        $branditem = request()->get('brand', 'all');
        $productCount = $productQuery->count();
        
        return view('components.list-filter-grid',compact('brands', 'categories', 'categoryitem', 'branditem','productCount'));
    }
}
