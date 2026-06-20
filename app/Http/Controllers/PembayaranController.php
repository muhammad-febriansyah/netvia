<?php

namespace App\Http\Controllers;

use App\Actions\Pembayaran\BuatPembayaranQrisAction;
use App\Actions\Pembayaran\KonfirmasiPembayaranManualAction;
use App\Actions\Pembayaran\ProsesCallbackPakasirAction;
use App\Enums\TagihanStatus;
use App\Exceptions\TagihanNotPayableException;
use App\Http\Requests\KonfirmasiPembayaranManualRequest;
use App\Models\Tagihan;
use App\Repositories\PembayaranRepository;
use Illuminate\Http\JsonResponse;

class PembayaranController extends Controller
{
    public function __construct(private PembayaranRepository $pembayarans) {}

    /**
     * Open (or reuse) a QRIS payment for a tagihan.
     */
    public function createQris(Tagihan $tagihan, BuatPembayaranQrisAction $action): JsonResponse
    {
        try {
            $pembayaran = $action->execute($tagihan);
        } catch (TagihanNotPayableException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'QRIS pembayaran dibuat.',
            'data' => [
                'order_id' => $pembayaran->pakasir_order_id,
                'qr_string' => $pembayaran->qr_string,
                'payment_url' => $pembayaran->payment_url,
                'expired_at' => $pembayaran->expired_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Confirm a manual transfer/cash settlement.
     */
    public function konfirmasiManual(KonfirmasiPembayaranManualRequest $request, Tagihan $tagihan, KonfirmasiPembayaranManualAction $action): JsonResponse
    {
        $buktiPath = $request->file('bukti_transfer')?->store('bukti-transfer', 'public');

        try {
            $action->execute(
                $tagihan,
                $request->metode(),
                (int) $request->integer('jumlah_bayar'),
                $request->user(),
                $buktiPath,
            );
        } catch (TagihanNotPayableException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil dikonfirmasi.',
        ]);
    }

    /**
     * Reconcile a tagihan against Pakasir when a webhook is delayed.
     */
    public function cekStatus(Tagihan $tagihan, ProsesCallbackPakasirAction $action): JsonResponse
    {
        $pembayaran = $this->pembayarans->activeQrisFor($tagihan);

        if ($pembayaran !== null) {
            $action->execute(['order_id' => $pembayaran->pakasir_order_id]);
            $tagihan->refresh();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'status' => $tagihan->status->value,
                'lunas' => $tagihan->status === TagihanStatus::Paid,
            ],
        ]);
    }
}
