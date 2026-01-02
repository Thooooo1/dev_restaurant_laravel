<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Keyword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreKeywordRequest;
use App\Http\Requests\UpdateKeywordRequest;

class KeywordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $keywords = keyword::orderBy('created_at','DESC')->select()->paginate(10);
        return view('backend.keyword.index',compact('keywords'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $keywords = Keyword::get();
        return view('backend.keyword.index',compact('keywords'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreKeywordRequest $request)
    {
        $keyword = new Keyword();
        $keyword->title = $request->title;
        $keyword->slug = $request->slug;
        $keyword->type = $request->type;
        $keyword->created_by = Auth::id()?? 1;
        $keyword->created_at = date('Y-m-d H:i:s');
        $keyword->status = $request->status;
        $keyword->save();
        return redirect()->route('admin.keyword.index')->with('success','Thêm thành công');   
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $keyword = Keyword::where('id',$id)->first();
        if($keyword == null){
            return redirect()->back()->with('error','Không tồn tại ');
        }
        return view('backend.keyword.show',compact('keyword'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $keyword = Keyword::where('id',$id)->first();
        return view('backend.keyword.edit',compact('keyword'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateKeywordRequest $request, string $id)
    {
        $keyword = Keyword::where('id',$id)->first();
        $keyword->title = $request->title;
        $keyword->slug = $request->slug;
        $keyword->type = $request->type;
        $keyword->created_by = Auth::id()?? 1;
        $keyword->created_at = date('Y-m-d H:i:s');
        $keyword->status = $request->status;
        if($keyword->save()){
            return redirect()->route('admin.keyword.index')->with('success','Keyword đã được cập nhật');
            
        }
        return redirect()->back()->with('error','Lỗi thêm');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
         //xoa sp khoi csdl
         $keyword = Keyword::withTrashed()->where('id',$id)->first();
         if($keyword != null){           
             $keyword->forceDelete();
             return redirect()->route("admin.keyword.trash")->with('success','Xóa thành công');
         }
         return redirect()->route("admin.keyword.trash")->with('error','Mẫu tin không tồn tại');
    }
    //danhsachsanphamrac
    public function trash()
    {
        $keywords = Keyword::onlyTrashed() ->orderBy('created_at','DESC') ->get();
        return view('backend.keyword.trash',compact('keywords'));
    }   
    public function status(string $id)
    {
        //xulythaydoitrangthai
        $keyword = Keyword::find($id);
        if ($keyword != null) {
            $keyword->status = ($keyword->status == 1) ? 2 : 1;
            $keyword->save();

            return redirect()->route('admin.keyword.index')->with('success', 'Trạng thái đã được thay đổi');
        }
        return redirect()->route('admin.keyword.index')->with('error', 'Mẫu tin không tồn tại');
    }
    public function delete(string $id)
    {
        //xulyxoavaothungrac
        $keyword = Keyword::find($id);
        if($keyword != null){
            $keyword->delete();
            return redirect()->route("admin.keyword.index")->with('success','Xóa vào thùng rác thành công');
        }
        return redirect()->route("admin.keyword.index")->with('error','Mẫu tin không tồn tại');
    }
    public function restore(string $id)
    {
        //xulykhoiphuckhoithungrac
        $keyword = Keyword::withTrashed()->where('id',$id);
        if($keyword->first() != null){
            $keyword->restore();
            return redirect()->route("admin.keyword.trash")->with('success','Khôi phục thành công');
        }
        return redirect()->route("admin.keyword.trash")->with('error','Mẫu này không tồn tại');
    }
}
