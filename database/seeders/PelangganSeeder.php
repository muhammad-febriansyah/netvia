<?php

namespace Database\Seeders;

use App\Models\Paket;
use App\Models\Pelanggan;
use Illuminate\Database\Seeder;

class PelangganSeeder extends Seeder
{
    /**
     * Run the database seeds. Dev sample data only.
     */
    public function run(): void
    {
        $pakets = Paket::all();

        if ($pakets->isEmpty()) {
            return;
        }

        Pelanggan::factory()
            ->count(25)
            ->recycle($pakets)
            ->create();
    }
}
