<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Profil perusahaan
            'nama_perusahaan' => 'Netvia Net',
            'logo' => null,
            'alamat' => '',
            'no_telp' => '',
            'no_wa_cs' => '',
            'email_cs' => '',
            'footer_invoice' => 'Terima kasih telah berlangganan.',

            // Parameter billing
            'generate_hari_sebelum_jatuh_tempo' => '7',
            'reminder_overdue_hari' => '1,3,7',
            'kirim_invoice_baru' => '1',

            // Pembayaran manual
            'bank_nama' => '',
            'bank_no_rekening' => '',
            'bank_atas_nama' => '',
            'qris_aktif' => '1',

            // WhatsApp
            'wa_driver' => 'cloud_api',
            'wa_nomor_pengirim' => '',

            // Email
            'email_from_name' => 'Netvia Net',
            'email_from_address' => '',
        ];

        foreach ($settings as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
