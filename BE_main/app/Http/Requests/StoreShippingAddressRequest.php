<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShippingAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'receiver_name' => ['required', 'string', 'max:150'],
            'receiver_phone' => ['required', 'string', 'max:20'],
            'province' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'ward' => ['required', 'string', 'max:100'],
            'address_detail' => ['required', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
            'is_default' => ['sometimes', 'boolean'],
        ];
    }


    public function messages(): array
    {
        return [
            'required' => ':attribute không được để trống.',
            'string' => ':attribute phải là chuỗi ký tự.',
            'integer' => ':attribute phải là số nguyên.',
            'numeric' => ':attribute phải là số.',
            'boolean' => ':attribute phải là true hoặc false.',
            'array' => ':attribute phải là một danh sách.',
            'date' => ':attribute phải là ngày hợp lệ.',
            'url' => ':attribute phải là đường dẫn hợp lệ.',
            'email' => ':attribute phải là email hợp lệ.',
            'max' => ':attribute không được vượt quá :max ký tự.',
            'min' => ':attribute phải có giá trị tối thiểu là :min.',
            'unique' => ':attribute đã tồn tại.',
            'exists' => ':attribute không tồn tại trong hệ thống.',
            'in' => ':attribute không hợp lệ.',
            'confirmed' => ':attribute xác nhận không khớp.',
            'not_in' => ':attribute không được bằng :values.',
            'lte' => ':attribute phải nhỏ hơn hoặc bằng :value.',
        ];
    }

    public function attributes(): array
    {
        return [
            'receiver_name' => 'Tên người nhận',
            'receiver_phone' => 'Số điện thoại người nhận',
            'province' => 'Tỉnh/Thành phố',
            'district' => 'Quận/Huyện',
            'ward' => 'Phường/Xã',
            'address_detail' => 'Địa chỉ chi tiết',
            'note' => 'Ghi chú',
            'is_default' => 'Địa chỉ mặc định',
        ];
    }

}
