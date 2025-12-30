<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBrandRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'name'=>'required',
            'image'=>'image|mimes:jpeg,png,jpg,gif,webp',
        ];
    }
    public function messages():array
    {
        return[
            'name.required'=>'Tên bắt buộc phải nhập',
            'image.image'=>'Tệp tải lên phải là một hình ảnh',
            'image.mimes'=>'hình ảnh phải thuộc các định dạng sau : jpeg,png,jpg,gif,webp.',
        ];
    }
}
