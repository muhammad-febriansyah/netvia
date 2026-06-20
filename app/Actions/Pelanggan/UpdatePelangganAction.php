<?php

namespace App\Actions\Pelanggan;

use App\Models\Pelanggan;
use App\Repositories\PelangganRepository;
use Illuminate\Support\Facades\DB;

class UpdatePelangganAction
{
    public function __construct(private PelangganRepository $pelangganRepository) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Pelanggan $pelanggan, array $data): Pelanggan
    {
        return DB::transaction(fn (): Pelanggan => $this->pelangganRepository->update($pelanggan, $data));
    }
}
