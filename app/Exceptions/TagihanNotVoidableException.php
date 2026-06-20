<?php

namespace App\Exceptions;

use RuntimeException;

class TagihanNotVoidableException extends RuntimeException
{
    public static function alreadyPaid(): self
    {
        return new self('Tagihan yang sudah lunas tidak bisa dibatalkan.');
    }

    public static function alreadyVoid(): self
    {
        return new self('Tagihan sudah dibatalkan sebelumnya.');
    }
}
