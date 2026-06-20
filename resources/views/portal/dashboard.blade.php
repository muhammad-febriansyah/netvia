<x-layouts.customer title="Dashboard">
    <x-slot:header>Halo, {{ $pelanggan->nama }}</x-slot:header>
    <x-slot:subheader>{{ $pelanggan->kode_pelanggan }}</x-slot:subheader>

    @if ($pelanggan->status === \App\Enums\PelangganStatus::Pending)
        <div class="mb-6 flex items-start gap-3 rounded-2xl border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-800">
            <i data-lucide="clock" class="mt-0.5 size-5 flex-none"></i>
            <div>
                <div class="font-semibold">Akun menunggu aktivasi</div>
                <p>Pendaftaran Anda sedang ditinjau admin. Tagihan mulai terbit setelah langganan diaktifkan.</p>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
        <div class="rounded-2xl border border-line bg-white p-6 lg:col-span-2">
            <h2 class="mb-5 text-sm font-semibold text-ink">Langganan Saya</h2>
            <dl class="grid grid-cols-1 gap-x-6 gap-y-4 text-sm sm:grid-cols-2">
                <div><dt class="text-muted">Paket</dt><dd class="mt-0.5 font-medium">{{ $pelanggan->paket?->nama ?? '-' }}</dd></div>
                <div><dt class="text-muted">Harga / bulan</dt><dd class="mt-0.5 font-medium">{{ rupiah($pelanggan->paket?->harga ?? 0) }}</dd></div>
                <div>
                    <dt class="text-muted">Status</dt>
                    <dd class="mt-0.5"><span class="rounded-full px-2 py-1 text-xs font-medium {{ $pelanggan->status->badgeClass() }}">{{ $pelanggan->status->label() }}</span></dd>
                </div>
                <div><dt class="text-muted">Jatuh Tempo</dt><dd class="mt-0.5 font-medium">Tanggal {{ $pelanggan->tgl_jatuh_tempo }} tiap bulan</dd></div>
                <div class="sm:col-span-2"><dt class="text-muted">Alamat</dt><dd class="mt-0.5 font-medium">{{ $pelanggan->alamat ?: '-' }}</dd></div>
            </dl>
            <div class="mt-5 flex gap-2 border-t border-line pt-5">
                <a href="{{ route('portal.langganan') }}" class="inline-flex h-9 items-center gap-1.5 rounded-[10px] border border-line px-3.5 text-[13px] font-medium text-ink hover:bg-canvas"><i data-lucide="package"></i> Kelola Paket</a>
                <a href="{{ route('portal.riwayat') }}" class="inline-flex h-9 items-center gap-1.5 rounded-[10px] border border-line px-3.5 text-[13px] font-medium text-ink hover:bg-canvas"><i data-lucide="history"></i> Riwayat</a>
            </div>
        </div>

        <div class="rounded-2xl border border-line bg-white p-6">
            <h2 class="mb-5 text-sm font-semibold text-ink">Tagihan</h2>
            <div class="rounded-xl bg-canvas p-4">
                <div class="text-xs text-muted">Total Tunggakan</div>
                <div class="mt-1 text-2xl font-semibold {{ $ringkasan['outstanding'] > 0 ? 'text-red-600' : 'text-green-600' }}">{{ rupiah($ringkasan['outstanding']) }}</div>
            </div>
            @if ($ringkasan['terakhir'])
                <div class="mt-4 text-sm">
                    <div class="text-xs text-muted">Tagihan Terakhir</div>
                    <div class="mt-1 font-medium">{{ $ringkasan['terakhir']->nomor_tagihan }}</div>
                    <div class="mt-1 flex items-center gap-2 text-muted">
                        <span>{{ $ringkasan['terakhir']->periode->translatedFormat('F Y') }}</span>
                        <span class="rounded-full px-2 py-0.5 text-xs {{ $ringkasan['terakhir']->status->badgeClass() }}">{{ $ringkasan['terakhir']->status->label() }}</span>
                    </div>
                    @if (in_array($ringkasan['terakhir']->status, [\App\Enums\TagihanStatus::Unpaid, \App\Enums\TagihanStatus::Overdue], true))
                        <a href="{{ $ringkasan['terakhir']->publicUrl() }}" target="_blank"
                            class="mt-3 inline-flex h-10 w-full items-center justify-center gap-2 rounded-[10px] bg-brand px-4 text-sm font-semibold text-white hover:bg-brand-dark">
                            <i data-lucide="credit-card"></i> Bayar Sekarang
                        </a>
                    @endif
                </div>
            @else
                <p class="mt-4 text-sm text-muted">Belum ada tagihan.</p>
            @endif
        </div>
    </div>
</x-layouts.customer>
