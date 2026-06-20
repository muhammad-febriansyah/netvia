<?php

namespace App\Events;

use App\Models\Pembayaran;
use App\Models\Tagihan;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Raised once a tagihan is settled (QRIS webhook or manual confirmation).
 *
 * The notifikasi module (09) listens to this to dispatch the `struk_lunas`
 * WhatsApp + Email messages; keeping it an event decouples payment from
 * notification delivery.
 */
class TagihanLunas
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Tagihan $tagihan,
        public Pembayaran $pembayaran,
    ) {}
}
