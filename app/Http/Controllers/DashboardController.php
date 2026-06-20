<?php

namespace App\Http\Controllers;

use App\Enums\PelangganStatus;
use App\Enums\TagihanStatus;
use App\Models\Pelanggan;
use App\Models\Tagihan;
use App\Repositories\LaporanRepository;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private LaporanRepository $laporan) {}

    /**
     * Show the dashboard with headline billing stats for the current month.
     */
    public function index(): View
    {
        $periode = Carbon::now('Asia/Jakarta')->startOfMonth();

        $bulanIni = Tagihan::query()
            ->whereYear('periode', $periode->year)
            ->whereMonth('periode', $periode->month);

        $stats = [
            'pelanggan_aktif' => Pelanggan::where('status', PelangganStatus::Aktif)->count(),
            'tagihan_bulan_ini' => (clone $bulanIni)->count(),
            'tagihan_bulan_ini_nominal' => (int) (clone $bulanIni)->sum('jumlah'),
            'dibayar' => (clone $bulanIni)->where('status', TagihanStatus::Paid)->count(),
            'outstanding_nominal' => (int) Tagihan::query()
                ->whereIn('status', [TagihanStatus::Unpaid, TagihanStatus::Overdue])
                ->sum('jumlah'),
        ];

        $trend = $this->laporan->monthlyRevenueTrend(6);
        $dueSoon = $this->laporan->dueSoon();
        $overdue = $this->laporan->overdue();

        return view('dashboard', compact('stats', 'periode', 'trend', 'dueSoon', 'overdue'));
    }
}
