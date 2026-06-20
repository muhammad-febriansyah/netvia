<?php

namespace Database\Seeders;

use App\Models\MessageTemplate;
use Illuminate\Database\Seeder;

class MessageTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $waBodies = [
            'invoice_baru' => "Halo {nama}, tagihan internet {nomor_tagihan} periode {periode} sebesar {jumlah} telah terbit. Jatuh tempo {jatuh_tempo}.\nBayar via QRIS: {link_bayar}\nTerima kasih 🙏",
            'reminder_h3' => "Halo {nama}, pengingat tagihan {nomor_tagihan} sebesar {jumlah} akan jatuh tempo pada {jatuh_tempo} (3 hari lagi).\nBayar: {link_bayar}\nTerima kasih 🙏",
            'reminder_due' => "Halo {nama}, tagihan {nomor_tagihan} periode {periode} sebesar {jumlah} jatuh tempo hari ini ({jatuh_tempo}).\nBayar mudah via QRIS: {link_bayar}\nTerima kasih 🙏",
            'reminder_overdue' => "Halo {nama}, tagihan {nomor_tagihan} sebesar {jumlah} telah melewati jatuh tempo ({jatuh_tempo}). Mohon segera melunasi.\nBayar: {link_bayar}\nTerima kasih 🙏",
            'struk_lunas' => "Halo {nama}, pembayaran tagihan {nomor_tagihan} sebesar {jumlah} telah kami terima. LUNAS ✅\nStruk: {link_bayar}\nTerima kasih 🙏",
        ];

        $emailSubjects = [
            'invoice_baru' => 'Tagihan Internet Baru - {nomor_tagihan}',
            'reminder_h3' => 'Pengingat Tagihan - {nomor_tagihan}',
            'reminder_due' => 'Tagihan Jatuh Tempo Hari Ini - {nomor_tagihan}',
            'reminder_overdue' => 'Tagihan Lewat Tempo - {nomor_tagihan}',
            'struk_lunas' => 'Pembayaran Diterima - {nomor_tagihan}',
        ];

        foreach ($waBodies as $jenis => $body) {
            MessageTemplate::firstOrCreate(
                ['jenis' => $jenis, 'channel' => 'whatsapp'],
                ['subject' => null, 'body' => $body, 'is_active' => true],
            );

            MessageTemplate::firstOrCreate(
                ['jenis' => $jenis, 'channel' => 'email'],
                ['subject' => $emailSubjects[$jenis], 'body' => $body, 'is_active' => true],
            );
        }
    }
}
