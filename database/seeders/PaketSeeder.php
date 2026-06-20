<?php

namespace Database\Seeders;

use App\Models\Paket;
use Illuminate\Database\Seeder;

class PaketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pakets = [
            ['nama' => 'Home 10 Mbps', 'kecepatan_mbps' => 10, 'harga' => 150000, 'deskripsi' => 'Cocok untuk browsing & media sosial.'],
            ['nama' => 'Home 20 Mbps', 'kecepatan_mbps' => 20, 'harga' => 200000, 'deskripsi' => 'Streaming HD untuk keluarga kecil.'],
            ['nama' => 'Home 50 Mbps', 'kecepatan_mbps' => 50, 'harga' => 350000, 'deskripsi' => 'Streaming 4K & gaming.'],
            ['nama' => 'Business 100 Mbps', 'kecepatan_mbps' => 100, 'harga' => 750000, 'deskripsi' => 'Untuk kantor & usaha.'],
        ];

        foreach ($pakets as $paket) {
            Paket::firstOrCreate(['nama' => $paket['nama']], $paket + ['is_active' => true]);
        }
    }
}
