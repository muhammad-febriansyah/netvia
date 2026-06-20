<?php

namespace App\Enums;

enum PelangganStatus: string
{
    case Pending = 'pending';
    case Aktif = 'aktif';
    case Nonaktif = 'nonaktif';
    case Isolir = 'isolir';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Aktivasi',
            self::Aktif => 'Aktif',
            self::Nonaktif => 'Nonaktif',
            self::Isolir => 'Isolir',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'bg-yellow-100 text-yellow-800',
            self::Aktif => 'bg-green-100 text-green-700',
            self::Nonaktif => 'bg-gray-100 text-gray-600',
            self::Isolir => 'bg-red-100 text-red-700',
        };
    }
}
