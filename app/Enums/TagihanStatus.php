<?php

namespace App\Enums;

enum TagihanStatus: string
{
    case Unpaid = 'unpaid';
    case Paid = 'paid';
    case Overdue = 'overdue';
    case Void = 'void';

    public function label(): string
    {
        return match ($this) {
            self::Unpaid => 'Belum Bayar',
            self::Paid => 'Lunas',
            self::Overdue => 'Lewat Tempo',
            self::Void => 'Dibatalkan',
        };
    }

    /**
     * Tailwind badge color classes per status.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Unpaid => 'bg-yellow-100 text-yellow-800',
            self::Paid => 'bg-green-100 text-green-800',
            self::Overdue => 'bg-red-100 text-red-800',
            self::Void => 'bg-gray-100 text-gray-800',
        };
    }
}
