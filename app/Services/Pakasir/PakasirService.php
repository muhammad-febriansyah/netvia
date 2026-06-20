<?php

namespace App\Services\Pakasir;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

/**
 * Thin abstraction over the Pakasir QRIS payment gateway.
 *
 * Pakasir hosts the QRIS payment page itself; we build a tokenized payment URL
 * and confirm settlement by re-fetching the transaction detail endpoint rather
 * than trusting the webhook payload. Endpoint shapes follow Pakasir's public
 * docs and are isolated here so they can be adjusted in one place.
 */
class PakasirService
{
    /**
     * Build the hosted QRIS payment URL for an amount + order id.
     */
    public function paymentUrl(int $amount, string $orderId): string
    {
        $base = rtrim((string) config('services.pakasir.base_url'), '/');
        $project = (string) config('services.pakasir.project');

        $query = http_build_query(array_filter([
            'order_id' => $orderId,
            'qris_only' => 1,
            'redirect' => config('services.pakasir.callback_url'),
        ]));

        return "{$base}/pay/{$project}/{$amount}?{$query}";
    }

    /**
     * Open a QRIS transaction. Returns the data needed to display/poll it.
     *
     * @return array{payment_url: string, qr_string: string|null, expired_at: Carbon}
     */
    public function createTransaction(int $amount, string $orderId): array
    {
        return [
            'payment_url' => $this->paymentUrl($amount, $orderId),
            'qr_string' => null,
            'expired_at' => Carbon::now()->addMinutes((int) config('services.pakasir.expiry_minutes', 60)),
        ];
    }

    /**
     * Fetch a transaction's current state from Pakasir.
     *
     * @return array<string, mixed>
     */
    public function fetchTransaction(int $amount, string $orderId): array
    {
        $base = rtrim((string) config('services.pakasir.base_url'), '/');

        $response = Http::acceptJson()->get("{$base}/api/transactiondetail", [
            'project' => config('services.pakasir.project'),
            'api_key' => config('services.pakasir.api_key'),
            'amount' => $amount,
            'order_id' => $orderId,
        ]);

        return $response->successful() ? (array) $response->json() : [];
    }

    /**
     * Whether a transaction detail payload represents a settled payment.
     *
     * @param  array<string, mixed>  $detail
     */
    public function isPaid(array $detail): bool
    {
        $transaction = $detail['transaction'] ?? $detail;

        return ($transaction['status'] ?? null) === 'completed';
    }

    /**
     * Confirm settlement authoritatively by re-fetching from Pakasir, regardless
     * of what the (untrusted) webhook payload claims.
     */
    public function confirmPaid(int $amount, string $orderId): bool
    {
        return $this->isPaid($this->fetchTransaction($amount, $orderId));
    }

    /**
     * Reference returned by Pakasir for a settled transaction, if present.
     *
     * @param  array<string, mixed>  $detail
     */
    public function reference(array $detail): ?string
    {
        $transaction = $detail['transaction'] ?? $detail;

        return $transaction['payment_method'] ?? ($transaction['reference'] ?? null);
    }
}
