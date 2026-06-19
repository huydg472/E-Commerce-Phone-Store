<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSiteSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'site_name' => ['required', 'string', 'max:150'],
            'brand_name' => ['required', 'string', 'max:150'],
            'slogan' => ['nullable', 'string', 'max:255'],
            'logo_url' => ['nullable', 'url', 'max:500'],
            'favicon_url' => ['nullable', 'url', 'max:500'],
            'support_phone' => ['nullable', 'string', 'max:30'],
            'support_email' => ['nullable', 'email', 'max:150'],
            'contact_email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'footer_description' => ['nullable', 'string'],
            'facebook_url' => ['nullable', 'url', 'max:500'],
            'instagram_url' => ['nullable', 'url', 'max:500'],
            'tiktok_url' => ['nullable', 'url', 'max:500'],
            'youtube_url' => ['nullable', 'url', 'max:500'],
            'zalo_url' => ['nullable', 'url', 'max:500'],
            'shipping_fee_standard' => ['required', 'integer', 'min:0'],
            'shipping_fee_express' => ['required', 'integer', 'min:0'],
            'cash_on_delivery_note' => ['nullable', 'string'],
            'bank_name' => ['nullable', 'string', 'max:150'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_account_name' => ['nullable', 'string', 'max:150'],
            'bank_transfer_note' => ['nullable', 'string'],
            'maintenance_mode' => ['required', 'boolean'],
        ];
    }
}
