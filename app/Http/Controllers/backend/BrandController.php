<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Http\Requests\UpdateBrandRequest;
use Illuminate\Support\Facades\Auth;

class BrandController extends Controller
{
    public function index()
    {

        $brands = brand::orderBy('created_at','DESC')->select()->paginate(4);
        return view('backend.brand.index',compact('brands'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $brands = Brand::orderBy('sort_order','ASC') >get();
        return view('backend.brand.create',compact('brands'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $brand = new Brand();
        if($request->hasFile('image')){
            $file = $request->file('image');
            $extension = $file->extension();
            $filename = date('YmdHis') . "." . $extension;
            $file->move(public_path('images/brand'),$filename);
            $brand->image = $filename;
           
            $brand->name = $request->name;
            $brand->slug = $request->slug;
            $brand->description =$request->description;
            $brand->sort_order = $request-> sort_order;
            $brand->created_by = Auth::id()?? 1;
            $brand->created_at = date('Y-m-d H:i:s');
            $brand->status = $request->status;
            $brand->save();
            return redirect()->route('admin.brand.index')->with('success','Thêm thành công');
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
        $brand = Brand::where('id',$id)->first();
        if($brand == null){
            return redirect()->back()->with('error','Không tồn tại brand');
        }
        return view('backend.brand.show',compact('brand'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $brand = Brand::where('id',$id)->first();
        $brands = Brand::orderBy('sort_order','ASC')
                    ->select("id","name","sort_order","status")
                    ->get();
        return view('backend.brand.edit',compact('brand','brands'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBrandRequest $request, string $id)
    {
        $brand = Brand::where('id',$id)->first();
        $brand->name = $request->name;
        $brand->slug =$request->slug;
        // upload file 
        if($request->hasFile('image')){
            //xoa hinh
            if($brand->image && File::exists(public_path("images/brand/" . $brand->image))){
                File::delete(public_path("images/brand/" . $brand->image));
            }
            $file = $request->file('image');
            $extension=$file->extension();
            $filename = date('YmdHis') . "." . $extension;
            $file->move(public_path("images/brand"),$filename);
            $brand->image = $filename;
        }
        //end upload file
        $brand->description=$request->description;
        $brand->sort_order=$request->sort_order;
        $brand->updated_by=Auth::id() ?? 1 ;
        $brand->updated_at= date('Y-m-d H:i:s');
        $brand->status = $request->status;
        if($brand->save()){
            return redirect()->route('admin.brand.index')->with('success','Brand đã được cập nhật');
            
        }
        return redirect()->back()->with('error','Lỗi thêm');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $brand =Brand::withTrashed()->where('id',$id)->first();
        if($brand != null){
            // xóa hình
            if($brand -> image && File::exists(public_path("images/brand/" . $brand->image))){
                File::delete(public_path("images/brand/" . $brand->image));
            }
            $brand->forceDelete();
            return redirect()->route("admin.brand.trash")->with('success','Xóa thành công');
        }
        return redirect()->route("admin.brand.trash")->with('error','Mẫu tin không tồn tại');
    }
    //danhsachsanphamrac
    public function trash()
    {
        $brands = Brand::onlyTrashed() ->orderBy('created_at','DESC') ->get();
        return view('backend.brand.trash',compact('brands'));
    }   
    public function status(string $id)
    {
        $brand = Brand::find($id);
        if ($brand != null) {
            $brand->status = ($brand->status == 1) ? 2 : 1;
            $brand->save();

            return redirect()->route('admin.brand.index')->with('success', 'Trạng thái đã được thay đổi');
        }
        return redirect()->route('admin.brand.index')->with('error', 'Mẫu tin không tồn tại');
    }
    public function delete(string $id)
    {
        $brand = Brand::find($id);
        if($brand != null){
            $brand->delete();
            return redirect()->route("admin.brand.index")->with('success','Xóa vào thùng rác thành công');
        }
        return redirect()->route("admin.brand.index")->with('error','Mẫu tin không tồn tại');
    }
    public function restore(string $id)
    {
        $brand = Brand::withTrashed()->where('id',$id);
        if($brand->first() != null){
            $brand->restore();
            return redirect()->route("admin.brand.trash")->with('success','Khôi phục thành công');
        }
        return redirect()->route("admin.brand.trash")->with('error','Mẫu này không tồn tại');
    }
}
