<?php

use App\Jobs\KirimWhatsappNotifikasi;
use App\Models\MessageTemplate;
use App\Models\NotifikasiLog;
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

    $this->finance = User::factory()->create();
    $this->finance->assignRole('finance');
});

it('lists notifikasi logs via the datatable', function () {
    NotifikasiLog::factory()->count(3)->create();

    $this->actingAs($this->admin)
        ->getJson(route('notifikasi.data'))
        ->assertOk()
        ->assertJsonPath('recordsTotal', 3);
});

it('filters logs by status', function () {
    NotifikasiLog::factory()->create(['status' => 'sent']);
    NotifikasiLog::factory()->create(['status' => 'failed']);

    $this->actingAs($this->admin)
        ->getJson(route('notifikasi.data', ['status' => 'failed']))
        ->assertOk()
        ->assertJsonPath('recordsFiltered', 1);
});

it('resends a notification', function () {
    Queue::fake();
    $pelanggan = Pelanggan::factory()->create(['no_wa' => '6281234567890']);
    $tagihan = Tagihan::factory()->create(['pelanggan_id' => $pelanggan->id]);
    $log = NotifikasiLog::factory()->create([
        'tagihan_id' => $tagihan->id,
        'pelanggan_id' => $pelanggan->id,
        'channel' => 'whatsapp',
        'jenis' => 'reminder_due',
        'status' => 'failed',
    ]);

    $this->actingAs($this->admin)
        ->postJson(route('notifikasi.resend', $log))
        ->assertOk()
        ->assertJsonPath('success', true);

    Queue::assertPushed(KirimWhatsappNotifikasi::class);
});

it('renders the template editor', function () {
    $this->withoutVite();
    MessageTemplate::factory()->create(['jenis' => 'reminder_due', 'channel' => 'whatsapp']);

    $this->actingAs($this->admin)
        ->get(route('notifikasi.template'))
        ->assertOk()
        ->assertSee('Template Pesan')
        ->assertSee('{link_bayar}');
});

it('updates a template body', function () {
    $template = MessageTemplate::factory()->create(['channel' => 'whatsapp', 'body' => 'lama']);

    $this->actingAs($this->admin)
        ->put(route('notifikasi.templateUpdate', $template), [
            'body' => 'Halo {nama}, tagihan {nomor_tagihan} jatuh tempo {jatuh_tempo}.',
            'is_active' => '1',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($template->fresh()->body)->toContain('{nama}');
});

it('requires a body when saving a template', function () {
    $template = MessageTemplate::factory()->create();

    $this->actingAs($this->admin)
        ->put(route('notifikasi.templateUpdate', $template), ['body' => ''])
        ->assertSessionHasErrors(['body' => 'Isi pesan wajib diisi.']);
});

it('forbids finance from resend and template management', function () {
    $this->withoutVite();
    $log = NotifikasiLog::factory()->create();
    $template = MessageTemplate::factory()->create();

    $this->actingAs($this->finance)->postJson(route('notifikasi.resend', $log))->assertForbidden();
    $this->actingAs($this->finance)->get(route('notifikasi.template'))->assertForbidden();
    $this->actingAs($this->finance)->put(route('notifikasi.templateUpdate', $template), ['body' => 'x'])->assertForbidden();

    // finance can still view the log list
    $this->actingAs($this->finance)->get(route('notifikasi.index'))->assertOk();
});
