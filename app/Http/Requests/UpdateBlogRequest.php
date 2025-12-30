<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBlogRequest extends FormRequest
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
    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:post,slug,' . $this->route('blog'), 
            'content' => 'required|string',
            'description' => 'nullable|string|max:500',
            'topic_id' => 'required|exists:topic,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', 
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
