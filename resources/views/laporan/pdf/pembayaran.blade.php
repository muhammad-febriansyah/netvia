<x-laporan.pdf-layout :title="$title" :company="$company">
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>No Tagihan</th>
                <th>Pelanggan</th>
                <th>Metode</th>
                <th>Status</th>
                <th class="num">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row->created_at?->locale('id')->isoFormat('D MMM Y HH:mm') ?? '-' }}</td>
                    <td>{{ $row->tagihan?->nomor_tagihan ?? '-' }}</td>
                    <td>{{ $row->tagihan?->pelanggan?->nama ?? '-' }}</td>
                    <td>{{ $row->metode->label() }}</td>
                    <td>{{ $row->status->label() }}</td>
                    <td class="num">{{ rupiah($row->jumlah_bayar) }}</td>
                </tr>
            @empty
                <tr><td colspan="6">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>
</x-laporan.pdf-layout>
