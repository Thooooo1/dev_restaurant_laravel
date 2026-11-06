<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\Topic;
use App\Models\Post;

class ListBlog extends Component
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
        $topics = Topic::where('status', 1)
            ->withCount(['posts' => function ($q) {
                $q->where('status', 1);
            }])
            ->get();
        
        $totalAllPosts = Post::where('status', 1)->count();
        $topicitem = request()->get('topic', 'all');  

        return view('components.list-blog', compact('topics', 'topicitem', 'totalAllPosts'));
    }
}
