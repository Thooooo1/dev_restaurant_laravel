<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\File;
use App\Http\Requests\UpdateCategoryRequest;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = category::orderBy('created_at','DESC')->select()->paginate(4);
        $categors = Category::orderBy('sort_order','ASC') ->get();
        return view('backend.category.index',compact('categories','categors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::orderBy('sort_order','ASC') >get();
        return view('backend.category.index',compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $category = new Category();
        if($request->hasFile('image')){
            $file = $request->file('image');
            $extension = $file->extension();
            $filename = date('YmdHis') . "." . $extension;
            $file->move(public_path('images/category'),$filename);
            $category->image = $filename;
           
            $category->name = $request->name;
            $category->slug = $request->slug;
            $category->parent_id = $request->parent_id;
            $category->description =$request->description;
            $category->sort_order = $request-> sort_order;
            $category->created_by = Auth::id()?? 1;
            $category->created_at = date('Y-m-d H:i:s');
            $category->status = $request->status;
            $category->save();
            return redirect()->route('admin.category.index')->with('success','Thêm thành công');
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
        $category = Category::where('id',$id)->first();
        if($category == null){
            return redirect()->back()->with('error','Không tồn tại category');
        }
        return view('backend.category.show',compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $category = Category::where('id',$id)->first();
        $categories = Category::orderBy('sort_order','ASC')
                    ->select("id","name","sort_order","status")
                    ->get();
        return view('backend.category.edit',compact('category','categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $category = Category::where('id',$id)->first();
        $category->name = $request->name;
        $category->slug =$request->slug;
        $category->parent_id =$request->parent_id;
        // upload file 
        if($request->hasFile('image')){
            //xoa hinh
            if($category->image && File::exists(public_path("images/category/" . $category->image))){
                File::delete(public_path("images/category/" . $category->image));
            }
            $file = $request->file('image');
            $extension=$file->extension();
            $filename = date('YmdHis') . "." . $extension;
            $file->move(public_path("images/category"),$filename);
            $category->image = $filename;
        }
        //end upload file
        $category->description=$request->description;
        $category->sort_order=$request->sort_order;
        $category->updated_by=Auth::id() ?? 1 ;
        $category->updated_at= date('Y-m-d H:i:s');
        $category->status = $request->status;
        if($category->save()){
            return redirect()->route('admin.category.index')->with('success','Category đã được cập nhật');
            
        }
        return redirect()->back()->with('error','Lỗi thêm');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category =Category::withTrashed()->where('id',$id)->first();
        if($category != null){
            // xóa hình
            if($category -> image && File::exists(public_path("images/category/" . $category->image))){
                File::delete(public_path("images/category/" . $category->image));
            }
            $category->forceDelete();
            return redirect()->route("admin.category.trash")->with('success','Xóa thành công');
        }
        return redirect()->route("admin.category.trash")->with('error','Mẫu tin không tồn tại');
    }
    //danhsachsanphamrac
    public function trash()
    {
        $categories = Category::onlyTrashed() ->orderBy('created_at','DESC') ->get();
        return view('backend.category.trash',compact('categories'));
    }   
    public function status(string $id)
    {
        $category = Category::find($id);
        if ($category != null) {
            $category->status = ($category->status == 1) ? 2 : 1;
            $category->save();

            return redirect()->route('admin.category.index')->with('success', 'Trạng thái đã được thay đổi');
        }
        return redirect()->route('admin.category.index')->with('error', 'Mẫu tin không tồn tại');
    }
    public function delete(string $id)
    {
        $category = Category::find($id);
        if($category != null){
            $category->delete();
            return redirect()->route("admin.category.index")->with('success','Xóa vào thùng rác thành công');
        }
        return redirect()->route("admin.category.index")->with('error','Mẫu tin không tồn tại');
    }
    public function restore(string $id)
    {
        $category = Category::withTrashed()->where('id',$id);
        if($category->first() != null){
            $category->restore();
            return redirect()->route("admin.category.trash")->with('success','Khôi phục thành công');
        }
        return redirect()->route("admin.category.trash")->with('error','Mẫu này không tồn tại');
    }
}
