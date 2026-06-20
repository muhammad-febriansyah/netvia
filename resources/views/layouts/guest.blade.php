<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Masuk' }} — {{ config('app.name', 'Netvia') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-gray-50 font-sans text-gray-900 antialiased">
    <div class="flex min-h-full flex-col justify-center px-6 py-12">
        <div class="mx-auto w-full max-w-sm">
            <div class="mb-8 text-center">
                <h1 class="text-2xl font-bold text-indigo-600">{{ config('app.name', 'Netvia') }}</h1>
                <p class="mt-1 text-sm text-gray-500">Manajemen Langganan &amp; Tagihan Internet</p>
            </div>

            <div class="rounded-xl bg-white p-8 shadow-sm ring-1 ring-gray-200">
                {{ $slot }}
            </div>
        </div>
    </div>
</body>
</html>
