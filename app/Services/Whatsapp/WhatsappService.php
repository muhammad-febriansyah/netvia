<?php

namespace App\Services\Whatsapp;

interface WhatsappService
{
    /**
     * Send a WhatsApp message to a normalized 62xxx number.
     */
    public function send(string $to62, string $message): WhatsappResult;
}
