<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\Component;
use App\Models\Post;

class PostNew extends Component
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
        $posts = Cache::remember('frontend_home_posts_new_v1', 300, function () {
            $query = Post::where('status', 1);
            if (Schema::hasColumn('post', 'new')) {
                $query->where('new', 1);
            }
            $items = $query->orderBy('created_at', 'desc')
                ->limit(3)
                ->get();

            if ($items->isEmpty()) {
                $items = Post::where('status', 1)
                    ->orderBy('created_at', 'desc')
                    ->limit(3)
                    ->get();
            }
            return $items;
        });

        return view('components.post-new', compact('posts'));
    }
}
