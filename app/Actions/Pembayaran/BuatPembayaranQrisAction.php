<?php

namespace App\Actions\Pembayaran;

use App\Enums\PembayaranMetode;
use App\Enums\PembayaranStatus;
use App\Enums\TagihanStatus;
use App\Exceptions\TagihanNotPayableException;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Repositories\PembayaranRepository;
use App\Services\Pakasir\PakasirService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BuatPembayaranQrisAction
{
    public function __construct(
        private PembayaranRepository $pembayarans,
        private PakasirService $pakasir,
    ) {}

    /**
     * Open (or reuse) a QRIS payment for a tagihan and return the pending record.
     *
     * @throws TagihanNotPayableException when the tagihan is not in a payable state.
     */
    public function execute(Tagihan $tagihan): Pembayaran
    {
        if (! in_array($tagihan->status, [TagihanStatus::Unpaid, TagihanStatus::Overdue], true)) {
            throw new TagihanNotPayableException('Tagihan ini tidak dapat dibayar.');
        }

        if ($existing = $this->pembayarans->activeQrisFor($tagihan)) {
            return $existing;
        }

        return DB::transaction(function () use ($tagihan): Pembayaran {
            $orderId = 'TGH-'.$tagihan->id.'-'.Str::lower(Str::random(12));
            $amount = (int) $tagihan->jumlah;

            $transaction = $this->pakasir->createTransaction($amount, $orderId);

            return $this->pembayarans->create([
                'tagihan_id' => $tagihan->id,
                'metode' => PembayaranMetode::QrisPakasir,
                'jumlah_bayar' => $amount,
                'status' => PembayaranStatus::Pending,
                'pakasir_order_id' => $orderId,
                'qr_string' => $transaction['qr_string'],
                'payment_url' => $transaction['payment_url'],
                'expired_at' => $transaction['expired_at'],
            ]);
        });
    }
}
