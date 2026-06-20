<?php

use App\Enums\NotifikasiJenis;
use App\Enums\NotifikasiStatus;
use App\Jobs\KirimEmailNotifikasi;
use App\Jobs\KirimWhatsappNotifikasi;
use App\Mail\NotifikasiMail;
use App\Models\NotifikasiLog;
use App\Models\Pelanggan;
use App\Models\Tagihan;
use App\Services\MessageTemplateService;
use App\Services\Whatsapp\WhatsappResult;
use App\Services\Whatsapp\WhatsappService;
use Database\Seeders\MessageTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(MessageTemplateSeeder::class);
});

function logUntuk(string $channel): NotifikasiLog
{
    $pelanggan = Pelanggan::factory()->create([
        'no_wa' => '6281234567890',
        'email' => 'pelanggan@example.com',
    ]);
    $tagihan = Tagihan::factory()->for($pelanggan)->create();

    return NotifikasiLog::factory()->create([
        'tagihan_id' => $tagihan->id,
        'pelanggan_id' => $pelanggan->id,
        'channel' => $channel,
        'jenis' => NotifikasiJenis::ReminderDue->value,
        'status' => NotifikasiStatus::Pending->value,
        'recipient' => $channel === 'email' ? 'pelanggan@example.com' : '6281234567890',
    ]);
}

it('sends the email and marks the log sent', function () {
    Mail::fake();
    $log = logUntuk('email');

    (new KirimEmailNotifikasi($log))->handle(app(MessageTemplateService::class));

    Mail::assertSent(NotifikasiMail::class, fn ($mail) => $mail->hasTo('pelanggan@example.com'));
    expect($log->fresh()->status)->toBe(NotifikasiStatus::Sent)
        ->and($log->fresh()->payload)->not->toBeNull();
});

it('sends whatsapp via the bound driver and marks the log sent', function () {
    $log = logUntuk('whatsapp');

    (new KirimWhatsappNotifikasi($log))->handle(
        app(MessageTemplateService::class),
        app(WhatsappService::class),
    );

    expect($log->fresh()->status)->toBe(NotifikasiStatus::Sent);
});

it('skips an already-sent log (idempotent)', function () {
    Mail::fake();
    $log = logUntuk('email');
    $log->update(['status' => NotifikasiStatus::Sent]);

    (new KirimEmailNotifikasi($log))->handle(app(MessageTemplateService::class));

    Mail::assertNothingSent();
});

it('marks the log failed when whatsapp delivery fails', function () {
    $this->mock(WhatsappService::class, function ($mock) {
        $mock->shouldReceive('send')->andReturn(WhatsappResult::failure('gateway down'));
    });

    $log = logUntuk('whatsapp');
    $job = new KirimWhatsappNotifikasi($log);

    try {
        $job->handle(app(MessageTemplateService::class), app(WhatsappService::class));
    } catch (Throwable $e) {
        $job->failed($e);
    }

    expect($log->fresh()->status)->toBe(NotifikasiStatus::Failed)
        ->and($log->fresh()->error_message)->toContain('gateway down');
});
