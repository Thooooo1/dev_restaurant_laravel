<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\Component;
use App\Models\Keyword;

class KeyWordHot extends Component
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
        $keywords = Cache::remember('frontend_hot_keywords_v1', 300, function () {
            $args = [
                ['status', '=', 1],
                ['type', '=', 'search']
            ];
            return Keyword::where($args)
                ->limit(6)
                ->get();
        });
        return view('components.key-word-hot', compact('keywords'));
    }
}
