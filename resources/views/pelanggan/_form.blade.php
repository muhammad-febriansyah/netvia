@php
    $pelanggan = $pelanggan ?? null;
    $paketOptions = $pakets->pluck('nama', 'id');
    $statusOptions = collect(\App\Enums\PelangganStatus::cases())
        ->mapWithKeys(fn ($s) => [$s->value => $s->label()]);
    $kode = $pelanggan?->kode_pelanggan ?? ($kodePelanggan ?? '');
@endphp

<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <div class="form-field">
        <label class="mb-[7px] block text-[13px] font-medium text-ink">Kode Pelanggan</label>
        <input type="text" value="{{ $kode }}" readonly
            class="form-input cursor-not-allowed bg-canvas text-muted">
    </div>

    <x-form.input name="nama" label="Nama Pelanggan" :value="$pelanggan?->nama" :required="true"
        placeholder="cth: Budi Santoso" />

    <x-form.input name="no_wa" label="No. WhatsApp" :value="$pelanggan?->no_wa" :required="true"
        placeholder="cth: 081234567890" />

    <x-form.input name="email" type="email" label="Email" :value="$pelanggan?->email"
        placeholder="cth: budi@email.com" />

    <x-form.select name="paket_id" label="Paket" :options="$paketOptions"
        :selected="$pelanggan?->paket_id" :required="true" placeholder="Pilih paket" />

    <x-form.input name="tanggal_aktivasi" type="date" label="Tanggal Aktivasi"
        :value="$pelanggan?->tanggal_aktivasi?->format('Y-m-d')" :required="true" />

    <x-form.input name="tgl_jatuh_tempo" type="number" label="Tanggal Jatuh Tempo" min="1" max="28"
        :value="$pelanggan?->tgl_jatuh_tempo" :required="true"
        placeholder="cth: 5" help="Tanggal dalam bulan (1–28)." />

    <x-form.select name="status" label="Status" :options="$statusOptions"
        :selected="$pelanggan?->status?->value ?? 'aktif'" :required="true" placeholder="Pilih status" />

    <div class="md:col-span-2">
        <x-form.textarea name="alamat" label="Alamat" :value="$pelanggan?->alamat" rows="2"
            placeholder="Alamat pemasangan" />
    </div>

    <div class="md:col-span-2">
        <x-form.textarea name="catatan" label="Catatan" :value="$pelanggan?->catatan" rows="2"
            placeholder="Catatan internal (opsional)" />
    </div>
</div>

<div class="mt-7 flex items-center gap-3">
    <button type="submit" class="btn-submit inline-flex h-11 items-center justify-center gap-2 rounded-[10px] bg-brand px-5 text-sm font-semibold text-white transition hover:bg-brand-dark disabled:opacity-60">
        <i data-lucide="save"></i> Simpan
    </button>
    <a href="{{ route('pelanggan.index') }}" class="inline-flex h-11 items-center rounded-[10px] border border-line px-5 text-sm font-medium text-muted hover:bg-canvas">
        Batal
    </a>
</div>
