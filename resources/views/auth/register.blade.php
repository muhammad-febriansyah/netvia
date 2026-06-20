<x-layouts.guest title="Daftar">
    <div class="mb-7 flex items-center gap-2.5 lg:hidden">
        @if (! empty($site['logo']))
            <img src="{{ Storage::url($site['logo']) }}" alt="{{ $site['nama_perusahaan'] }}"
                class="max-h-11 w-auto max-w-[170px] object-contain">
        @else
            <x-brand-logo :size="36" />
        @endif
    </div>

    <h1 class="text-[26px] font-semibold tracking-tight text-ink">Daftar langganan</h1>
    <p class="mb-7 mt-2 text-sm font-normal text-muted">Buat akun &amp; pilih paket. Aktivasi oleh admin.</p>

    @if ($errors->any())
        <div class="mb-5 rounded-[10px] border border-red-200 bg-red-50 p-3 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('register.attempt') }}" class="space-y-4">
        @csrf

        <x-form.input name="name" label="Nama Lengkap" required value="{{ old('name') }}" placeholder="cth: Budi Santoso" />
        <x-form.input name="email" label="Email" type="email" required value="{{ old('email') }}" placeholder="cth: budi@email.com" />
        <x-form.input name="no_wa" label="No. WhatsApp" required value="{{ old('no_wa') }}" placeholder="cth: 081234567890" />
        <x-form.textarea name="alamat" label="Alamat Pemasangan" value="{{ old('alamat') }}" placeholder="alamat lengkap pemasangan" />

        <div class="form-field">
            <label for="paket_id" class="mb-[7px] block text-[13px] font-medium text-ink">Paket <span class="text-red-500">*</span></label>
            <select id="paket_id" name="paket_id" class="form-select select2 @error('paket_id') input-invalid @enderror" data-placeholder="Pilih paket">
                <option value=""></option>
                @foreach ($pakets as $paket)
                    <option value="{{ $paket->id }}" @selected(old('paket_id') == $paket->id)>
                        {{ $paket->nama }} — {{ rupiah($paket->harga) }}/bln
                    </option>
                @endforeach
            </select>
            @error('paket_id')<p class="field-error mt-1.5 text-[12.5px] text-red-600">{{ $message }}</p>@enderror
        </div>

        <x-form.input name="tgl_jatuh_tempo" label="Tanggal Jatuh Tempo (1–28)" type="number" required
            value="{{ old('tgl_jatuh_tempo', 5) }}" placeholder="cth: 5" />

        <div class="grid gap-4 sm:grid-cols-2">
            <x-form.input name="password" label="Kata Sandi" type="password" required placeholder="minimal 8 karakter" />
            <x-form.input name="password_confirmation" label="Ulangi Kata Sandi" type="password" required placeholder="ulangi kata sandi" />
        </div>

        <button type="submit"
            class="flex h-12 w-full items-center justify-center gap-2 rounded-[10px] bg-brand text-[15px] font-semibold text-white shadow-[0_3px_10px_rgba(37,71,249,.16)] transition hover:bg-brand-dark">
            Daftar
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
        </button>

        <p class="text-center text-[13px] text-muted">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="font-semibold text-brand hover:underline">Masuk</a>
        </p>
    </form>
</x-layouts.guest>
