<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\Topic;
use App\Models\Post;

class ListPost extends Component
{
    public $topic_item;

    public function __construct($topicitem = null)
    {
        $this->topic_item = $topicitem;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $query = Post::where('status', 1)->with('topic');

        if ($this->topic_item && $this->topic_item !== 'all') {
            $query->where('topic_id', $this->topic_item);
        }

        $search = request()->get('search', '');
        if (!empty($search)) {
            $query->where('title', 'like', '%' . $search . '%');
        }

        $posts = $query->orderBy('created_at', 'desc')->paginate(4);
        $posts->appends(request()->all());

        return view('components.list-post', compact('posts'));
    }
}
