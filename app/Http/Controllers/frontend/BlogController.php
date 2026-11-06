<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Schema;

class BlogController extends Controller
{
    function index()
    {
        return view('frontend.blog');
    }
    public function blog_detail($slug)
    {
        $post = Post::where('slug', $slug)->first();

        if (!$post) {
            return redirect()->route('site.blog')->with('error', 'Bài viết không tồn tại.');
        }

        // Tăng lượt xem thực tế khi có người đọc
        if (Schema::hasColumn('post', 'view')) {
            $post->increment('view');
        }

        return view('frontend.blog-detail', compact('post'));
    }
}
