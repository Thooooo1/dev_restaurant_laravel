<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateBannerRequest;
use App\Http\Requests\StoreBannerRequest;
use Illuminate\Support\Facades\File;

use Illuminate\Support\Facades\Auth;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::orderBy('created_at','DESC')->select()->paginate(5);
        return view('backend.banner.index',compact('banners'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $banners = Banner::orderBy('sort_order','ASC') ->get();
        return view('backend.banner.create',compact('banners'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBannerRequest $request)
    {
        $banner = new Banner();
        if($request->hasFile('image')){
            $file = $request->file('image');
            $extension = $file->extension();
            $filename = date('YmdHis') . "." . $extension;
            $file->move(public_path('images/banner'),$filename);
            $banner->image = $filename;
           
            $banner->name = $request->name;
            $banner->link = $request->link;
            $banner->position = $request->position;
            $banner->description =$request->description;
            $banner->sort_order = $request-> sort_order;
            $banner->created_by = Auth::id()?? 1;
            $banner->created_at = date('Y-m-d H:i:s');
            $banner->status = $request->status;
            $banner->save();
            return redirect()->route('admin.banner.index')->with('success','Thêm thành công');
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
        $banner = Banner::where('id',$id)->first();
        if($banner == null){
            return redirect()->back()->with('error','Không tồn tại banner');
        }
        return view('backend.banner.show',compact('banner'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        
        $banner = Banner::where('id',$id)->first();
        $banners = Banner::orderBy('sort_order','ASC')
                    ->select("id","name","sort_order","status")
                    ->get();
        return view('backend.banner.edit',compact('banner','banners'));
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBannerRequest $request, string $id)
    {
        $banner = Banner::where('id',$id)->first();
        $banner->name = $request->name;
        $banner->link =$request->link;
        // upload file 
        if($request->hasFile('image')){
            //xoa hinh
            if($banner->image && File::exists(public_path("images/banner/" . $banner->image))){
                File::delete(public_path("images/banner/" . $banner->image));
            }
            $file = $request->file('image');
            $extension=$file->extension();
            $filename = date('YmdHis') . "." . $extension;
            $file->move(public_path("images/banner"),$filename);
            $banner->image = $filename;
        }
        //end upload file
        $banner->position=$request->position;
        $banner->description=$request->description;
        $banner->sort_order=$request->sort_order;
        $banner->updated_by=Auth::id() ?? 1 ;
        $banner->updated_at= date('Y-m-d H:i:s');
        $banner->status = $request->status;
        if($banner->save()){
            return redirect()->route('admin.banner.index')->with('success','Banner đã được cập nhật');
        }
        return redirect()->back()->with('error','Lỗi thêm');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $banner =Banner::withTrashed()->where('id',$id)->first();
        if($banner != null){
            // xóa hình
            if($banner -> image && File::exists(public_path("images/banner/" . $banner->image))){
                File::delete(public_path("images/banner/" . $banner->image));
            }
            $banner->forceDelete();
            return redirect()->route("admin.banner.trash")->with('success','Xóa thành công');
        }
        return redirect()->route("admin.banner.trash")->with('error','Mẫu tin không tồn tại');
    }
    //danhsachsanphamrac
    public function trash()
    {
        $banners = Banner::onlyTrashed() ->orderBy('created_at','DESC') ->paginate(5);
        return view('backend.banner.trash',compact('banners'));
    }   
    public function status(string $id)
    {
        $banner = Banner::find($id);
        if ($banner != null) {
            $banner->status = ($banner->status == 1) ? 2 : 1;
            $banner->save();

            return redirect()->route('admin.banner.index')->with('success', 'Trạng thái đã được thay đổi');
        }
        return redirect()->route('admin.banner.index')->with('error', 'Mẫu tin không tồn tại');
    }

    public function delete(string $id)
    {
        $banner = Banner::find($id);
        if($banner != null){
            $banner->delete();
            return redirect()->route("admin.banner.index")->with('success','Xóa vào thùng rác thành công');
        }
        return redirect()->route("admin.banner.index")->with('error','Mẫu tin không tồn tại');
    }
    public function restore(string $id)
    {
        $banner = Banner::withTrashed()->where('id',$id);
        if($banner->first() != null){
            $banner->restore();
            return redirect()->route("admin.banner.trash")->with('success','Khôi phục thành công');
        }
        return redirect()->route("admin.banner.trash")->with('error','Mẫu này không tồn tại');
    }
}