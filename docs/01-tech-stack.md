# 01 — Tech Stack & Arsitektur

## Stack

| Layer | Teknologi |
|-------|-----------|
| Bahasa | PHP 8.3+ |
| Framework | **Laravel 13** |
| View | **Blade** (server-rendered) |
| Styling | **Tailwind CSS** (build via Vite) |
| Interaktivitas | **jQuery + AJAX** |
| Tabel | **DataTables** dengan **server-side processing** (`yajra/laravel-datatables`) |
| Dropdown | **Select2** (mode AJAX untuk data besar) |
| Notifikasi UI | **SweetAlert2** |
| Input uang | mask Rupiah (jQuery, mis. `cleave.js` atau custom) |
| Ikon | **Lucide** (paket `mallardduck/blade-lucide-icons` atau SVG statis) |
| Auth & ACL | **`spatie/laravel-permission`** |
| Queue | database/redis (untuk job notifikasi) |
| Scheduler | Laravel Scheduler (cron) |
| Payment | **Pakasir** (QRIS) |
| WhatsApp | driver pluggable (lihat `09-notifikasi.md`) |
| Email | Laravel Mail + provider transactional (Resend/Brevo/SMTP) |
| Font | **Poppins** |

## Arsitektur: Service–Repository–Action

Pisahkan tanggung jawab agar controller tipis dan logika mudah diuji:

- **Controller** — terima request, validasi via FormRequest, panggil Action/Service, kembalikan response/redirect. Tidak ada query Eloquent langsung.
- **FormRequest** — validasi + pesan error (Bahasa Indonesia) + authorization (cek permission).
- **Action** — satu use-case tunggal (mis. `GenerateTagihanAction`, `KonfirmasiPembayaranManualAction`). Berisi orkestrasi proses + transaksi DB.
- **Service** — logika domain yang dipakai lintas action (mis. `PakasirService`, `WhatsappService`, `BillingService`).
- **Repository** — abstraksi akses data (query Eloquent, eager loading, pagination). Action/Service memanggil repository, bukan model langsung.
- **Model** — definisi tabel, relasi, casts, scope.

Contoh alur "buat pembayaran QRIS":
`PembayaranController@createQris` → `BuatPembayaranQrisAction` → `PakasirService::createTransaction()` + `PembayaranRepository::create()`.

## Daftar package utama (composer)

- `laravel/framework` ^13
- `spatie/laravel-permission`
- `yajra/laravel-datatables-oracle`
- `mallardduck/blade-lucide-icons` (opsional, ikon)
- `barryvdh/laravel-dompdf` (untuk struk/laporan PDF)
- `maatwebsite/excel` (export laporan Excel)
- `guzzlehttp/guzzle` (HTTP ke Pakasir / gateway WA)

## Frontend (npm)

- `tailwindcss`, `@tailwindcss/forms`
- `jquery`
- `datatables.net`, `datatables.net-dt` (atau styling Tailwind)
- `select2`
- `sweetalert2`
- `cleave.js` (mask Rupiah) — atau implementasi custom
- `lucide` (jika pakai SVG via JS)

## Struktur folder (ringkas)

```
app/
├── Actions/
│   ├── Tagihan/
│   ├── Pembayaran/
│   └── Notifikasi/
├── Services/
│   ├── PakasirService.php
│   ├── Whatsapp/            # interface + driver
│   └── BillingService.php
├── Repositories/
│   ├── PelangganRepository.php
│   ├── TagihanRepository.php
│   └── ...
├── Models/
├── Http/
│   ├── Controllers/
│   ├── Requests/           # FormRequest
│   └── Middleware/
├── Console/Commands/
│   ├── GenerateTagihanCommand.php   # billing:generate
│   └── KirimReminderCommand.php     # billing:remind
├── Jobs/
│   ├── KirimWhatsappNotifikasi.php
│   └── KirimEmailNotifikasi.php
└── DataTables/             # class DataTable yajra (opsional)

resources/views/
├── layouts/
├── components/             # blade component reusable (form-input, dll)
├── paket/
├── pelanggan/
├── tagihan/
├── pembayaran/
├── laporan/
├── pengaturan/
└── invoice-publik/         # halaman tagihan publik

routes/
├── web.php
└── console.php             # registrasi scheduler
```

## Konvensi engineering kunci

- **Eager loading wajib** untuk relasi yang ditampilkan; aktifkan `Model::preventLazyLoading()` di `AppServiceProvider::boot()` (non-production) agar N+1 ketahuan.
- **Server-side pagination** untuk semua list (DataTables server-side / `paginate`).
- Job notifikasi **berjalan di queue**, bukan sinkron, agar tidak nge-block scheduler.
- Semua proses multi-tabel dibungkus `DB::transaction()`.

> Aturan detail koding & UI ada di `02-conventions.md`.
