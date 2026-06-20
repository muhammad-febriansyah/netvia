<?php

namespace App\Actions\Pelanggan;

use App\Models\Pelanggan;
use App\Repositories\PelangganRepository;
use Illuminate\Support\Facades\DB;

class StorePelangganAction
{
    public function __construct(
        private PelangganRepository $pelangganRepository,
        private GenerateKodePelangganAction $generateKode,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): Pelanggan
    {
        return DB::transaction(function () use ($data): Pelanggan {
            $data['kode_pelanggan'] = $this->generateKode->handle();

            return $this->pelangganRepository->create($data);
        });
    }
}
