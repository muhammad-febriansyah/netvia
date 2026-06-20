<?php

namespace App\Enums;

enum NotifikasiChannel: string
{
    case Whatsapp = 'whatsapp';
    case Email = 'email';

    public function label(): string
    {
        return match ($this) {
            self::Whatsapp => 'WhatsApp',
            self::Email => 'Email',
        };
    }
}
