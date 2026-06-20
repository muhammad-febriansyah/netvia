<?php

namespace App\Actions\Tagihan;

use App\Models\Pelanggan;
use App\Models\Tagihan;
use App\Repositories\TagihanRepository;
use App\Services\BillingService;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GenerateTagihanAction
{
    public function __construct(
        private TagihanRepository $tagihans,
        private BillingService $billing,
    ) {}

    /**
     * Create a tagihan for the given pelanggan + period.
     *
     * Idempotent: returns null when a tagihan for (pelanggan, periode) already
     * exists, so it is safe to run repeatedly. Snapshots paket nama + harga.
     */
    public function execute(Pelanggan $pelanggan, CarbonInterface $periode): ?Tagihan
    {
        $periode = CarbonImmutable::parse($periode)->startOfMonth();
        $paket = $pelanggan->paket;

        if ($paket === null) {
            return null;
        }

        return DB::transaction(function () use ($pelanggan, $periode, $paket): ?Tagihan {
            if ($this->tagihans->existsForPeriode($pelanggan->id, $periode)) {
                return null;
            }

            $sequence = $this->tagihans->countForMonth($periode) + 1;
            $dueDate = $this->billing->dueDateFor($pelanggan, $periode);

            return $this->tagihans->create([
                'nomor_tagihan' => $this->billing->buildNomorTagihan($periode, $sequence),
                'pelanggan_id' => $pelanggan->id,
                'paket_id' => $paket->id,
                'periode' => $periode->format('Y-m-d'),
                'paket_nama' => $paket->nama,
                'harga' => $paket->harga,
                'jumlah' => $paket->harga,
                'tanggal_terbit' => CarbonImmutable::now()->format('Y-m-d'),
                'tanggal_jatuh_tempo' => $dueDate->format('Y-m-d'),
                'status' => 'unpaid',
                'public_token' => Str::random(48),
            ]);
        });
    }
}
