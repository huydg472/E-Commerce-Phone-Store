<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'site_name',
        'brand_name',
        'slogan',
        'logo_url',
        'favicon_url',
        'support_phone',
        'support_email',
        'contact_email',
        'address',
        'footer_description',
        'facebook_url',
        'instagram_url',
        'tiktok_url',
        'youtube_url',
        'zalo_url',
        'shipping_fee_standard',
        'shipping_fee_express',
        'cash_on_delivery_note',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'bank_transfer_note',
        'maintenance_mode',
    ];

    protected $casts = [
        'shipping_fee_standard' => 'integer',
        'shipping_fee_express' => 'integer',
        'maintenance_mode' => 'boolean',
    ];

    public function restoreMissingDefaults(bool $persist = false): self
    {
        $defaults = self::defaults();
        $missing = [];

        foreach ($defaults as $key => $defaultValue) {
            $currentValue = $this->getAttribute($key);

            if ($currentValue === null || (is_string($currentValue) && trim($currentValue) === '')) {
                $missing[$key] = $defaultValue;
            }
        }

        if (!empty($missing)) {
            $this->forceFill($missing);

            if ($persist) {
                $this->save();
            }
        }

        return $this;
    }

    public static function current(): self
    {
        $settings = self::query()->firstOrCreate(
            ['id' => 1],
            self::defaults()
        );

        return $settings->restoreMissingDefaults(true)->fresh() ?? $settings;
    }

    public static function defaults(): array
    {
        return [
            'site_name' => 'ZinMobile',
            'brand_name' => 'ZinMobile',
            'slogan' => 'Hệ thống bán lẻ điện thoại chính hãng',
            'logo_url' => null,
            'favicon_url' => null,
            'support_phone' => '0909 000 000',
            'support_email' => 'support@zinmobile.vn',
            'contact_email' => 'contact@zinmobile.vn',
            'address' => 'TP. Hồ Chí Minh, Việt Nam',
            'footer_description' => 'Hệ thống bán lẻ điện thoại chính hãng, giá tốt, giao hàng nhanh và hỗ trợ tận tâm.',
            'facebook_url' => null,
            'instagram_url' => null,
            'tiktok_url' => null,
            'youtube_url' => null,
            'zalo_url' => null,
            'shipping_fee_standard' => 0,
            'shipping_fee_express' => 40000,
            'cash_on_delivery_note' => 'Thanh toán khi nhận hàng áp dụng cho đơn đủ điều kiện.',
            'bank_name' => 'Vietcombank',
            'bank_account_number' => '0123456789',
            'bank_account_name' => 'CTY TNHH ZinMobile',
            'bank_transfer_note' => 'Nội dung chuyển khoản: Mã đơn hàng + SĐT',
            'maintenance_mode' => false,
        ];
    }
}
