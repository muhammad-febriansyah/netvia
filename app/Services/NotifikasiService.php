<?php

namespace App\Services;

use App\Enums\NotifikasiChannel;
use App\Enums\NotifikasiJenis;
use App\Enums\NotifikasiStatus;
use App\Jobs\KirimEmailNotifikasi;
use App\Jobs\KirimWhatsappNotifikasi;
use App\Models\NotifikasiLog;
use App\Models\Tagihan;

class NotifikasiService
{
    /**
     * Queue a notification of the given kind for a bill across the given channels.
     *
     * Respects the unique(tagihan_id, channel, jenis) constraint: an already-sent
     * notification is skipped unless $force is true (ad-hoc resend).
     *
     * @param  list<NotifikasiChannel>|null  $channels
     */
    public function kirim(Tagihan $tagihan, NotifikasiJenis $jenis, ?array $channels = null, bool $force = false): void
    {
        $tagihan->loadMissing('pelanggan');
        $channels ??= [NotifikasiChannel::Whatsapp, NotifikasiChannel::Email];

        foreach ($channels as $channel) {
            $recipient = $this->recipientFor($tagihan, $channel);

            if ($recipient === null) {
                continue;
            }

            $log = NotifikasiLog::query()->firstOrNew([
                'tagihan_id' => $tagihan->id,
                'channel' => $channel->value,
                'jenis' => $jenis->value,
            ]);

            if ($log->exists && $log->status === NotifikasiStatus::Sent && ! $force) {
                continue;
            }

            $log->fill([
                'pelanggan_id' => $tagihan->pelanggan_id,
                'status' => NotifikasiStatus::Pending,
                'recipient' => $recipient,
                'error_message' => null,
                'sent_at' => null,
            ])->save();

            $this->dispatchFor($channel, $log);
        }
    }

    /**
     * Resolve the recipient address for a channel, or null if unavailable.
     */
    protected function recipientFor(Tagihan $tagihan, NotifikasiChannel $channel): ?string
    {
        return match ($channel) {
            NotifikasiChannel::Whatsapp => $tagihan->pelanggan->no_wa ?: null,
            NotifikasiChannel::Email => $tagihan->pelanggan->email ?: null,
        };
    }

    protected function dispatchFor(NotifikasiChannel $channel, NotifikasiLog $log): void
    {
        match ($channel) {
            NotifikasiChannel::Whatsapp => KirimWhatsappNotifikasi::dispatch($log),
            NotifikasiChannel::Email => KirimEmailNotifikasi::dispatch($log),
        };
    }
}
