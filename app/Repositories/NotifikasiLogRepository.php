<?php

namespace App\Repositories;

use App\Models\NotifikasiLog;
use Illuminate\Database\Eloquent\Builder;

class NotifikasiLogRepository
{
    /**
     * Base query for the notifikasi log DataTable.
     *
     * @param  array{channel?: string|null, jenis?: string|null, status?: string|null}  $filters
     * @return Builder<NotifikasiLog>
     */
    public function dataTableQuery(array $filters = []): Builder
    {
        $query = NotifikasiLog::query()->with('pelanggan:id,nama');

        if (! empty($filters['channel'])) {
            $query->where('channel', $filters['channel']);
        }

        if (! empty($filters['jenis'])) {
            $query->where('jenis', $filters['jenis']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query;
    }
}
