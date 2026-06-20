<?php

use App\Enums\TagihanStatus;
use App\Models\NotifikasiLog;
use App\Models\Pelanggan;
use App\Models\Tagihan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

function tagihanJatuhTempo(Carbon $tanggal, TagihanStatus $status): Tagihan
{
    $pelanggan = Pelanggan::factory()->create([
        'no_wa' => '6281234567890',
        'email' => fake()->unique()->safeEmail(),
    ]);

    return Tagihan::factory()->for($pelanggan)->create([
        'tanggal_jatuh_tempo' => $tanggal->toDateString(),
        'status' => $status->value,
    ]);
}

it('queues reminders for H-3, due-today, and overdue bills only', function () {
    Queue::fake();
    $today = Carbon::now('Asia/Jakarta')->startOfDay();

    tagihanJatuhTempo($today->copy()->addDays(3), TagihanStatus::Unpaid);   // H-3
    tagihanJatuhTempo($today->copy(), TagihanStatus::Unpaid);                // due today
    tagihanJatuhTempo($today->copy()->subDay(), TagihanStatus::Overdue);     // overdue (H+1)
    tagihanJatuhTempo($today->copy()->addDays(10), TagihanStatus::Unpaid);   // outside any window

    $this->artisan('billing:remind')->assertSuccessful();

    expect(NotifikasiLog::where('jenis', 'reminder_h3')->count())->toBe(2)       // wa + email
        ->and(NotifikasiLog::where('jenis', 'reminder_due')->count())->toBe(2)
        ->and(NotifikasiLog::where('jenis', 'reminder_overdue')->count())->toBe(2)
        ->and(NotifikasiLog::count())->toBe(6);
});

it('does not remind paid bills', function () {
    Queue::fake();
    $today = Carbon::now('Asia/Jakarta')->startOfDay();

    tagihanJatuhTempo($today->copy(), TagihanStatus::Paid);

    $this->artisan('billing:remind')->assertSuccessful();

    expect(NotifikasiLog::count())->toBe(0);
});
