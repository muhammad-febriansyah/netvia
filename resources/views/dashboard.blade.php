<x-layouts.app title="Dashboard">
    <x-slot:header>Dashboard</x-slot:header>
    <x-slot:subheader>Ringkasan tagihan &amp; langganan · {{ $periode->translatedFormat('F Y') }}</x-slot:subheader>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        {{-- Pelanggan aktif --}}
        <div class="rounded-2xl border border-line bg-white p-[18px] shadow-[0_1px_2px_rgba(16,24,64,.035)]">
            <div class="mb-3.5 flex size-10 items-center justify-center rounded-[11px] bg-brand-soft text-brand">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div class="text-[12.5px] font-medium text-muted">Pelanggan Aktif</div>
            <div class="mt-0.5 text-[28px] font-bold tracking-tight">{{ number_format($stats['pelanggan_aktif'], 0, ',', '.') }}</div>
        </div>

        {{-- Tagihan bulan ini --}}
        <div class="rounded-2xl border border-line bg-white p-[18px] shadow-[0_1px_2px_rgba(16,24,64,.035)]">
            <div class="mb-3.5 flex size-10 items-center justify-center rounded-[11px] bg-cyan-50 text-cyan-600">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 17.5v-11"/></svg>
            </div>
            <div class="text-[12.5px] font-medium text-muted">Tagihan Bulan Ini</div>
            <div class="mt-0.5 text-[28px] font-bold tracking-tight">{{ number_format($stats['tagihan_bulan_ini'], 0, ',', '.') }}</div>
            <div class="mt-1 text-xs font-medium text-muted">Total {{ rupiah($stats['tagihan_bulan_ini_nominal']) }}</div>
        </div>

        {{-- Sudah dibayar --}}
        <div class="rounded-2xl border border-line bg-white p-[18px] shadow-[0_1px_2px_rgba(16,24,64,.035)]">
            <div class="mb-3.5 flex size-10 items-center justify-center rounded-[11px] bg-green-100 text-green-700">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.801 10A10 10 0 1 1 17 3.335"/><path d="m9 11 3 3L22 4"/></svg>
            </div>
            <div class="text-[12.5px] font-medium text-muted">Sudah Dibayar</div>
            <div class="mt-0.5 text-[28px] font-bold tracking-tight">{{ number_format($stats['dibayar'], 0, ',', '.') }}</div>
            <div class="mt-1 text-xs font-medium text-green-700">
                {{ $stats['tagihan_bulan_ini'] > 0 ? round($stats['dibayar'] / $stats['tagihan_bulan_ini'] * 100) : 0 }}% terbayar
            </div>
        </div>

        {{-- Pendapatan / outstanding --}}
        <div class="relative overflow-hidden rounded-2xl bg-brand p-[18px] text-white shadow-[0_2px_8px_rgba(37,71,249,.12)]">
            <div class="absolute -right-5 -top-5 size-24 rounded-full bg-white/10"></div>
            <div class="mb-3.5 flex size-10 items-center justify-center rounded-[11px] bg-white/20">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
            </div>
            <div class="text-[12.5px] font-medium text-white/85">Outstanding</div>
            <div class="mt-0.5 whitespace-nowrap text-2xl font-bold tracking-tight">{{ rupiah($stats['outstanding_nominal']) }}</div>
            <div class="mt-1 text-[11.5px] font-medium text-white/90">Belum bayar &amp; lewat tempo</div>
        </div>
    </div>

    {{-- Tren pendapatan (6 bulan) --}}
    @php($trendMax = max(1, collect($trend)->max('total')))
    <div class="mt-5 rounded-2xl border border-line bg-white p-6 shadow-[0_1px_2px_rgba(16,24,64,.035)]">
        <div class="mb-5 flex items-center justify-between">
            <h2 class="text-[15px] font-semibold text-ink">Tren Pendapatan</h2>
            <span class="text-xs font-medium text-muted">6 bulan terakhir</span>
        </div>
        <div class="flex h-44 items-end gap-3">
            @foreach ($trend as $bar)
                <div class="flex flex-1 flex-col items-center gap-2">
                    <div class="flex w-full flex-1 items-end">
                        <div class="w-full rounded-t-md bg-brand/85 transition-all hover:bg-brand"
                            style="height: {{ max(2, (int) round($bar['total'] / $trendMax * 100)) }}%"
                            title="{{ rupiah($bar['total']) }}"></div>
                    </div>
                    <span class="text-[11px] font-medium text-muted">{{ $bar['label'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Jatuh tempo terdekat & overdue --}}
    <div class="mt-5 grid grid-cols-1 gap-5 lg:grid-cols-2">
        @foreach ([['Jatuh Tempo Terdekat', $dueSoon, 'text-amber-600'], ['Lewat Tempo', $overdue, 'text-red-600']] as [$judul, $rows, $tone])
            <div class="rounded-2xl border border-line bg-white p-5 shadow-[0_1px_2px_rgba(16,24,64,.035)]">
                <h2 class="mb-4 text-[15px] font-semibold text-ink">{{ $judul }}</h2>
                <div class="divide-y divide-line">
                    @forelse ($rows as $t)
                        <a href="{{ $t->publicUrl() }}" target="_blank"
                            class="flex items-center justify-between gap-3 py-2.5 hover:opacity-80">
                            <div class="min-w-0">
                                <div class="truncate text-sm font-medium text-ink">{{ $t->pelanggan?->nama ?? '-' }}</div>
                                <div class="text-xs text-muted">{{ $t->nomor_tagihan }} · {{ $t->tanggal_jatuh_tempo->locale('id')->isoFormat('D MMM Y') }}</div>
                            </div>
                            <span class="flex-none text-sm font-semibold {{ $tone }}">{{ rupiah($t->jumlah) }}</span>
                        </a>
                    @empty
                        <p class="py-2.5 text-sm text-muted">Tidak ada data.</p>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</x-layouts.app>
