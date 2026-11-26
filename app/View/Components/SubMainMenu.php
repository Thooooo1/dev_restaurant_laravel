<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\Component;
use App\Models\Menu;

class SubMainMenu extends Component
{
    public $menu_item;
    public $sub_menus;

    public function __construct($menuitem, $submenus = null)
    {
        $this->menu_item = $menuitem;
        $this->sub_menus = $submenus;
    }

    public function render(): View|Closure|string
    {
        $menu = $this->menu_item;
        if ($this->sub_menus !== null) {
            $menus = $this->sub_menus;
        } else {
            $menuTree = Cache::remember('frontend_mainmenu_tree_v1', 300, function () {
                return Menu::where('status', 1)
                    ->where('position', 'mainmenu')
                    ->orderBy('sort_order', 'DESC')
                    ->get()
                    ->groupBy('parent_id');
            });
            $menus = $menuTree->get($menu->id, collect());
        }
        return view('components.sub-main-menu', compact('menus', 'menu'));
    }
}
