<?php

namespace App\Repositories;

use App\Enums\PembayaranStatus;
use App\Enums\TagihanStatus;
use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class LaporanRepository
{
    /**
     * Successful payments within a date range (revenue report).
     *
     * @return Builder<Pembayaran>
     */
    public function pendapatanQuery(Carbon $from, Carbon $to): Builder
    {
        return Pembayaran::query()
            ->with('tagihan.pelanggan')
            ->where('status', PembayaranStatus::Success)
            ->whereDate('dibayar_at', '>=', $from->format('Y-m-d'))
            ->whereDate('dibayar_at', '<=', $to->format('Y-m-d'))
            ->latest('dibayar_at');
    }

    /**
     * Outstanding bills (unpaid + overdue) — arrears report.
     *
     * @return Builder<Tagihan>
     */
    public function tunggakanQuery(?Carbon $periode = null): Builder
    {
        return Tagihan::query()
            ->with('pelanggan')
            ->whereIn('status', [TagihanStatus::Unpaid, TagihanStatus::Overdue])
            ->when($periode, fn (Builder $q) => $q
                ->whereYear('periode', $periode->year)
                ->whereMonth('periode', $periode->month))
            ->orderBy('tanggal_jatuh_tempo');
    }

    /**
     * All payments within a date range (payment history report).
     *
     * @return Builder<Pembayaran>
     */
    public function pembayaranQuery(Carbon $from, Carbon $to, ?PembayaranStatus $status = null): Builder
    {
        return Pembayaran::query()
            ->with('tagihan.pelanggan')
            ->whereDate('created_at', '>=', $from->format('Y-m-d'))
            ->whereDate('created_at', '<=', $to->format('Y-m-d'))
            ->when($status, fn (Builder $q) => $q->where('status', $status))
            ->latest('created_at');
    }

    /**
     * Customer recap with package and outstanding balance.
     *
     * @return Builder<Pelanggan>
     */
    public function pelangganQuery(): Builder
    {
        return Pelanggan::query()
            ->with('paket')
            ->withSum(['tagihans as tunggakan_total' => fn ($q) => $q
                ->whereIn('status', [TagihanStatus::Unpaid->value, TagihanStatus::Overdue->value]),
            ], 'jumlah')
            ->orderBy('nama');
    }

    /**
     * Monthly revenue totals for the last N months (oldest first).
     *
     * @return list<array{label: string, periode: string, total: int}>
     */
    public function monthlyRevenueTrend(int $months = 6): array
    {
        $trend = [];
        $cursor = Carbon::now('Asia/Jakarta')->startOfMonth()->subMonths($months - 1);

        for ($i = 0; $i < $months; $i++) {
            $month = $cursor->copy()->addMonths($i);

            $total = (int) Pembayaran::query()
                ->where('status', PembayaranStatus::Success)
                ->whereYear('dibayar_at', $month->year)
                ->whereMonth('dibayar_at', $month->month)
                ->sum('jumlah_bayar');

            $trend[] = [
                'label' => $month->locale('id')->isoFormat('MMM Y'),
                'periode' => $month->format('Y-m'),
                'total' => $total,
            ];
        }

        return $trend;
    }

    /**
     * Bills due within the next $days days (still unpaid), soonest first.
     *
     * @return Collection<int, Tagihan>
     */
    public function dueSoon(int $days = 7, int $limit = 5)
    {
        $today = Carbon::now('Asia/Jakarta')->startOfDay();

        return Tagihan::query()
            ->with('pelanggan')
            ->where('status', TagihanStatus::Unpaid)
            ->whereDate('tanggal_jatuh_tempo', '>=', $today->format('Y-m-d'))
            ->whereDate('tanggal_jatuh_tempo', '<=', $today->copy()->addDays($days)->format('Y-m-d'))
            ->orderBy('tanggal_jatuh_tempo')
            ->limit($limit)
            ->get();
    }

    /**
     * Overdue bills needing follow-up, oldest due first.
     *
     * @return Collection<int, Tagihan>
     */
    public function overdue(int $limit = 5)
    {
        return Tagihan::query()
            ->with('pelanggan')
            ->where('status', TagihanStatus::Overdue)
            ->orderBy('tanggal_jatuh_tempo')
            ->limit($limit)
            ->get();
    }
}
