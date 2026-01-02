<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class InquiryForm extends Component
{
    public function render()
    {
        return view('components.inquiry-form');
    }
}
