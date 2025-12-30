<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\Topic;
use App\Models\Post;

class ListPost extends Component
{
    /**
     * Create a new component instance.
     */
    
     public $topic_item;
    public function __construct($topicitem)
    {
        $this->topic_item = $topicitem;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $list_topicid = [];
        if ($this->topic_item && $this->topic_item !== 'all') {
            $topic = Topic::find($this->topic_item);
            array_push($list_topicid, $topic->id);
        }
        
        $search = request()->get('search', ''); 

        $query = Post::whereIn('status', [1, 2]);


        if (!empty($search)) {
            $query->where('title', 'like', '%' . $search . '%');
        }

        if (!empty($list_topicid)) {
            $query->whereIn('topic_id', $list_topicid);
        }
        
        $posts = $query->paginate(3); 

        return view('components.list-post', compact('posts'));
    }
}