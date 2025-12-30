<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
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
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            // 'reply_id' => 'nullable|integer|exists:contacts,id',
            // 'user_id' => 'nullable|integer|exists:users,id',
            'timeline' => 'nullable|date',
            
        ];
    }

    /**
     * Custom error messages
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Họ tên không được để trống.',
            'email.required' => 'Email không được để trống.',
            'email.email' => 'Email không hợp lệ.',
            'phone.max' => 'Số điện thoại không được quá 20 ký tự.',
            'title.required' => 'Tiêu đề không được để trống.',
            'content.required' => 'Nội dung không được để trống.',
            // 'reply_id.exists' => 'Phản hồi không hợp lệ.',
            // 'user_id.exists' => 'Người dùng không tồn tại.',
            
        ];
    }
}
