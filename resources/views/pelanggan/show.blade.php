<x-layouts.app title="Detail Pelanggan">
    <x-slot:header>{{ $pelanggan->nama }}</x-slot:header>
    <x-slot:subheader>{{ $pelanggan->kode_pelanggan }}</x-slot:subheader>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Detail --}}
        <div class="lg:col-span-2 rounded-2xl bg-white p-7 shadow-sm ring-1 ring-line">
            <div class="mb-5 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-ink">Informasi Pelanggan</h2>
                @can('pelanggan.update')
                    <a href="{{ route('pelanggan.edit', $pelanggan) }}"
                        class="inline-flex h-9 items-center gap-1.5 rounded-[10px] border border-line px-3.5 text-[13px] font-medium text-blue-600 hover:bg-canvas">
                        <i data-lucide="pencil"></i> Edit
                    </a>
                @endcan
            </div>

            <dl class="grid grid-cols-1 gap-x-6 gap-y-4 text-sm sm:grid-cols-2">
                <div>
                    <dt class="text-muted">No. WhatsApp</dt>
                    <dd class="mt-0.5 font-medium">{{ $pelanggan->no_wa }}</dd>
                </div>
                <div>
                    <dt class="text-muted">Email</dt>
                    <dd class="mt-0.5 font-medium">{{ $pelanggan->email ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-muted">Paket</dt>
                    <dd class="mt-0.5 font-medium">{{ $pelanggan->paket?->nama ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-muted">Status</dt>
                    <dd class="mt-0.5">
                        <span class="rounded-full px-2 py-1 text-xs font-medium
                            {{ match ($pelanggan->status) {
                                \App\Enums\PelangganStatus::Aktif => 'bg-green-100 text-green-700',
                                \App\Enums\PelangganStatus::Isolir => 'bg-red-100 text-red-700',
                                \App\Enums\PelangganStatus::Nonaktif => 'bg-gray-100 text-gray-600',
                            } }}">
                            {{ $pelanggan->status->label() }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-muted">Tanggal Aktivasi</dt>
                    <dd class="mt-0.5 font-medium">{{ $pelanggan->tanggal_aktivasi?->translatedFormat('d M Y') ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-muted">Jatuh Tempo</dt>
                    <dd class="mt-0.5 font-medium">Tanggal {{ $pelanggan->tgl_jatuh_tempo }} tiap bulan</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-muted">Alamat</dt>
                    <dd class="mt-0.5 font-medium">{{ $pelanggan->alamat ?: '-' }}</dd>
                </div>
                @if ($pelanggan->catatan)
                    <div class="sm:col-span-2">
                        <dt class="text-muted">Catatan</dt>
                        <dd class="mt-0.5 font-medium">{{ $pelanggan->catatan }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        {{-- Ringkasan tagihan --}}
        <div class="rounded-2xl bg-white p-7 shadow-sm ring-1 ring-line">
            <h2 class="mb-5 text-sm font-semibold text-ink">Ringkasan Tagihan</h2>

            <div class="rounded-xl bg-canvas p-4">
                <div class="text-xs text-muted">Total Tunggakan</div>
                <div class="mt-1 text-2xl font-semibold {{ $ringkasan['outstanding'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                    {{ rupiah($ringkasan['outstanding']) }}
                </div>
            </div>

            <div class="mt-4 text-sm">
                <div class="text-xs text-muted">Tagihan Terakhir</div>
                @if ($ringkasan['terakhir'])
                    <div class="mt-1 font-medium">{{ $ringkasan['terakhir']->nomor_tagihan }}</div>
                    <div class="text-muted">
                        {{ $ringkasan['terakhir']->periode->translatedFormat('F Y') }} ·
                        <span class="font-medium {{ $ringkasan['terakhir']->status->badgeClass() }} rounded-full px-2 py-0.5 text-xs">
                            {{ $ringkasan['terakhir']->status->label() }}
                        </span>
                    </div>
                @else
                    <div class="mt-1 text-muted">Belum ada tagihan.</div>
                @endif
            </div>

            @if (\Illuminate\Support\Facades\Route::has('tagihan.generateManual'))
                @can('tagihan.generate')
                    <form method="POST" action="{{ route('tagihan.generateManual') }}" class="mt-5">
                        @csrf
                        <input type="hidden" name="pelanggan_id" value="{{ $pelanggan->id }}">
                        <button type="submit"
                            class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-[10px] bg-brand px-4 text-sm font-semibold text-white hover:bg-brand-dark">
                            <i data-lucide="file-plus"></i> Buat Tagihan Manual
                        </button>
                    </form>
                @endcan
            @endif
        </div>
    </div>

    <div class="mt-6">
        <a href="{{ route('pelanggan.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-muted hover:text-ink">
            <i data-lucide="arrow-left"></i> Kembali ke daftar
        </a>
    </div>
</x-layouts.app>
