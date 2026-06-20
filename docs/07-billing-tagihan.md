# 07 — Billing & Tagihan

Jantung sistem. Tagihan dibuat otomatis per pelanggan setiap periode, dengan model **anniversary billing** (jatuh tempo per-pelanggan).

## Model billing

- Setiap pelanggan punya `tgl_jatuh_tempo` (hari 1–28).
- Setiap bulan (periode), sistem membuat satu tagihan untuk tiap pelanggan **aktif**.
- `tanggal_jatuh_tempo` tagihan = periode (bulan berjalan) pada hari `tgl_jatuh_tempo` pelanggan.

## Generate tagihan otomatis

### Command: `billing:generate`

Dijadwalkan harian (lihat `routes/console.php`). Disarankan generate **H-N hari sebelum jatuh tempo** (N dari pengaturan, default 7) agar pelanggan punya waktu bayar.

Logika (di `GenerateTagihanAction` / `BillingService`):

```
Untuk setiap pelanggan status = aktif:
  Tentukan periode berjalan (awal bulan).
  tanggal_jatuh_tempo = periode + (tgl_jatuh_tempo - 1) hari.
  Jika hari ini >= (tanggal_jatuh_tempo - N hari)  // sudah masuk window terbit
     DAN belum ada tagihan utk (pelanggan, periode):   // cek unique
        Buat tagihan:
          - nomor_tagihan = INV/{YYYYMM}/{urut}
          - SNAPSHOT paket_nama = paket.nama, harga = paket.harga
          - jumlah = harga (+ biaya lain bila ada)
          - tanggal_terbit = hari ini
          - status = unpaid
          - public_token = random unik
        (opsional) trigger notifikasi jenis `invoice_baru`.
```

Bungkus dalam `DB::transaction()`. Hormati constraint `unique(pelanggan_id, periode)` agar idempotent (boleh dijalankan ulang tanpa dobel).

### Generate manual

Admin bisa membuat tagihan untuk satu pelanggan dari halaman Detail Pelanggan (`tagihan.generate` permission). Misal untuk pelanggan baru atau tagihan susulan. Pilih periode → buat tagihan (tetap snapshot + cek dobel).

## Deteksi overdue

### Command: `billing:mark-overdue` (atau bagian dari `billing:remind`)

Harian: set `status = overdue` untuk tagihan `unpaid` yang `tanggal_jatuh_tempo < hari ini`.

## Lifecycle status tagihan

```
unpaid ──(lewat jatuh tempo)──▶ overdue
unpaid/overdue ──(pembayaran sukses)──▶ paid   (+ set paid_at)
unpaid/overdue ──(dibatalkan admin)──▶ void
```

- `paid` di-set oleh proses pembayaran (webhook Pakasir sukses ATAU konfirmasi manual). Lihat `08-pembayaran-pakasir.md`.
- `void` hanya oleh admin (`tagihan.void`), dengan alasan; tagihan void tidak ditagih & tidak masuk pendapatan.

## Halaman & UI

- **List tagihan** (`tagihan.index`): DataTable server-side. Kolom: No Tagihan, Pelanggan (eager load), Periode, Jumlah (`rupiah()`), Jatuh Tempo, Status (badge berwarna per status), Aksi (Detail, Bayar/Buat QRIS, Tandai Lunas Manual, Void).
- **Filter**: periode (bulan/tahun), status, pelanggan (Select2 AJAX).
- **Detail tagihan** (`tagihan.show`): info lengkap + riwayat pembayaran + tombol aksi + tombol "Kirim Reminder" + link tagihan publik.
- **Void**: SweetAlert konfirmasi + input alasan.
- **Tandai lunas manual**: untuk pembayaran cash/transfer (lihat modul pembayaran).

Badge status: `unpaid` (kuning), `overdue` (merah), `paid` (hijau), `void` (abu).

## Routes

```
GET    /tagihan                 tagihan.index
GET    /tagihan/data            tagihan.data
GET    /tagihan/{id}            tagihan.show
POST   /tagihan/generate-manual tagihan.generateManual   (dari detail pelanggan)
POST   /tagihan/{id}/void       tagihan.void
POST   /tagihan/{id}/lunas-manual tagihan.lunasManual     (delegasi ke modul pembayaran)
POST   /tagihan/{id}/kirim-reminder tagihan.kirimReminder (ad-hoc, lihat 09)
```

## Penomoran tagihan

`INV/{YYYYMM}/{nomor urut per bulan, padding}` — mis. `INV/202606/000045`. Pastikan unik (gunakan locking/transaksi saat ambil nomor urut).

## Acceptance Criteria

- [ ] `billing:generate` membuat tagihan untuk pelanggan aktif sesuai window H-N, **idempotent** (aman dijalankan ulang).
- [ ] Snapshot `paket_nama` & `harga` tersimpan di tagihan.
- [ ] Constraint unique (pelanggan, periode) mencegah dobel.
- [ ] `billing:mark-overdue` menandai tagihan lewat tempo.
- [ ] Lifecycle status benar; void butuh alasan & permission.
- [ ] List DataTable server-side, eager load pelanggan, badge status berwarna.
- [ ] Generate manual & kirim reminder ad-hoc berfungsi dari UI.
