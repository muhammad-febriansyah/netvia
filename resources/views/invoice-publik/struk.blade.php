<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { color: #041933; font-size: 12px; margin: 0; padding: 32px; }
        .header { text-align: center; border-bottom: 2px solid #2547f9; padding-bottom: 12px; margin-bottom: 20px; }
        .company { font-size: 18px; font-weight: bold; }
        .muted { color: #64748b; }
        .paid-stamp { display: inline-block; border: 2px solid #16a34a; color: #16a34a; font-weight: bold;
            padding: 4px 14px; border-radius: 6px; letter-spacing: 1px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        td { padding: 6px 0; vertical-align: top; }
        td.label { color: #64748b; width: 45%; }
        td.value { text-align: right; font-weight: 600; }
        .total-row td { border-top: 1px solid #edf0f6; padding-top: 12px; font-size: 16px; }
        .total-row .value { color: #2547f9; }
        .footer { margin-top: 28px; text-align: center; color: #64748b; font-size: 11px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company">{{ $perusahaan['nama'] ?? 'Netvia' }}</div>
        @if (! empty($perusahaan['alamat']))
            <div class="muted">{{ $perusahaan['alamat'] }}</div>
        @endif
        <div style="margin-top: 10px;"><span class="paid-stamp">LUNAS</span></div>
    </div>

    <table>
        <tr><td class="label">No. Tagihan</td><td class="value">{{ $tagihan->nomor_tagihan }}</td></tr>
        <tr><td class="label">Pelanggan</td><td class="value">{{ $tagihan->pelanggan->nama }}</td></tr>
        <tr><td class="label">Paket</td><td class="value">{{ $tagihan->paket_nama }}</td></tr>
        <tr><td class="label">Periode</td><td class="value">{{ $tagihan->periode->translatedFormat('F Y') }}</td></tr>
        @if ($pembayaran)
            <tr><td class="label">Metode Bayar</td><td class="value">{{ $pembayaran->metode->label() }}</td></tr>
            <tr><td class="label">Waktu Bayar</td><td class="value">{{ optional($pembayaran->dibayar_at)->translatedFormat('d M Y H:i') }}</td></tr>
            @if ($pembayaran->pakasir_reference)
                <tr><td class="label">Referensi</td><td class="value">{{ $pembayaran->pakasir_reference }}</td></tr>
            @endif
        @endif
        <tr class="total-row"><td class="label">Total Dibayar</td><td class="value">{{ rupiah($tagihan->jumlah) }}</td></tr>
    </table>

    <div class="footer">
        {{ $perusahaan['footer_invoice'] ?? 'Terima kasih telah berlangganan.' }}
    </div>
</body>
</html>
