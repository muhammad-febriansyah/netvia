<?php

namespace App\Http\Controllers;

use App\Actions\Tagihan\GenerateTagihanAction;
use App\Actions\Tagihan\VoidTagihanAction;
use App\Enums\NotifikasiJenis;
use App\Enums\TagihanStatus;
use App\Exceptions\TagihanNotVoidableException;
use App\Http\Requests\GenerateManualTagihanRequest;
use App\Http\Requests\VoidTagihanRequest;
use App\Models\Pelanggan;
use App\Models\Tagihan;
use App\Repositories\TagihanRepository;
use App\Services\NotifikasiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class TagihanController extends Controller
{
    public function __construct(private TagihanRepository $tagihans) {}

    public function index(): View
    {
        return view('tagihan.index', [
            'statuses' => TagihanStatus::cases(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $query = $this->tagihans->dataTableQuery($request->only(['periode', 'status', 'pelanggan_id']));

        return DataTables::eloquent($query)
            ->addColumn('pelanggan', fn (Tagihan $t): string => e($t->pelanggan?->nama ?? '-'))
            ->editColumn('periode', fn (Tagihan $t): string => $t->periode->translatedFormat('M Y'))
            ->editColumn('jumlah', fn (Tagihan $t): string => rupiah($t->jumlah))
            ->editColumn('tanggal_jatuh_tempo', fn (Tagihan $t): string => $t->tanggal_jatuh_tempo->translatedFormat('d M Y'))
            ->addColumn('status_badge', fn (Tagihan $t): string => $this->statusBadge($t))
            ->addColumn('aksi', fn (Tagihan $t): string => $this->actionButtons($t))
            ->rawColumns(['status_badge', 'aksi'])
            ->filterColumn('pelanggan', function ($query, $keyword) {
                $query->whereHas('pelanggan', fn ($q) => $q->where('nama', 'like', "%{$keyword}%"));
            })
            ->toJson();
    }

    public function show(Tagihan $tagihan): View
    {
        $tagihan->load([
            'pelanggan',
            'pembayarans' => fn ($q) => $q->with('dikonfirmasiBy:id,name')->latest(),
            'notifikasiLogs' => fn ($q) => $q->latest(),
        ]);

        return view('tagihan.show', compact('tagihan'));
    }

    public function generateManual(GenerateManualTagihanRequest $request, GenerateTagihanAction $action): RedirectResponse
    {
        $pelanggan = Pelanggan::findOrFail($request->integer('pelanggan_id'));
        $periode = $request->filled('periode')
            ? Carbon::parse($request->string('periode')->value().'-01')
            : Carbon::now('Asia/Jakarta')->startOfMonth();

        $tagihan = $action->execute($pelanggan, $periode);

        if ($tagihan === null) {
            return back()->with('error', 'Tagihan untuk pelanggan & periode ini sudah ada atau paket tidak valid.');
        }

        return redirect()
            ->route('tagihan.show', $tagihan)
            ->with('success', 'Tagihan '.$tagihan->nomor_tagihan.' berhasil dibuat.');
    }

    public function void(VoidTagihanRequest $request, Tagihan $tagihan, VoidTagihanAction $action): JsonResponse
    {
        try {
            $action->execute($tagihan, $request->string('alasan')->value());
        } catch (TagihanNotVoidableException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true, 'message' => 'Tagihan berhasil dibatalkan.']);
    }

    public function kirimReminder(Tagihan $tagihan, NotifikasiService $notifikasi): JsonResponse
    {
        if (in_array($tagihan->status, [TagihanStatus::Paid, TagihanStatus::Void], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Reminder hanya untuk tagihan belum lunas.',
            ], 422);
        }

        $jenis = $tagihan->status === TagihanStatus::Overdue
            ? NotifikasiJenis::ReminderOverdue
            : NotifikasiJenis::ReminderDue;

        $notifikasi->kirim($tagihan, $jenis, force: true);

        return response()->json(['success' => true, 'message' => 'Reminder sedang dikirim.']);
    }

    private function statusBadge(Tagihan $tagihan): string
    {
        return '<span class="rounded-full px-2 py-1 text-xs font-medium '.$tagihan->status->badgeClass().'">'
            .e($tagihan->status->label()).'</span>';
    }

    private function actionButtons(Tagihan $tagihan): string
    {
        $url = route('tagihan.show', $tagihan);

        return <<<HTML
            <a href="{$url}" class="inline-flex items-center gap-1 text-brand">
                <i data-lucide="eye"></i> Detail
            </a>
        HTML;
    }
}
