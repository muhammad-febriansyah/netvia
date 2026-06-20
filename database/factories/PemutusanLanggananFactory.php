<?php

namespace Database\Factories;

use App\Models\Pelanggan;
use App\Models\PemutusanLangganan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PemutusanLangganan>
 */
class PemutusanLanggananFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pelanggan_id' => Pelanggan::factory(),
            'alasan' => fake()->sentence(),
            'foto' => 'pemutusan/'.fake()->uuid().'.jpg',
            'status' => 'pending',
            'catatan_admin' => null,
            'diproses_by' => null,
            'diproses_at' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'approved']);
    }
}
