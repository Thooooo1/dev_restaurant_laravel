<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\Contact;

class ContactH extends Component
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
            ['status','=',1]
        ];
        $contacts = Contact::where($args)
        ->limit(6)
        ->get();
        return view('components.contact-h',compact('contacts'));
    }
}
