<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\Component;
use App\Models\Menu;

class MainMenu extends Component
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
        $menuTree = Cache::remember('frontend_mainmenu_tree_v1', 300, function () {
            return Menu::where('status', 1)
                ->where('position', 'mainmenu')
                ->orderBy('sort_order', 'DESC')
                ->get()
                ->groupBy('parent_id');
        });

        $menus = $menuTree->get(0, collect());
        return view('components.main-menu', compact('menus', 'menuTree'));
    }
}
