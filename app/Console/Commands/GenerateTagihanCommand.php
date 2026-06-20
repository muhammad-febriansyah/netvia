<?php

namespace App\Console\Commands;

use App\Actions\Tagihan\GenerateTagihanAction;
use App\Models\Pelanggan;
use App\Repositories\PelangganRepository;
use App\Repositories\SettingRepository;
use App\Services\BillingService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class GenerateTagihanCommand extends Command
{
    protected $signature = 'billing:generate';

    protected $description = 'Generate monthly tagihan for active pelanggan within the issuing window (idempotent).';

    public function handle(
        PelangganRepository $pelanggans,
        SettingRepository $settings,
        BillingService $billing,
        GenerateTagihanAction $generate,
    ): int {
        $today = CarbonImmutable::now('Asia/Jakarta')->startOfDay();
        $daysBefore = $settings->getInt('generate_hari_sebelum_jatuh_tempo', 7);
        $periode = $billing->currentPeriode($today);

        $created = 0;
        $skipped = 0;

        $pelanggans->activeWithPaket()->chunkById(200, function ($chunk) use (
            $today, $daysBefore, $periode, $billing, $generate, &$created, &$skipped
        ) {
            foreach ($chunk as $pelanggan) {
                /** @var Pelanggan $pelanggan */
                $dueDate = $billing->dueDateFor($pelanggan, $periode);

                if (! $billing->isWithinGenerationWindow($today, $dueDate, $daysBefore)) {
                    $skipped++;

                    continue;
                }

                $tagihan = $generate->execute($pelanggan, $periode);

                if ($tagihan === null) {
                    $skipped++;
                } else {
                    $created++;
                }
            }
        });

        $this->info("Tagihan dibuat: {$created}, dilewati: {$skipped} (periode {$periode->format('Y-m')}).");

        return self::SUCCESS;
    }
}
