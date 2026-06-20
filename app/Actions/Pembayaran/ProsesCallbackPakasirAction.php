<?php

namespace App\Actions\Pembayaran;

use App\Enums\PembayaranStatus;
use App\Models\Pembayaran;
use App\Repositories\PembayaranRepository;
use App\Services\Pakasir\PakasirService;

class ProsesCallbackPakasirAction
{
    public function __construct(
        private PembayaranRepository $pembayarans,
        private PakasirService $pakasir,
        private TandaiTagihanLunasAction $tandaiLunas,
    ) {}

    /**
     * Process a Pakasir webhook payload.
     *
     * Looks up the payment by order_id, re-confirms settlement directly with
     * Pakasir (never trusting the payload), then idempotently marks it paid.
     * Returns the settled payment, or null when the callback cannot be honored
     * (unknown order, or not actually paid yet).
     *
     * @param  array<string, mixed>  $payload
     */
    public function execute(array $payload): ?Pembayaran
    {
        $orderId = $payload['order_id'] ?? null;

        if (! is_string($orderId) || $orderId === '') {
            return null;
        }

        $pembayaran = $this->pembayarans->findByOrderId($orderId);

        if ($pembayaran === null) {
            return null;
        }

        if ($pembayaran->status === PembayaranStatus::Success) {
            return $pembayaran;
        }

        if (! $this->pakasir->confirmPaid((int) $pembayaran->jumlah_bayar, $orderId)) {
            return null;
        }

        $detail = $this->pakasir->fetchTransaction((int) $pembayaran->jumlah_bayar, $orderId);

        return $this->tandaiLunas->execute(
            $pembayaran,
            reference: $this->pakasir->reference($detail),
            rawCallback: $payload,
        );
    }
}
