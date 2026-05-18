<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50', 'unique:roles,name'],
            'display_name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
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
            'name' => 'Tên vai trò',
            'display_name' => 'Tên hiển thị',
            'description' => 'Mô tả',
        ];
    }

}
