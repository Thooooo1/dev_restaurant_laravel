<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\Product;
use App\Models\Review;

class ProductReview extends Component
{
    public $product;
    public $reviews;

    public function __construct(Product $product, $reviews)
    {
        $this->product = $product;
        $this->reviews = $reviews;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.product-review');
    }
}

