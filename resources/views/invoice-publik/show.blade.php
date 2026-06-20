<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tagihan {{ $tagihan->nomor_tagihan }} — {{ $perusahaan['nama'] ?? config('app.name', 'Netvia') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-canvas font-sans text-ink antialiased">
    <div class="mx-auto flex min-h-screen w-full max-w-md flex-col px-4 py-8">
        {{-- Header perusahaan --}}
        <div class="mb-6 text-center">
            <div class="text-xl font-semibold tracking-tight">{{ $perusahaan['nama'] ?? 'Netvia' }}</div>
            @if (! empty($perusahaan['alamat']))
                <p class="mt-1 text-sm text-muted">{{ $perusahaan['alamat'] }}</p>
            @endif
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-line">
            {{-- Status --}}
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs font-medium uppercase tracking-wide text-muted">No. Tagihan</div>
                    <div class="font-semibold">{{ $tagihan->nomor_tagihan }}</div>
                </div>
                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $tagihan->status->badgeClass() }}">
                    {{ $tagihan->status->label() }}
                </span>
            </div>

            <dl class="mt-5 space-y-3 border-t border-line pt-5 text-sm">
                <div class="flex justify-between">
                    <dt class="text-muted">Pelanggan</dt>
                    <dd class="font-medium">{{ $tagihan->pelanggan->nama }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-muted">Paket</dt>
                    <dd class="font-medium">{{ $tagihan->paket_nama }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-muted">Periode</dt>
                    <dd class="font-medium">{{ $tagihan->periode->translatedFormat('F Y') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-muted">Jatuh Tempo</dt>
                    <dd class="font-medium">{{ $tagihan->tanggal_jatuh_tempo->translatedFormat('d M Y') }}</dd>
                </div>
            </dl>

            <div class="mt-5 flex items-center justify-between border-t border-line pt-5">
                <span class="text-sm text-muted">Total Tagihan</span>
                <span class="text-2xl font-semibold text-brand">{{ rupiah($tagihan->jumlah) }}</span>
            </div>
        </div>

        @if ($tagihan->status === \App\Enums\TagihanStatus::Paid)
            <div class="mt-6 rounded-2xl bg-green-50 p-6 text-center ring-1 ring-green-100">
                <div class="text-lg font-semibold text-green-700">Tagihan Sudah Lunas</div>
                <p class="mt-1 text-sm text-green-600">Terima kasih atas pembayaran Anda.</p>
                <a href="{{ route('publik.struk', $tagihan->public_token) }}"
                    class="mt-4 inline-flex items-center justify-center gap-2 rounded-xl bg-green-600 px-5 py-3 text-sm font-semibold text-white">
                    Unduh Struk (PDF)
                </a>
            </div>
        @else
            @if ($pembayaran && $pembayaran->payment_url)
                <div class="mt-6 rounded-2xl bg-white p-6 text-center shadow-sm ring-1 ring-line">
                    <div class="font-semibold">Bayar via QRIS</div>
                    <p class="mt-1 text-sm text-muted">Scan atau buka halaman pembayaran untuk menyelesaikan transaksi.</p>
                    <a href="{{ $pembayaran->payment_url }}" target="_blank" rel="noopener"
                        class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-brand px-5 py-3 text-sm font-semibold text-white">
                        Bayar Sekarang
                    </a>
                    <a href="{{ route('publik.invoice', $tagihan->public_token) }}"
                        class="mt-3 inline-block text-xs font-medium text-muted underline">
                        Sudah bayar? Muat ulang halaman
                    </a>
                </div>
            @endif

            @if (! empty($perusahaan['bank_no_rekening']))
                <div class="mt-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-line">
                    <div class="text-sm font-semibold">Transfer Manual</div>
                    <dl class="mt-3 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-muted">Bank</dt>
                            <dd class="font-medium">{{ $perusahaan['bank_nama'] }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted">No. Rekening</dt>
                            <dd class="font-medium">{{ $perusahaan['bank_no_rekening'] }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted">Atas Nama</dt>
                            <dd class="font-medium">{{ $perusahaan['bank_atas_nama'] }}</dd>
                        </div>
                    </dl>
                </div>
            @endif
        @endif

        <div class="mt-auto pt-8 text-center text-xs text-muted">
            @if (! empty($perusahaan['footer_invoice']))
                <p>{{ $perusahaan['footer_invoice'] }}</p>
            @endif
            @if (! empty($perusahaan['no_wa_cs']))
                <p class="mt-1">Bantuan: {{ $perusahaan['no_wa_cs'] }}</p>
            @endif
        </div>
    </div>
</body>
</html>
