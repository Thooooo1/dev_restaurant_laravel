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

    protected function prepareForValidation()
    {
        if ($this->has('phone') && !empty($this->phone)) {
            $this->merge([
                'phone' => preg_replace('/[^0-9]/', '', (string)$this->phone)
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user');
        
        // Nếu route parameter là object, lấy ID
        if (is_object($userId)) {
            $userId = $userId->id;
        }
        
        return [
            'fullname' => 'nullable|string|max:255',
            'phone' => 'nullable|string|digits_between:10,15|unique:user,phone,' . $userId,
            'email' => 'nullable|email|unique:user,email,' . $userId,
            'username' => 'nullable|string|min:3|max:50|unique:user,username,' . $userId,
            'gender' => 'nullable|in:0,1,Nam,Nữ',
            'address' => 'nullable|string|max:255',
            'roles' => 'nullable|in:customer,admin,employee',
            'status' => 'nullable|in:1,2',
            'password' => 'nullable|string|min:6',
            'password_re' => 'nullable|same:password',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
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
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
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
