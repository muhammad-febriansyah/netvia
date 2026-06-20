<?php

use App\Models\Paket;
use App\Models\Pelanggan;
use App\Models\Tagihan;
use Carbon\CarbonImmutable;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(SettingSeeder::class); // generate_hari_sebelum_jatuh_tempo = 7
    Carbon::setTestNow(CarbonImmutable::parse('2026-06-10 00:00:00', 'Asia/Jakarta'));
});

afterEach(function () {
    Carbon::setTestNow();
});

it('generates a tagihan for an active pelanggan inside the issuing window', function () {
    $paket = Paket::factory()->create(['nama' => 'Home 20 Mbps', 'harga' => 150000]);
    $pelanggan = Pelanggan::factory()->create([
        'paket_id' => $paket->id,
        'tgl_jatuh_tempo' => 15,
        'status' => 'aktif',
    ]);

    $this->artisan('billing:generate')->assertSuccessful();

    $tagihan = Tagihan::where('pelanggan_id', $pelanggan->id)->first();

    expect($tagihan)->not->toBeNull()
        ->and($tagihan->nomor_tagihan)->toBe('INV/202606/000001')
        ->and($tagihan->paket_nama)->toBe('Home 20 Mbps')   // snapshot
        ->and($tagihan->harga)->toBe(150000)
        ->and($tagihan->jumlah)->toBe(150000)
        ->and($tagihan->periode->format('Y-m-d'))->toBe('2026-06-01')
        ->and($tagihan->tanggal_jatuh_tempo->format('Y-m-d'))->toBe('2026-06-15')
        ->and($tagihan->status->value)->toBe('unpaid')
        ->and($tagihan->public_token)->not->toBeEmpty();
});

it('is idempotent — running twice does not duplicate tagihan', function () {
    $pelanggan = Pelanggan::factory()->create(['tgl_jatuh_tempo' => 15, 'status' => 'aktif']);

    $this->artisan('billing:generate')->assertSuccessful();
    $this->artisan('billing:generate')->assertSuccessful();

    expect(Tagihan::where('pelanggan_id', $pelanggan->id)->count())->toBe(1);
});

it('skips pelanggan whose due date is still outside the window', function () {
    // due day 28 => due 2026-06-28, window opens 06-21, today is 06-10 => skip.
    $pelanggan = Pelanggan::factory()->create(['tgl_jatuh_tempo' => 28, 'status' => 'aktif']);

    $this->artisan('billing:generate')->assertSuccessful();

    expect(Tagihan::where('pelanggan_id', $pelanggan->id)->exists())->toBeFalse();
});

it('skips non-active pelanggan', function () {
    $pelanggan = Pelanggan::factory()->nonaktif()->create(['tgl_jatuh_tempo' => 15]);

    $this->artisan('billing:generate')->assertSuccessful();

    expect(Tagihan::where('pelanggan_id', $pelanggan->id)->exists())->toBeFalse();
});

it('snapshots harga so later paket price changes do not affect issued tagihan', function () {
    $paket = Paket::factory()->create(['harga' => 100000]);
    $pelanggan = Pelanggan::factory()->create([
        'paket_id' => $paket->id,
        'tgl_jatuh_tempo' => 15,
        'status' => 'aktif',
    ]);

    $this->artisan('billing:generate')->assertSuccessful();
    $paket->update(['harga' => 999000]);

    expect(Tagihan::where('pelanggan_id', $pelanggan->id)->value('harga'))->toBe(100000);
});
