<x-layouts.customer title="Riwayat Transaksi">
    <x-slot:header>Riwayat Transaksi</x-slot:header>
    <x-slot:subheader>Daftar tagihan &amp; pembayaran Anda.</x-slot:subheader>

    <div class="rounded-2xl border border-line bg-white p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-line text-left text-[12px] uppercase tracking-wide text-muted">
                        <th class="pb-3 pr-3">No. Tagihan</th>
                        <th class="pb-3 pr-3">Periode</th>
                        <th class="pb-3 pr-3">Jumlah</th>
                        <th class="pb-3 pr-3">Jatuh Tempo</th>
                        <th class="pb-3 pr-3">Status</th>
                        <th class="pb-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tagihans as $t)
                        <tr class="border-b border-line last:border-0">
                            <td class="py-3 pr-3 font-medium">{{ $t->nomor_tagihan }}</td>
                            <td class="py-3 pr-3">{{ $t->periode->translatedFormat('M Y') }}</td>
                            <td class="py-3 pr-3 font-medium">{{ rupiah($t->jumlah) }}</td>
                            <td class="py-3 pr-3">{{ $t->tanggal_jatuh_tempo->translatedFormat('d M Y') }}</td>
                            <td class="py-3 pr-3"><span class="rounded-full px-2 py-1 text-xs font-medium {{ $t->status->badgeClass() }}">{{ $t->status->label() }}</span></td>
                            <td class="py-3 text-right">
                                @if (in_array($t->status, [\App\Enums\TagihanStatus::Unpaid, \App\Enums\TagihanStatus::Overdue], true))
                                    <a href="{{ $t->publicUrl() }}" target="_blank" class="font-semibold text-brand hover:underline">Bayar</a>
                                @elseif ($t->status === \App\Enums\TagihanStatus::Paid)
                                    <a href="{{ route('publik.struk', $t->public_token) }}" target="_blank" class="text-muted hover:text-ink">Struk</a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-6 text-center text-muted">Belum ada transaksi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $tagihans->links() }}</div>
    </div>
</x-layouts.customer>
