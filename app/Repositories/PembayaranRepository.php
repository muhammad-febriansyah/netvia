<?php

namespace App\Repositories;

use App\Enums\PembayaranMetode;
use App\Enums\PembayaranStatus;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use Illuminate\Support\Carbon;

class PembayaranRepository
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Pembayaran
    {
        return Pembayaran::create($data);
    }

    public function findByOrderId(string $orderId): ?Pembayaran
    {
        return Pembayaran::query()
            ->with('tagihan')
            ->where('pakasir_order_id', $orderId)
            ->first();
    }

    /**
     * The newest still-valid pending QRIS payment for a tagihan, if any, so a
     * fresh QR is not generated while an unexpired one already exists.
     */
    public function activeQrisFor(Tagihan $tagihan): ?Pembayaran
    {
        return $tagihan->pembayarans()
            ->where('metode', PembayaranMetode::QrisPakasir)
            ->where('status', PembayaranStatus::Pending)
            ->where(function ($query) {
                $query->whereNull('expired_at')
                    ->orWhere('expired_at', '>', Carbon::now());
            })
            ->latest('id')
            ->first();
    }
}
