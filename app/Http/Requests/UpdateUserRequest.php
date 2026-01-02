<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $role = $this->input('roles', 'customer');
        $userId = $this->route('user');
        
        // Nếu route parameter là object, lấy ID
        if (is_object($userId)) {
            $userId = $userId->id;
        }
        
        $rules = [
            'fullname' => 'required|string|max:255',
            'phone' => 'required|string|digits_between:10,15|unique:user,phone,' . $userId,
            'gender' => 'required|in:0,1',
            'address' => 'nullable|string|max:255',
            'roles' => 'required|in:customer,admin',
            'status' => 'required|in:1,2',
        ];

        // Với customer: email, username, password, image là optional
        if ($role === 'customer') {
            $rules['email'] = 'nullable|email|unique:user,email,' . $userId;
            $rules['username'] = 'nullable|string|min:4|max:50|unique:user,username,' . $userId;
            $rules['password'] = 'nullable|string|min:6';
            $rules['image'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048';
        } else {
            // Với admin: username, password là required
            $rules['email'] = 'nullable|email|unique:user,email,' . $userId;
            $rules['username'] = 'required|string|min:4|max:50|unique:user,username,' . $userId;
            $rules['password'] = 'required|string|min:6';
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
            'phone.digits_between' => 'Số điện thoại phải có từ 10 đến 15 chữ số.',
            'phone.unique' => 'Số điện thoại này đã tồn tại.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email này đã tồn tại.',
            'username.required' => 'Tên người dùng không được để trống.',
            'username.min' => 'Tên người dùng phải có ít nhất 4 ký tự.',
            'username.max' => 'Tên người dùng không được vượt quá 50 ký tự.',
            'username.unique' => 'Tên người dùng đã tồn tại.',
            'password.required' => 'Mật khẩu không được để trống.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
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
