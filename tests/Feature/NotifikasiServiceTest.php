<?php

use App\Enums\NotifikasiChannel;
use App\Enums\NotifikasiJenis;
use App\Enums\NotifikasiStatus;
use App\Jobs\KirimEmailNotifikasi;
use App\Jobs\KirimWhatsappNotifikasi;
use App\Models\NotifikasiLog;
use App\Models\Pelanggan;
use App\Models\Tagihan;
use App\Services\NotifikasiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

function tagihanDenganKontak(array $pelangganAttrs = []): Tagihan
{
    $pelanggan = Pelanggan::factory()->create(array_merge([
        'no_wa' => '6281234567890',
        'email' => 'pelanggan@example.com',
    ], $pelangganAttrs));

    return Tagihan::factory()->for($pelanggan)->create();
}

it('queues whatsapp and email jobs and creates pending logs', function () {
    Queue::fake();
    $tagihan = tagihanDenganKontak();

    app(NotifikasiService::class)->kirim($tagihan, NotifikasiJenis::ReminderDue);

    Queue::assertPushed(KirimWhatsappNotifikasi::class, 1);
    Queue::assertPushed(KirimEmailNotifikasi::class, 1);

    expect(NotifikasiLog::count())->toBe(2)
        ->and(NotifikasiLog::where('channel', 'whatsapp')->first()->status)->toBe(NotifikasiStatus::Pending)
        ->and(NotifikasiLog::where('channel', 'email')->first()->recipient)->toBe('pelanggan@example.com');
});

it('skips the email channel when the customer has no email', function () {
    Queue::fake();
    $tagihan = tagihanDenganKontak(['email' => null]);

    app(NotifikasiService::class)->kirim($tagihan, NotifikasiJenis::ReminderDue);

    Queue::assertPushed(KirimWhatsappNotifikasi::class, 1);
    Queue::assertNotPushed(KirimEmailNotifikasi::class);
    expect(NotifikasiLog::where('channel', 'email')->exists())->toBeFalse();
});

it('does not resend a notification already marked sent', function () {
    Queue::fake();
    $tagihan = tagihanDenganKontak();

    NotifikasiLog::factory()->create([
        'tagihan_id' => $tagihan->id,
        'pelanggan_id' => $tagihan->pelanggan_id,
        'channel' => NotifikasiChannel::Email->value,
        'jenis' => NotifikasiJenis::ReminderDue->value,
        'status' => NotifikasiStatus::Sent->value,
    ]);

    app(NotifikasiService::class)->kirim($tagihan, NotifikasiJenis::ReminderDue, [NotifikasiChannel::Email]);

    Queue::assertNotPushed(KirimEmailNotifikasi::class);
    expect(NotifikasiLog::where('channel', 'email')->count())->toBe(1);
});

it('forces a resend and resets the log to pending', function () {
    Queue::fake();
    $tagihan = tagihanDenganKontak();

    NotifikasiLog::factory()->create([
        'tagihan_id' => $tagihan->id,
        'pelanggan_id' => $tagihan->pelanggan_id,
        'channel' => NotifikasiChannel::Email->value,
        'jenis' => NotifikasiJenis::ReminderDue->value,
        'status' => NotifikasiStatus::Sent->value,
        'sent_at' => now(),
    ]);

    app(NotifikasiService::class)->kirim($tagihan, NotifikasiJenis::ReminderDue, [NotifikasiChannel::Email], force: true);

    Queue::assertPushed(KirimEmailNotifikasi::class, 1);
    $log = NotifikasiLog::where('channel', 'email')->first();
    expect($log->status)->toBe(NotifikasiStatus::Pending)
        ->and($log->sent_at)->toBeNull();
});

it('respects the unique constraint by reusing the same log row', function () {
    Queue::fake();
    $tagihan = tagihanDenganKontak();

    app(NotifikasiService::class)->kirim($tagihan, NotifikasiJenis::ReminderDue, [NotifikasiChannel::Whatsapp]);
    app(NotifikasiService::class)->kirim($tagihan, NotifikasiJenis::ReminderDue, [NotifikasiChannel::Whatsapp], force: true);

    expect(NotifikasiLog::where('channel', 'whatsapp')->count())->toBe(1);
});
