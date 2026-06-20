<?php

namespace App\Exports;

use App\Models\Pembayaran;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PendapatanExport implements FromQuery, WithHeadings, WithMapping
{
    /**
     * @param  Builder<Pembayaran>  $query
     */
    public function __construct(private Builder $query) {}

    /**
     * @return Builder<Pembayaran>
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
        return ['Tanggal Bayar', 'No Tagihan', 'Pelanggan', 'Metode', 'Jumlah'];
    }

    /**
     * @param  Pembayaran  $row
     * @return list<string>
     */
    public function map($row): array
    {
        return [
            $row->dibayar_at?->locale('id')->isoFormat('D MMM Y HH:mm') ?? '-',
            $row->tagihan?->nomor_tagihan ?? '-',
            $row->tagihan?->pelanggan?->nama ?? '-',
            $row->metode->label(),
            rupiah($row->jumlah_bayar),
        ];
    }
}
