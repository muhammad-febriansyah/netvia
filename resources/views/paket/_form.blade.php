@php($paket = $paket ?? null)

<div class="space-y-5">
    <x-form.input
        name="nama"
        label="Nama Paket"
        :value="$paket?->nama"
        :required="true"
        placeholder="cth: Home 20 Mbps" />

    <x-form.input
        name="kecepatan_mbps"
        type="number"
        label="Kecepatan (Mbps)"
        :value="$paket?->kecepatan_mbps"
        min="1"
        placeholder="cth: 20" />

    <x-form.rupiah
        name="harga"
        label="Harga Bulanan"
        :value="$paket?->harga"
        :required="true"
        placeholder="0" />

    <x-form.textarea
        name="deskripsi"
        label="Deskripsi"
        :value="$paket?->deskripsi"
        rows="3"
        placeholder="Keterangan singkat paket (opsional)" />

    <div class="form-field">
        <label class="flex cursor-pointer items-center gap-3">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" class="size-[18px] accent-brand"
                @checked(old('is_active', $paket?->is_active ?? true))>
            <span class="text-[13px] font-medium text-ink">Paket aktif (bisa dipilih pelanggan)</span>
        </label>
    </div>
</div>

<div class="mt-7 flex items-center gap-3">
    <button type="submit" class="btn-submit inline-flex h-11 items-center justify-center gap-2 rounded-[10px] bg-brand px-5 text-sm font-semibold text-white transition hover:bg-brand-dark disabled:opacity-60">
        <i data-lucide="save"></i> Simpan
    </button>
    <a href="{{ route('paket.index') }}" class="inline-flex h-11 items-center rounded-[10px] border border-line px-5 text-sm font-medium text-muted hover:bg-canvas">
        Batal
    </a>
</div>
