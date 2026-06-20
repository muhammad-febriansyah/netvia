<?php

namespace Database\Factories;

use App\Models\MessageTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MessageTemplate>
 */
class MessageTemplateFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'jenis' => fake()->randomElement(['invoice_baru', 'reminder_h3', 'reminder_due', 'reminder_overdue', 'struk_lunas']),
            'channel' => fake()->randomElement(['whatsapp', 'email']),
            'subject' => fake()->optional()->sentence(4),
            'body' => 'Halo {nama}, tagihan {nomor_tagihan} sebesar {jumlah} jatuh tempo {jatuh_tempo}. Bayar: {link_bayar}',
            'is_active' => true,
        ];
    }
}
