<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTableOrderRequest extends FormRequest
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
            'table_id' => 'required|exists:tables,id',
            'phone' => 'required|numeric|digits_between:10,15',
            'customer_name' => 'required|string|max:255',
            'gender' => 'nullable|in:0,1',
            'number_of_guests' => 'required|integer|min:1',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'table_id.required' => 'Vui lòng chọn bàn',
            'table_id.exists' => 'Bàn không tồn tại',
            'phone.required' => 'Số điện thoại không được để trống',
            'phone.numeric' => 'Số điện thoại chỉ được chứa các chữ số.',
            'phone.digits_between' => 'Số điện thoại phải có từ 10 đến 15 chữ số.',
            'customer_name.required' => 'Tên khách hàng không được để trống',
            'number_of_guests.required' => 'Vui lòng nhập số lượng khách',
            'number_of_guests.min' => 'Số lượng khách phải ít nhất là 1',
        ];
    }
}
