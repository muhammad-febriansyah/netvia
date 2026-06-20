<?php

namespace App\Enums;

enum PembayaranMetode: string
{
    case QrisPakasir = 'qris_pakasir';
    case TransferManual = 'transfer_manual';
    case Cash = 'cash';

    public function label(): string
    {
        return match ($this) {
            self::QrisPakasir => 'QRIS (Pakasir)',
            self::TransferManual => 'Transfer Manual',
            self::Cash => 'Tunai',
        };
    }
}
