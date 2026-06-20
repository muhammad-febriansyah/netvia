<?php

namespace App\Support;

class WhatsappNumber
{
    /**
     * Normalize an Indonesian phone number to the `62xxxxxxxxxx` format.
     *
     * Strips spaces, dashes, dots and a leading `+`, then converts a leading
     * `0` or bare `8` prefix into the canonical `62` prefix.
     */
    public static function normalize(?string $value): string
    {
        $digits = preg_replace('/\D/', '', (string) $value);

        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '62')) {
            return $digits;
        }

        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }

        if (str_starts_with($digits, '8')) {
            return '62'.$digits;
        }

        return $digits;
    }
}
