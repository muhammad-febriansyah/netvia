<?php

use App\Enums\NotifikasiChannel;
use App\Enums\NotifikasiJenis;
use App\Models\MessageTemplate;
use App\Models\Pelanggan;
use App\Models\Tagihan;
use App\Services\MessageTemplateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

it('renders all placeholders with formatted values', function () {
    MessageTemplate::create([
        'jenis' => NotifikasiJenis::ReminderDue->value,
        'channel' => NotifikasiChannel::Whatsapp->value,
        'subject' => null,
        'body' => '{nama}|{kode_pelanggan}|{nomor_tagihan}|{periode}|{jumlah}|{jatuh_tempo}|{link_bayar}',
        'is_active' => true,
    ]);

    $pelanggan = Pelanggan::factory()->create([
        'nama' => 'Budi Santoso',
        'kode_pelanggan' => 'PLG-000123',
    ]);

    $tagihan = Tagihan::factory()->for($pelanggan)->create([
        'nomor_tagihan' => 'INV/202606/000045',
        'periode' => Carbon::parse('2026-06-01'),
        'jumlah' => 150000,
        'tanggal_jatuh_tempo' => Carbon::parse('2026-06-05'),
    ]);

    $rendered = app(MessageTemplateService::class)
        ->render(NotifikasiJenis::ReminderDue, NotifikasiChannel::Whatsapp, $tagihan);

    expect($rendered['body'])
        ->toContain('Budi Santoso')
        ->toContain('PLG-000123')
        ->toContain('INV/202606/000045')
        ->toContain('Juni 2026')
        ->toContain('Rp 150.000')
        ->toContain('5 Juni 2026')
        ->toContain($tagihan->public_token);
});

it('renders the email subject with placeholders', function () {
    MessageTemplate::create([
        'jenis' => NotifikasiJenis::InvoiceBaru->value,
        'channel' => NotifikasiChannel::Email->value,
        'subject' => 'Tagihan {nomor_tagihan}',
        'body' => 'Halo {nama}',
        'is_active' => true,
    ]);

    $tagihan = Tagihan::factory()->create(['nomor_tagihan' => 'INV/202606/000099']);

    $rendered = app(MessageTemplateService::class)
        ->render(NotifikasiJenis::InvoiceBaru, NotifikasiChannel::Email, $tagihan);

    expect($rendered['subject'])->toBe('Tagihan INV/202606/000099');
});

it('falls back to a default body when no template exists', function () {
    $tagihan = Tagihan::factory()->create();

    $rendered = app(MessageTemplateService::class)
        ->render(NotifikasiJenis::StrukLunas, NotifikasiChannel::Whatsapp, $tagihan);

    expect($rendered['body'])->toContain($tagihan->pelanggan->nama)
        ->and($rendered['subject'])->toBeNull();
});
