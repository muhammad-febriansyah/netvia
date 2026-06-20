<?php

namespace App\Http\Controllers;

use App\Actions\Pelanggan\DeletePelangganAction;
use App\Actions\Pelanggan\GenerateKodePelangganAction;
use App\Actions\Pelanggan\StorePelangganAction;
use App\Actions\Pelanggan\UpdatePelangganAction;
use App\Enums\PelangganStatus;
use App\Http\Requests\PelangganRequest;
use App\Models\Pelanggan;
use App\Repositories\PaketRepository;
use App\Repositories\PelangganRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class PelangganController extends Controller
{
    public function __construct(
        private PelangganRepository $pelanggans,
        private PaketRepository $pakets,
    ) {}

    public function index(): View
    {
        return view('pelanggan.index', [
            'pakets' => $this->pakets->activeForSelect()->get(['id', 'nama']),
        ]);
    }

    /**
     * Server-side DataTable data endpoint.
     */
    public function data(Request $request): JsonResponse
    {
        $query = $this->pelanggans->dataTableQuery(
            $request->only(['paket_id', 'status']),
        );

        return DataTables::eloquent($query)
            ->addColumn('paket_nama', fn (Pelanggan $p): string => $p->paket?->nama ?? '-')
            ->addColumn('status_badge', fn (Pelanggan $p): string => $this->statusBadge($p))
            ->addColumn('aksi', fn (Pelanggan $p): string => $this->actionButtons($p))
            ->rawColumns(['status_badge', 'aksi'])
            ->toJson();
    }

    public function create(GenerateKodePelangganAction $generateKode): View
    {
        return view('pelanggan.create', [
            'kodePelanggan' => $generateKode->execute(),
            'pakets' => $this->pakets->activeForSelect()->get(['id', 'nama']),
        ]);
    }

    public function store(PelangganRequest $request, StorePelangganAction $action): RedirectResponse
    {
        $action->execute($request->validated());

        return redirect()
            ->route('pelanggan.index')
            ->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    public function show(Pelanggan $pelanggan): View
    {
        $pelanggan->load('paket');

        return view('pelanggan.show', [
            'pelanggan' => $pelanggan,
            'ringkasan' => $this->pelanggans->ringkasanTagihan($pelanggan),
        ]);
    }

    public function edit(Pelanggan $pelanggan): View
    {
        return view('pelanggan.edit', [
            'pelanggan' => $pelanggan,
            'pakets' => $this->pakets->activeForSelect()
                ->orWhere('id', $pelanggan->paket_id)
                ->get(['id', 'nama']),
        ]);
    }

    public function update(PelangganRequest $request, Pelanggan $pelanggan, UpdatePelangganAction $action): RedirectResponse
    {
        $action->execute($pelanggan, $request->validated());

        return redirect()
            ->route('pelanggan.index')
            ->with('success', 'Pelanggan berhasil diperbarui.');
    }

    public function destroy(Pelanggan $pelanggan, DeletePelangganAction $action): JsonResponse
    {
        $action->execute($pelanggan);

        return response()->json([
            'success' => true,
            'message' => 'Pelanggan berhasil dihapus.',
        ]);
    }

    public function activate(Pelanggan $pelanggan, UpdatePelangganAction $action): RedirectResponse
    {
        $action->execute($pelanggan, ['status' => PelangganStatus::Aktif->value]);

        return back()->with('success', 'Pelanggan berhasil diaktifkan.');
    }

    private function statusBadge(Pelanggan $pelanggan): string
    {
        $classes = $pelanggan->status->badgeClass();
        $label = e($pelanggan->status->label());

        return "<span class=\"px-2 py-1 text-xs font-medium rounded-full {$classes}\">{$label}</span>";
    }

    private function actionButtons(Pelanggan $pelanggan): string
    {
        $buttons = '';
        $showUrl = route('pelanggan.show', $pelanggan);

        $buttons .= <<<HTML
            <a href="{$showUrl}" class="btn-act btn-act--view">
                <i data-lucide="eye"></i> Detail
            </a>
        HTML;

        if (auth()->user()?->can('pelanggan.update')) {
            $editUrl = route('pelanggan.edit', $pelanggan);
            $buttons .= <<<HTML
                <a href="{$editUrl}" class="btn-act btn-act--edit">
                    <i data-lucide="pencil"></i> Edit
                </a>
            HTML;
        }

        if (auth()->user()?->can('pelanggan.delete')) {
            $buttons .= <<<HTML
                <button type="button" class="btn-delete btn-act btn-act--delete" data-id="{$pelanggan->id}">
                    <i data-lucide="trash-2"></i> Hapus
                </button>
            HTML;
        }

        return "<div class=\"flex items-center gap-2\">{$buttons}</div>";
    }
}
