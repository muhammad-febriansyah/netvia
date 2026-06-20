<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Masuk' }} — {{ $site['nama_perusahaan'] }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="h-full bg-canvas font-sans text-ink antialiased">
    <div class="flex min-h-dvh">
        {{-- Brand panel --}}
        <div class="relative hidden flex-[1.05] flex-col justify-between overflow-hidden bg-brand p-14 text-white lg:flex">
            {{-- Decorative background --}}
            <div aria-hidden="true" class="pointer-events-none absolute inset-0">
                <div class="absolute inset-0 opacity-[0.07]"
                    style="background-image:linear-gradient(#fff 1px,transparent 1px),linear-gradient(90deg,#fff 1px,transparent 1px);background-size:32px 32px;"></div>
                <div class="absolute -right-24 -top-24 size-96 rounded-full bg-white/10 blur-2xl"></div>
                <div class="absolute -bottom-28 -left-16 size-80 rounded-full bg-brand-cyan/20 blur-3xl"></div>
            </div>

            <div class="relative flex items-center gap-3">
                @if (! empty($site['logo']))
                    <span class="inline-flex items-center rounded-xl bg-white/95 p-2.5 shadow-sm">
                        <img src="{{ Storage::url($site['logo']) }}" alt="{{ $site['nama_perusahaan'] }}"
                            class="max-h-10 w-auto max-w-[170px] object-contain">
                    </span>
                @else
                    <x-brand-logo :size="42" class="[&_circle]:!fill-white/90 [&_path]:!stroke-white" />
                @endif
            </div>

            <div class="relative max-w-md">
                <div class="text-4xl font-semibold leading-tight tracking-tight">Tagihan &amp; reminder langganan, otomatis.</div>
                <p class="mt-4 text-[15px] font-normal leading-relaxed text-white/85">
                    Kelola pelanggan, terbitkan tagihan, dan kirim pengingat WhatsApp untuk jaringan internet RT-RW Anda — semua dalam satu dasbor.
                </p>

                <ul class="mt-8 space-y-3.5 text-[14px] font-medium text-white/90">
                    @foreach (['Tagihan bulanan otomatis per pelanggan', 'Reminder WhatsApp & Email terjadwal', 'Pembayaran QRIS & konfirmasi manual'] as $fitur)
                        <li class="flex items-center gap-3">
                            <span class="flex size-6 flex-none items-center justify-center rounded-full bg-white/15">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                            </span>
                            {{ $fitur }}
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="relative flex items-center gap-2 text-[13px] font-medium text-white/75">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/></svg>
                <span>Data terenkripsi · Akses berbasis peran</span>
            </div>
        </div>

        {{-- Form panel --}}
        <div class="relative flex flex-1 items-center justify-center p-6 sm:p-10">
            <div aria-hidden="true" class="pointer-events-none absolute inset-0 overflow-hidden lg:hidden">
                <div class="absolute -right-20 -top-20 size-72 rounded-full bg-brand-soft blur-3xl"></div>
            </div>

            <div class="relative w-full max-w-[400px]">
                <div class="rounded-2xl border border-line bg-white p-8 shadow-[0_8px_30px_rgba(16,24,64,.06)] sm:p-10">
                    {{ $slot }}
                </div>

                <p class="mt-6 text-center text-xs font-normal text-muted">
                    © {{ date('Y') }} {{ config('app.name', 'Netvia') }} · Sistem Manajemen Tagihan
                </p>
            </div>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
