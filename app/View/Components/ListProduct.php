<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;

class ListProduct extends Component
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
        $search = request()->get('search', '');
        $list_catid = [];
        if ($this->category_item && $this->category_item !== 'all') {
            $category = Category::find($this->category_item);
            $args1 = [
                ['status', '=', 1],
                ['parent_id', '=', $category->id],
            ];
            array_push($list_catid, $category->id);

            $categories1 = Category::select("id", "parent_id", "status")->where($args1)->get();

            if ($categories1->isNotEmpty()) {
                foreach ($categories1 as $category1) {
                    array_push($list_catid, $category1->id);

                    $args2 = [
                        ['status', '=', 1],
                        ['parent_id', '=', $category1->id],
                    ];
                    $categories2 = Category::select("id", "parent_id", "status")->where($args2)->get();

                    if ($categories2->isNotEmpty()) {
                        foreach ($categories2 as $category2) {
                            array_push($list_catid, $category2->id);
                        }
                    }
                }
            }
        }

        $list_brandid = [];
        if ($this->brand_item && $this->brand_item !== 'all') {
            $brand = Brand::find($this->brand_item);
            array_push($list_brandid, $brand->id);
        }

        $query = Product::where('status', '=', 1);

        if (!empty($search)) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        if (!empty($list_catid)) {
            $query->whereIn('category_id', $list_catid);
        }

        if (!empty($list_brandid)) {
            $query->whereIn('brand_id', $list_brandid);
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

        $products = $query->paginate(10);

        return view('components.list-product', compact('products'));
    }

}
