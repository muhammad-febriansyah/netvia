<?php

namespace App\Actions\Paket;

use App\Exceptions\PaketInUseException;
use App\Models\Paket;
use App\Repositories\PaketRepository;
use Illuminate\Support\Facades\DB;

class DeletePaketAction
{
    public function __construct(private PaketRepository $pakets) {}

    /**
     * @throws PaketInUseException when the paket still has active pelanggan.
     */
    public function execute(Paket $paket): void
    {
        $activeCount = $this->pakets->countActivePelanggan($paket);

        if ($activeCount > 0) {
            throw PaketInUseException::withActivePelanggan($activeCount);
        }

        DB::transaction(fn () => $this->pakets->delete($paket));
    }
}
