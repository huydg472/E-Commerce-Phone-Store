<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $payment = $this->route('payment');
        $paymentId = is_object($payment) ? $payment->id : $payment;

        return [
            'payment_method' => ['sometimes', 'required', 'string', 'in:cod,bank_transfer,vnpay,momo'],
            'payment_status' => ['sometimes', 'required', 'string', 'in:pending,paid,failed,cancelled,refunded'],
            'amount' => ['sometimes', 'required', 'numeric', 'min:0'],
            'transaction_code' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('payments', 'transaction_code')->ignore($paymentId),
            ],
            'paid_at' => ['nullable', 'date'],
            'note' => ['nullable', 'string'],
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
            'payment_method' => 'Phương thức thanh toán',
            'payment_status' => 'Trạng thái thanh toán',
            'amount' => 'Số tiền thanh toán',
            'transaction_code' => 'Mã giao dịch',
            'paid_at' => 'Thời gian thanh toán',
            'note' => 'Ghi chú',
        ];
    }

}
