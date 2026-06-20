<?php

namespace App\Actions\Paket;

use App\Models\Paket;
use App\Repositories\PaketRepository;
use Illuminate\Support\Facades\DB;

class UpdatePaketAction
{
    public function __construct(private PaketRepository $pakets) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Paket $paket, array $data): Paket
    {
        return DB::transaction(fn () => $this->pakets->update($paket, $data));
    }
}
