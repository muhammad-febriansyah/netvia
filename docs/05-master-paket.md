# 05 — Modul Master Paket

Kelola produk paket internet yang dijual.

## Field & Validasi

| Field | Input | Validasi |
|-------|-------|----------|
| nama | text, placeholder "cth: Home 20 Mbps" | required, string, max:100 |
| kecepatan_mbps | number, placeholder "cth: 20" | nullable, integer, min:1 |
| harga | **input Rupiah (mask)** | required, integer (parse dari mask), min:0 |
| deskripsi | textarea | nullable, string |
| is_active | toggle/switch (default aktif) | boolean |

Semua label field wajib diberi `*` merah. Pesan validasi Bahasa Indonesia (FormRequest `PaketRequest`).

## Halaman & UI

- **List** (`paket.index`): DataTable server-side. Kolom: Nama, Kecepatan, Harga (`rupiah()`), Status (badge aktif/nonaktif), Aksi (Edit, Hapus — ikon Lucide).
- **Create** (`paket.create`): halaman form terpisah.
- **Edit** (`paket.edit`): halaman form terpisah.
- **Hapus**: konfirmasi SweetAlert → AJAX delete → toast sukses → reload DataTable.
- Toggle status aktif via AJAX + toast.

## Routes

```
GET    /paket            paket.index
GET    /paket/data       paket.data      (JSON DataTable)
GET    /paket/create     paket.create
POST   /paket            paket.store
GET    /paket/{id}/edit  paket.edit
PUT    /paket/{id}       paket.update
DELETE /paket/{id}       paket.destroy
PATCH  /paket/{id}/toggle paket.toggle   (aktif/nonaktif)
```

## Business Rules

- **Tidak boleh menghapus** paket yang masih dipakai pelanggan aktif → tampilkan error SweetAlert ("Paket masih digunakan oleh N pelanggan"). Gunakan soft delete; cegah delete bila ada relasi aktif.
- Mengubah `harga` paket **tidak** mengubah tagihan yang sudah terbit (snapshot). Hanya berlaku untuk tagihan periode berikutnya.
- Paket `is_active=false` tidak muncul di Select2 pemilihan paket pelanggan baru, tapi pelanggan lama tetap memakainya.

## Acceptance Criteria

- [ ] List tampil via DataTable server-side dengan harga terformat Rupiah.
- [ ] Create/Edit di halaman terpisah; input harga pakai mask Rupiah & tersimpan integer.
- [ ] Validasi gagal menampilkan error per-field (Bahasa Indonesia).
- [ ] Hapus paket terpakai ditolak dengan pesan jelas.
- [ ] Toggle status & hapus memakai SweetAlert + toast.
- [ ] Tombol aksi memakai ikon Lucide & dihormati permission.
