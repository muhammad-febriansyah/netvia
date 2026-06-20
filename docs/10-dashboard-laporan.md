# 10 — Dashboard & Laporan

## Dashboard

Halaman utama setelah login. Komponen:

### Kartu statistik (ringkasan)
- Total pelanggan **aktif**.
- Tagihan periode berjalan: jumlah & total nominal (`rupiah()`).
- Sudah dibayar bulan ini: jumlah & nominal.
- Outstanding (belum bayar + overdue): jumlah & nominal.
- Pendapatan bulan ini (sum pembayaran sukses).

### Grafik
- Tren pendapatan beberapa bulan terakhir (bar/line). Sumber: agregasi pembayaran sukses per bulan.
- (Opsional) komposisi pelanggan per paket.

### Daftar cepat
- Tagihan **jatuh tempo terdekat** (mendekati / hari ini).
- Tagihan **overdue** (perlu ditindaklanjuti) — dengan tombol kirim reminder.

Semua angka uang via helper `rupiah()`. Hitung agregat dengan query efisien (groupBy), bukan loop.

## Laporan

Menu laporan dengan filter periode & status. Tabel laporan pakai **DataTable server-side**; sediakan **export Excel & PDF**.

### Jenis laporan
1. **Pendapatan** — pembayaran sukses per rentang periode (per hari/bulan), total.
2. **Tunggakan / Outstanding** — tagihan unpaid + overdue, dikelompokkan per pelanggan/periode.
3. **Riwayat pembayaran** — daftar pembayaran (metode, waktu, nominal, tagihan terkait).
4. **Rekap pelanggan** — daftar pelanggan + paket + status + saldo tunggakan.

### Filter umum
- Rentang periode (bulan/tahun atau date range).
- Status (untuk laporan tagihan/pembayaran).
- Paket / status pelanggan (Select2).

### Export
- **Excel** via `maatwebsite/excel`.
- **PDF** via `barryvdh/laravel-dompdf` (header perusahaan dari pengaturan).
- Tombol export menghormati permission `laporan.export`.

## Routes

```
GET  /dashboard                        dashboard.index

GET  /laporan/pendapatan               laporan.pendapatan
GET  /laporan/pendapatan/data          laporan.pendapatanData
GET  /laporan/pendapatan/export        laporan.pendapatanExport   (?type=excel|pdf)

GET  /laporan/tunggakan                laporan.tunggakan
GET  /laporan/tunggakan/data           laporan.tunggakanData
GET  /laporan/tunggakan/export         laporan.tunggakanExport

GET  /laporan/pembayaran               laporan.pembayaran
GET  /laporan/pembayaran/data          laporan.pembayaranData
GET  /laporan/pembayaran/export        laporan.pembayaranExport

GET  /laporan/pelanggan                laporan.pelanggan
GET  /laporan/pelanggan/data           laporan.pelangganData
GET  /laporan/pelanggan/export         laporan.pelangganExport
```

## Acceptance Criteria

- [ ] Dashboard menampilkan kartu statistik akurat (aktif, tagihan, dibayar, outstanding, pendapatan).
- [ ] Grafik tren pendapatan tampil dari agregasi pembayaran sukses.
- [ ] Daftar jatuh tempo terdekat & overdue tampil dengan aksi reminder.
- [ ] Setiap laporan punya filter periode/status & DataTable server-side.
- [ ] Export Excel & PDF berfungsi dan menghormati permission.
- [ ] Semua nominal terformat Rupiah; agregasi efisien tanpa N+1.
