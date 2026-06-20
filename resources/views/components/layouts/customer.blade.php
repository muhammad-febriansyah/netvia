<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Portal' }} — {{ $site['nama_perusahaan'] ?? config('app.name', 'Netvia') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    @if (session('success') || session('error'))
        <script>
            window.__flash = @json(['type' => session('error') ? 'error' : 'success', 'message' => session('error') ?? session('success')]);
        </script>
    @endif
</head>
<body class="h-full bg-canvas font-sans text-ink antialiased">
@php
    $nav = [
        ['route' => 'portal.dashboard', 'label' => 'Dashboard', 'active' => 'portal.dashboard',
            'icon' => '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/>'],
        ['route' => 'portal.langganan', 'label' => 'Langganan', 'active' => 'portal.langganan',
            'icon' => '<path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>'],
        ['route' => 'portal.riwayat', 'label' => 'Riwayat Transaksi', 'active' => 'portal.riwayat',
            'icon' => '<path d="M3 3v18h18"/><path d="M18.7 8l-5.1 5.2-2.8-2.7L7 14.3"/>'],
        ['route' => 'portal.pemutusan', 'label' => 'Pemutusan', 'active' => 'portal.pemutusan',
            'icon' => '<circle cx="12" cy="12" r="10"/><path d="m4.9 4.9 14.2 14.2"/>'],
    ];
    $user = auth()->user();
    $initials = collect(explode(' ', $user?->name ?? 'U'))->take(2)->map(fn ($w) => mb_substr($w, 0, 1))->implode('');
@endphp

<div class="flex min-h-full">
    <aside class="sticky top-0 flex h-screen w-60 min-w-60 flex-none flex-col overflow-hidden border-r border-line bg-white px-3.5 py-5">
        <div class="flex items-center gap-2.5 px-2 pb-5.5 pt-1.5">
            <x-brand-logo :size="34" />
            <span class="text-xl font-semibold tracking-tight text-ink">{{ $site['nama_perusahaan'] ?? config('app.name', 'Netvia') }}</span>
        </div>

        <div class="px-2.5 pb-1.5 pt-2 text-[11px] font-semibold tracking-wide text-muted/80">MENU</div>
        <nav class="flex flex-col gap-1">
            @foreach ($nav as $item)
                @php($isActive = request()->routeIs($item['active']))
                <a href="{{ route($item['route']) }}"
                    class="relative flex items-center gap-3 rounded-[10px] px-3.5 py-2.5 text-sm font-medium transition
                        {{ $isActive ? 'bg-brand-soft text-brand' : 'text-slate-500 hover:bg-canvas hover:text-ink' }}">
                    @if ($isActive)<span class="absolute inset-y-2 left-0 w-[3px] rounded-full bg-brand"></span>@endif
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $item['icon'] !!}</svg>
                    <span class="whitespace-nowrap">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>
    </aside>

    <div class="flex min-w-0 flex-1 flex-col">
        <header class="sticky top-0 z-20 flex h-[68px] flex-none items-center gap-4 border-b border-line bg-white px-7">
            <div class="ml-auto flex items-center gap-3">
                <div class="flex items-center gap-2.5">
                    <span class="flex size-[34px] items-center justify-center rounded-[9px] bg-brand text-[13px] font-semibold text-white">{{ strtoupper($initials) }}</span>
                    <span class="hidden text-left leading-tight sm:block">
                        <span class="block text-[13px] font-semibold text-ink">{{ $user?->name }}</span>
                        <span class="block text-[11px] font-normal text-slate-400">Pelanggan</span>
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
                    @isset($subheader)<p class="mt-1.5 text-sm font-normal text-muted">{{ $subheader }}</p>@endisset
                </div>
            @endif
            {{ $slot }}
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>
