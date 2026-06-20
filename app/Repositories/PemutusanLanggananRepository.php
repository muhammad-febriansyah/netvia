<?php

namespace App\Repositories;

use App\Models\PemutusanLangganan;
use Illuminate\Database\Eloquent\Builder;

class PemutusanLanggananRepository
{
    /**
     * Admin DataTable query, eager loading the pelanggan.
     *
     * @param  array{status?: string|null}  $filters
     * @return Builder<PemutusanLangganan>
     */
    public function dataTableQuery(array $filters = []): Builder
    {
        $query = PemutusanLangganan::query()->with('pelanggan:id,nama,kode_pelanggan');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): PemutusanLangganan
    {
        return PemutusanLangganan::create($data);
    }

    public function hasPending(int $pelangganId): bool
    {
        return PemutusanLangganan::query()
            ->where('pelanggan_id', $pelangganId)
            ->where('status', 'pending')
            ->exists();
    }
}
