<?php

namespace Database\Factories;

use App\Models\Paket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Paket>
 */
class PaketFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $kecepatan = fake()->randomElement([10, 20, 30, 50, 100]);

        return [
            'nama' => "Home {$kecepatan} Mbps",
            'kecepatan_mbps' => $kecepatan,
            'harga' => fake()->numberBetween(100, 500) * 1000,
            'deskripsi' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
