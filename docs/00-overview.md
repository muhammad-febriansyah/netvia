# 00 — Overview Produk

## Visi

**Netvia** adalah aplikasi manajemen langganan & tagihan untuk penyedia internet skala kecil–menengah (ISP lokal, RT-RW Net, WISP). Fokusnya **menagih pelanggan tepat waktu** lewat reminder otomatis (WhatsApp + Email) dan memudahkan pembayaran via QRIS, **tanpa** menyentuh sisi teknis jaringan.

## Masalah yang diselesaikan

Operator internet kecil biasanya menagih manual (chat satu-satu, catat di Excel). Akibatnya: pelanggan lupa bayar, tunggakan menumpuk, rekap pendapatan berantakan. Netvia mengotomasi siklus tagihan: terbit tagihan → reminder bertahap → pembayaran → struk → rekap.

## Target user

- Operator / pemilik RT-RW Net & WISP kecil (puluhan s/d ribuan pelanggan).
- Staf admin/finance yang mengelola tagihan harian.

## Ruang lingkup — IN scope

1. **Master data**: Paket internet, Pelanggan.
2. **Billing otomatis**: generate tagihan bulanan per pelanggan (model anniversary / jatuh tempo per-pelanggan).
3. **Reminder otomatis**: WhatsApp + Email pada H-3, hari-H, dan saat lewat tempo.
4. **Pembayaran**: QRIS via **Pakasir** (otomatis lunas via webhook) + jalur manual (transfer/cash, dikonfirmasi admin).
5. **Halaman tagihan publik**: pelanggan buka link → lihat QR & nominal → bayar.
6. **Dashboard & laporan**: statistik, pendapatan, tunggakan, riwayat pembayaran, export.
7. **Pengaturan**: profil perusahaan, konfigurasi gateway/notifikasi, template pesan, parameter billing.
8. **Multi-user**: role Super Admin, Admin, Finance.

## Ruang lingkup — OUT of scope (penting, eksplisit)

Netvia **TIDAK** menangani:

- Integrasi Mikrotik / router apa pun (RouterOS API).
- Auto-isolir / suspend / enable koneksi otomatis.
- Manajemen PPPoE / Hotspot / voucher.
- Monitoring bandwidth, uptime, atau topologi jaringan.

> Status pelanggan (`aktif`/`nonaktif`/`isolir`) di Netvia hanya **penanda administratif** untuk keperluan billing — tidak mengeksekusi tindakan ke perangkat jaringan. Pemutusan/penyambungan dilakukan operator secara manual di luar aplikasi.

## Peran user (ringkas)

| Role | Akses |
|------|-------|
| **Super Admin** | Semua modul + manajemen user + pengaturan sistem |
| **Admin / Operator** | Master data, tagihan, pembayaran, kirim reminder |
| **Finance** | Tagihan, pembayaran, konfirmasi manual, laporan keuangan |

Detail permission ada di `04-auth-and-roles.md`.

## Glosarium

- **Paket** — produk langganan internet (mis. "Home 20 Mbps") dengan harga bulanan.
- **Pelanggan** — pelanggan yang berlangganan satu paket.
- **Jatuh tempo (tanggal)** — tanggal dalam sebulan (1–28) saat tagihan pelanggan harus dibayar. Bersifat per-pelanggan (anniversary billing).
- **Periode** — bulan tagihan, disimpan sebagai tanggal awal bulan (mis. `2026-06-01`).
- **Tagihan (invoice)** — kewajiban bayar satu pelanggan untuk satu periode.
- **Pembayaran** — transaksi pelunasan sebuah tagihan (QRIS Pakasir / manual).
- **Struk / kwitansi** — bukti lunas yang dikirim via WA + Email.
- **Reminder** — notifikasi penagihan terjadwal (H-3 / hari-H / lewat tempo).
- **Snapshot** — penyimpanan nilai (nama & harga paket) ke dalam tagihan saat dibuat, agar tidak berubah meski master paket diedit kemudian.

## Alur tingkat tinggi

```
Pelanggan + Paket (punya tanggal jatuh tempo)
        ↓
Scheduler harian → generate tagihan + cek jatuh tempo (H-3 / hari-H / telat)
        ↓
Kirim notifikasi (WhatsApp + Email)
        ↓
Pelanggan bayar → QRIS Pakasir (webhook auto-lunas) / Transfer manual (admin konfirmasi)
        ↓
Tagihan LUNAS → kirim struk (WA + Email)
```
