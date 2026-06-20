<?php

use App\Models\Pelanggan;
use App\Models\PemutusanLangganan;
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

it('lists termination requests for admin', function () {
    PemutusanLangganan::factory()->count(3)->create();

    $this->actingAs($this->admin)
        ->getJson(route('pemutusan.data'))
        ->assertOk()
        ->assertJsonPath('recordsTotal', 3);
});

it('approves a request and deactivates the pelanggan', function () {
    $pelanggan = Pelanggan::factory()->create(['status' => 'aktif']);
    $req = PemutusanLangganan::factory()->create(['pelanggan_id' => $pelanggan->id, 'status' => 'pending']);

    $this->actingAs($this->admin)
        ->postJson(route('pemutusan.approve', $req))
        ->assertOk()
        ->assertJsonPath('success', true);

    expect($req->fresh()->status->value)->toBe('approved')
        ->and($pelanggan->fresh()->status->value)->toBe('nonaktif');
});

it('rejects a request with an admin note', function () {
    $req = PemutusanLangganan::factory()->create(['status' => 'pending']);

    $this->actingAs($this->admin)
        ->postJson(route('pemutusan.reject', $req), ['catatan' => 'data tidak lengkap'])
        ->assertOk();

    expect($req->fresh()->status->value)->toBe('rejected')
        ->and($req->fresh()->catatan_admin)->toBe('data tidak lengkap');
});

it('requires a note to reject', function () {
    $req = PemutusanLangganan::factory()->create(['status' => 'pending']);

    $response = $this->actingAs($this->admin)
        ->postJson(route('pemutusan.reject', $req), []);

    expect($response->status())->toBe(422);
});

it('cannot re-process an already processed request', function () {
    $req = PemutusanLangganan::factory()->approved()->create();

    $this->actingAs($this->admin)
        ->postJson(route('pemutusan.approve', $req))
        ->assertStatus(422)
        ->assertJsonPath('success', false);
});

it('forbids customer from admin termination management', function () {
    $req = PemutusanLangganan::factory()->create();

    $this->actingAs($this->customer)->getJson(route('pemutusan.data'))->assertForbidden();
    $this->actingAs($this->customer)->postJson(route('pemutusan.approve', $req))->assertForbidden();
});
