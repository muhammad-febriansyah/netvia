<?php

use App\Models\Paket;
use App\Models\Pelanggan;
use App\Models\User;
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

it('lists pakets via the server-side datatable endpoint', function () {
    Paket::factory()->count(3)->create();

    $this->actingAs($this->admin)
        ->getJson(route('paket.data'))
        ->assertOk()
        ->assertJsonPath('recordsTotal', 3);
});

it('renders the paket index, create, and edit pages', function () {
    $this->withoutVite();
    $paket = Paket::factory()->create();

    $this->actingAs($this->admin)->get(route('paket.index'))
        ->assertOk()->assertSee('Daftar Paket')->assertSee('Tambah Paket');

    $this->actingAs($this->admin)->get(route('paket.create'))
        ->assertOk()->assertSee('Nama Paket')->assertSee('Harga Bulanan');

    $this->actingAs($this->admin)->get(route('paket.edit', $paket))
        ->assertOk()->assertSee('Edit Paket')->assertSee($paket->nama);
});

it('stores a paket and parses the masked rupiah input to an integer', function () {
    $this->actingAs($this->admin)
        ->post(route('paket.store'), [
            'nama' => 'Home 20 Mbps',
            'kecepatan_mbps' => 20,
            'harga' => 'Rp 150.000',
            'is_active' => '1',
        ])
        ->assertRedirect(route('paket.index'))
        ->assertSessionHas('success');

    expect(Paket::where('nama', 'Home 20 Mbps')->value('harga'))->toBe(150000);
});

it('validates required fields with indonesian messages', function () {
    $this->actingAs($this->admin)
        ->post(route('paket.store'), [])
        ->assertSessionHasErrors([
            'nama' => 'Nama paket wajib diisi.',
            'harga' => 'Harga wajib diisi.',
        ]);
});

it('updates a paket', function () {
    $paket = Paket::factory()->create(['harga' => 100000]);

    $this->actingAs($this->admin)
        ->put(route('paket.update', $paket), [
            'nama' => $paket->nama,
            'harga' => 'Rp 250.000',
            'is_active' => '1',
        ])
        ->assertRedirect(route('paket.index'));

    expect($paket->fresh()->harga)->toBe(250000);
});

it('toggles paket active status', function () {
    $paket = Paket::factory()->create(['is_active' => true]);

    $this->actingAs($this->admin)
        ->patchJson(route('paket.toggle', $paket))
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.is_active', false);

    expect($paket->fresh()->is_active)->toBeFalse();
});

it('soft deletes a paket with no active pelanggan', function () {
    $paket = Paket::factory()->create();

    $this->actingAs($this->admin)
        ->deleteJson(route('paket.destroy', $paket))
        ->assertOk()
        ->assertJsonPath('success', true);

    expect($paket->fresh()->trashed())->toBeTrue();
});

it('refuses to delete a paket still used by active pelanggan', function () {
    $paket = Paket::factory()->create();
    Pelanggan::factory()->create(['paket_id' => $paket->id, 'status' => 'aktif']);

    $this->actingAs($this->admin)
        ->deleteJson(route('paket.destroy', $paket))
        ->assertStatus(422)
        ->assertJsonPath('success', false);

    expect($paket->fresh()->trashed())->toBeFalse();
});

it('forbids customer from creating a paket', function () {
    $this->actingAs($this->customer)
        ->post(route('paket.store'), [
            'nama' => 'Home 20 Mbps',
            'harga' => 'Rp 150.000',
        ])
        ->assertForbidden();

    expect(Paket::count())->toBe(0);
});
