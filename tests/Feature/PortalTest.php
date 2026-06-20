<?php

use App\Models\Paket;
use App\Models\Pelanggan;
use App\Models\PemutusanLangganan;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->pelanggan = Pelanggan::factory()->create(['status' => 'aktif']);
    $this->customer = User::factory()->create(['pelanggan_id' => $this->pelanggan->id]);
    $this->customer->assignRole('customer');

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

it('shows the customer portal pages', function (string $route) {
    $this->withoutVite();
    $this->actingAs($this->customer)->get(route($route))->assertOk();
})->with(['portal.dashboard', 'portal.langganan', 'portal.riwayat', 'portal.pemutusan']);

it('lets a customer change their paket', function () {
    $newPaket = Paket::factory()->create(['is_active' => true]);

    $this->actingAs($this->customer)
        ->put(route('portal.langgananUpdate'), ['paket_id' => $newPaket->id])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($this->pelanggan->fresh()->paket_id)->toBe($newPaket->id);
});

it('lets an active customer submit a termination request with a photo', function () {
    Storage::fake('public');

    $this->actingAs($this->customer)
        ->post(route('portal.pemutusanStore'), [
            'alasan' => 'Pindah rumah',
            'foto' => UploadedFile::fake()->image('bukti.jpg'),
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $req = PemutusanLangganan::where('pelanggan_id', $this->pelanggan->id)->first();
    expect($req)->not->toBeNull()
        ->and($req->status->value)->toBe('pending')
        ->and($req->alasan)->toBe('Pindah rumah');
    Storage::disk('public')->assertExists($req->foto);
});

it('requires reason and photo for termination', function () {
    $this->actingAs($this->customer)
        ->post(route('portal.pemutusanStore'), [])
        ->assertSessionHasErrors(['alasan', 'foto']);
});

it('blocks a second pending termination request', function () {
    Storage::fake('public');
    PemutusanLangganan::factory()->create(['pelanggan_id' => $this->pelanggan->id, 'status' => 'pending']);

    $this->actingAs($this->customer)
        ->post(route('portal.pemutusanStore'), [
            'alasan' => 'lagi',
            'foto' => UploadedFile::fake()->image('b.jpg'),
        ])
        ->assertRedirect()
        ->assertSessionHas('error');

    expect(PemutusanLangganan::where('pelanggan_id', $this->pelanggan->id)->count())->toBe(1);
});

it('blocks termination when the pelanggan is not active', function () {
    Storage::fake('public');
    $this->pelanggan->update(['status' => 'nonaktif']);

    $this->actingAs($this->customer)
        ->post(route('portal.pemutusanStore'), [
            'alasan' => 'x',
            'foto' => UploadedFile::fake()->image('b.jpg'),
        ])
        ->assertSessionHas('error');

    expect(PemutusanLangganan::count())->toBe(0);
});

it('forbids staff from the customer portal', function () {
    $this->actingAs($this->admin)->get(route('portal.dashboard'))->assertForbidden();
});

it('forbids customer from the staff dashboard', function () {
    $this->actingAs($this->customer)->get(route('dashboard'))->assertForbidden();
});
