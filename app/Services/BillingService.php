<?php

namespace App\Services;

use App\Models\Pelanggan;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

class BillingService
{
    /**
     * Billing period for a given day: the first day of that day's month.
     */
    public function currentPeriode(CarbonInterface $today): CarbonImmutable
    {
        return CarbonImmutable::parse($today)->startOfMonth();
    }

    /**
     * Full due date for a pelanggan in a given period:
     * the period's month on the pelanggan's tgl_jatuh_tempo (1–28).
     */
    public function dueDateFor(Pelanggan $pelanggan, CarbonInterface $periode): CarbonImmutable
    {
        return CarbonImmutable::parse($periode)
            ->startOfMonth()
            ->addDays($pelanggan->tgl_jatuh_tempo - 1);
    }

    /**
     * Whether the issuing window is open: today is within N days before the due date.
     */
    public function isWithinGenerationWindow(CarbonInterface $today, CarbonInterface $dueDate, int $daysBefore): bool
    {
        $windowOpensAt = CarbonImmutable::parse($dueDate)->subDays($daysBefore);

        return CarbonImmutable::parse($today)->startOfDay()->greaterThanOrEqualTo($windowOpensAt->startOfDay());
    }

    /**
     * Build nomor_tagihan: INV/{YYYYMM}/{6-digit monthly sequence}.
     */
    public function buildNomorTagihan(CarbonInterface $periode, int $sequence): string
    {
        return sprintf('INV/%s/%06d', CarbonImmutable::parse($periode)->format('Ym'), $sequence);
    }
}
