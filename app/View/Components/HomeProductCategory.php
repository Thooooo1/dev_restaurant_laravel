<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\Category;
use App\Models\Product;

class HomeProductCategory extends Component
{
    /**
     * Create a new component instance.
     */
    public $category_item;
    public function __construct($categoryitem)
    {
        $this->category_item = $categoryitem;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $category = $this->category_item;

        if (isset($category->category_ids) && is_array($category->category_ids)) {
            $list_catid = $category->category_ids;
        } elseif ($category->id === 'drinks') {
            $list_catid = [13, 15, 16, 18, 19];
        } else {
            $list_catid = [$category->id];
            $categories1 = Category::select("id", "parent_id", "status")
                ->where([['status', '=', 1], ['parent_id', '=', $category->id]])
                ->get();
            foreach ($categories1 as $cat1) {
                $list_catid[] = $cat1->id;
            }
        }

        $products = Product::where('status', '=', 1)
            ->whereIn('category_id', $list_catid)
            ->limit(8)
            ->get();

        return view('components.home-product-category', compact('products'));
    }
}
