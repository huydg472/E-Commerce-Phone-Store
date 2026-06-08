<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:225', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'username' => ['required', 'string', 'max:100', 'unique:users,username'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'status' => ['sometimes', 'string', 'in:active,inactive'],
            'email_verified_at' => ['nullable', 'date'],
        ];
    }
}
