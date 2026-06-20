@php($total = $rows->sum('jumlah_bayar'))
<x-laporan.pdf-layout :title="$title" :company="$company">
    <table>
        <thead>
            <tr>
                <th>Tanggal Bayar</th>
                <th>No Tagihan</th>
                <th>Pelanggan</th>
                <th>Metode</th>
                <th class="num">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row->dibayar_at?->locale('id')->isoFormat('D MMM Y HH:mm') ?? '-' }}</td>
                    <td>{{ $row->tagihan?->nomor_tagihan ?? '-' }}</td>
                    <td>{{ $row->tagihan?->pelanggan?->nama ?? '-' }}</td>
                    <td>{{ $row->metode->label() }}</td>
                    <td class="num">{{ rupiah($row->jumlah_bayar) }}</td>
                </tr>
            @empty
                <tr><td colspan="5">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4">Total</td>
                <td class="num">{{ rupiah((int) $total) }}</td>
            </tr>
        </tfoot>
    </table>
</x-laporan.pdf-layout>
