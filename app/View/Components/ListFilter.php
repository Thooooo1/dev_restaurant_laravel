<?php

namespace App\View\Components;


use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\Category;
use App\Models\Brand;
class ListFilter extends Component
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

        $categoryitem = request()->get('category', 'all');
        $branditem = request()->get('brand', 'all');

        return view('components.list-filter', compact('brands', 'categories', 'categoryitem', 'branditem'));
    }

}
