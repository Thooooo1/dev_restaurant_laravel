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
        $menuTree = Cache::remember('frontend_mainmenu_tree_v2', 300, function () {
            $all = Menu::where('status', 1)
                ->where('position', 'mainmenu')
                ->get();

            $grouped = $all->groupBy('parent_id');
            if ($grouped->has(0)) {
                $grouped[0] = $grouped[0]->sortBy([['sort_order', 'asc'], ['id', 'asc']])->values();
            }
            foreach ($grouped as $pid => $items) {
                if ((int) $pid !== 0) {
                    $grouped[$pid] = $items->sortByDesc('sort_order')->values();
                }
            }
            return $grouped;
        });

        $menus = $menuTree->get(0, collect());
        return view('components.main-menu', compact('menus', 'menuTree'));
    }
}
