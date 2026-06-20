# 06 — Modul Master Pelanggan

Kelola data pelanggan dan langganan paketnya. Tanggal jatuh tempo per-pelanggan menjadi dasar billing.

## Field & Validasi

| Field | Input | Validasi |
|-------|-------|----------|
| kode_pelanggan | text (auto-generate, read-only) | unique; dibuat sistem `PLG-000123` |
| nama | text, placeholder "cth: Budi Santoso" | required, string, max:100 |
| no_wa | text, placeholder "cth: 081234567890" | required; dinormalkan ke `62xxx` |
| email | email, placeholder "cth: budi@email.com" | nullable, email |
| alamat | textarea, placeholder "alamat pemasangan" | nullable, string |
| paket_id | **Select2** (paket aktif), placeholder "Pilih paket" | required, exists:pakets,id |
| tanggal_aktivasi | date | required, date |
| tgl_jatuh_tempo | number/select (1–28), placeholder "cth: 5" | required, integer, between:1,28 |
| status | Select2 (aktif/nonaktif/isolir) default aktif | required, in:aktif,nonaktif,isolir |
| catatan | textarea | nullable |

`PelangganRequest` dengan pesan Bahasa Indonesia. Label wajib `*` merah.

### Normalisasi nomor WA

Simpan dalam format `62xxxxxxxxxx`. Konversi otomatis: `0812...` → `62812...`, hapus spasi/strip/`+`. Validasi panjang wajar (10–15 digit).

## Halaman & UI

- **List** (`pelanggan.index`): DataTable server-side. Kolom: Kode, Nama, No WA, Paket (eager load), Jatuh Tempo (tgl), Status (badge), Aksi (Detail, Edit, Hapus — ikon Lucide).
- **Create / Edit**: halaman form terpisah. Paket & Status pakai Select2.
- **Detail** (`pelanggan.show`): info pelanggan + ringkasan tagihan (terakhir, outstanding) + tombol "Buat Tagihan Manual".
- **Hapus**: SweetAlert konfirmasi → AJAX → toast.
- **Filter** di list: by paket, by status (Select2).

## Routes

```
GET    /pelanggan             pelanggan.index
GET    /pelanggan/data        pelanggan.data
GET    /pelanggan/create      pelanggan.create
POST   /pelanggan             pelanggan.store
GET    /pelanggan/{id}        pelanggan.show
GET    /pelanggan/{id}/edit   pelanggan.edit
PUT    /pelanggan/{id}        pelanggan.update
DELETE /pelanggan/{id}        pelanggan.destroy
```

## Business Rules

- `kode_pelanggan` digenerate otomatis & unik (mis. prefix `PLG-` + nomor urut berpadding).
- Hanya pelanggan **status `aktif`** yang ikut digenerate tagihannya oleh scheduler (lihat `07-billing-tagihan.md`).
- `tgl_jatuh_tempo` 1–28 (lihat catatan di `03-database-schema.md`).
- Ganti paket pelanggan **tidak** mengubah tagihan periode berjalan yang sudah terbit; berlaku untuk periode berikutnya.
- Soft delete; pelanggan terhapus tidak digenerate tagihan.

## Import (opsional, fase lanjut)

- Import pelanggan dari Excel/CSV (`maatwebsite/excel`): kolom nama, no_wa, email, alamat, nama_paket, tanggal_aktivasi, tgl_jatuh_tempo. Validasi baris + laporan error.

## Acceptance Criteria

- [ ] Kode pelanggan auto-generate & unik.
- [ ] No WA dinormalkan ke `62xxx`.
- [ ] Paket & status pakai Select2; paket hanya menampilkan yang aktif saat create.
- [ ] List DataTable server-side dengan eager load paket (no N+1).
- [ ] Filter by paket & status berfungsi.
- [ ] Detail menampilkan ringkasan tagihan & tombol buat tagihan manual.
- [ ] Validasi Bahasa Indonesia, label `*` merah, SweetAlert untuk hapus.
