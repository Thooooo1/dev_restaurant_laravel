<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Image;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\UpdateImageRequest;
use App\Http\Requests\StoreImageRequest;

class ImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $images = image::orderBy('created_at','DESC')->select()->paginate(6);
        return view('backend.image.index',compact('images'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $images = Image::get();
        return view('backend.image.index',compact('images'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreImageRequest $request)
    {
        $image = new Image();
        if($request->hasFile('image')){
            $file = $request->file('image');
            $extension = $file->extension();
            $filename = date('YmdHis') . "." . $extension;
            $file->move(public_path('images/library'),$filename);
            $image->image = $filename;         
            $image->title = $request->title;
            $image->slug = $request->slug;
            $image->created_by = Auth::id()?? 1;
            $image->created_at = date('Y-m-d H:i:s');
            $image->status = $request->status;
            $image->save();
            return redirect()->route('admin.image.index')->with('success','Thêm thành công');
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
        $image = Image::where('id',$id)->first();
        if($image == null){
            return redirect()->back()->with('error','Không tồn tại image');
        }
        return view('backend.image.show',compact('image'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $image = Image::where('id',$id)->first();
        $images = Image::select("id","title","status")
                    ->get();
        return view('backend.image.edit',compact('image','images'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateImageRequest $request, string $id)
    {
        $image = Image::where('id',$id)->first();
        $image->title = $request->title;
        $image->slug =$request->slug;
        // upload file 
        if($request->hasFile('image')){
            //xoa hinh
            if($image->image && File::exists(public_path("images/library/" . $image->image))){
                File::delete(public_path("images/library/" . $image->image));
            }
            $file = $request->file('image');
            $extension=$file->extension();
            $filename = date('YmdHis') . "." . $extension;
            $file->move(public_path("images/library"),$filename);
            $image->image = $filename;
        }
        //end upload file
      
        $image->updated_by=Auth::id() ?? 1 ;
        $image->updated_at= date('Y-m-d H:i:s');
        $image->status = $request->status;
        if($image->save()){
            return redirect()->route('admin.image.index')->with('success','image đã được cập nhật');
        }
        return redirect()->back()->with('error','Lỗi thêm');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $image =Image::withTrashed()->where('id',$id)->first();
        if($image != null){
            // xóa hình
            if($image -> image && File::exists(public_path("images/library/" . $image->image))){
                File::delete(public_path("images/library/" . $image->image));
            }
            $image->forceDelete();
            return redirect()->route("admin.image.trash")->with('success','Xóa thành công');
        }
        return redirect()->route("admin.image.trash")->with('error','Mẫu tin không tồn tại');
    }
    //danhsachsanphamrac
    public function trash()
    {
        $images = Image::onlyTrashed() ->orderBy('created_at','DESC') ->get();
        return view('backend.image.trash',compact('images'));
    }   
    public function status(string $id)
    {
        //xulythaydoitrangthai
        $image = Image::find($id);
        if ($image != null) {
            $image->status = ($image->status == 1) ? 2 : 1;
            $image->save();

            return redirect()->route('admin.image.index')->with('success', 'Trạng thái đã được thay đổi');
        }
        return redirect()->route('admin.image.index')->with('error', 'Mẫu tin không tồn tại');
    }
    public function delete(string $id)
    {
        //xulyxoavaothungrac
        $image = Image::find($id);
        if($image != null){
            $image->delete();
            return redirect()->route("admin.image.index")->with('success','Xóa vào thùng rác thành công');
        }
        return redirect()->route("admin.image.index")->with('error','Mẫu tin không tồn tại');
    }
    public function restore(string $id)
    {
        //xulykhoiphuckhoithungrac
        $image = Image::withTrashed()->where('id',$id);
        if($image->first() != null){
            $image->restore();
            return redirect()->route("admin.image.trash")->with('success','Khôi phục thành công');
        }
        return redirect()->route("admin.image.trash")->with('error','Mẫu này không tồn tại');
    }
}