<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
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
        $args =[
            ['status','=',1],
            ['type','=','search']
        ];
        $keywords = Keyword::where($args)
        ->limit(7)
        ->get();
        return view('components.key-word-hot',compact('keywords'));
    }
}
