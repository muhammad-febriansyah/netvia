<?php

namespace App\Enums;

enum PemutusanStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu',
            self::Approved => 'Disetujui',
            self::Rejected => 'Ditolak',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'bg-yellow-100 text-yellow-800',
            self::Approved => 'bg-green-100 text-green-700',
            self::Rejected => 'bg-red-100 text-red-700',
        };
    }
}
