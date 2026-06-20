<?php

namespace App\Actions\Pembayaran;

use App\Enums\PembayaranMetode;
use App\Enums\PembayaranStatus;
use App\Enums\TagihanStatus;
use App\Exceptions\TagihanNotPayableException;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\User;
use App\Repositories\PembayaranRepository;
use Illuminate\Support\Facades\DB;

class KonfirmasiPembayaranManualAction
{
    public function __construct(
        private PembayaranRepository $pembayarans,
        private TandaiTagihanLunasAction $tandaiLunas,
    ) {}

    /**
     * Record a manual (transfer/cash) settlement and mark the tagihan paid.
     *
     * @throws TagihanNotPayableException when the tagihan is not payable.
     */
    public function execute(
        Tagihan $tagihan,
        PembayaranMetode $metode,
        int $jumlahBayar,
        User $confirmedBy,
        ?string $buktiTransfer = null,
    ): Pembayaran {
        if (! in_array($tagihan->status, [TagihanStatus::Unpaid, TagihanStatus::Overdue], true)) {
            throw new TagihanNotPayableException('Tagihan ini tidak dapat dibayar.');
        }

        return DB::transaction(function () use ($tagihan, $metode, $jumlahBayar, $confirmedBy, $buktiTransfer): Pembayaran {
            $pembayaran = $this->pembayarans->create([
                'tagihan_id' => $tagihan->id,
                'metode' => $metode,
                'jumlah_bayar' => $jumlahBayar,
                'status' => PembayaranStatus::Pending,
                'dikonfirmasi_by' => $confirmedBy->id,
                'bukti_transfer' => $buktiTransfer,
            ]);

            return $this->tandaiLunas->execute($pembayaran);
        });
    }
}
