<?php

use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    $this->customer = User::factory()->create();
    $this->customer->assignRole('customer');
});

it('shows the four report index pages', function (string $route) {
    $this->actingAs($this->admin)->get(route($route))->assertSuccessful();
})->with([
    'laporan.pendapatan',
    'laporan.tunggakan',
    'laporan.pembayaran',
    'laporan.pelanggan',
]);

it('returns datatable json for the revenue report', function () {
    Pembayaran::factory()->success()->count(3)->create(['dibayar_at' => now()]);

    $this->actingAs($this->admin)
        ->getJson(route('laporan.pendapatanData'))
        ->assertSuccessful()
        ->assertJsonStructure(['data', 'recordsTotal', 'recordsFiltered']);
});

it('only includes successful payments in the revenue report', function () {
    Pembayaran::factory()->success()->create(['dibayar_at' => now()]);
    Pembayaran::factory()->create(['status' => 'pending', 'dibayar_at' => now()]);

    $json = $this->actingAs($this->admin)
        ->getJson(route('laporan.pendapatanData'))
        ->json();

    expect($json['recordsTotal'])->toBe(1);
});

it('returns the arrears report for unpaid and overdue bills', function () {
    Tagihan::factory()->create(['status' => 'unpaid']);
    Tagihan::factory()->overdue()->create();
    Tagihan::factory()->paid()->create();

    $json = $this->actingAs($this->admin)
        ->getJson(route('laporan.tunggakanData'))
        ->json();

    expect($json['recordsTotal'])->toBe(2);
});

it('downloads an excel export', function () {
    Excel::fake();

    $this->actingAs($this->admin)
        ->get(route('laporan.pendapatanExport'))
        ->assertSuccessful();

    Excel::assertDownloaded('laporan-pendapatan.xlsx');
});

it('downloads a pdf export', function () {
    Tagihan::factory()->overdue()->create();

    $this->actingAs($this->admin)
        ->get(route('laporan.tunggakanExport', ['type' => 'pdf']))
        ->assertDownload('laporan-tunggakan.pdf');
});

it('forbids export without the laporan.export permission', function () {
    $viewer = User::factory()->create();
    $viewer->givePermissionTo('laporan.view');

    $this->actingAs($viewer)
        ->get(route('laporan.pendapatanExport'))
        ->assertForbidden();
});

it('forbids users without laporan.view', function () {
    $stranger = User::factory()->create();

    $this->actingAs($stranger)
        ->get(route('laporan.pendapatan'))
        ->assertForbidden();
});

it('forbids customer from accessing reports', function () {
    $this->actingAs($this->customer)
        ->get(route('laporan.pelanggan'))
        ->assertForbidden();
});
