<?php

namespace App\Http\Controllers;

use App\Enums\NotifikasiChannel;
use App\Enums\NotifikasiJenis;
use App\Enums\NotifikasiStatus;
use App\Http\Requests\MessageTemplateRequest;
use App\Models\MessageTemplate;
use App\Models\NotifikasiLog;
use App\Repositories\NotifikasiLogRepository;
use App\Services\NotifikasiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class NotifikasiController extends Controller
{
    public function __construct(private NotifikasiLogRepository $logs) {}

    public function index(): View
    {
        return view('notifikasi.index', [
            'channels' => NotifikasiChannel::cases(),
            'jenisList' => NotifikasiJenis::cases(),
            'statuses' => NotifikasiStatus::cases(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $query = $this->logs->dataTableQuery($request->only(['channel', 'jenis', 'status']));

        return DataTables::eloquent($query)
            ->editColumn('created_at', fn (NotifikasiLog $l): string => $l->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y H:i'))
            ->addColumn('pelanggan', fn (NotifikasiLog $l): string => e($l->pelanggan?->nama ?? '-'))
            ->addColumn('channel_label', fn (NotifikasiLog $l): string => ucfirst($l->channel->value))
            ->addColumn('jenis_label', fn (NotifikasiLog $l): string => $l->jenis->label())
            ->addColumn('status_badge', fn (NotifikasiLog $l): string => $this->statusBadge($l))
            ->addColumn('aksi', fn (NotifikasiLog $l): string => $this->actionButtons($l))
            ->rawColumns(['status_badge', 'aksi'])
            ->orderColumn('created_at', 'created_at $1')
            ->toJson();
    }

    public function resend(NotifikasiLog $log, NotifikasiService $notifikasi): JsonResponse
    {
        $log->loadMissing('tagihan');

        if ($log->tagihan === null) {
            return response()->json([
                'success' => false,
                'message' => 'Tagihan terkait tidak ditemukan.',
            ], 422);
        }

        $notifikasi->kirim($log->tagihan, $log->jenis, [$log->channel], force: true);

        return response()->json(['success' => true, 'message' => 'Notifikasi sedang dikirim ulang.']);
    }

    public function template(): View
    {
        $templates = MessageTemplate::query()
            ->orderBy('jenis')
            ->orderBy('channel')
            ->get();

        return view('notifikasi.template', [
            'templates' => $templates,
            'placeholders' => ['{nama}', '{kode_pelanggan}', '{nomor_tagihan}', '{periode}', '{jumlah}', '{jatuh_tempo}', '{link_bayar}'],
        ]);
    }

    public function templateUpdate(MessageTemplateRequest $request, MessageTemplate $template): RedirectResponse
    {
        $template->update($request->templateData());

        return back()->with('success', 'Template berhasil disimpan.');
    }

    private function statusBadge(NotifikasiLog $log): string
    {
        $class = match ($log->status) {
            NotifikasiStatus::Sent => 'bg-green-100 text-green-800',
            NotifikasiStatus::Failed => 'bg-red-100 text-red-800',
            NotifikasiStatus::Pending => 'bg-yellow-100 text-yellow-800',
        };

        return '<span class="rounded-full px-2 py-1 text-xs font-medium '.$class.'">'.e($log->status->label()).'</span>';
    }

    private function actionButtons(NotifikasiLog $log): string
    {
        $payload = e($log->payload ?? '');
        $error = e($log->error_message ?? '');
        $buttons = <<<HTML
            <button type="button" class="btn-payload btn-act btn-act--view" data-payload="{$payload}" data-error="{$error}">
                <i data-lucide="eye"></i> Lihat
            </button>
        HTML;

        if (auth()->user()?->can('notifikasi.kirim')) {
            $buttons .= <<<HTML
                <button type="button" class="btn-resend btn-act btn-act--send" data-id="{$log->id}">
                    <i data-lucide="send"></i> Kirim Ulang
                </button>
            HTML;
        }

        return "<div class=\"flex items-center justify-end gap-2\">{$buttons}</div>";
    }
}
