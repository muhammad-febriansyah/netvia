<?php

namespace App\Actions\Paket;

use App\Models\Paket;
use App\Repositories\PaketRepository;
use Illuminate\Support\Facades\DB;

class StorePaketAction
{
    public function __construct(private PaketRepository $pakets) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): Paket
    {
        return DB::transaction(fn () => $this->pakets->create($data));
    }
}
