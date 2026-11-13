<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }

    /**
     * @return array{bank_name: string, account_number: string, account_holder: string, qris_path: string, contact_name: string, contact_phone: string, sponsorship_note: string}
     */
    public static function donationChannel(): array
    {
        $stored = static::query()->where('key', 'donation_channel')->first()?->value;
        if (is_string($stored)) {
            $stored = json_decode($stored, true);
        }

        return array_merge([
            'bank_name' => '',
            'account_number' => '',
            'account_holder' => '',
            'qris_path' => '',
            'contact_name' => '',
            'contact_phone' => '',
            'sponsorship_note' => '',
        ], is_array($stored) ? $stored : []);
    }
}
