<?php

namespace App\Enums;

enum PelangganStatus: string
{
    case Aktif = 'aktif';
    case Nonaktif = 'nonaktif';
    case Isolir = 'isolir';

    public function label(): string
    {
        return match ($this) {
            self::Aktif => 'Aktif',
            self::Nonaktif => 'Nonaktif',
            self::Isolir => 'Isolir',
        };
    }
}
