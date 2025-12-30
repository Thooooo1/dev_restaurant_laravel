<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\User;

class ListAdmin extends Component
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
            ['roles','=','admin'],
            ['admin_lever','=',2],
           
        ];
        $admins = User::where($args)
        ->limit(2)
        ->get();
        return view('components.list-admin',compact('admins'));
    }
}
