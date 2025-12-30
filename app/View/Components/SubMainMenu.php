<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\Menu;

class SubMainMenu extends Component
{
    /**
     * Create a new component instance.
     */
    public $menu_item;
    public function __construct($menuitem)
    {
        $this->menu_item = $menuitem;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $menu =$this->menu_item;
        $args=[
            ['status','=',1],
            ['position','=','mainmenu'],
            ['parent_id','=',$menu->id]
        ];
        $menus = Menu::where($args)->orderBy('sort_order','DESC')->get();
        return view('components.sub-main-menu',compact('menus','menu'));
    }
}
