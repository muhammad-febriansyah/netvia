<?php

namespace App\Enums;

enum NotifikasiJenis: string
{
    case InvoiceBaru = 'invoice_baru';
    case ReminderH3 = 'reminder_h3';
    case ReminderDue = 'reminder_due';
    case ReminderOverdue = 'reminder_overdue';
    case StrukLunas = 'struk_lunas';

    public function label(): string
    {
        return match ($this) {
            self::InvoiceBaru => 'Tagihan Baru',
            self::ReminderH3 => 'Pengingat H-3',
            self::ReminderDue => 'Pengingat Jatuh Tempo',
            self::ReminderOverdue => 'Pengingat Lewat Tempo',
            self::StrukLunas => 'Struk Lunas',
        };
    }
}
