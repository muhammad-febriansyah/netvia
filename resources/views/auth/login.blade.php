<x-layouts.guest title="Masuk">
    <div class="mb-6 flex items-center gap-2.5">
        <span class="flex size-11 items-center justify-center rounded-xl bg-brand-soft">
            <x-brand-logo :size="26" />
        </span>
        <span class="text-[19px] font-semibold tracking-tight text-ink lg:hidden">{{ config('app.name', 'Netvia') }}</span>
    </div>

    <h1 class="text-[24px] font-semibold tracking-tight text-ink">Masuk ke dasbor</h1>
    <p class="mb-7 mt-1.5 text-sm font-normal text-muted">Silakan masuk dengan akun admin Anda.</p>

    @if ($errors->any())
        <div role="alert" aria-live="assertive"
            class="mb-5 flex items-start gap-2.5 rounded-[10px] border border-red-200 bg-red-50 p-3 text-sm text-red-700">
            <svg class="mt-0.5 size-4 flex-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('login.attempt') }}" class="space-y-[18px]" novalidate>
        @csrf

        {{-- Email --}}
        <div>
            <label for="email" class="mb-[7px] block text-[13px] font-medium text-ink">
                Email <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <span class="pointer-events-none absolute left-3.5 top-1/2 flex -translate-y-1/2 text-slate-400">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                </span>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                    autocomplete="username" inputmode="email"
                    @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
                    placeholder="cth: admin@netvia.id"
                    class="h-[46px] w-full rounded-[10px] border bg-white pl-10 pr-3.5 text-base text-ink outline-none transition-colors motion-reduce:transition-none focus:ring-[3px] sm:text-sm
                        @error('email') border-red-400 focus:border-red-500 focus:ring-red-100 @else border-line focus:border-brand focus:ring-brand-soft @enderror">
            </div>
            @error('email')
                <p id="email-error" class="mt-1.5 text-[12.5px] font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password --}}
        <div>
            <label for="password" class="mb-[7px] block text-[13px] font-medium text-ink">
                Kata Sandi <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <span class="pointer-events-none absolute left-3.5 top-1/2 flex -translate-y-1/2 text-slate-400">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </span>
                <input id="password" type="password" name="password" required
                    autocomplete="current-password"
                    @error('password') aria-invalid="true" aria-describedby="password-error" @enderror
                    placeholder="••••••••"
                    class="h-[46px] w-full rounded-[10px] border bg-white pl-10 pr-12 text-base text-ink outline-none transition-colors motion-reduce:transition-none focus:ring-[3px] sm:text-sm
                        @error('password') border-red-400 focus:border-red-500 focus:ring-red-100 @else border-line focus:border-brand focus:ring-brand-soft @enderror">
                <button type="button" id="toggle-password" aria-label="Tampilkan kata sandi" aria-pressed="false"
                    class="absolute right-1 top-1/2 flex size-11 -translate-y-1/2 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-canvas hover:text-ink focus:outline-none focus:ring-2 focus:ring-brand-soft">
                    <svg data-icon="show" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg data-icon="hide" class="hidden" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49"/><path d="M14.084 14.158a3 3 0 0 1-4.242-4.242"/><path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143"/><path d="m2 2 20 20"/></svg>
                </button>
            </div>
            @error('password')
                <p id="password-error" class="mt-1.5 text-[12.5px] font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between">
            <label class="flex cursor-pointer items-center gap-2 text-[13px] font-normal text-muted">
                <input type="checkbox" name="remember" class="size-[15px] accent-brand"> Ingat saya
            </label>
        </div>

        <button type="submit" id="submit-btn"
            class="group flex h-12 w-full items-center justify-center gap-2 rounded-[10px] bg-brand text-[15px] font-semibold text-white shadow-[0_3px_10px_rgba(37,71,249,.16)] transition hover:bg-brand-dark focus:outline-none focus:ring-[3px] focus:ring-brand-soft disabled:cursor-not-allowed disabled:opacity-70 motion-reduce:transition-none">
            <span data-label>Masuk</span>
            <svg data-arrow width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
            <svg data-spinner class="hidden size-[18px] animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
        </button>
    </form>

    @push('scripts')
        <script>
            (() => {
                const pw = document.getElementById('password');
                const toggle = document.getElementById('toggle-password');
                toggle?.addEventListener('click', () => {
                    const show = pw.type === 'password';
                    pw.type = show ? 'text' : 'password';
                    toggle.setAttribute('aria-pressed', String(show));
                    toggle.setAttribute('aria-label', show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
                    toggle.querySelector('[data-icon=show]').classList.toggle('hidden', show);
                    toggle.querySelector('[data-icon=hide]').classList.toggle('hidden', !show);
                });

                const form = document.querySelector('form');
                const btn = document.getElementById('submit-btn');
                form?.addEventListener('submit', () => {
                    btn.disabled = true;
                    btn.querySelector('[data-label]').textContent = 'Memproses…';
                    btn.querySelector('[data-arrow]').classList.add('hidden');
                    btn.querySelector('[data-spinner]').classList.remove('hidden');
                });
            })();
        </script>
    @endpush
</x-layouts.guest>
