<?php

namespace App\Http\Controllers;

use App\Actions\Paket\DeletePaketAction;
use App\Actions\Paket\StorePaketAction;
use App\Actions\Paket\TogglePaketStatusAction;
use App\Actions\Paket\UpdatePaketAction;
use App\Exceptions\PaketInUseException;
use App\Http\Requests\PaketRequest;
use App\Models\Paket;
use App\Repositories\PaketRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class PaketController extends Controller
{
    public function __construct(private PaketRepository $pakets) {}

    public function index(): View
    {
        return view('paket.index');
    }

    /**
     * Server-side DataTable data endpoint.
     */
    public function data(Request $request): JsonResponse
    {
        $query = $this->pakets->dataTableQuery();

        return DataTables::eloquent($query)
            ->editColumn('harga', fn (Paket $paket): string => rupiah($paket->harga))
            ->addColumn('status', fn (Paket $paket): string => $this->statusBadge($paket))
            ->addColumn('aksi', fn (Paket $paket): string => $this->actionButtons($paket))
            ->rawColumns(['status', 'aksi'])
            ->toJson();
    }

    public function create(): View
    {
        return view('paket.create');
    }

    public function store(PaketRequest $request, StorePaketAction $action): RedirectResponse
    {
        $action->execute($request->validatedData());

        return redirect()
            ->route('paket.index')
            ->with('success', 'Paket berhasil ditambahkan.');
    }

    public function edit(Paket $paket): View
    {
        return view('paket.edit', compact('paket'));
    }

    public function update(PaketRequest $request, Paket $paket, UpdatePaketAction $action): RedirectResponse
    {
        $action->execute($paket, $request->validatedData());

        return redirect()
            ->route('paket.index')
            ->with('success', 'Paket berhasil diperbarui.');
    }

    public function destroy(Paket $paket, DeletePaketAction $action): JsonResponse
    {
        try {
            $action->execute($paket);
        } catch (PaketInUseException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Paket berhasil dihapus.',
        ]);
    }

    public function toggle(Paket $paket, TogglePaketStatusAction $action): JsonResponse
    {
        $paket = $action->execute($paket);

        return response()->json([
            'success' => true,
            'message' => $paket->is_active ? 'Paket diaktifkan.' : 'Paket dinonaktifkan.',
            'data' => ['is_active' => $paket->is_active],
        ]);
    }

    private function statusBadge(Paket $paket): string
    {
        return $paket->is_active
            ? '<span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">Aktif</span>'
            : '<span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-600">Nonaktif</span>';
    }

    private function actionButtons(Paket $paket): string
    {
        $buttons = '';

        if (auth()->user()?->can('paket.update')) {
            $editUrl = route('paket.edit', $paket);
            $toggleLabel = $paket->is_active ? 'Nonaktifkan' : 'Aktifkan';
            $toggleIcon = $paket->is_active ? 'toggle-right' : 'toggle-left';
            $buttons .= <<<HTML
                <a href="{$editUrl}" class="btn-act btn-act--edit">
                    <i data-lucide="pencil"></i> Edit
                </a>
                <button type="button" class="btn-toggle btn-act btn-act--neutral" data-id="{$paket->id}" title="{$toggleLabel}">
                    <i data-lucide="{$toggleIcon}"></i> {$toggleLabel}
                </button>
            HTML;
        }

        if (auth()->user()?->can('paket.delete')) {
            $buttons .= <<<HTML
                <button type="button" class="btn-delete btn-act btn-act--delete" data-id="{$paket->id}">
                    <i data-lucide="trash-2"></i> Hapus
                </button>
            HTML;
        }

        return "<div class=\"flex items-center gap-2\">{$buttons}</div>";
    }
}
