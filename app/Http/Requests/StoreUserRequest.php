<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    
    public function authorize(): bool
    {
        return true;
    }

    
    public function rules(): array
    {
        $role = $this->input('roles', 'customer');
        
        $rules = [
            'fullname' => 'required|string|max:255',
            'phone' => 'required|string|regex:/^0[0-9]{9,10}$/|unique:user,phone',
            'gender' => 'required|in:0,1,Nam,Nữ',
            'address' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:user,email',
        ];

        // Với customer: chỉ lưu trữ thông tin cơ bản
        if ($role === 'customer') {
            $rules['roles'] = 'nullable|in:customer,admin,employee';
            $rules['status'] = 'nullable|in:1,2';
            $rules['username'] = 'nullable|string|max:50|unique:user,username';
            $rules['password'] = 'nullable|string|max:50';
            $rules['password_re'] = 'nullable';
            $rules['image'] = 'nullable';
        } else {
            // Với admin/employee: username, password là required
            $rules['roles'] = 'required|in:customer,admin,employee';
            $rules['status'] = 'required|in:1,2';
            $rules['username'] = 'required|string|min:4|max:50|unique:user,username';
            $rules['password'] = 'required|string|min:6|max:50';
            $rules['password_re'] = 'required|same:password';
            $rules['image'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048';
        }

        return $rules;
    }

    /**
     * Tùy chỉnh thông báo lỗi.
     */
    public function messages(): array
    {
        return [
            'fullname.required' => 'Họ tên không được để trống.',
            'phone.required' => 'Số điện thoại không được để trống.',
            'phone.regex' => 'Số điện thoại không đúng định dạng! Phải bắt đầu bằng số 0 và có đủ 10 chữ số, chỉ cho phép nhập số.',
            'phone.unique' => 'Số điện thoại này đã tồn tại.',
            'email.email' => 'Email không đúng định dạng! Phải theo dạng a@gmail.com.',
            'email.unique' => 'Email này đã tồn tại.',
            'username.required' => 'Tên người dùng không được để trống.',
            'username.min' => 'Tên người dùng phải có ít nhất 4 ký tự.',
            'username.max' => 'Tên người dùng không được vượt quá 50 ký tự.',
            'username.unique' => 'Tên người dùng đã tồn tại.',
            'password.required' => 'Mật khẩu không được để trống.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.max' => 'Mật khẩu không được vượt quá 50 ký tự.',
            'password_re.required' => 'Xác nhận mật khẩu không được để trống.',
            'password_re.same' => 'Mật khẩu xác nhận không khớp.',
            'gender.required' => 'Vui lòng chọn giới tính.',
            'gender.in' => 'Giới tính không hợp lệ.',
            'roles.required' => 'Vui lòng chọn quyền.',
            'roles.in' => 'Quyền không hợp lệ.',
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',
            'image.image' => 'Tệp tải lên phải là hình ảnh.',
            'image.mimes' => 'Hình ảnh phải có định dạng: jpeg, png, jpg, gif.',
            'image.max' => 'Kích thước hình ảnh không được vượt quá 2MB.',
        ];
    }
}
