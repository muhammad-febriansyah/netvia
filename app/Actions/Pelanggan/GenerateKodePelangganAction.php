<?php

namespace App\Actions\Pelanggan;

use App\Repositories\PelangganRepository;

class GenerateKodePelangganAction
{
    public function __construct(private PelangganRepository $pelanggans) {}

    /**
     * Build the next unique customer code, e.g. `PLG-000123`.
     */
    public function execute(): string
    {
        $next = $this->pelanggans->maxKodeSequence() + 1;

        return 'PLG-'.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }
}
