<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class LayoutBackend extends Component
{
    public $breadcrumb;
    public $noPadding;

    /**
     * Create a new component instance.
     */
    public function __construct($breadcrumb = null, $noPadding = false)
    {
        $this->breadcrumb = $breadcrumb;
        $this->noPadding = (bool)$noPadding;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.layout-backend');
    }
}
