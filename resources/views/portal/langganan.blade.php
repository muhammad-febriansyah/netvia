<x-layouts.customer title="Langganan">
    <x-slot:header>Langganan</x-slot:header>
    <x-slot:subheader>Paket internet yang Anda gunakan.</x-slot:subheader>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
        <div class="rounded-2xl border border-line bg-white p-6">
            <h2 class="mb-4 text-sm font-semibold text-ink">Paket Aktif</h2>
            <div class="rounded-xl bg-brand p-5 text-white">
                <div class="text-sm text-white/85">{{ $pelanggan->paket?->nama ?? 'Belum ada paket' }}</div>
                <div class="mt-1 text-3xl font-bold tracking-tight">{{ rupiah($pelanggan->paket?->harga ?? 0) }}<span class="text-base font-medium text-white/80">/bln</span></div>
                @if ($pelanggan->paket?->kecepatan_mbps)
                    <div class="mt-2 text-sm text-white/85">{{ $pelanggan->paket->kecepatan_mbps }} Mbps</div>
                @endif
            </div>
        </div>

        <div class="rounded-2xl border border-line bg-white p-6">
            <h2 class="mb-4 text-sm font-semibold text-ink">Ganti Paket</h2>
            <form method="POST" action="{{ route('portal.langgananUpdate') }}" class="space-y-4">
                @csrf @method('PUT')
                <div class="form-field">
                    <label for="paket_id" class="mb-[7px] block text-[13px] font-medium text-ink">Pilih Paket <span class="text-red-500">*</span></label>
                    <select id="paket_id" name="paket_id" class="form-select select2" data-placeholder="Pilih paket">
                        @foreach ($pakets as $paket)
                            <option value="{{ $paket->id }}" @selected($pelanggan->paket_id == $paket->id)>{{ $paket->nama }} — {{ rupiah($paket->harga) }}/bln</option>
                        @endforeach
                    </select>
                    @error('paket_id')<p class="field-error mt-1.5 text-[12.5px] text-red-600">{{ $message }}</p>@enderror
                </div>
                <p class="text-[12.5px] text-muted">Perubahan paket berlaku pada periode tagihan berikutnya.</p>
                <button type="submit" class="inline-flex h-10 items-center gap-2 rounded-[10px] bg-brand px-5 text-sm font-semibold text-white hover:bg-brand-dark">
                    <i data-lucide="save"></i> Simpan
                </button>
            </form>
        </div>
    </div>
</x-layouts.customer>
