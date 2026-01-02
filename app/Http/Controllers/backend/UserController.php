<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\StoreUserRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;

use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $role = $request->query('role');
        $status = $request->query('status'); 

        $users = User::query();

        if ($role) {
            $users->where('roles', $role);
        }

        if ($status !== null) {
            $users->where('status', $status);
        }

        $users = $users->orderBy('created_at', 'DESC')->paginate(6)->appends([
            'role' => $role,
            'status' => $status
        ]);

        return view('backend.user.index', compact('users'));
    }



    /**
     * Display a listing of customers.
     */
    public function customers(Request $request)
    {
        $status = $request->query('status'); 

        $users = User::where('roles', 'customer');

        if ($status !== null) {
            $users->where('status', $status);
        }

        $users = $users->orderBy('created_at', 'DESC')->paginate(6)->appends([
            'status' => $status
        ]);
        
        $type = 'customers';
        return view('backend.user.index', compact('users', 'type'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $role = $request->query('role', 'customer'); // Mặc định là customer
        $users = User::get();
        return view('backend.user.create', compact('users', 'role'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $user = new User();
        
        // Xử lý image (optional)
        if($request->hasFile('image')){
            $file = $request->file('image');
            $extension = $file->extension();
            $filename = date('YmdHis') . "." . $extension;
            $file->move(public_path('images/user'),$filename);
            $user->image = $filename;
        } else {
            // Nếu không có ảnh upload, sử dụng ảnh mặc định
            $user->image = 'non_image.png';
        }
       
        $user->username = $request->username ?? null;
        // Chỉ hash password nếu có password
        if ($request->password) {
            $user->password = Hash::make($request->password);
        }
        $user->fullname = $request->fullname;
        $user->gender = $request->gender;
        $user->email = $request->email ?? null;
        $user->phone = $request->phone;
        $user->roles = $request->roles;
        // Set admin_lever khi tạo user với vai trò admin
        if ($request->roles == 'admin') {
            $user->admin_lever = 2;
        }
        $user->address = $request->address ?? null;
        $user->created_by = Auth::id() ?? 1;
        $user->status = $request->status;
        $user->save();
        
        // Redirect về đúng trang dựa trên role
        if ($request->roles == 'admin') {
            return redirect()->route('admin.user.employees')->with('success','Thêm nhân viên thành công');
        } else {
            return redirect()->route('admin.user.customers')->with('success','Thêm khách hàng thành công');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::where('id',$id)->first();
        if($user == null){
            return redirect()->back()->with('error','Không tồn tại user');
        }
        return view('backend.user.show',compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::where('id',$id)->first();
        $users = User::get();
        $role = $user->roles; // Lấy role từ user hiện tại
        return view('backend.user.edit', compact('user', 'users', 'role'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, string $id)
    {
        $user = User::where('id',$id)->first();
        
        // Chỉ cập nhật username nếu có giá trị
        if ($request->has('username')) {
            $user->username = $request->username ?? null;
        }
        
        // Chỉ cập nhật password nếu có password mới
        if ($request->password) {
            $user->password = Hash::make($request->password);
        }
        
        // upload file 
        if($request->hasFile('image')){
            //xoa hinh
            if($user->image && File::exists(public_path("images/user/" . $user->image))){
                File::delete(public_path("images/user/" . $user->image));
            }
            $file = $request->file('image');
            $extension=$file->extension();
            $filename = date('YmdHis') . "." . $extension;
            $file->move(public_path("images/user"),$filename);
            $user->image = $filename;
        }
        //end upload file
        $user->fullname = $request->fullname;
        $user->gender = $request->gender;
        
        // Chỉ cập nhật email nếu có giá trị
        if ($request->has('email')) {
            $user->email = $request->email ?? null;
        }
        
        $user->phone = $request->phone;
        $user->roles = $request->roles;
        $user->admin_lever = 2;
        $user->address = $request->address ?? null;
        $user->updated_by=Auth::id() ?? 1 ;
        $user->updated_at= date('Y-m-d H:i:s');
        $user->status = $request->status;
        if($user->save()){
            // Redirect về đúng trang dựa trên role
            if ($request->roles == 'admin') {
                return redirect()->route('admin.user.employees')->with('success','Nhân viên đã được cập nhật');
            } else {
                return redirect()->route('admin.user.customers')->with('success','Khách hàng đã được cập nhật');
            }
        }
        return redirect()->back()->with('error','Lỗi cập nhật');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user =User::withTrashed()->where('id',$id)->first();
        if($user != null){
            // xóa hình
            if($user -> image && File::exists(public_path("images/user/" . $user->image))){
                File::delete(public_path("images/user/" . $user->image));
            }
            $user->forceDelete();
            return redirect()->route("admin.user.trash")->with('success','Xóa thành công');
        }
        return redirect()->route("admin.user.trash")->with('error','Mẫu tin không tồn tại');
    }
    //danhsachsanphamrac
    public function trash()
    {
        $users = User::onlyTrashed() ->orderBy('created_at','DESC') ->paginate(5);
        return view('backend.user.trash',compact('users'));
    }   
    public function status(string $id)
    {
        $user = User::find($id);
        if ($user != null) {
            $user->status = ($user->status == 1) ? 2 : 1;
            $user->save();

            return redirect()->route('admin.user.index')->with('success', 'Trạng thái đã được thay đổi');
        }
        return redirect()->route('admin.user.index')->with('error', 'Mẫu tin không tồn tại');
    }
    public function delete(string $id)
    {
        $user = User::find($id);
        if($user != null){
            $user->delete();
            return redirect()->route("admin.user.index")->with('success','Xóa vào thùng rác thành công');
        }
        return redirect()->route("admin.user.index")->with('error','Mẫu tin không tồn tại');
    }
    public function restore(string $id)
    {
        $user = User::withTrashed()->where('id',$id);
        if($user->first() != null){
            $user->restore();
            return redirect()->route("admin.user.trash")->with('success','Khôi phục thành công');
        }
        return redirect()->route("admin.user.trash")->with('error','Mẫu này không tồn tại');
    }
    public function toggleLine(User $user)
    {
        $currentUser = Auth::user();

        if ($currentUser->admin_lever == 2 && $currentUser->id != $user->id) {
            return redirect()->route('admin.user.employees')->with('error', 'Bạn không có quyền thay đổi trạng thái của người khác!');
        }

        if ($currentUser->admin_lever == 1 || $currentUser->id == $user->id) {
            $user->line = $user->line == 1 ? 0 : 1;
            $user->save();
            return redirect()->route('admin.user.employees')->with('success', 'Cập nhật trạng thái thành công!');
        }

        return redirect()->route('admin.user.employees')->with('error', 'Bạn không có quyền thực hiện thao tác này!');
    }

    public function removeAdmin($id)
    {
        $user = User::find($id);

        if (!$user || $user->roles != 'admin') {
            return redirect()->route('admin.user.index')->with('error', 'Không tìm thấy admin.');
        }

        $user->roles = 'customer';
        $user->admin_lever = null;
        $user->save();

        return redirect()->route('admin.user.index')->with('success', 'Xóa quyền admin thành công.');
    }
    public function addAdmin($id)
    {
        $user = User::find($id);

        if (!$user || $user->roles != 'customer') {
            return redirect()->route('admin.user.index')->with('error', 'Không tìm thấy người dùng hoặc người dùng không hợp lệ.');
        }

        $user->roles = 'admin';
        $user->admin_lever = 2; 
        $user->save();

        return redirect()->route('admin.user.index')->with('success', 'Cấp quyền admin thành công.');
    }


    public function listEmployees()
    {
           $users = User::where('roles', 'admin')
               ->where('admin_lever', 2)
               ->orderBy('created_at', 'DESC')
               ->paginate(10);
            
           $type = 'employees';
           return view('backend.user.index', compact('users', 'type'));
    }


}
