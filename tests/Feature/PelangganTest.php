<?php

use App\Enums\PelangganStatus;
use App\Models\Paket;
use App\Models\Pelanggan;
use App\Models\User;
use App\Support\WhatsappNumber;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    $this->customer = User::factory()->create();
    $this->customer->assignRole('customer');
});

it('lists pelanggans via the server-side datatable endpoint', function () {
    Pelanggan::factory()->count(3)->create();

    $this->actingAs($this->admin)
        ->getJson(route('pelanggan.data'))
        ->assertOk()
        ->assertJsonPath('recordsTotal', 3);
});

it('renders the pelanggan index, create, edit, and show pages', function () {
    $this->withoutVite();
    $pelanggan = Pelanggan::factory()->create();

    $this->actingAs($this->admin)->get(route('pelanggan.index'))
        ->assertOk()->assertSee('Tambah Pelanggan')->assertSee('Filter Paket');

    $this->actingAs($this->admin)->get(route('pelanggan.create'))
        ->assertOk()->assertSee('Nama Pelanggan')->assertSee('Kode Pelanggan');

    $this->actingAs($this->admin)->get(route('pelanggan.edit', $pelanggan))
        ->assertOk()->assertSee('Edit Pelanggan')->assertSee($pelanggan->nama);

    $this->actingAs($this->admin)->get(route('pelanggan.show', $pelanggan))
        ->assertOk()->assertSee('Ringkasan Tagihan')->assertSee($pelanggan->kode_pelanggan);
});

it('filters the datatable by paket and status', function () {
    $paket = Paket::factory()->create();
    Pelanggan::factory()->create(['paket_id' => $paket->id, 'status' => 'aktif']);
    Pelanggan::factory()->create(['status' => 'nonaktif']);

    $this->actingAs($this->admin)
        ->getJson(route('pelanggan.data', ['paket_id' => $paket->id, 'status' => 'aktif']))
        ->assertOk()
        ->assertJsonPath('recordsTotal', 1);
});

it('stores a pelanggan, generating a code and normalizing the wa number', function () {
    $paket = Paket::factory()->create();

    $this->actingAs($this->admin)
        ->post(route('pelanggan.store'), [
            'nama' => 'Budi Santoso',
            'no_wa' => '081234567890',
            'email' => 'budi@email.com',
            'paket_id' => $paket->id,
            'tanggal_aktivasi' => '2026-06-01',
            'tgl_jatuh_tempo' => 5,
            'status' => 'aktif',
        ])
        ->assertRedirect(route('pelanggan.index'))
        ->assertSessionHas('success');

    $pelanggan = Pelanggan::where('nama', 'Budi Santoso')->first();

    expect($pelanggan)->not->toBeNull();
    expect($pelanggan->no_wa)->toBe('6281234567890');
    expect($pelanggan->kode_pelanggan)->toBe('PLG-000001');
});

it('generates sequential unique customer codes', function () {
    $paket = Paket::factory()->create();

    $payload = fn (string $nama) => [
        'nama' => $nama,
        'no_wa' => '081234567890',
        'paket_id' => $paket->id,
        'tanggal_aktivasi' => '2026-06-01',
        'tgl_jatuh_tempo' => 5,
        'status' => 'aktif',
    ];

    $this->actingAs($this->admin)->post(route('pelanggan.store'), $payload('A'));
    $this->actingAs($this->admin)->post(route('pelanggan.store'), $payload('B'));

    expect(Pelanggan::orderBy('id')->pluck('kode_pelanggan')->all())
        ->toBe(['PLG-000001', 'PLG-000002']);
});

it('validates required fields with indonesian messages', function () {
    $this->actingAs($this->admin)
        ->post(route('pelanggan.store'), [])
        ->assertSessionHasErrors([
            'nama' => 'Nama wajib diisi.',
            'no_wa' => 'Nomor WhatsApp wajib diisi.',
            'paket_id' => 'Paket wajib dipilih.',
            'tgl_jatuh_tempo' => 'Tanggal jatuh tempo wajib diisi.',
        ]);
});

it('rejects tgl_jatuh_tempo outside 1-28', function () {
    $paket = Paket::factory()->create();

    $this->actingAs($this->admin)
        ->post(route('pelanggan.store'), [
            'nama' => 'Budi',
            'no_wa' => '081234567890',
            'paket_id' => $paket->id,
            'tanggal_aktivasi' => '2026-06-01',
            'tgl_jatuh_tempo' => 30,
            'status' => 'aktif',
        ])
        ->assertSessionHasErrors([
            'tgl_jatuh_tempo' => 'Tanggal jatuh tempo harus antara 1 sampai 28.',
        ]);
});

it('updates a pelanggan', function () {
    $pelanggan = Pelanggan::factory()->create(['nama' => 'Lama']);

    $this->actingAs($this->admin)
        ->put(route('pelanggan.update', $pelanggan), [
            'nama' => 'Baru',
            'no_wa' => '081299998888',
            'paket_id' => $pelanggan->paket_id,
            'tanggal_aktivasi' => '2026-06-01',
            'tgl_jatuh_tempo' => 10,
            'status' => 'isolir',
        ])
        ->assertRedirect(route('pelanggan.index'));

    $fresh = $pelanggan->fresh();
    expect($fresh->nama)->toBe('Baru');
    expect($fresh->no_wa)->toBe('6281299998888');
    expect($fresh->status)->toBe(PelangganStatus::Isolir);
});

it('soft deletes a pelanggan', function () {
    $pelanggan = Pelanggan::factory()->create();

    $this->actingAs($this->admin)
        ->deleteJson(route('pelanggan.destroy', $pelanggan))
        ->assertOk()
        ->assertJsonPath('success', true);

    expect($pelanggan->fresh()->trashed())->toBeTrue();
});

it('forbids customer from creating a pelanggan', function () {
    $paket = Paket::factory()->create();

    $this->actingAs($this->customer)
        ->post(route('pelanggan.store'), [
            'nama' => 'Budi',
            'no_wa' => '081234567890',
            'paket_id' => $paket->id,
            'tanggal_aktivasi' => '2026-06-01',
            'tgl_jatuh_tempo' => 5,
            'status' => 'aktif',
        ])
        ->assertForbidden();

    expect(Pelanggan::count())->toBe(0);
});

it('normalizes indonesian wa numbers to 62 format', function () {
    expect(WhatsappNumber::normalize('081234567890'))->toBe('6281234567890');
    expect(WhatsappNumber::normalize('+62 812-3456-7890'))->toBe('6281234567890');
    expect(WhatsappNumber::normalize('81234567890'))->toBe('6281234567890');
    expect(WhatsappNumber::normalize('6281234567890'))->toBe('6281234567890');
});
