<?php

namespace App\Http\Controllers;

use App\Actions\Pemutusan\ProsesPemutusanAction;
use App\Enums\PemutusanStatus;
use App\Models\PemutusanLangganan;
use App\Repositories\PemutusanLanggananRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Yajra\DataTables\Facades\DataTables;

class PemutusanController extends Controller
{
    public function __construct(private PemutusanLanggananRepository $pemutusan) {}

    public function index(): View
    {
        return view('pemutusan.index', ['statuses' => PemutusanStatus::cases()]);
    }

    public function data(Request $request): JsonResponse
    {
        $query = $this->pemutusan->dataTableQuery($request->only('status'));

        return DataTables::eloquent($query)
            ->editColumn('created_at', fn (PemutusanLangganan $p): string => $p->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y H:i'))
            ->addColumn('pelanggan', fn (PemutusanLangganan $p): string => e($p->pelanggan?->nama ?? '-'))
            ->addColumn('status_badge', fn (PemutusanLangganan $p): string => '<span class="rounded-full px-2 py-1 text-xs font-medium '.$p->status->badgeClass().'">'.e($p->status->label()).'</span>')
            ->addColumn('aksi', fn (PemutusanLangganan $p): string => $this->actionButtons($p))
            ->rawColumns(['status_badge', 'aksi'])
            ->toJson();
    }

    public function approve(Request $request, PemutusanLangganan $pemutusan, ProsesPemutusanAction $action): JsonResponse
    {
        try {
            $action->execute($pemutusan, PemutusanStatus::Approved, $request->user());
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true, 'message' => 'Pengajuan disetujui. Pelanggan dinonaktifkan.']);
    }

    public function reject(Request $request, PemutusanLangganan $pemutusan, ProsesPemutusanAction $action): JsonResponse
    {
        $data = $request->validate(
            ['catatan' => ['required', 'string', 'max:255']],
            ['catatan.required' => 'Alasan penolakan wajib diisi.'],
        );

        try {
            $action->execute($pemutusan, PemutusanStatus::Rejected, $request->user(), $data['catatan']);
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true, 'message' => 'Pengajuan ditolak.']);
    }

    private function actionButtons(PemutusanLangganan $pemutusan): string
    {
        $fotoUrl = e(Storage::url($pemutusan->foto));
        $buttons = <<<HTML
            <a href="{$fotoUrl}" target="_blank" class="btn-act btn-act--view"><i data-lucide="image"></i> Foto</a>
        HTML;

        if ($pemutusan->status === PemutusanStatus::Pending) {
            $buttons .= <<<HTML
                <button type="button" class="btn-approve btn-act btn-act--send" data-id="{$pemutusan->id}"><i data-lucide="check"></i> Setujui</button>
                <button type="button" class="btn-reject btn-act btn-act--delete" data-id="{$pemutusan->id}"><i data-lucide="x"></i> Tolak</button>
            HTML;
        }

        return "<div class=\"flex items-center justify-end gap-2\">{$buttons}</div>";
    }
}
