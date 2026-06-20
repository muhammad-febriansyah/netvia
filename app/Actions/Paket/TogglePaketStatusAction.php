<?php

namespace App\Actions\Paket;

use App\Models\Paket;
use App\Repositories\PaketRepository;
use Illuminate\Support\Facades\DB;

class TogglePaketStatusAction
{
    public function __construct(private PaketRepository $pakets) {}

    public function execute(Paket $paket): Paket
    {
        return DB::transaction(fn () => $this->pakets->update($paket, [
            'is_active' => ! $paket->is_active,
        ]));
    }
}
