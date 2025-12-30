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
        $list_catid=[];
        $category=$this->category_item;
        $args1=[
            ['status','=',1],
            ['parent_id','=',$category->id]
        ];
        array_push($list_catid,$category->id);
        $categories1 = Category::select("id","parent_id","status")->where($args1)->get();
        if($categories1 != null)
        {
            foreach ($categories1 as $category1){
                array_push($list_catid,$category1->id);
                $args2=[
                    ['status','=',1],
                    ['parent_id','=',$category1->id]
                ];
                $categories2=Category::select("id","parent_id","status")->where($args2)->get();
                if($categories1 != null){
                    foreach($categories2 as $category2){
                        array_push($list_catid,$category2->id);
                    }
                }
            }
        }
        //print_r($list_catid);
        $products = Product::where('status','=',1)
        ->WhereIn('category_id',$list_catid)
        ->limit(5)
        ->get();
        return view('components.home-product-category',compact('products'));
    }
}
