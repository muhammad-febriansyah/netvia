<?php

namespace App\Repositories;

use App\Models\Paket;
use Illuminate\Database\Eloquent\Builder;

class PaketRepository
{
    /**
     * Base query for the paket DataTable (server-side processing).
     *
     * @return Builder<Paket>
     */
    public function dataTableQuery(): Builder
    {
        return Paket::query()
            ->select(['id', 'nama', 'kecepatan_mbps', 'harga', 'is_active', 'created_at']);
    }

    /**
     * @return Builder<Paket>
     */
    public function activeForSelect(): Builder
    {
        return Paket::query()
            ->where('is_active', true)
            ->orderBy('nama');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Paket
    {
        return Paket::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Paket $paket, array $data): Paket
    {
        $paket->update($data);

        return $paket;
    }

    public function delete(Paket $paket): void
    {
        $paket->delete();
    }

    /**
     * Count pelanggan with status `aktif` still using this paket.
     */
    public function countActivePelanggan(Paket $paket): int
    {
        return $paket->pelanggans()
            ->where('status', 'aktif')
            ->count();
    }
}
