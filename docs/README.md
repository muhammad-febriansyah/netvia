# Netvia — Product Requirements Document (Modular)

PRD untuk **Netvia**: aplikasi manajemen langganan & tagihan internet (ISP / RT-RW Net / WISP kecil) yang fokus ke **billing + reminder otomatis**, **bukan** kontrol jaringan (tidak ada integrasi Mikrotik / PPPoE / auto-isolir).

Dokumen ini sengaja dipecah jadi banyak file agar mudah dijadikan konteks per-modul saat ngoding dengan **Claude Code (Opus 4.8)**.

## Cara pakai dengan Claude Code

1. Taruh seluruh folder `netvia-prd/` di dalam repo, misal `docs/prd/`.
2. **Selalu** sertakan `02-conventions.md` sebagai konteks di setiap sesi — file ini adalah "konstitusi" proyek (stack, arsitektur, aturan UI/form/notif). Bila perlu, salin poin-poin pentingnya ke `CLAUDE.md` di root repo.
3. Saat menggarap satu modul, load file modul terkait + `02-conventions.md` + `03-database-schema.md`.
4. Tambahkan instruksi spesifik di prompt, contoh: *"Implementasikan modul Pelanggan sesuai `06-master-pelanggan.md`, ikuti `02-conventions.md`."*

## Index file

| File | Isi |
|------|-----|
| `00-overview.md` | Visi produk, ruang lingkup (in/out of scope), peran user, glosarium |
| `01-tech-stack.md` | Tech stack lengkap, arsitektur Service-Repository-Action, daftar package, struktur folder |
| `02-conventions.md` | **Konstitusi proyek** — semua aturan koding, form, UI, notifikasi, uang, waktu |
| `03-database-schema.md` | Skema database, ERD, semua tabel, relasi, index, snapshot |
| `04-auth-and-roles.md` | Autentikasi, role & permission (spatie), matriks akses |
| `05-master-paket.md` | Modul master Paket internet |
| `06-master-pelanggan.md` | Modul master Pelanggan |
| `07-billing-tagihan.md` | Generate tagihan otomatis, scheduler, lifecycle tagihan |
| `08-pembayaran-pakasir.md` | Integrasi pembayaran QRIS via Pakasir + webhook |
| `09-notifikasi.md` | Reminder WhatsApp + Email, scheduler, log anti dobel-kirim, template |
| `10-dashboard-laporan.md` | Dashboard statistik + laporan + export |
| `11-pengaturan.md` | Pengaturan: profil perusahaan, konfigurasi gateway, template, billing |

## Prinsip non-negotiable (ringkas)

- Stack: **Laravel 13 + Blade + Tailwind CSS + jQuery + AJAX**, tabel pakai **DataTables server-side**.
- Arsitektur: **Service-Repository-Action**.
- Uang disimpan **BIGINT (integer Rupiah)**, tanpa desimal.
- Waktu disimpan **UTC**, ditampilkan **WIB (Asia/Jakarta)**.
- Form: label wajib ada tanda `*` merah, setiap input punya placeholder, input Rupiah pakai **mask Rupiah**, dropdown pakai **Select2**.
- Notifikasi UI pakai **SweetAlert2** (toast sukses, konfirmasi hapus, error).
- Payment gateway: **Pakasir (QRIS)**.
- Data transaksi pakai **snapshot field** (nama & harga paket disimpan saat tagihan dibuat).
