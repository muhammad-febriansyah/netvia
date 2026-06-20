# 09 — Notifikasi (WhatsApp + Email)

Reminder penagihan & struk lunas, dikirim via WhatsApp dan Email, terjadwal dan anti dobel-kirim.

## Channel

- **WhatsApp** — lewat driver yang dapat dikonfigurasi (lihat "Driver WhatsApp").
- **Email** — Laravel Mail + provider transactional (Resend/Brevo/SMTP).

## Jenis notifikasi

| Jenis | Pemicu | Channel |
|-------|--------|---------|
| `invoice_baru` | Tagihan baru terbit (opsional) | WA + Email |
| `reminder_h3` | H-3 sebelum jatuh tempo | WA + Email |
| `reminder_due` | Hari-H jatuh tempo | WA + Email |
| `reminder_overdue` | Lewat tempo (mis. H+1, H+3) | WA + Email |
| `struk_lunas` | Pembayaran sukses | WA + Email |

> Karena Netvia tidak melakukan auto-isolir, reminder bertahap inilah alat utama menekan tunggakan.

## Scheduler

### Command: `billing:remind`

Dijadwalkan harian (mis. jam 08:00 WIB). Logika (`KirimReminderCommand` → dispatch jobs):

```
Hari ini (WIB).
reminder_h3:      tagihan unpaid dgn tanggal_jatuh_tempo = hari ini + 3
reminder_due:     tagihan unpaid dgn tanggal_jatuh_tempo = hari ini
reminder_overdue: tagihan overdue/unpaid dgn tanggal_jatuh_tempo dalam {hari ini - 1, hari ini - 3, ...}

Untuk tiap tagihan terpilih, untuk tiap channel (wa, email):
  - cek notifikasi_logs unique(tagihan_id, channel, jenis):
      jika sudah 'sent' → skip.
  - buat log status=pending lalu dispatch job kirim.
```

Pisahkan pengiriman ke **queued job** (`KirimWhatsappNotifikasi`, `KirimEmailNotifikasi`) agar scheduler tidak nge-block dan bisa retry.

### Registrasi (`routes/console.php`)

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('billing:generate')->dailyAt('01:00');
Schedule::command('billing:mark-overdue')->dailyAt('02:00');
Schedule::command('billing:remind')->dailyAt('08:00');
```

(Timezone scheduler bisa diatur `->timezone('Asia/Jakarta')`.)

## Anti dobel-kirim

- Tabel `notifikasi_logs` dengan **unique(`tagihan_id`,`channel`,`jenis`)**.
- Buat log SEBELUM kirim (status `pending`); update ke `sent`/`failed` setelah job selesai.
- Job idempotent: jika log untuk kombinasi itu sudah `sent`, jangan kirim ulang.

## Job & retry

- Job kirim memanggil `WhatsappService::send()` / Mailable.
- `tries = 3`, backoff bertahap. Jika gagal final → log `failed` + simpan `error_message`.
- Bisa kirim ulang manual dari UI (lihat ad-hoc).

## Driver WhatsApp (pluggable)

Definisikan interface agar provider mudah diganti:

```php
interface WhatsappService {
    public function send(string $to62, string $message): WhatsappResult;
}
```

Pilihan driver (konfigurasi via `.env` / pengaturan):
- **Meta WhatsApp Cloud API** (resmi) — pakai template kategori *utility* untuk reminder; paling aman & legit untuk blast terjadwal.
- **Gateway pihak ketiga** (mis. Fonnte/Wablas) — cepat untuk MVP; risiko nomor terblokir bila volume blast tinggi.
- **Integrasi ChatCepat** — bila ingin reuse infrastruktur pengiriman milik sendiri.

> Rekomendasi: untuk reminder terjadwal, **hindari WhatsApp unofficial pada nomor utama** (rawan banned). Default ke Cloud API (template utility) atau relay ke ChatCepat.

## Template pesan

Disimpan di `message_templates`, dapat diedit admin (modul pengaturan). Placeholder didukung:

`{nama}` `{kode_pelanggan}` `{nomor_tagihan}` `{periode}` `{jumlah}` `{jatuh_tempo}` `{link_bayar}`

Contoh body `reminder_due` (WA):
```
Halo {nama}, tagihan internet Anda {nomor_tagihan} periode {periode}
sebesar {jumlah} jatuh tempo hari ini ({jatuh_tempo}).
Bayar mudah via QRIS di: {link_bayar}
Terima kasih 🙏
```

`{jumlah}` dirender dengan format Rupiah; `{link_bayar}` mengarah ke halaman tagihan publik (`08-pembayaran-pakasir.md`).

## Notifikasi ad-hoc

- Dari detail tagihan: tombol "Kirim Reminder Sekarang" (`tagihan.kirimReminder`) — kirim ulang reminder pilihan via WA/Email (membuat/menimpa log sesuai kebijakan).
- Konfirmasi & hasil pakai SweetAlert.

## Halaman & UI

- **Log notifikasi** (`notifikasi.index`): DataTable server-side — tanggal, pelanggan, channel, jenis, status, aksi (lihat payload / kirim ulang).
- **Template** (`notifikasi.template`): kelola template per jenis & channel (editor body + daftar placeholder).

## Routes

```
GET    /notifikasi              notifikasi.index
GET    /notifikasi/data         notifikasi.data
POST   /notifikasi/{log}/resend notifikasi.resend
GET    /notifikasi/template     notifikasi.template
PUT    /notifikasi/template/{id} notifikasi.templateUpdate
```

## Acceptance Criteria

- [ ] `billing:remind` mengirim reminder H-3 / hari-H / overdue lewat WA + Email.
- [ ] Pengiriman lewat **queued job** dengan retry.
- [ ] **unique(tagihan_id, channel, jenis)** mencegah dobel-kirim; log mencatat status.
- [ ] Driver WhatsApp pluggable via interface + konfigurasi.
- [ ] Template dengan placeholder bisa diedit admin; `{jumlah}` ter-Rupiah, `{link_bayar}` benar.
- [ ] Struk lunas terkirim otomatis saat pembayaran sukses.
- [ ] Kirim ulang manual tersedia dari UI.
