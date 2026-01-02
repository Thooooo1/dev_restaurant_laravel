<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreImageRequest extends FormRequest
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
            
            'title' => 'required|string|max:255',
            'image'=>'image|mimes:jpeg,png,jpg,gif,webp',
            
        ];
    }

    /**
     * Custom error messages
     */
    public function messages(): array
    {
        return [            
            'title.required' => 'Tiêu đề không được để trống.',
            'image.image'=>'Tệp tải lên phải là một hình ảnh',
            'image.mimes'=>'hình ảnh phải thuộc các định dạng sau : jpeg,png,jpg,gif,webp.',
        ];
    }
}
