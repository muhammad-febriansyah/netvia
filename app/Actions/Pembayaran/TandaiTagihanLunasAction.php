<?php

namespace App\Actions\Pembayaran;

use App\Enums\PembayaranStatus;
use App\Enums\TagihanStatus;
use App\Events\TagihanLunas;
use App\Models\Pembayaran;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Settle a payment and mark its tagihan as paid. Shared by the QRIS webhook and
 * the manual confirmation flow.
 *
 * Idempotent: if the payment is already marked success the tagihan is left
 * untouched and no duplicate event fires, so a doubled webhook is harmless.
 */
class TandaiTagihanLunasAction
{
    /**
     * @param  array<string, mixed>|null  $rawCallback
     */
    public function execute(Pembayaran $pembayaran, ?string $reference = null, ?array $rawCallback = null): Pembayaran
    {
        return DB::transaction(function () use ($pembayaran, $reference, $rawCallback): Pembayaran {
            $pembayaran = $pembayaran->newQuery()->lockForUpdate()->findOrFail($pembayaran->id);

            if ($pembayaran->status === PembayaranStatus::Success) {
                return $pembayaran;
            }

            $now = Carbon::now();

            $pembayaran->forceFill(array_filter([
                'status' => PembayaranStatus::Success,
                'dibayar_at' => $now,
                'pakasir_reference' => $reference,
                'raw_callback' => $rawCallback,
            ], fn ($value) => $value !== null))->save();

            $tagihan = $pembayaran->tagihan;

            if ($tagihan->status !== TagihanStatus::Paid) {
                $tagihan->forceFill([
                    'status' => TagihanStatus::Paid,
                    'paid_at' => $now,
                ])->save();
            }

            TagihanLunas::dispatch($tagihan, $pembayaran);

            return $pembayaran;
        });
    }
}
