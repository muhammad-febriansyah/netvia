<?php

namespace App\Actions\Pelanggan;

use App\Models\Pelanggan;
use App\Repositories\PelangganRepository;
use Illuminate\Support\Facades\DB;

class DeletePelangganAction
{
    public function __construct(private PelangganRepository $pelangganRepository) {}

    public function handle(Pelanggan $pelanggan): void
    {
        DB::transaction(fn () => $this->pelangganRepository->delete($pelanggan));
    }
}
