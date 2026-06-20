@php($total = $rows->sum('jumlah'))
<x-laporan.pdf-layout :title="$title" :company="$company">
    <table>
        <thead>
            <tr>
                <th>No Tagihan</th>
                <th>Pelanggan</th>
                <th>Periode</th>
                <th>Jatuh Tempo</th>
                <th>Status</th>
                <th class="num">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row->nomor_tagihan }}</td>
                    <td>{{ $row->pelanggan?->nama ?? '-' }}</td>
                    <td>{{ $row->periode->locale('id')->isoFormat('MMMM Y') }}</td>
                    <td>{{ $row->tanggal_jatuh_tempo->locale('id')->isoFormat('D MMM Y') }}</td>
                    <td>{{ $row->status->label() }}</td>
                    <td class="num">{{ rupiah($row->jumlah) }}</td>
                </tr>
            @empty
                <tr><td colspan="6">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5">Total Tunggakan</td>
                <td class="num">{{ rupiah((int) $total) }}</td>
            </tr>
        </tfoot>
    </table>
</x-laporan.pdf-layout>
