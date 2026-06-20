<?php

namespace Database\Factories;

use App\Models\Paket;
use App\Models\Pelanggan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pelanggan>
 */
class PelangganFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kode_pelanggan' => 'PLG-'.str_pad((string) fake()->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'nama' => fake()->name(),
            'no_wa' => '628'.fake()->numerify('##########'),
            'email' => fake()->optional()->safeEmail(),
            'alamat' => fake()->optional()->address(),
            'paket_id' => Paket::factory(),
            'tanggal_aktivasi' => fake()->dateTimeBetween('-1 year', 'now'),
            'tgl_jatuh_tempo' => fake()->numberBetween(1, 28),
            'status' => 'aktif',
            'catatan' => fake()->optional()->sentence(),
        ];
    }

    public function nonaktif(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'nonaktif',
        ]);
    }

    public function isolir(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'isolir',
        ]);
    }
}
