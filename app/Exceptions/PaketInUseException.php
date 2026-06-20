<?php

namespace App\Exceptions;

use RuntimeException;

class PaketInUseException extends RuntimeException
{
    public static function withActivePelanggan(int $count): self
    {
        return new self("Paket masih digunakan oleh {$count} pelanggan aktif.");
    }
}
