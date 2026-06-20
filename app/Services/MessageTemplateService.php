<?php

namespace App\Services;

use App\Enums\NotifikasiChannel;
use App\Enums\NotifikasiJenis;
use App\Models\MessageTemplate;
use App\Models\Tagihan;

class MessageTemplateService
{
    /**
     * Render a message template for a bill into a subject/body pair with all
     * placeholders replaced.
     *
     * @return array{subject: ?string, body: string}
     */
    public function render(NotifikasiJenis $jenis, NotifikasiChannel $channel, Tagihan $tagihan): array
    {
        $template = MessageTemplate::query()
            ->where('jenis', $jenis->value)
            ->where('channel', $channel->value)
            ->where('is_active', true)
            ->first();

        $body = $template?->body ?? $this->fallbackBody();
        $subject = $template?->subject;

        $replacements = $this->replacements($tagihan);

        return [
            'subject' => $subject !== null ? strtr($subject, $replacements) : null,
            'body' => strtr($body, $replacements),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function replacements(Tagihan $tagihan): array
    {
        $pelanggan = $tagihan->pelanggan;

        return [
            '{nama}' => $pelanggan->nama,
            '{kode_pelanggan}' => $pelanggan->kode_pelanggan,
            '{nomor_tagihan}' => $tagihan->nomor_tagihan,
            '{periode}' => $tagihan->periode->locale('id')->isoFormat('MMMM Y'),
            '{jumlah}' => rupiah($tagihan->jumlah),
            '{jatuh_tempo}' => $tagihan->tanggal_jatuh_tempo->locale('id')->isoFormat('D MMMM Y'),
            '{link_bayar}' => $tagihan->publicUrl(),
        ];
    }

    protected function fallbackBody(): string
    {
        return 'Halo {nama}, tagihan {nomor_tagihan} periode {periode} sebesar {jumlah} '
            .'jatuh tempo {jatuh_tempo}. Bayar: {link_bayar}';
    }
}
