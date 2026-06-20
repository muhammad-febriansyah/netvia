<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} — {{ config('app.name', 'Netvia') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    @if (session('success') || session('error'))
        <script>
            window.__flash = @json([
                'type' => session('error') ? 'error' : 'success',
                'message' => session('error') ?? session('success'),
            ]);
        </script>
    @endif
</head>
<body class="h-full bg-canvas font-sans text-ink antialiased">
@php
    $nav = [
        ['route' => 'dashboard', 'label' => 'Dashboard', 'active' => 'dashboard', 'can' => null,
            'icon' => '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/>'],
        ['route' => 'pelanggan.index', 'label' => 'Pelanggan', 'active' => 'pelanggan.*', 'can' => 'pelanggan.view',
            'icon' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>'],
        ['route' => 'tagihan.index', 'label' => 'Tagihan', 'active' => 'tagihan.*', 'can' => 'tagihan.view',
            'icon' => '<path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 17.5v-11"/>'],
        ['route' => 'paket.index', 'label' => 'Paket', 'active' => 'paket.*', 'can' => 'paket.view',
            'icon' => '<path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>'],
        ['route' => 'notifikasi.index', 'label' => 'Notifikasi', 'active' => 'notifikasi.*', 'can' => 'notifikasi.view',
            'icon' => '<path d="M10.268 21a2 2 0 0 0 3.464 0"/><path d="M3.262 15.326A1 1 0 0 0 4 17h16a1 1 0 0 0 .74-1.673C19.41 13.956 18 12.499 18 8A6 6 0 0 0 6 8c0 4.499-1.411 5.956-2.738 7.326"/>'],
        ['route' => 'laporan.pendapatan', 'label' => 'Laporan', 'active' => 'laporan.*', 'can' => 'laporan.view',
            'icon' => '<path d="M3 3v16a2 2 0 0 0 2 2h16"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/>'],
        ['route' => 'user.index', 'label' => 'Pengguna', 'active' => 'user.*', 'can' => 'user.view',
            'icon' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>'],
        ['route' => 'pengaturan.index', 'label' => 'Pengaturan', 'active' => 'pengaturan.*', 'can' => 'pengaturan.view',
            'icon' => '<path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/>'],
    ];
    $user = auth()->user();
    $initials = collect(explode(' ', $user?->name ?? 'U'))->take(2)->map(fn ($w) => mb_substr($w, 0, 1))->implode('');
    $roleLabel = $user?->getRoleNames()->map(fn ($r) => str($r)->headline())->implode(', ') ?: 'Pengguna';
@endphp

<div class="flex min-h-full">
    {{-- SIDEBAR --}}
    <aside class="sticky top-0 flex h-screen w-60 min-w-60 flex-none flex-col overflow-hidden border-r border-line bg-white px-3.5 py-5">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-2 pb-5.5 pt-1.5">
            <x-brand-logo :size="34" />
            <span class="text-xl font-semibold tracking-tight text-ink">{{ config('app.name', 'Netvia') }}</span>
        </a>

        <div class="px-2.5 pb-1.5 pt-2 text-[11px] font-semibold tracking-wide text-muted/80">MENU</div>
        <nav class="flex flex-col gap-1">
            @foreach ($nav as $item)
                @continue($item['can'] && ! $user?->can($item['can']))
                @php
                    $exists = \Illuminate\Support\Facades\Route::has($item['route']);
                    $isActive = request()->routeIs($item['active']);
                @endphp
                <a href="{{ $exists ? route($item['route']) : '#' }}"
                    class="relative flex items-center gap-3 rounded-[10px] px-3.5 py-2.5 text-sm font-medium transition
                        {{ $isActive ? 'bg-brand-soft text-brand' : 'text-slate-500 hover:bg-canvas hover:text-ink' }}">
                    @if ($isActive)
                        <span class="absolute inset-y-2 left-0 w-[3px] rounded-full bg-brand"></span>
                    @endif
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $item['icon'] !!}</svg>
                    <span class="whitespace-nowrap">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>

        <div class="mt-auto rounded-2xl bg-brand p-4 text-white">
            <div class="flex items-center gap-2.5">
                <span class="flex size-9 flex-none items-center justify-center rounded-[9px] bg-white/20">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13.832 16.568a1 1 0 0 0 1.213-.303l.355-.465A2 2 0 0 1 17 15h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2A18 18 0 0 1 2 4a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-.8 1.6l-.468.351a1 1 0 0 0-.292 1.233 14 14 0 0 0 6.392 6.391z"/></svg>
                </span>
                <div>
                    <div class="text-[13px] font-semibold">Butuh bantuan?</div>
                    <div class="text-[11.5px] font-normal text-white/80">Hubungi tim kami</div>
                </div>
            </div>
        </div>
    </aside>

    {{-- MAIN --}}
    <div class="flex min-w-0 flex-1 flex-col">
        {{-- TOPBAR --}}
        <header class="sticky top-0 z-20 flex h-[68px] flex-none items-center gap-4 border-b border-line bg-white px-7">
            <div class="relative hidden max-w-md flex-1 sm:block">
                <span class="absolute left-3.5 top-1/2 flex -translate-y-1/2 text-slate-400">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                </span>
                <input type="search" placeholder="Cari pelanggan, tagihan, no. WA…"
                    class="h-[42px] w-full rounded-[10px] border border-line bg-canvas pl-10 pr-3.5 text-[13.5px] outline-none focus:border-brand focus:bg-white focus:ring-[3px] focus:ring-brand-soft">
            </div>

            <div class="ml-auto flex items-center gap-2">
                <button type="button" class="flex size-[42px] items-center justify-center rounded-[10px] bg-canvas text-slate-600 hover:bg-brand-soft hover:text-brand">
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                </button>
                <div class="mx-1 h-7 w-px bg-line"></div>
                <div class="flex items-center gap-2.5 rounded-[10px] py-1 pl-1 pr-2">
                    <span class="flex size-[34px] items-center justify-center rounded-[9px] bg-brand text-[13px] font-semibold text-white">{{ strtoupper($initials) }}</span>
                    <span class="hidden text-left leading-tight sm:block">
                        <span class="block text-[13px] font-semibold text-ink">{{ $user?->name }}</span>
                        <span class="block text-[11px] font-normal text-slate-400">{{ $roleLabel }}</span>
                    </span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Keluar" class="flex size-[42px] items-center justify-center rounded-[10px] bg-canvas text-slate-600 hover:bg-red-100 hover:text-red-700">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/></svg>
                    </button>
                </form>
            </div>
        </header>

        <main class="min-w-0 flex-1 p-7">
            @if (isset($header))
                <div class="mb-6">
                    <h1 class="text-[26px] font-semibold tracking-tight text-ink">{{ $header }}</h1>
                    @isset($subheader)
                        <p class="mt-1.5 text-sm font-normal text-muted">{{ $subheader }}</p>
                    @endisset
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>
