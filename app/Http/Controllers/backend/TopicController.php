<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\UpdateTopicRequest;
use App\Http\Requests\StoreTopicRequest;
use Illuminate\Support\Facades\File;

class TopicController extends Controller
{
    public function index()
    {
        $topics = topic::orderBy('created_at','DESC')->select()->paginate(4);
        return view('backend.topic.index',compact('topics'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $topics = Topic::orderBy('sort_order','ASC') ->get();
        return view('backend.topic.index',compact('topics'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTopicRequest $request)
    {
        $topic = new Topic();
        $topic->name = $request->name;
        $topic->sort_order = $request->sort_order;
        $topic->slug = $request->slug;
        $topic->description = $request->description;
        $topic->created_by = Auth::id()?? 1;
        $topic->created_at = date('Y-m-d H:i:s');
        $topic->status = $request->status;
        $topic->save();
        return redirect()->route('admin.topic.index')->with('success','Thêm thành công');   
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $topic = Topic::where('id',$id)->first();
        if($topic == null){
            return redirect()->back()->with('error','Không tồn tại ');
        }
        return view('backend.topic.show',compact('topic'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $topic = Topic::where('id',$id)->first();
        return view('backend.topic.edit',compact('topic'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTopicRequest $request, string $id)
    {
        $topic = Topic::where('id',$id)->first();
        $topic->name = $request->name;
        $topic->sort_order = $request->sort_order;
        $topic->slug = $request->slug;
        $topic->description = $request->description;
        $topic->created_by = Auth::id()?? 1;
        $topic->created_at = date('Y-m-d H:i:s');
        $topic->status = $request->status;
        if($topic->save()){
            return redirect()->route('admin.topic.index')->with('success','Chủ để đã được cập nhật');
            
        }
        return redirect()->back()->with('error','Lỗi thêm');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
       //xoa sp khoi csdl
       $topic = Topic::withTrashed()->where('id',$id)->first();
       if($topic != null){           
           $topic->forceDelete();
           return redirect()->route("admin.topic.trash")->with('success','Xóa thành công');
       }
       return redirect()->route("admin.topic.trash")->with('error','Mẫu tin không tồn tại');
    }
    //danhsachsanphamrac
    public function trash()
    {
        $topics = Topic::onlyTrashed() ->orderBy('created_at','DESC') ->get();
        return view('backend.topic.trash',compact('topics'));
    }   
    public function status(string $id)
    {
         //xulythaydoitrangthai
         $topic = Topic::find($id);
         if ($topic != null) {
             $topic->status = ($topic->status == 1) ? 2 : 1;
             $topic->save();
 
             return redirect()->route('admin.topic.index')->with('success', 'Trạng thái đã được thay đổi');
         }
         return redirect()->route('admin.topic.index')->with('error', 'Mẫu tin không tồn tại');
    }
    public function delete(string $id)
    {
        //xulyxoavaothungrac
        $topic = Topic::find($id);
        if($topic != null){
            $topic->delete();
            return redirect()->route("admin.topic.index")->with('success','Xóa vào thùng rác thành công');
        }
        return redirect()->route("admin.topic.index")->with('error','Mẫu tin không tồn tại');
    }
    public function restore(string $id)
    {
        //xulykhoiphuckhoithungrac
        $topic = Topic::withTrashed()->where('id',$id);
        if($topic->first() != null){
            $topic->restore();
            return redirect()->route("admin.topic.trash")->with('success','Khôi phục thành công');
        }
        return redirect()->route("admin.topic.trash")->with('error','Mẫu này không tồn tại');
    }
}
