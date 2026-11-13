<?php

namespace App\Support;

class WhatsApp
{
    public static function digits(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone) ?? '';
        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }
        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        } elseif ($digits !== '' && str_starts_with($digits, '8')) {
            $digits = '62'.$digits;
        }

        if (strlen($digits) < 10 || strlen($digits) > 15) {
            return null;
        }

        return $digits;
    }

    public static function link(?string $phone, string $message): ?string
    {
        $digits = self::digits($phone);

        return $digits ? 'https://wa.me/'.$digits.'?text='.rawurlencode($message) : null;
    }
}
