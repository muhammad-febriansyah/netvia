<?php

namespace App\Exports;

use App\Models\Pelanggan;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RekapPelangganExport implements FromQuery, WithHeadings, WithMapping
{
    /**
     * @param  Builder<Pelanggan>  $query
     */
    public function __construct(private Builder $query) {}

    /**
     * @return Builder<Pelanggan>
     */
    public function query(): Builder
    {
        return $this->query;
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return ['Kode', 'Nama', 'Paket', 'Status', 'Tunggakan'];
    }

    /**
     * @param  Pelanggan  $row
     * @return list<string>
     */
    public function map($row): array
    {
        return [
            $row->kode_pelanggan,
            $row->nama,
            $row->paket?->nama ?? '-',
            $row->status->label(),
            rupiah((int) ($row->tunggakan_total ?? 0)),
        ];
    }
}
