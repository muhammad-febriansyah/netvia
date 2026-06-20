<?php

namespace App\Services\Whatsapp;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Stub WhatsApp driver: logs the outgoing message and reports success.
 * Replace with a real driver (Meta Cloud API / gateway / ChatCepat) later.
 */
class LogWhatsappDriver implements WhatsappService
{
    public function send(string $to62, string $message): WhatsappResult
    {
        Log::info('WhatsApp (stub) terkirim', [
            'to' => $to62,
            'message' => $message,
        ]);

        return WhatsappResult::success('log-'.Str::random(12));
    }
}
