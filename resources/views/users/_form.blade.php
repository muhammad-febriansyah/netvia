@php($isEdit = isset($user))

<div class="grid gap-4 sm:grid-cols-2">
    <x-form.input name="name" label="Nama" required :value="$user->name ?? ''" placeholder="cth: Rangga Admin" />
    <x-form.input name="email" label="Email" type="email" required :value="$user->email ?? ''" placeholder="cth: admin@netvia.id" />
</div>

<div class="grid gap-4 sm:grid-cols-2">
    <x-form.input name="password" label="{{ $isEdit ? 'Kata Sandi Baru' : 'Kata Sandi' }}" type="password"
        :required="! $isEdit" placeholder="minimal 8 karakter"
        :help="$isEdit ? 'Kosongkan jika tidak ingin mengubah.' : null" />

    <div class="form-field">
        <label for="role" class="mb-[7px] block text-[13px] font-medium text-ink">
            Peran <span class="text-red-500">*</span>
        </label>
        <select id="role" name="role" class="form-select select2 @error('role') input-invalid @enderror" data-placeholder="Pilih peran">
            <option value=""></option>
            @foreach ($roles as $value => $label)
                <option value="{{ $value }}" @selected(old('role', $currentRole ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('role')<p class="field-error mt-1.5 text-[12.5px] text-red-600">{{ $message }}</p>@enderror
    </div>
</div>

<label class="flex cursor-pointer items-center gap-3">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" class="peer sr-only" @checked(old('is_active', $user->is_active ?? true))>
    <span class="relative h-6 w-11 flex-none rounded-full bg-slate-300 transition peer-checked:bg-brand
        after:absolute after:left-0.5 after:top-0.5 after:size-5 after:rounded-full after:bg-white after:transition peer-checked:after:translate-x-5"></span>
    <span class="text-sm font-medium text-ink">Akun aktif</span>
</label>

<div class="flex items-center gap-3 border-t border-line pt-5">
    <button type="submit"
        class="inline-flex h-11 items-center gap-2 rounded-[10px] bg-brand px-5 text-sm font-semibold text-white hover:bg-brand-dark">
        <i data-lucide="save"></i> Simpan
    </button>
    <a href="{{ route('user.index') }}" class="inline-flex h-11 items-center rounded-[10px] px-4 text-sm font-medium text-muted hover:text-ink">Batal</a>
</div>
