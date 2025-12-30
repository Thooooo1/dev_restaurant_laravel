<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\Category;

class SubListCategory extends Component
{
    /**
     * Create a new component instance.
     */
    public $category_id;
    public function __construct($categoryid)
    {
        $this->category_id = $categoryid;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $catid = $this->category_id;
        $args =[
            ['status','=','1'],
            ['parent_id','=',$catid]
        ];
        $category_list = Category::where($args)->get();
        return view('components.sub-list-category',compact('category_list'));
    }
}
