<?php

namespace Database\Factories;

use App\Models\Pembayaran;
use App\Models\Tagihan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Pembayaran>
 */
class PembayaranFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tagihan_id' => Tagihan::factory(),
            'metode' => 'qris_pakasir',
            'jumlah_bayar' => fake()->numberBetween(100, 500) * 1000,
            'status' => 'pending',
            'pakasir_order_id' => 'ORD-'.Str::upper(Str::random(12)),
            'pakasir_reference' => null,
            'qr_string' => fake()->optional()->text(50),
            'payment_url' => null,
            'expired_at' => now()->addHours(1),
            'bukti_transfer' => null,
            'dikonfirmasi_by' => null,
            'dibayar_at' => null,
            'raw_callback' => null,
        ];
    }

    public function success(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'success',
            'dibayar_at' => now(),
        ]);
    }

    public function manual(): static
    {
        return $this->state(fn (array $attributes) => [
            'metode' => 'transfer_manual',
            'pakasir_order_id' => null,
            'qr_string' => null,
            'expired_at' => null,
        ]);
    }
}
