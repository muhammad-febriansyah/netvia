<?php

use App\Mail\NotifikasiMail;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->superAdmin = User::factory()->create();
    $this->superAdmin->assignRole('super_admin');

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

it('shows the settings page to a user who can view', function () {
    $this->actingAs($this->superAdmin)
        ->get(route('pengaturan.index'))
        ->assertOk()
        ->assertSee('Profil Perusahaan');
});

it('saves profil settings', function () {
    $this->actingAs($this->superAdmin)
        ->put(route('pengaturan.updateProfil'), [
            'nama_perusahaan' => 'Netvia Jaya',
            'email_cs' => 'cs@netvia.id',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(Setting::where('key', 'nama_perusahaan')->value('value'))->toBe('Netvia Jaya')
        ->and(Setting::where('key', 'email_cs')->value('value'))->toBe('cs@netvia.id');
});

it('stores an uploaded logo and persists its path', function () {
    Storage::fake('public');

    $this->actingAs($this->superAdmin)
        ->put(route('pengaturan.updateProfil'), [
            'nama_perusahaan' => 'Netvia',
            'logo' => UploadedFile::fake()->image('logo.png'),
        ])
        ->assertRedirect();

    $path = Setting::where('key', 'logo')->value('value');
    expect($path)->not->toBeNull();
    Storage::disk('public')->assertExists($path);
});

it('saves billing settings and normalizes the boolean toggle', function () {
    $this->actingAs($this->superAdmin)
        ->put(route('pengaturan.updateBilling'), [
            'generate_hari_sebelum_jatuh_tempo' => 5,
            'reminder_overdue_hari' => '1,3,7',
            // kirim_invoice_baru unchecked => absent
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(Setting::where('key', 'generate_hari_sebelum_jatuh_tempo')->value('value'))->toBe('5')
        ->and(Setting::where('key', 'kirim_invoice_baru')->value('value'))->toBe('0');
});

it('rejects invalid billing input with indonesian messages', function () {
    $this->actingAs($this->superAdmin)
        ->put(route('pengaturan.updateBilling'), [
            'generate_hari_sebelum_jatuh_tempo' => 99,
            'reminder_overdue_hari' => 'abc',
        ])
        ->assertSessionHasErrors([
            'generate_hari_sebelum_jatuh_tempo' => 'Jumlah hari antara 0 sampai 28.',
            'reminder_overdue_hari' => 'Format hari reminder harus angka dipisah koma, mis. 1,3,7.',
        ]);
});

it('forbids a non-super-admin from viewing or updating settings', function () {
    $this->actingAs($this->admin)->get(route('pengaturan.index'))->assertForbidden();

    $this->actingAs($this->admin)
        ->put(route('pengaturan.updateProfil'), ['nama_perusahaan' => 'X'])
        ->assertForbidden();
});

it('sends a whatsapp test message', function () {
    $this->actingAs($this->superAdmin)
        ->postJson(route('pengaturan.tesWhatsapp'), ['no_wa' => '081234567890'])
        ->assertOk()
        ->assertJsonPath('success', true);
});

it('sends an email test message', function () {
    Mail::fake();

    $this->actingAs($this->superAdmin)
        ->postJson(route('pengaturan.tesEmail'), ['email' => 'test@netvia.id'])
        ->assertOk()
        ->assertJsonPath('success', true);

    Mail::assertSent(NotifikasiMail::class);
});
