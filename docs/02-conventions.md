# 02 — Conventions (Konstitusi Proyek)

> File ini WAJIB di-load di setiap sesi Claude Code. Semua kode harus mengikuti aturan di sini.

## Bahasa

- **Kode, nama variabel, nama tabel/kolom, komentar**: Bahasa Inggris.
- **Teks UI (label, tombol, judul), pesan validasi, pesan notifikasi**: Bahasa Indonesia.

## Visual & Tema

- **Font: Poppins** secara eksklusif (di seluruh aplikasi).
- **Light mode only** — tidak ada dark mode.
- **Ikon: Lucide** pada semua tombol aksi (Tambah, Edit, Hapus, Simpan, dll).

## Arsitektur

- Pola **Service–Repository–Action** (lihat `01-tech-stack.md`).
- Controller tipis: validasi via FormRequest → panggil Action → return response.
- Tidak ada query Eloquent di controller atau Blade.
- Proses multi-tabel selalu dalam `DB::transaction()`.

## Model & Database

- **Eager loading wajib** untuk relasi yang dipakai. Aktifkan `Model::preventLazyLoading(! app()->isProduction())` di `AppServiceProvider`.
- Selalu definisikan `$fillable`, `$casts`, dan relasi pada model.
- **Uang disimpan sebagai BIGINT (integer Rupiah)** — tanpa desimal, tanpa pemisah. Contoh: Rp 150.000 disimpan `150000`.
- **Waktu disimpan UTC**, ditampilkan **WIB (Asia/Jakarta)**. `config/app.php` timezone tetap `UTC`; konversi ke WIB hanya saat tampil (mis. accessor / helper).
- **Snapshot field** untuk data transaksional: saat tagihan dibuat, simpan `paket_nama` dan `harga` ke baris tagihan; jangan mengandalkan join ke master paket saat menampilkan/menghitung tagihan lama.
- Gunakan **soft deletes** untuk master data (Paket, Pelanggan, User).
- Tambahkan index pada foreign key & kolom yang sering difilter (status, periode, tanggal_jatuh_tempo).

## Helper Rupiah

Sediakan helper global:

```php
rupiah(150000);        // => "Rp 150.000"
rupiah_clean("150.000"); // => 150000 (int), untuk parsing input
```

## Tabel / DataTables

- Semua list pakai **DataTables server-side processing** (`yajra/laravel-datatables`).
- Endpoint data terpisah (mis. `GET /pelanggan/data`) yang mengembalikan JSON.
- **Eager load** relasi pada query DataTable; jangan query di dalam loop kolom.
- Kolom uang ditampilkan via helper `rupiah()`.
- Kolom aksi berisi tombol ber-ikon Lucide (lihat pola tombol di bawah).
- Searching, sorting, paging dilakukan di sisi server.

## Form — aturan WAJIB

1. **Label field wajib** diberi tanda bintang merah:
   ```html
   <label>Nama Pelanggan <span class="text-red-500">*</span></label>
   ```
2. **Setiap input punya `placeholder`** yang jelas (contoh isian), mis. `placeholder="cth: Budi Santoso"`.
3. **Input nominal Rupiah** memakai **mask Rupiah** (auto pemisah ribuan saat diketik), tetapi **dikirim & disimpan sebagai integer**. Gunakan `cleave.js` atau implementasi setara; parse balik dengan `rupiah_clean()` di server.
4. **Semua dropdown pakai Select2.** Untuk data besar (mis. pelanggan), pakai Select2 mode AJAX.
5. **CRUD tidak pakai modal** — gunakan **halaman terpisah** untuk Create dan Edit (Blade page penuh). Modal hanya untuk konfirmasi ringan (dan konfirmasi hapus pakai SweetAlert, bukan modal manual).
6. Validasi dilakukan di **FormRequest** dengan **pesan Bahasa Indonesia**. Tampilkan error per-field di bawah input.
7. Tombol submit menampilkan state loading (disable + spinner) saat proses AJAX.

### Pola Blade component input (disarankan)

Buat komponen reusable `<x-form.input>`, `<x-form.select>`, `<x-form.rupiah>` yang sudah menangani label+bintang, placeholder, dan tampilan error secara konsisten.

## Notifikasi UI — SweetAlert2

Standarisasi feedback ke user dengan SweetAlert2:

- **Sukses (toast):** setelah simpan/update/hapus berhasil.
  ```js
  Swal.fire({ toast:true, position:'top-end', icon:'success',
    title:'Data berhasil disimpan', showConfirmButton:false, timer:2500 });
  ```
- **Konfirmasi hapus:** sebelum delete.
  ```js
  Swal.fire({ title:'Hapus data ini?', text:'Tindakan tidak bisa dibatalkan.',
    icon:'warning', showCancelButton:true,
    confirmButtonText:'Ya, hapus', cancelButtonText:'Batal' })
    .then(r => { if (r.isConfirmed) { /* AJAX delete */ } });
  ```
- **Error:** saat AJAX gagal / validasi gagal → `icon:'error'` dengan ringkasan pesan.
- Flash message dari redirect server (mis. setelah submit halaman Create) dikonversi jadi SweetAlert toast saat halaman tujuan dimuat.

## Pola tombol aksi (DataTable)

```html
<a href="/pelanggan/{id}/edit" class="btn-edit"><i data-lucide="pencil"></i> Edit</a>
<button class="btn-delete" data-id="{id}"><i data-lucide="trash-2"></i> Hapus</button>
```

Semua tombol memuat ikon Lucide + teks. Jalankan `lucide.createIcons()` setiap kali DataTable redraw.

## AJAX

- Pakai `meta csrf-token` + header `X-CSRF-TOKEN` di setup global `$.ajaxSetup`.
- Response API konsisten: `{ success: bool, message: string, data?: any, errors?: object }`.
- Tangani `422` (validasi) dengan menampilkan error per-field + SweetAlert.

## Autorisasi

- Gunakan `spatie/laravel-permission`. Cek permission di FormRequest `authorize()` dan/atau middleware route.
- Sembunyikan tombol aksi di Blade berdasarkan permission (`@can('pelanggan.delete')`).

## Validasi (contoh pesan ID)

```php
public function messages(): array {
    return [
        'nama.required'  => 'Nama wajib diisi.',
        'harga.required' => 'Harga wajib diisi.',
        'harga.integer'  => 'Harga harus berupa angka.',
        'email.email'    => 'Format email tidak valid.',
    ];
}
```

## Definition of Done (per fitur)

- [ ] Mengikuti pola Service-Repository-Action.
- [ ] Tidak ada N+1 (eager loading benar, lolos `preventLazyLoading`).
- [ ] Form: bintang merah, placeholder, mask Rupiah, Select2 sesuai aturan.
- [ ] Feedback pakai SweetAlert2 (sukses/konfirmasi/error).
- [ ] DataTable server-side untuk list.
- [ ] Validasi FormRequest + pesan Bahasa Indonesia.
- [ ] Permission dicek (route/FormRequest + sembunyikan tombol).
- [ ] Uang disimpan integer; waktu UTC tampil WIB.
- [ ] Snapshot diterapkan untuk data transaksional.
