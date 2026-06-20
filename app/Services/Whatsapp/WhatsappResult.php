<?php

namespace App\Services\Whatsapp;

class WhatsappResult
{
    public function __construct(
        public bool $success,
        public ?string $reference = null,
        public ?string $error = null,
    ) {}

    public static function success(?string $reference = null): self
    {
        return new self(true, $reference);
    }

    public static function failure(string $error): self
    {
        return new self(false, null, $error);
    }
}
