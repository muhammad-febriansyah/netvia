<?php

use App\Models\Paket;
use App\Models\Pelanggan;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed(RolePermissionSeeder::class));

it('shows the registration page', function () {
    $this->withoutVite();
    Paket::factory()->create();

    $this->get(route('register'))->assertOk()->assertSee('Daftar langganan');
});

it('registers a customer with a pending pelanggan and customer account', function () {
    $paket = Paket::factory()->create(['is_active' => true]);

    $this->post(route('register.attempt'), [
        'name' => 'Budi Santoso',
        'email' => 'budi@email.com',
        'no_wa' => '081234567890',
        'alamat' => 'Jl. Mawar 1',
        'paket_id' => $paket->id,
        'tgl_jatuh_tempo' => 5,
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
    ])->assertRedirect(route('portal.dashboard'));

    $pelanggan = Pelanggan::where('email', 'budi@email.com')->first();
    expect($pelanggan)->not->toBeNull()
        ->and($pelanggan->status->value)->toBe('pending')
        ->and($pelanggan->no_wa)->toBe('6281234567890');

    $user = User::where('email', 'budi@email.com')->first();
    expect($user->hasRole('customer'))->toBeTrue()
        ->and($user->pelanggan_id)->toBe($pelanggan->id);

    $this->assertAuthenticatedAs($user);
});

it('validates registration input', function () {
    $this->post(route('register.attempt'), [
        'password' => 'short',
        'password_confirmation' => 'mismatch',
    ])->assertSessionHasErrors(['name', 'email', 'no_wa', 'paket_id', 'tgl_jatuh_tempo', 'password']);
});

it('rejects an inactive paket on registration', function () {
    $paket = Paket::factory()->inactive()->create();

    $this->post(route('register.attempt'), [
        'name' => 'Budi',
        'email' => 'budi@email.com',
        'no_wa' => '081234567890',
        'paket_id' => $paket->id,
        'tgl_jatuh_tempo' => 5,
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
    ])->assertSessionHasErrors('paket_id');
});
