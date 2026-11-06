<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\Post;
use Illuminate\Support\Facades\Schema;

class ListPostNew extends Component
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
        $query = Post::where('status', 1);

        if (Schema::hasColumn('post', 'view')) {
            $query->orderBy('view', 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $posts = $query->limit(4)->get();

        return view('components.list-post-new', compact('posts'));
    }
}
