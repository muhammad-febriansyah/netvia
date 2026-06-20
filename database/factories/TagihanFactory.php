<?php

namespace Database\Factories;

use App\Models\Paket;
use App\Models\Pelanggan;
use App\Models\Tagihan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tagihan>
 */
class TagihanFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $periode = Carbon::parse(fake()->dateTimeBetween('-6 months', 'now'))->startOfMonth();
        $harga = fake()->numberBetween(100, 500) * 1000;
        $jatuhTempoHari = fake()->numberBetween(1, 28);

        return [
            'nomor_tagihan' => 'INV/'.$periode->format('Ym').'/'.str_pad((string) fake()->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'pelanggan_id' => Pelanggan::factory(),
            'paket_id' => Paket::factory(),
            'periode' => $periode,
            'paket_nama' => 'Home '.fake()->randomElement([10, 20, 50]).' Mbps',
            'harga' => $harga,
            'jumlah' => $harga,
            'tanggal_terbit' => $periode->copy(),
            'tanggal_jatuh_tempo' => $periode->copy()->addDays($jatuhTempoHari - 1),
            'status' => 'unpaid',
            'paid_at' => null,
            'public_token' => Str::random(40),
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'overdue',
        ]);
    }

    public function void(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'void',
        ]);
    }
}
