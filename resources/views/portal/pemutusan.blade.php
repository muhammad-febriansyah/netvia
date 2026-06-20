<x-layouts.customer title="Pemutusan Langganan">
    <x-slot:header>Pemutusan Langganan</x-slot:header>
    <x-slot:subheader>Ajukan berhenti berlangganan beserta alasan &amp; foto.</x-slot:subheader>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
        {{-- Form --}}
        <div class="rounded-2xl border border-line bg-white p-6">
            <h2 class="mb-4 text-sm font-semibold text-ink">Ajukan Pemutusan</h2>

            @if (! $canRequest)
                <div class="rounded-xl bg-canvas p-4 text-sm text-muted">
                    Pengajuan pemutusan hanya tersedia untuk langganan berstatus <span class="font-medium text-ink">aktif</span>.
                </div>
            @else
                <form method="POST" action="{{ route('portal.pemutusanStore') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div class="form-field">
                        <label for="alasan" class="mb-[7px] block text-[13px] font-medium text-ink">Alasan <span class="text-red-500">*</span></label>
                        <textarea id="alasan" name="alasan" rows="4" class="form-textarea @error('alasan') input-invalid @enderror"
                            placeholder="cth: pindah rumah, ganti provider, dll">{{ old('alasan') }}</textarea>
                        @error('alasan')<p class="field-error mt-1.5 text-[12.5px] text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-field">
                        <label for="foto" class="mb-[7px] block text-[13px] font-medium text-ink">Foto Bukti <span class="text-red-500">*</span></label>
                        <input type="file" id="foto" name="foto" accept="image/*"
                            class="block w-full text-sm text-muted file:mr-3 file:rounded-lg file:border-0 file:bg-brand-soft file:px-3 file:py-2 file:text-sm file:font-medium file:text-brand">
                        <p class="mt-1.5 text-[12px] text-muted">JPG/PNG, maks 2 MB. Mis. foto perangkat / kondisi.</p>
                        @error('foto')<p class="field-error mt-1.5 text-[12.5px] text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="inline-flex h-10 items-center gap-2 rounded-[10px] bg-red-600 px-5 text-sm font-semibold text-white hover:bg-red-700">
                        <i data-lucide="send"></i> Kirim Pengajuan
                    </button>
                </form>
            @endif
        </div>

        {{-- Riwayat pengajuan --}}
        <div class="rounded-2xl border border-line bg-white p-6">
            <h2 class="mb-4 text-sm font-semibold text-ink">Riwayat Pengajuan</h2>
            <div class="space-y-3">
                @forelse ($pengajuan as $p)
                    <div class="rounded-xl border border-line p-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-muted">{{ $p->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y H:i') }}</span>
                            <span class="rounded-full px-2 py-1 text-xs font-medium {{ $p->status->badgeClass() }}">{{ $p->status->label() }}</span>
                        </div>
                        <p class="mt-2 text-sm text-ink">{{ $p->alasan }}</p>
                        @if ($p->catatan_admin)
                            <p class="mt-2 rounded-lg bg-canvas p-2 text-[12.5px] text-muted"><span class="font-medium text-ink">Catatan admin:</span> {{ $p->catatan_admin }}</p>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-muted">Belum ada pengajuan.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.customer>
