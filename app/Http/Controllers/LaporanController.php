<?php

namespace App\Http\Controllers;

use App\Enums\PembayaranStatus;
use App\Exports\PembayaranExport;
use App\Exports\PendapatanExport;
use App\Exports\RekapPelangganExport;
use App\Exports\TunggakanExport;
use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\Setting;
use App\Models\Tagihan;
use App\Repositories\LaporanRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;

class LaporanController extends Controller
{
    public function __construct(private LaporanRepository $laporan) {}

    /* ---------------------------------------------------------------- Pendapatan */

    public function pendapatan(): View
    {
        return view('laporan.pendapatan', $this->rangeDefaults());
    }

    public function pendapatanData(Request $request): JsonResponse
    {
        [$from, $to] = $this->range($request);

        return DataTables::eloquent($this->laporan->pendapatanQuery($from, $to))
            ->addColumn('tanggal', fn (Pembayaran $p): string => $p->dibayar_at?->locale('id')->isoFormat('D MMM Y HH:mm') ?? '-')
            ->addColumn('nomor_tagihan', fn (Pembayaran $p): string => $p->tagihan?->nomor_tagihan ?? '-')
            ->addColumn('pelanggan', fn (Pembayaran $p): string => $p->tagihan?->pelanggan?->nama ?? '-')
            ->addColumn('metode', fn (Pembayaran $p): string => $p->metode->label())
            ->editColumn('jumlah_bayar', fn (Pembayaran $p): string => rupiah($p->jumlah_bayar))
            ->toJson();
    }

    public function pendapatanExport(Request $request): BinaryFileResponse|Response
    {
        [$from, $to] = $this->range($request);
        $query = $this->laporan->pendapatanQuery($from, $to);
        $title = 'Laporan Pendapatan '.$from->format('d-m-Y').' s/d '.$to->format('d-m-Y');

        return $this->export(
            $request,
            fn () => new PendapatanExport($query),
            'laporan-pendapatan',
            fn () => Pdf::loadView('laporan.pdf.pendapatan', [
                'rows' => $query->get(),
                'title' => $title,
                'company' => $this->company(),
            ]),
        );
    }

    /* ---------------------------------------------------------------- Tunggakan */

    public function tunggakan(): View
    {
        return view('laporan.tunggakan');
    }

    public function tunggakanData(Request $request): JsonResponse
    {
        return DataTables::eloquent($this->laporan->tunggakanQuery($this->periode($request)))
            ->addColumn('pelanggan', fn (Tagihan $t): string => $t->pelanggan?->nama ?? '-')
            ->editColumn('periode', fn (Tagihan $t): string => $t->periode->locale('id')->isoFormat('MMMM Y'))
            ->editColumn('tanggal_jatuh_tempo', fn (Tagihan $t): string => $t->tanggal_jatuh_tempo->locale('id')->isoFormat('D MMM Y'))
            ->addColumn('status_label', fn (Tagihan $t): string => $t->status->label())
            ->editColumn('jumlah', fn (Tagihan $t): string => rupiah($t->jumlah))
            ->toJson();
    }

    public function tunggakanExport(Request $request): BinaryFileResponse|Response
    {
        $query = $this->laporan->tunggakanQuery($this->periode($request));

        return $this->export(
            $request,
            fn () => new TunggakanExport($query),
            'laporan-tunggakan',
            fn () => Pdf::loadView('laporan.pdf.tunggakan', [
                'rows' => $query->get(),
                'title' => 'Laporan Tunggakan',
                'company' => $this->company(),
            ]),
        );
    }

    /* ---------------------------------------------------------------- Pembayaran */

    public function pembayaran(): View
    {
        return view('laporan.pembayaran', $this->rangeDefaults());
    }

    public function pembayaranData(Request $request): JsonResponse
    {
        [$from, $to] = $this->range($request);
        $status = $this->status($request);

        return DataTables::eloquent($this->laporan->pembayaranQuery($from, $to, $status))
            ->addColumn('tanggal', fn (Pembayaran $p): string => $p->created_at?->locale('id')->isoFormat('D MMM Y HH:mm') ?? '-')
            ->addColumn('nomor_tagihan', fn (Pembayaran $p): string => $p->tagihan?->nomor_tagihan ?? '-')
            ->addColumn('pelanggan', fn (Pembayaran $p): string => $p->tagihan?->pelanggan?->nama ?? '-')
            ->addColumn('metode', fn (Pembayaran $p): string => $p->metode->label())
            ->addColumn('status_label', fn (Pembayaran $p): string => $p->status->label())
            ->editColumn('jumlah_bayar', fn (Pembayaran $p): string => rupiah($p->jumlah_bayar))
            ->toJson();
    }

    public function pembayaranExport(Request $request): BinaryFileResponse|Response
    {
        [$from, $to] = $this->range($request);
        $query = $this->laporan->pembayaranQuery($from, $to, $this->status($request));

        return $this->export(
            $request,
            fn () => new PembayaranExport($query),
            'laporan-pembayaran',
            fn () => Pdf::loadView('laporan.pdf.pembayaran', [
                'rows' => $query->get(),
                'title' => 'Laporan Riwayat Pembayaran',
                'company' => $this->company(),
            ]),
        );
    }

    /* ---------------------------------------------------------------- Rekap Pelanggan */

    public function pelanggan(): View
    {
        return view('laporan.pelanggan');
    }

    public function pelangganData(): JsonResponse
    {
        return DataTables::eloquent($this->laporan->pelangganQuery())
            ->addColumn('paket', fn (Pelanggan $p): string => $p->paket?->nama ?? '-')
            ->addColumn('status_label', fn (Pelanggan $p): string => $p->status->label())
            ->addColumn('tunggakan', fn (Pelanggan $p): string => rupiah((int) ($p->tunggakan_total ?? 0)))
            ->toJson();
    }

    public function pelangganExport(Request $request): BinaryFileResponse|Response
    {
        $query = $this->laporan->pelangganQuery();

        return $this->export(
            $request,
            fn () => new RekapPelangganExport($query),
            'rekap-pelanggan',
            fn () => Pdf::loadView('laporan.pdf.rekap-pelanggan', [
                'rows' => $query->get(),
                'title' => 'Rekap Pelanggan',
                'company' => $this->company(),
            ]),
        );
    }

    /* ---------------------------------------------------------------- Helpers */

    /**
     * Dispatch an Excel or PDF download based on the ?type query parameter.
     *
     * @param  callable(): FromQuery  $excel
     * @param  callable(): \Barryvdh\DomPDF\PDF  $pdf
     */
    private function export(Request $request, callable $excel, string $filename, callable $pdf): BinaryFileResponse|Response
    {
        if ($request->query('type') === 'pdf') {
            return $pdf()->download($filename.'.pdf');
        }

        return Excel::download($excel(), $filename.'.xlsx');
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function range(Request $request): array
    {
        $from = $request->filled('from')
            ? Carbon::parse($request->string('from')->toString())->startOfDay()
            : Carbon::now('Asia/Jakarta')->startOfMonth();

        $to = $request->filled('to')
            ? Carbon::parse($request->string('to')->toString())->endOfDay()
            : Carbon::now('Asia/Jakarta')->endOfMonth();

        return [$from, $to];
    }

    /**
     * @return array{from: string, to: string}
     */
    private function rangeDefaults(): array
    {
        return [
            'from' => Carbon::now('Asia/Jakarta')->startOfMonth()->format('Y-m-d'),
            'to' => Carbon::now('Asia/Jakarta')->endOfMonth()->format('Y-m-d'),
        ];
    }

    private function periode(Request $request): ?Carbon
    {
        return $request->filled('periode')
            ? Carbon::parse($request->string('periode')->toString().'-01')
            : null;
    }

    private function status(Request $request): ?PembayaranStatus
    {
        return $request->filled('status')
            ? PembayaranStatus::tryFrom($request->string('status')->toString())
            : null;
    }

    /**
     * @return array<string, ?string>
     */
    private function company(): array
    {
        return [
            'nama' => Setting::getValue('nama_perusahaan', config('app.name', 'Netvia')),
            'alamat' => Setting::getValue('alamat'),
        ];
    }
}
