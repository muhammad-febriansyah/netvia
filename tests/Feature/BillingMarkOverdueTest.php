<?php

use App\Models\Tagihan;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    Carbon::setTestNow(CarbonImmutable::parse('2026-06-10 00:00:00', 'Asia/Jakarta'));
});

afterEach(function () {
    Carbon::setTestNow();
});

it('marks unpaid past-due tagihan as overdue', function () {
    $overdue = Tagihan::factory()->create([
        'status' => 'unpaid',
        'tanggal_jatuh_tempo' => '2026-06-05',
    ]);
    $future = Tagihan::factory()->create([
        'status' => 'unpaid',
        'tanggal_jatuh_tempo' => '2026-06-20',
    ]);
    $paid = Tagihan::factory()->create([
        'status' => 'paid',
        'tanggal_jatuh_tempo' => '2026-06-05',
    ]);

    $this->artisan('billing:mark-overdue')->assertSuccessful();

    expect($overdue->fresh()->status->value)->toBe('overdue')
        ->and($future->fresh()->status->value)->toBe('unpaid')
        ->and($paid->fresh()->status->value)->toBe('paid');
});
