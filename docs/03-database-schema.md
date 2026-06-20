# 03 — Database Schema

Konvensi: uang `BIGINT` (Rupiah integer), timestamp `UTC`, soft deletes pada master data, snapshot pada tagihan.

## ERD (ringkas)

```
users ──< (spatie) roles/permissions

pakets ──1:N── pelanggans ──1:N── tagihans ──1:N── pembayarans
                                      │
                                      └──1:N── notifikasi_logs ──N:1── pelanggans

settings (key-value)        message_templates
```

## Tabel

### `users`
Standar Laravel + relasi spatie.

| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | bigIncrements | |
| name | string | |
| email | string, unique | |
| password | string | |
| is_active | boolean, default true | |
| timestamps, softDeletes | | |

Role/permission dikelola tabel bawaan `spatie/laravel-permission`.

### `pakets`
Master paket internet.

| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | bigIncrements | |
| nama | string | mis. "Home 20 Mbps" |
| kecepatan_mbps | unsignedInteger, nullable | |
| harga | **bigInteger** | harga bulanan (Rupiah integer) |
| deskripsi | text, nullable | |
| is_active | boolean, default true | paket bisa dipilih atau tidak |
| timestamps, softDeletes | | |

### `pelanggans`
Master pelanggan.

| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | bigIncrements | |
| kode_pelanggan | string, unique | auto-generate, mis. `PLG-000123` |
| nama | string | |
| no_wa | string | format `62xxx` (dinormalkan) |
| email | string, nullable | untuk notifikasi email |
| alamat | text, nullable | |
| paket_id | foreignId → pakets | |
| tanggal_aktivasi | date | tanggal mulai berlangganan |
| tgl_jatuh_tempo | unsignedTinyInteger | **hari dalam bulan (1–28)** |
| status | enum(`aktif`,`nonaktif`,`isolir`) default `aktif` | penanda administratif (lihat 00-overview) |
| catatan | text, nullable | |
| timestamps, softDeletes | | |

Index: `paket_id`, `status`, `tgl_jatuh_tempo`.

> `tgl_jatuh_tempo` dibatasi 1–28 untuk menghindari masalah bulan pendek (Feb). Bila pelanggan ingin tanggal 29–31, simpan 28 atau tangani "akhir bulan" secara eksplisit di BillingService.

### `tagihans`
Tagihan per pelanggan per periode. **Mengandung snapshot.**

| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | bigIncrements | |
| nomor_tagihan | string, unique | mis. `INV/202606/000123` |
| pelanggan_id | foreignId → pelanggans | |
| paket_id | foreignId → pakets, nullable | referensi, boleh null jika paket terhapus |
| periode | date | awal bulan, mis. `2026-06-01` |
| paket_nama | string | **snapshot** nama paket saat terbit |
| harga | **bigInteger** | **snapshot** harga paket saat terbit |
| jumlah | **bigInteger** | total tagihan (= harga + biaya lain bila ada) |
| tanggal_terbit | date | |
| tanggal_jatuh_tempo | date | tanggal penuh (periode + tgl_jatuh_tempo pelanggan) |
| status | enum(`unpaid`,`paid`,`overdue`,`void`) default `unpaid` | |
| paid_at | timestamp, nullable | |
| public_token | string, unique | untuk URL tagihan publik |
| timestamps | | |

Constraint: **unique(`pelanggan_id`,`periode`)** — cegah tagihan dobel untuk pelanggan+bulan yang sama.
Index: `status`, `periode`, `tanggal_jatuh_tempo`.

### `pembayarans`
Transaksi pelunasan sebuah tagihan.

| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | bigIncrements | |
| tagihan_id | foreignId → tagihans | |
| metode | enum(`qris_pakasir`,`transfer_manual`,`cash`) | |
| jumlah_bayar | **bigInteger** | |
| status | enum(`pending`,`success`,`failed`,`expired`) default `pending` | |
| pakasir_order_id | string, nullable, index | order id yang dikirim ke Pakasir |
| pakasir_reference | string, nullable | reference dari Pakasir |
| qr_string | text, nullable | payload QRIS |
| payment_url | string, nullable | link halaman bayar Pakasir (bila ada) |
| expired_at | timestamp, nullable | kadaluarsa QRIS |
| bukti_transfer | string, nullable | path file (jalur manual) |
| dikonfirmasi_by | foreignId → users, nullable | admin yang konfirmasi manual |
| dibayar_at | timestamp, nullable | waktu pembayaran sukses |
| raw_callback | json, nullable | simpan payload webhook untuk audit |
| timestamps | | |

Index: `tagihan_id`, `status`, `pakasir_order_id`.

### `notifikasi_logs`
Catatan setiap notifikasi terkirim. **Mencegah dobel-kirim.**

| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | bigIncrements | |
| tagihan_id | foreignId → tagihans, nullable | |
| pelanggan_id | foreignId → pelanggans | |
| channel | enum(`whatsapp`,`email`) | |
| jenis | enum(`invoice_baru`,`reminder_h3`,`reminder_due`,`reminder_overdue`,`struk_lunas`) | |
| status | enum(`pending`,`sent`,`failed`) default `pending` | |
| recipient | string | nomor WA / email tujuan |
| payload | text, nullable | isi pesan terkirim |
| error_message | text, nullable | |
| sent_at | timestamp, nullable | |
| timestamps | | |

Constraint: **unique(`tagihan_id`,`channel`,`jenis`)** — satu jenis reminder hanya sekali per tagihan per channel.

### `settings`
Key-value untuk konfigurasi sistem (profil perusahaan, kredensial gateway, parameter billing). Lihat `11-pengaturan.md`.

| Kolom | Tipe |
|-------|------|
| id | bigIncrements |
| key | string, unique |
| value | text, nullable |
| timestamps | |

> Kredensial sensitif (API key Pakasir, token WA) sebaiknya dari `.env`, bukan tabel publik. `settings` untuk konfigurasi non-rahasia / yang perlu diubah lewat UI.

### `message_templates`
Template pesan reminder/struk yang bisa diedit admin.

| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | bigIncrements | |
| jenis | enum(sama dengan notifikasi_logs.jenis) | |
| channel | enum(`whatsapp`,`email`) | |
| subject | string, nullable | untuk email |
| body | text | mendukung placeholder `{nama}`, `{nomor_tagihan}`, `{jumlah}`, `{jatuh_tempo}`, `{link_bayar}`, `{periode}` |
| is_active | boolean, default true | |
| timestamps | | |

## Relasi (Eloquent)

- `Paket` hasMany `Pelanggan`; `Pelanggan` belongsTo `Paket`.
- `Pelanggan` hasMany `Tagihan`; `Tagihan` belongsTo `Pelanggan`.
- `Tagihan` hasMany `Pembayaran`; `Pembayaran` belongsTo `Tagihan`.
- `Tagihan`/`Pelanggan` hasMany `NotifikasiLog`.

## Catatan snapshot

Saat `GenerateTagihanAction` membuat tagihan, ia menyalin `paket.nama` → `tagihan.paket_nama` dan `paket.harga` → `tagihan.harga`. Penghitungan dan tampilan tagihan **selalu** memakai field snapshot, bukan join ke `pakets`. Ini menjaga riwayat tagihan tetap akurat meski harga paket berubah di kemudian hari.
