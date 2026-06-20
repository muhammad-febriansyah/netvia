# 08 — Pembayaran (Pakasir QRIS)

Pembayaran tagihan via **QRIS Pakasir** (otomatis lunas lewat webhook) dan jalur **manual** (transfer/cash, dikonfirmasi admin).

> **Catatan implementasi:** nama endpoint, field payload, dan metode verifikasi signature harus mengikuti **dokumentasi resmi Pakasir**. Dokumen ini mendefinisikan *requirement & alur*, bukan kontrak API final. Sediakan abstraksi `PakasirService` agar mudah disesuaikan.

## Konfigurasi

Dari `.env` (rahasia) + sebagian di pengaturan:

```
PAKASIR_API_KEY=...
PAKASIR_PROJECT=...        # slug/project id
PAKASIR_BASE_URL=...       # sesuai docs
PAKASIR_CALLBACK_URL=https://app-netvia/.../webhook/pakasir
```

## Alur QRIS (online)

```
1. Admin/pelanggan klik "Bayar QRIS" pada sebuah tagihan unpaid/overdue.
2. BuatPembayaranQrisAction:
   - buat record pembayarans (status=pending, metode=qris_pakasir,
     pakasir_order_id = unik mengacu tagihan).
   - PakasirService.createTransaction(amount=tagihan.jumlah, order_id, ...)
   - simpan qr_string / payment_url / expired_at dari response.
3. Tampilkan QR ke pelanggan (halaman tagihan publik / detail).
4. Pelanggan scan & bayar.
5. Pakasir kirim webhook ke PAKASIR_CALLBACK_URL.
6. PakasirWebhookController:
   - verifikasi keaslian (signature/secret sesuai docs).
   - cari pembayaran by order_id.
   - IDEMPOTEN: jika sudah success, abaikan (return 200).
   - jika status sukses: set pembayaran.status=success, dibayar_at=now,
     simpan raw_callback; set tagihan.status=paid, paid_at=now.
   - dispatch notifikasi jenis `struk_lunas` (WA + Email).
   - return 200.
```

### Aturan webhook (wajib)

- **Verifikasi signature/secret** sebelum memproses. Tolak (4xx) jika tidak valid.
- **Idempotent**: webhook bisa terkirim ganda — proses pelunasan hanya sekali (cek status pembayaran/tagihan sebelum update). Gunakan `DB::transaction()` + locking bila perlu.
- Simpan `raw_callback` untuk audit.
- Selalu balas cepat (200) setelah sukses agar Pakasir tidak retry berlebihan.
- Webhook route **dikecualikan dari CSRF**.

### Expiry & cek status

- Simpan `expired_at`; QRIS kadaluarsa → pembayaran `expired`, pelanggan bisa generate QR baru.
- Sediakan tombol "Cek Status" (opsional) yang memanggil status API Pakasir untuk rekonsiliasi manual bila webhook telat.

## Jalur manual (transfer / cash)

```
1. Admin buka detail tagihan → "Tandai Lunas Manual".
2. KonfirmasiPembayaranManualAction:
   - buat pembayarans (metode=transfer_manual/cash, status=success,
     jumlah_bayar, dikonfirmasi_by=admin, dibayar_at=now,
     bukti_transfer=optional upload).
   - set tagihan.status=paid, paid_at=now.
   - dispatch notifikasi `struk_lunas`.
```

- Upload bukti transfer opsional (simpan path).
- Konfirmasi memakai SweetAlert (input nominal + opsi upload) → toast sukses.

## Halaman tagihan publik

Pelanggan tidak punya akun. Akses tagihan via **URL bertoken**: `GET /tagihan-publik/{public_token}`.

Isi halaman:
- Info perusahaan (dari pengaturan) + nomor tagihan, periode, nominal, jatuh tempo, status.
- Jika belum lunas: tombol/QRIS untuk bayar (generate bila belum ada / sudah expired).
- Jika lunas: tampilkan status "LUNAS" + tombol unduh struk (PDF).
- Mobile-first, font Poppins, light mode.

Link ini yang dikirim di pesan WA/email (`{link_bayar}`).

## Routes

```
POST   /tagihan/{id}/bayar-qris        pembayaran.createQris
POST   /tagihan/{id}/lunas-manual      pembayaran.konfirmasiManual
GET    /tagihan/{id}/cek-status        pembayaran.cekStatus      (opsional)
POST   /webhook/pakasir                pembayaran.webhook        (tanpa CSRF, tanpa auth)
GET    /tagihan-publik/{token}         publik.invoice
GET    /tagihan-publik/{token}/struk   publik.struk             (PDF)
```

## Struk / kwitansi

- Generate PDF (`barryvdh/laravel-dompdf`): header perusahaan, detail tagihan, info pembayaran (metode, waktu, reference), cap "LUNAS".
- Bisa diunduh dari halaman publik & dilampirkan/di-link pada notifikasi struk.

## Acceptance Criteria

- [ ] Buat QRIS membuat record pembayaran pending + menyimpan qr_string/payment_url.
- [ ] Webhook **memverifikasi signature**, **idempotent**, set tagihan `paid`, simpan raw_callback.
- [ ] Webhook dikecualikan CSRF & tidak butuh auth.
- [ ] Pelunasan memicu notifikasi `struk_lunas` (WA + Email).
- [ ] Jalur manual mencatat pembayaran + konfirmator + bukti.
- [ ] Halaman tagihan publik bertoken menampilkan QR/LUNAS + unduh struk PDF.
- [ ] QRIS expired bisa di-generate ulang.
