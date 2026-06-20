<?php

namespace Database\Factories;

use App\Models\NotifikasiLog;
use App\Models\Pelanggan;
use App\Models\Tagihan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotifikasiLog>
 */
class NotifikasiLogFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $channel = fake()->randomElement(['whatsapp', 'email']);

        return [
            'tagihan_id' => Tagihan::factory(),
            'pelanggan_id' => Pelanggan::factory(),
            'channel' => $channel,
            'jenis' => fake()->randomElement(['invoice_baru', 'reminder_h3', 'reminder_due', 'reminder_overdue', 'struk_lunas']),
            'status' => 'pending',
            'recipient' => $channel === 'whatsapp' ? '628'.fake()->numerify('##########') : fake()->safeEmail(),
            'payload' => fake()->optional()->sentence(),
            'error_message' => null,
            'sent_at' => null,
        ];
    }

    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'error_message' => fake()->sentence(),
        ]);
    }
}
