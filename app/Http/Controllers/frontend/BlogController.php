<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;

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
            return redirect()->route('frontend.blog.index')->with('error', 'Bài viết không tồn tại.');
        }

        return view('frontend.blog-detail', compact('post'));
    }
}
