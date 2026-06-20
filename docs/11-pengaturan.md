# 11 — Pengaturan

Konfigurasi sistem (akses `pengaturan.*`, umumnya super_admin). Disimpan di tabel `settings` (key-value) kecuali kredensial rahasia yang diambil dari `.env`.

## 1. Profil Perusahaan
Dipakai pada invoice, struk, halaman tagihan publik, dan header email.

- nama_perusahaan
- logo (upload)
- alamat
- no_telp / no_wa_cs
- email_cs
- footer_invoice (teks bebas)

## 2. Parameter Billing
- `generate_hari_sebelum_jatuh_tempo` (N, default 7) — kapan tagihan terbit.
- `reminder_overdue_hari` (mis. `1,3,7`) — pada H+berapa reminder telat dikirim.
- `kirim_invoice_baru` (boolean) — apakah notif `invoice_baru` aktif.
- prefix nomor tagihan & pelanggan (opsional).

## 3. Konfigurasi Pembayaran (Pakasir)
- status aktif/nonaktif QRIS.
- (Kredensial `PAKASIR_API_KEY`, `PAKASIR_PROJECT` dari `.env`.)
- callback URL (tampilkan untuk disalin ke dashboard Pakasir).
- info rekening manual (untuk instruksi transfer manual): bank, no rekening, atas nama.

## 4. Konfigurasi WhatsApp
- pilih driver (cloud_api / gateway / chatcepat).
- field konfigurasi sesuai driver (token/endpoint) — simpan rahasia di `.env` bila memungkinkan.
- nomor pengirim.
- tombol "Tes Kirim" untuk verifikasi koneksi.

## 5. Konfigurasi Email
- driver/SMTP/provider (dari `.env`).
- from name & from address.
- tombol "Tes Kirim".

## 6. Template Pesan
- Kelola `message_templates` per jenis & channel (lihat `09-notifikasi.md`).
- Editor body + daftar placeholder yang tersedia + preview.

## 7. Manajemen User
- Link ke modul user (lihat `04-auth-and-roles.md`).

## UI

- Halaman pengaturan bertab/section (Profil, Billing, Pembayaran, WhatsApp, Email, Template).
- Form mengikuti aturan `02-conventions.md` (label `*` merah, placeholder, Select2, mask Rupiah bila ada nominal, SweetAlert untuk simpan).
- Simpan via AJAX → toast sukses.

## Routes

```
GET  /pengaturan                 pengaturan.index
PUT  /pengaturan/profil          pengaturan.updateProfil
PUT  /pengaturan/billing         pengaturan.updateBilling
PUT  /pengaturan/pembayaran      pengaturan.updatePembayaran
PUT  /pengaturan/whatsapp        pengaturan.updateWhatsapp
PUT  /pengaturan/email           pengaturan.updateEmail
POST /pengaturan/whatsapp/tes    pengaturan.tesWhatsapp
POST /pengaturan/email/tes       pengaturan.tesEmail
```

## Acceptance Criteria

- [ ] Profil perusahaan tersimpan & dipakai di invoice/struk/halaman publik/email.
- [ ] Parameter billing (H-N generate, hari reminder overdue) dibaca oleh command scheduler.
- [ ] Konfigurasi Pakasir menampilkan callback URL & info rekening manual.
- [ ] Driver WhatsApp & Email bisa dipilih + tombol "Tes Kirim".
- [ ] Template pesan dapat diedit dengan preview placeholder.
- [ ] Kredensial rahasia diambil dari `.env`, bukan disimpan/terlihat di UI publik.
