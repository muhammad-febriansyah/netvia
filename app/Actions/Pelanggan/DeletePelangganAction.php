<?php

namespace App\Actions\Pelanggan;

use App\Models\Pelanggan;
use App\Repositories\PelangganRepository;
use Illuminate\Support\Facades\DB;

class DeletePelangganAction
{
    public function __construct(private PelangganRepository $pelanggans) {}

    public function execute(Pelanggan $pelanggan): void
    {
        DB::transaction(fn () => $this->pelanggans->delete($pelanggan));
    }
}
