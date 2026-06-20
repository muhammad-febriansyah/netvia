<?php

namespace App\Repositories;

use App\Enums\TagihanStatus;
use App\Models\Tagihan;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class TagihanRepository
{
    /**
     * Base query for the tagihan DataTable, eager loading the pelanggan name.
     *
     * @param  array{periode?: string|null, status?: string|null, pelanggan_id?: int|string|null}  $filters
     * @return Builder<Tagihan>
     */
    public function dataTableQuery(array $filters = []): Builder
    {
        $query = Tagihan::query()->with('pelanggan:id,nama,kode_pelanggan');

        if (! empty($filters['periode'])) {
            $periode = Carbon::parse($filters['periode'].'-01');
            $query->whereYear('periode', $periode->year)->whereMonth('periode', $periode->month);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['pelanggan_id'])) {
            $query->where('pelanggan_id', $filters['pelanggan_id']);
        }

        return $query;
    }

    public function existsForPeriode(int $pelangganId, CarbonInterface $periode): bool
    {
        return Tagihan::query()
            ->where('pelanggan_id', $pelangganId)
            ->whereDate('periode', $periode->format('Y-m-d'))
            ->exists();
    }

    /**
     * Count tagihan already issued in the given period's month — used to build
     * the running monthly sequence for nomor_tagihan.
     */
    public function countForMonth(CarbonInterface $periode): int
    {
        return Tagihan::query()
            ->whereYear('periode', $periode->year)
            ->whereMonth('periode', $periode->month)
            ->count();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Tagihan
    {
        return Tagihan::create($data);
    }

    /**
     * Mark unpaid tagihan past their due date as overdue.
     *
     * @return int number of rows updated.
     */
    public function markOverdue(Carbon $today): int
    {
        return Tagihan::query()
            ->where('status', TagihanStatus::Unpaid)
            ->whereDate('tanggal_jatuh_tempo', '<', $today->format('Y-m-d'))
            ->update(['status' => TagihanStatus::Overdue]);
    }
}
