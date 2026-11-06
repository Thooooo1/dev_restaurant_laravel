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
        if ($role === 'customer') {
            return $this->customers($request);
        }
        return $this->listEmployees($request);
    }



    /**
     * Display a listing of customers.
     */
    public function customers(Request $request)
    {
        $status = $request->query('status'); 

        $users = User::where('roles', 'customer');

        if ($request->filled('q')) {
            $q = trim($request->q);
            $users->where(function($sub) use ($q) {
                $sub->where('fullname', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('username', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        if ($status !== null) {
            $users->where('status', $status);
        }

        $users = $users->orderBy('created_at', 'DESC')->paginate(10)->withQueryString();
        
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
        $role = $request->roles ?? 'customer';
        $user->roles = $role;
        $user->fullname = trim($request->fullname);
        $user->phone = preg_replace('/[^0-9]/', '', (string)$request->phone);
        $user->email = $request->filled('email') ? trim($request->email) : null;
        $user->gender = $request->gender;
        $user->address = $request->filled('address') ? trim($request->address) : null;
        $user->status = $request->status ?? 1;
        $user->created_by = Auth::id() ?? 1;

        if ($role === 'customer') {
            // Khách hàng: chỉ lưu trữ thông tin cơ bản, tự động gán username theo sđt và mật khẩu mặc định
            $user->username = $request->filled('username') ? trim($request->username) : $user->phone;
            $user->password = $request->filled('password') ? Hash::make($request->password) : Hash::make('123456');
            $user->image = 'non_image.png';
            $user->admin_lever = null;
        } else {
            // Nhân viên / Admin
            if($request->hasFile('image')){
                $file = $request->file('image');
                $extension = $file->extension();
                $filename = date('YmdHis') . "." . $extension;
                $file->move(public_path('images/user'),$filename);
                $user->image = $filename;
            } else {
                $user->image = 'non_image.png';
            }
            $user->username = $request->username ?? $user->phone;
            if ($request->password) {
                $user->password = Hash::make($request->password);
            }
            if ($request->roles === 'admin') {
                $user->admin_lever = 1;
            } elseif ($request->roles === 'employee') {
                $user->admin_lever = 2;
            } else {
                $user->admin_lever = null;
            }
        }

        $user->save();
        
        // Redirect về đúng trang dựa trên role
        if ($role == 'admin' || $role == 'employee') {
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
        $user = User::where('id', $id)->first();
        if (!$user) {
            return redirect()->back()->with('error', 'Không tìm thấy người dùng.');
        }
        
        // 1. Họ và tên: nếu có nhập thì cập nhật, nếu để trống thì giữ nguyên họ tên cũ
        if ($request->filled('fullname')) {
            $user->fullname = trim($request->fullname);
        }
        
        // 2. Tên đăng nhập: nếu có nhập thì cập nhật, nếu để trống thì giữ nguyên
        if ($request->filled('username')) {
            $user->username = trim($request->username);
        }
        
        // 3. Số điện thoại: nếu có nhập thì cập nhật, nếu để trống thì giữ nguyên
        if ($request->filled('phone')) {
            $user->phone = preg_replace('/[^0-9]/', '', (string)$request->phone);
            if ($user->roles == 'customer' && empty($user->username)) {
                $user->username = $user->phone;
            }
        }
        
        // 4. Mật khẩu: CHỈ cập nhật khi có nhập mật khẩu mới
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        
        // 5. Ảnh đại diện: CHỈ thay thế khi người dùng chọn file ảnh mới
        if ($request->hasFile('image')) {
            if ($user->image && File::exists(public_path("images/user/" . $user->image))) {
                File::delete(public_path("images/user/" . $user->image));
            }
            $file = $request->file('image');
            $extension = $file->extension();
            $filename = date('YmdHis') . "_" . uniqid() . "." . $extension;
            $file->move(public_path("images/user"), $filename);
            $user->image = $filename;
        }
        
        // 6. Giới tính: nếu có chọn thì cập nhật, không thì giữ nguyên
        if ($request->has('gender') && $request->gender !== null && $request->gender !== '') {
            $user->gender = $request->gender;
        }
        
        // 7. Email: cập nhật nếu có gửi trường email
        if ($request->has('email')) {
            $user->email = $request->email ? trim($request->email) : null;
        }
        
        // 8. Địa chỉ: cập nhật nếu có gửi trường address
        if ($request->has('address')) {
            $user->address = $request->address ? trim($request->address) : null;
        }
        
        // 9. Quyền / Vai trò: cập nhật nếu có chọn
        if ($request->filled('roles')) {
            $user->roles = $request->roles;
            if ($request->roles === 'admin') {
                $user->admin_lever = 1;
            } elseif ($request->roles === 'employee') {
                $user->admin_lever = 2;
            } else {
                $user->admin_lever = null;
            }
        }
        
        // 10. Trạng thái hoạt động: cập nhật nếu có chọn
        if ($request->has('status') && $request->status !== null && $request->status !== '') {
            $user->status = $request->status;
        }
        
        $user->updated_by = Auth::id() ?? 1;
        $user->updated_at = date('Y-m-d H:i:s');
        
        if ($user->save()) {
            $targetRole = $user->roles;
            if ($targetRole == 'admin' || $targetRole == 'employee') {
                return redirect()->route('admin.user.employees')->with('success', 'Thông tin nhân viên ' . $user->fullname . ' đã được cập nhật thành công!');
            } else {
                return redirect()->route('admin.user.customers')->with('success', 'Thông tin khách hàng ' . $user->fullname . ' đã được cập nhật thành công!');
            }
        }
        
        return redirect()->back()->with('error', 'Lỗi không thể lưu thông tin người dùng.');
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
            $role = $user->roles;
            $user->delete();
            if ($role == 'customer') {
                return redirect()->route("admin.user.customers")->with('success','Đã xóa khách hàng thành công');
            }
            return redirect()->route("admin.user.employees")->with('success','Xóa vào thùng rác thành công');
        }
        return redirect()->back()->with('error','Mẫu tin không tồn tại');
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

        if (!$user) {
            return redirect()->route('admin.user.employees')->with('error', 'Không tìm thấy người dùng.');
        }

        $user->roles = 'employee';
        $user->admin_lever = 2;
        $user->save();

        return redirect()->route('admin.user.employees')->with('success', 'Chuyển về quyền Nhân viên thành công.');
    }
    public function addAdmin($id)
    {
        $user = User::find($id);

        if (!$user) {
            return redirect()->route('admin.user.employees')->with('error', 'Không tìm thấy người dùng.');
        }

        $user->roles = 'admin';
        $user->admin_lever = 1; 
        $user->save();

        return redirect()->route('admin.user.employees')->with('success', 'Cấp quyền Quản trị viên thành công.');
    }


    public function listEmployees(Request $request)
    {
        $query = User::whereIn('roles', ['admin', 'employee']);

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function($sub) use ($q) {
                $sub->where('fullname', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('username', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        $users = $query->orderBy('id', 'ASC')->paginate(10)->withQueryString();
            
        $type = 'employees';
        return view('backend.user.index', compact('users', 'type'));
    }


}
