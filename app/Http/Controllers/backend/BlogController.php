<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateBlogRequest;
use Illuminate\Support\Facades\File;

use Illuminate\Support\Facades\Auth;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::query()->with('topic');

        if ($request->has('topic') && $request->topic) {
            $query->where('topic_id', $request->topic);
        }

        $topics = \App\Models\Topic::select('id', 'name')->get();
        $posts = $query->orderBy('created_at', 'DESC')->paginate(8)->appends(request()->query());
        return view('backend.blog.index', compact('posts', 'topics'));
        
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {

        $query = Post::query()->with('topic');
        $post = Post::get();
        if ($request->has('topic') && $request->topic) {
            $query->where('topic_id', $request->topic);
        }
        if ($request->has('search') && $request->search) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
        $topics = \App\Models\Topic::select('id', 'name')->get();

        return view('backend.blog.create',compact('topics','post'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
           
        ]);
        $post = new Post();
        if($request->hasFile('image')){
            $file = $request->file('image');
            $extension = $file->extension();
            $filename = date('YmdHis') . "." . $extension;
            $file->move(public_path('images/post'),$filename);
            $post->image = $filename;
           
            $post->title = $request->title;
            $post->slug = $request->slug;
            $post->content = $request->content;
            $post->description =$request->description;
            $post->topic_id =$request->topic_id;
            $post->type = $request-> type;
            $post->new = $request->new;
            $post->created_by = Auth::id()?? 1;
            $post->created_at = date('Y-m-d H:i:s');
            $post->status = $request->status;
            $post->save();
            return redirect()->route('admin.blog.index')->with('success','Thêm thành công');
        }
        else{
            return back()->with('error','Chưa chọn hình');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $post = Post::where('id',$id)->first();
        if($post == null){
            return redirect()->back()->with('error','Không tồn tại banner');
        }
        return view('backend.blog.show',compact('post'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id, Request $request)
    {
        $post = Post::find($id);
        if (!$post) {
            return redirect()->route('admin.blog.index')->with('error', 'Bài viết không tồn tại.');
        }
        $topics = \App\Models\Topic::select('id', 'name')->get();

        return view('backend.blog.edit', compact('post', 'topics'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBlogRequest $request, string $id)
    {
        $post = Post::where('id',$id)->first();
        $post->title = $request->title;
        $post->slug =$request->slug;
        // upload file 
        if($request->hasFile('image')){
            //xoa hinh
            if($post->image && File::exists(public_path("images/post/" . $post->image))){
                File::delete(public_path("images/post/" . $post->image));
            }
            $file = $request->file('image');
            $extension=$file->extension();
            $filename = date('YmdHis') . "." . $extension;
            $file->move(public_path("images/post"),$filename);
            $post->image = $filename;
        }
        //end upload file
        $post->content=$request->content;
        $post->description=$request->description;
        $post->topic_id=$request->topic_id;
        $post->type=$request->type;
        $post->updated_by=Auth::id() ?? 1 ;
        $post->updated_at= date('Y-m-d H:i:s');
        $post->status = $request->status;
        $post->new = $request->new;
        if($post->save()){
            return redirect()->route('admin.blog.index')->with('success','Post đã được cập nhật');
        }
        return redirect()->back()->with('error','Lỗi thêm');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $post =Post::withTrashed()->where('id',$id)->first();
        if($post != null){
            // xóa hình
            if($post -> image && File::exists(public_path("images/post/" . $post->image))){
                File::delete(public_path("images/post/" . $post->image));
            }
            $post->forceDelete();
            return redirect()->route("admin.blog.trash")->with('success','Xóa thành công');
        }
        return redirect()->route("admin.blog.trash")->with('error','Mẫu tin không tồn tại');
    }
    //danhsachsanphamrac
    public function trash()
    {
        $posts = Post::onlyTrashed() ->orderBy('created_at','DESC') ->get();
        return view('backend.blog.trash',compact('posts'));
    }
    public function status(string $id)
    {
        $post = Post::find($id);
        if ($post != null) {
            $post->status = ($post->status == 1) ? 2 : 1;
            $post->save();

            return redirect()->route('admin.blog.index')->with('success', 'Trạng thái đã được thay đổi');
        }
        return redirect()->route('admin.blog.index')->with('error', 'Mẫu tin không tồn tại');
    }
    public function delete(string $id)
    {
        $post = Post::find($id);
        if($post != null){
            $post->delete();
            return redirect()->route("admin.blog.index")->with('success','Xóa vào thùng rác thành công');
        }
        return redirect()->route("admin.blog.index")->with('error','Mẫu tin không tồn tại');
    }
    public function restore(string $id)
    {
        $post = Post::withTrashed()->where('id',$id);
        if($post->first() != null){
            $post->restore();
            return redirect()->route("admin.blog.trash")->with('success','Khôi phục thành công');
        }
        return redirect()->route("admin.blog.trash")->with('error','Mẫu này không tồn tại');
    }
}