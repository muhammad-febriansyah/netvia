@php($total = $rows->sum(fn ($r) => (int) ($r->tunggakan_total ?? 0)))
<x-laporan.pdf-layout :title="$title" :company="$company">
    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Nama</th>
                <th>Paket</th>
                <th>Status</th>
                <th class="num">Tunggakan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row->kode_pelanggan }}</td>
                    <td>{{ $row->nama }}</td>
                    <td>{{ $row->paket?->nama ?? '-' }}</td>
                    <td>{{ $row->status->label() }}</td>
                    <td class="num">{{ rupiah((int) ($row->tunggakan_total ?? 0)) }}</td>
                </tr>
            @empty
                <tr><td colspan="5">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4">Total Tunggakan</td>
                <td class="num">{{ rupiah((int) $total) }}</td>
            </tr>
        </tfoot>
    </table>
</x-laporan.pdf-layout>
