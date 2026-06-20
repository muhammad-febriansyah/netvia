<?php

namespace App\Exports;

use App\Models\Tagihan;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TunggakanExport implements FromQuery, WithHeadings, WithMapping
{
    /**
     * @param  Builder<Tagihan>  $query
     */
    public function __construct(private Builder $query) {}

    /**
     * @return Builder<Tagihan>
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
        return ['No Tagihan', 'Pelanggan', 'Periode', 'Jatuh Tempo', 'Status', 'Jumlah'];
    }

    /**
     * @param  Tagihan  $row
     * @return list<string>
     */
    public function map($row): array
    {
        return [
            $row->nomor_tagihan,
            $row->pelanggan?->nama ?? '-',
            $row->periode->locale('id')->isoFormat('MMMM Y'),
            $row->tanggal_jatuh_tempo->locale('id')->isoFormat('D MMM Y'),
            $row->status->label(),
            rupiah($row->jumlah),
        ];
    }
}
