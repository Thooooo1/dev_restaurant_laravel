<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\Menu;

class FooterMenu extends Component
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
        $args=[
            ['status','=',1],
            ['position','=','footermenu'],
            ['parent_id','=',0]
        ];
        $menus = Menu::where($args)->get();
        return view('components.footer-menu',compact('menus'));
    }
}
