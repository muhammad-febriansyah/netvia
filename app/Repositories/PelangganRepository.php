<?php

namespace App\Repositories;

use App\Models\Pelanggan;
use Illuminate\Database\Eloquent\Builder;

class PelangganRepository
{
    /**
     * Base query for DataTables list, eager loading the paket relation.
     *
     * @param  array{paket_id?: int|string|null, status?: string|null}  $filters
     * @return Builder<Pelanggan>
     */
    public function dataTableQuery(array $filters = []): Builder
    {
        $query = Pelanggan::query()->with('paket:id,nama');

        if (! empty($filters['paket_id'])) {
            $query->where('paket_id', $filters['paket_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query;
    }

    public function create(array $attributes): Pelanggan
    {
        return Pelanggan::create($attributes);
    }

    public function update(Pelanggan $pelanggan, array $attributes): Pelanggan
    {
        $pelanggan->update($attributes);

        return $pelanggan;
    }

    public function delete(Pelanggan $pelanggan): void
    {
        $pelanggan->delete();
    }

    /**
     * The numeric part of the highest existing kode_pelanggan, including
     * soft-deleted rows, so generated codes never collide.
     */
    public function maxKodeSequence(): int
    {
        $kode = Pelanggan::withTrashed()
            ->orderByDesc('id')
            ->value('kode_pelanggan');

        return $kode ? (int) preg_replace('/\D/', '', $kode) : 0;
    }

    /**
     * Latest tagihan and outstanding total for the customer detail page.
     *
     * @return array{terakhir: \App\Models\Tagihan|null, outstanding: int}
     */
    public function ringkasanTagihan(Pelanggan $pelanggan): array
    {
        return [
            'terakhir' => $pelanggan->tagihans()
                ->latest('periode')
                ->first(),
            'outstanding' => (int) $pelanggan->tagihans()
                ->whereIn('status', ['unpaid', 'overdue'])
                ->sum('jumlah'),
        ];
    }
}
