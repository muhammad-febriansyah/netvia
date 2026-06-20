<?php

namespace App\Jobs;

use App\Enums\NotifikasiStatus;
use App\Models\NotifikasiLog;
use App\Services\MessageTemplateService;
use App\Services\Whatsapp\WhatsappService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class KirimWhatsappNotifikasi implements ShouldQueue
{
    use Queueable;

    /**
     * Number of attempts before the job is marked failed.
     */
    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(public NotifikasiLog $log) {}

    /**
     * Exponential backoff (seconds) between retries.
     *
     * @return list<int>
     */
    public function backoff(): array
    {
        return [10, 30, 60];
    }

    /**
     * Execute the job.
     */
    public function handle(MessageTemplateService $templates, WhatsappService $whatsapp): void
    {
        $this->log->refresh();

        // Idempotent: skip if already sent.
        if ($this->log->status === NotifikasiStatus::Sent) {
            return;
        }

        $tagihan = $this->log->tagihan?->loadMissing('pelanggan');

        if ($tagihan === null) {
            $this->fail(new \RuntimeException('Tagihan tidak ditemukan untuk notifikasi.'));

            return;
        }

        $rendered = $templates->render($this->log->jenis, $this->log->channel, $tagihan);

        $result = $whatsapp->send($this->log->recipient, $rendered['body']);

        if (! $result->success) {
            throw new \RuntimeException($result->error ?? 'Pengiriman WhatsApp gagal.');
        }

        $this->log->update([
            'status' => NotifikasiStatus::Sent,
            'payload' => $rendered['body'],
            'sent_at' => now(),
            'error_message' => null,
        ]);
    }

    /**
     * Handle a final failure after all retries.
     */
    public function failed(?Throwable $exception): void
    {
        $this->log->update([
            'status' => NotifikasiStatus::Failed,
            'error_message' => $exception?->getMessage(),
        ]);
    }
}
