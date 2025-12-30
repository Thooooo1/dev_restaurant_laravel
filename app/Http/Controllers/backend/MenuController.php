<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateMenuRequest;
use App\Http\Requests\StoreMenuRequest;
use App\Models\Menu;
use Illuminate\Support\Facades\Auth;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::orderBy('created_at', 'DESC')->select()->paginate(10);
        $mumus = Menu::where('position', 'mainmenu')
        ->orderBy('sort_order', 'ASC')
        ->get();
        return view('backend.menu.index', compact('menus','mumus'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $mumus = Menu::where('position', 'mainmenu')
                 ->orderBy('sort_order', 'ASC')
                 ->get();
        return view('backend.menu.index', compact('mumus'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMenuRequest $request)
    {
        $menu = new Menu();
        $menu->name = $request->name;
        $menu->link = $request->link;
        $menu->parent_id = $request->parent_id;
        $menu->position = $request->position;
        $menu->type = $request->type;
        $menu->sort_order = $request->sort_order; 
        $menu->table_id = $request->table_id; 
        $menu->created_by = Auth::id()?? 1;
        $menu->created_at = date('Y-m-d H:i:s');
        $menu->status = $request->status;
        $menu->save();
        return redirect()->route('admin.menu.index')->with('success', 'Thêm thành công');

    }

    /**b
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $menu = Menu::where('id',$id)->first();
        if($menu == null){
            return redirect()->back()->with('error','Không tồn tại.');
        }
        return view('backend.menu.show',compact('menu'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $menu = Menu::where('id',$id)->first();
        $menus = Menu::orderBy('sort_order','ASC')
                    ->select("id","name","sort_order","status")
                    ->get();
        return view('backend.menu.edit',compact('menu','menus'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMenuRequest $request, string $id)
    {
        $menu = Menu::where('id',$id)->first();
        $menu->name = $request->name;
        $menu->link = $request->link;
        $menu->parent_id = $request->parent_id;
        $menu->position = $request->position;
        $menu->type = $request->type;
        $menu->sort_order = $request->sort_order; 
        $menu->table_id = $request->table_id; 
        $menu->updated_by=Auth::id() ?? 1 ;
        $menu->updated_at= date('Y-m-d H:i:s');
        $menu->status = $request->status;
        if($menu->save()){
            return redirect()->route('admin.menu.index')->with('success','Menu đã được cập nhật');
            
        }
        return redirect()->back()->with('error','Lỗi thêm');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $menu =Menu::withTrashed()->where('id',$id)->first();
        if($menu != null){           
            $menu->forceDelete();
            return redirect()->route("admin.menu.trash")->with('success','Xóa thành công');
        }
        return redirect()->route("admin.menu.trash")->with('error','Mẫu tin không tồn tại');
    }
    //danhsachsanphamrac
    public function trash()
    {
        $menus = Menu::onlyTrashed()->orderBy('created_at', 'DESC')->get();
        return view('backend.menu.trash', compact('menus'));
    }
    public function status(string $id)
    {
        $menu = Menu::find($id);
        if ($menu != null) {
            $menu->status = ($menu->status == 1) ? 2 : 1;
            $menu->save();

            return redirect()->route('admin.menu.index')->with('success', 'Trạng thái đã được thay đổi');
        }
        return redirect()->route('admin.menu.index')->with('error', 'Mẫu tin không tồn tại');
    }
    public function delete(string $id)
    {
        $menu = Menu::find($id);
        if($menu != null){
            $menu->delete();
            return redirect()->route("admin.menu.index")->with('success','Xóa vào thùng rác thành công');
        }
        return redirect()->route("admin.menu.index")->with('error','Mẫu tin không tồn tại');
    }
    public function restore(string $id)
    {
        $menu = Menu::withTrashed()->where('id',$id);
        if($menu->first() != null){
            $menu->restore();
            return redirect()->route("admin.menu.trash")->with('success','Khôi phục thành công');
        }
        return redirect()->route("admin.menu.trash")->with('error','Mẫu này không tồn tại');
    }
}