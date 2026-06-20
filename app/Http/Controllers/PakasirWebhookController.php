<?php

namespace App\Http\Controllers;

use App\Actions\Pembayaran\ProsesCallbackPakasirAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PakasirWebhookController extends Controller
{
    /**
     * Handle the Pakasir settlement webhook.
     *
     * Settlement is confirmed by re-fetching the transaction from Pakasir inside
     * the action, so the payload itself is untrusted. Always answers 200 once the
     * payload is understood, so Pakasir does not retry endlessly; an unknown
     * order still returns 200 (nothing to do) to avoid leaking which orders exist.
     */
    public function __invoke(Request $request, ProsesCallbackPakasirAction $action): JsonResponse
    {
        $action->execute($request->all());

        return response()->json(['success' => true]);
    }
}
