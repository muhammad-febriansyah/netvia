# 04 — Auth & Roles

## Autentikasi

- Guard `web`, login standar Laravel (email + password).
- **Tidak ada registrasi publik** — user dibuat oleh Super Admin lewat modul Manajemen User.
- Field `is_active` di `users`: user nonaktif tidak bisa login (cek di proses login).
- Rate limiting pada endpoint login.

## ACL — `spatie/laravel-permission`

### Roles

| Role | Deskripsi |
|------|-----------|
| `super_admin` | Akses penuh, termasuk manajemen user & pengaturan sistem |
| `admin` | Operasional: master data, tagihan, pembayaran, kirim reminder |
| `finance` | Tagihan, pembayaran, konfirmasi manual, laporan keuangan |

> `super_admin` di-bypass via `Gate::before` (selalu allow) — tidak perlu assign tiap permission.

### Permissions (per modul)

Format: `{modul}.{aksi}` → `view`, `create`, `update`, `delete`, plus aksi khusus.

| Modul | Permissions |
|-------|-------------|
| paket | `paket.view`, `paket.create`, `paket.update`, `paket.delete` |
| pelanggan | `pelanggan.view`, `pelanggan.create`, `pelanggan.update`, `pelanggan.delete` |
| tagihan | `tagihan.view`, `tagihan.create`, `tagihan.update`, `tagihan.void`, `tagihan.generate` |
| pembayaran | `pembayaran.view`, `pembayaran.konfirmasi`, `pembayaran.create_qris` |
| notifikasi | `notifikasi.view`, `notifikasi.kirim`, `notifikasi.template` |
| laporan | `laporan.view`, `laporan.export` |
| pengaturan | `pengaturan.view`, `pengaturan.update` |
| user | `user.view`, `user.create`, `user.update`, `user.delete` |

### Matriks akses (default)

| Permission grup | super_admin | admin | finance |
|-----------------|:--:|:--:|:--:|
| paket.* | ✓ | ✓ | view |
| pelanggan.* | ✓ | ✓ | view |
| tagihan.* | ✓ | ✓ | view, void |
| pembayaran.* | ✓ | ✓ | ✓ |
| notifikasi.* | ✓ | ✓ | view |
| laporan.* | ✓ | ✓ | ✓ |
| pengaturan.* | ✓ | – | – |
| user.* | ✓ | – | – |

(Seed permission & role lewat seeder; sesuaikan saat implementasi.)

## Penerapan

- Cek permission di **FormRequest `authorize()`** dan/atau **middleware route** (`->middleware('can:pelanggan.create')`).
- Di Blade, bungkus tombol/menu dengan `@can('...')`.
- Gunakan policy bila perlu aturan berbasis kepemilikan data.

## Modul Manajemen User (super_admin)

- List user (DataTable server-side): nama, email, role, status.
- Create/Edit user (halaman terpisah): nama, email, password, role (Select2), is_active.
- Aktif/nonaktif user (toggle, konfirmasi SweetAlert).
- Soft delete user.
- Reset password user.

Routes:

```
GET    /users            user.index   (DataTable view)
GET    /users/data       user.data
GET    /users/create     user.create
POST   /users            user.store
GET    /users/{id}/edit  user.edit
PUT    /users/{id}       user.update
DELETE /users/{id}       user.destroy
```
