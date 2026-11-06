<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\Topic;
use App\Models\Post;
use App\Models\Product;

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

        // 1. Bài viết mới nhất
        $recentPosts = Post::where('status', 1)
            ->orderBy('created_at', 'desc')
            ->limit(4)
            ->get();

        // 2. Món ăn đặc sắc gợi ý từ nhà hàng
        $signatureDishes = Product::where('status', 1)
            ->where('price_buy', '>', 0)
            ->whereNotNull('image')
            ->orderBy('id', 'desc')
            ->limit(2)
            ->get();

        // 3. Từ khóa ẩm thực thịnh hành
        $hotTags = [
            'Cá nướng than hoa',
            'Lẩu riêu cua',
            'Bắp bò muối',
            'Nem rán Hà Nội',
            'Đặt tiệc gia đình',
            'Món ngon Hà Thành',
            'Khuyến mãi tuần',
            'Tiệc liên hoan'
        ];

        return view('components.list-blog', compact(
            'topics', 
            'topicitem', 
            'totalAllPosts',
            'recentPosts',
            'signatureDishes',
            'hotTags'
        ));
    }
}
