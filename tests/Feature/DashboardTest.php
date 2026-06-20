<?php

use App\Models\Pelanggan;
use App\Models\Tagihan;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

it('renders the dashboard with stats, trend and due lists', function () {
    $pelanggan = Pelanggan::factory()->create(['nama' => 'Budi Tagihan']);
    Tagihan::factory()->for($pelanggan)->overdue()->create([
        'tanggal_jatuh_tempo' => now('Asia/Jakarta')->subDays(2),
    ]);

    $this->actingAs($this->admin)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('Tren Pendapatan')
        ->assertSee('Lewat Tempo')
        ->assertSee('Budi Tagihan');
});

it('shows upcoming due bills on the dashboard', function () {
    $pelanggan = Pelanggan::factory()->create(['nama' => 'Siti Jatuh Tempo']);
    Tagihan::factory()->for($pelanggan)->create([
        'status' => 'unpaid',
        'tanggal_jatuh_tempo' => now('Asia/Jakarta')->addDays(2),
    ]);

    $this->actingAs($this->admin)
        ->get(route('dashboard'))
        ->assertSee('Siti Jatuh Tempo');
});
