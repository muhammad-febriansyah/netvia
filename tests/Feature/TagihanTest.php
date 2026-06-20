<?php

use App\Jobs\KirimWhatsappNotifikasi;
use App\Models\Paket;
use App\Models\Pelanggan;
use App\Models\Tagihan;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    $this->customer = User::factory()->create();
    $this->customer->assignRole('customer');
});

it('lists tagihan via the datatable endpoint', function () {
    Tagihan::factory()->count(3)->create();

    $this->actingAs($this->admin)
        ->getJson(route('tagihan.data'))
        ->assertOk()
        ->assertJsonPath('recordsTotal', 3);
});

it('filters the datatable by status', function () {
    Tagihan::factory()->create(['status' => 'unpaid']);
    Tagihan::factory()->create(['status' => 'paid']);

    $this->actingAs($this->admin)
        ->getJson(route('tagihan.data', ['status' => 'paid']))
        ->assertOk()
        ->assertJsonPath('recordsFiltered', 1);
});

it('renders the detail page', function () {
    $this->withoutVite();
    $tagihan = Tagihan::factory()->create();

    $this->actingAs($this->admin)
        ->get(route('tagihan.show', $tagihan))
        ->assertOk()
        ->assertSee($tagihan->nomor_tagihan)
        ->assertSee('Informasi Tagihan');
});

it('generates a manual tagihan for a pelanggan', function () {
    $paket = Paket::factory()->create(['harga' => 120000]);
    $pelanggan = Pelanggan::factory()->create(['paket_id' => $paket->id, 'status' => 'aktif']);

    $this->actingAs($this->admin)
        ->post(route('tagihan.generateManual'), [
            'pelanggan_id' => $pelanggan->id,
            'periode' => '2026-06',
        ])
        ->assertRedirect();

    $tagihan = Tagihan::where('pelanggan_id', $pelanggan->id)->first();
    expect($tagihan)->not->toBeNull()
        ->and($tagihan->harga)->toBe(120000)
        ->and($tagihan->periode->format('Y-m'))->toBe('2026-06');
});

it('does not duplicate a manual tagihan for the same period', function () {
    $pelanggan = Pelanggan::factory()->create(['status' => 'aktif']);
    Tagihan::factory()->create([
        'pelanggan_id' => $pelanggan->id,
        'periode' => '2026-06-01',
    ]);

    $this->actingAs($this->admin)
        ->post(route('tagihan.generateManual'), ['pelanggan_id' => $pelanggan->id, 'periode' => '2026-06'])
        ->assertRedirect()
        ->assertSessionHas('error');

    expect(Tagihan::where('pelanggan_id', $pelanggan->id)->count())->toBe(1);
});

it('voids an unpaid tagihan with a reason', function () {
    $tagihan = Tagihan::factory()->create(['status' => 'unpaid']);

    $this->actingAs($this->admin)
        ->postJson(route('tagihan.void', $tagihan), ['alasan' => 'salah terbit'])
        ->assertOk()
        ->assertJsonPath('success', true);

    $tagihan->refresh();
    expect($tagihan->status->value)->toBe('void')
        ->and($tagihan->void_reason)->toBe('salah terbit');
});

it('refuses to void a paid tagihan', function () {
    $tagihan = Tagihan::factory()->create(['status' => 'paid']);

    $this->actingAs($this->admin)
        ->postJson(route('tagihan.void', $tagihan), ['alasan' => 'x'])
        ->assertStatus(422)
        ->assertJsonPath('success', false);

    expect($tagihan->fresh()->status->value)->toBe('paid');
});

it('requires a reason to void', function () {
    $tagihan = Tagihan::factory()->create(['status' => 'unpaid']);

    $response = $this->actingAs($this->admin)
        ->postJson(route('tagihan.void', $tagihan), []);

    expect($response->status())->toBe(422)
        ->and($response->json('errors.alasan.0'))->toBe('Alasan pembatalan wajib diisi.');
});

it('queues an ad-hoc reminder', function () {
    Queue::fake();
    $pelanggan = Pelanggan::factory()->create(['email' => 'a@netvia.id']);
    $tagihan = Tagihan::factory()->create(['pelanggan_id' => $pelanggan->id, 'status' => 'overdue']);

    $this->actingAs($this->admin)
        ->postJson(route('tagihan.kirimReminder', $tagihan))
        ->assertOk()
        ->assertJsonPath('success', true);

    Queue::assertPushed(KirimWhatsappNotifikasi::class);
});

it('forbids customer from generating or voiding tagihan', function () {
    $pelanggan = Pelanggan::factory()->create(['status' => 'aktif']);
    $tagihan = Tagihan::factory()->create(['status' => 'unpaid']);

    $this->actingAs($this->customer)
        ->post(route('tagihan.generateManual'), ['pelanggan_id' => $pelanggan->id])
        ->assertForbidden();

    $this->actingAs($this->customer)
        ->postJson(route('tagihan.void', $tagihan), ['alasan' => 'ok'])
        ->assertForbidden();
});
