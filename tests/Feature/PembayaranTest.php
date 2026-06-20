<?php

use App\Enums\PembayaranMetode;
use App\Enums\PembayaranStatus;
use App\Enums\TagihanStatus;
use App\Events\TagihanLunas;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    config()->set('services.pakasir.base_url', 'https://pakasir.test');
    config()->set('services.pakasir.project', 'netvia');
    config()->set('services.pakasir.api_key', 'secret-key');

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

function fakePakasirPaid(): void
{
    Http::fake([
        'pakasir.test/api/transactiondetail*' => Http::response([
            'transaction' => ['status' => 'completed', 'payment_method' => 'qris'],
        ]),
    ]);
}

it('opens a qris payment for an unpaid tagihan', function () {
    $tagihan = Tagihan::factory()->create(['status' => 'unpaid', 'jumlah' => 150000]);

    $this->actingAs($this->admin)
        ->postJson(route('pembayaran.createQris', $tagihan))
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.order_id', fn ($v) => str_starts_with($v, 'TGH-'.$tagihan->id.'-'));

    $pembayaran = Pembayaran::first();
    expect($pembayaran->status)->toBe(PembayaranStatus::Pending);
    expect($pembayaran->metode)->toBe(PembayaranMetode::QrisPakasir);
    expect($pembayaran->jumlah_bayar)->toBe(150000);
    expect($pembayaran->payment_url)->toContain('pakasir.test/pay/netvia/150000');
});

it('reuses an active qris instead of creating a duplicate', function () {
    $tagihan = Tagihan::factory()->create(['status' => 'unpaid']);

    $this->actingAs($this->admin)->postJson(route('pembayaran.createQris', $tagihan))->assertOk();
    $this->actingAs($this->admin)->postJson(route('pembayaran.createQris', $tagihan))->assertOk();

    expect(Pembayaran::count())->toBe(1);
});

it('refuses qris for a paid tagihan', function () {
    $tagihan = Tagihan::factory()->paid()->create();

    $this->actingAs($this->admin)
        ->postJson(route('pembayaran.createQris', $tagihan))
        ->assertStatus(422)
        ->assertJsonPath('success', false);

    expect(Pembayaran::count())->toBe(0);
});

it('confirms a manual payment and marks the tagihan paid', function () {
    Event::fake([TagihanLunas::class]);
    $tagihan = Tagihan::factory()->create(['status' => 'unpaid', 'jumlah' => 200000]);

    $this->actingAs($this->admin)
        ->postJson(route('pembayaran.konfirmasiManual', $tagihan), [
            'metode' => 'transfer_manual',
            'jumlah_bayar' => 'Rp 200.000',
        ])
        ->assertOk()
        ->assertJsonPath('success', true);

    $tagihan->refresh();
    expect($tagihan->status)->toBe(TagihanStatus::Paid);
    expect($tagihan->paid_at)->not->toBeNull();

    $pembayaran = Pembayaran::first();
    expect($pembayaran->status)->toBe(PembayaranStatus::Success);
    expect($pembayaran->jumlah_bayar)->toBe(200000);
    expect($pembayaran->dikonfirmasi_by)->toBe($this->admin->id);

    Event::assertDispatched(TagihanLunas::class);
});

it('settles a tagihan from the pakasir webhook, verifying with the gateway', function () {
    Event::fake([TagihanLunas::class]);
    fakePakasirPaid();

    $tagihan = Tagihan::factory()->create(['status' => 'unpaid', 'jumlah' => 150000]);
    $pembayaran = Pembayaran::factory()->create([
        'tagihan_id' => $tagihan->id,
        'metode' => 'qris_pakasir',
        'status' => 'pending',
        'jumlah_bayar' => 150000,
        'pakasir_order_id' => 'TGH-'.$tagihan->id.'-abc123',
    ]);

    $this->postJson(route('pembayaran.webhook'), [
        'order_id' => $pembayaran->pakasir_order_id,
        'status' => 'completed',
        'amount' => 150000,
    ])->assertOk();

    expect($tagihan->fresh()->status)->toBe(TagihanStatus::Paid);
    expect($pembayaran->fresh()->status)->toBe(PembayaranStatus::Success);
    Event::assertDispatched(TagihanLunas::class);
});

it('is idempotent when the webhook fires twice', function () {
    Event::fake([TagihanLunas::class]);
    fakePakasirPaid();

    $tagihan = Tagihan::factory()->create(['status' => 'unpaid', 'jumlah' => 150000]);
    $pembayaran = Pembayaran::factory()->create([
        'tagihan_id' => $tagihan->id,
        'metode' => 'qris_pakasir',
        'status' => 'pending',
        'jumlah_bayar' => 150000,
        'pakasir_order_id' => 'TGH-'.$tagihan->id.'-dup',
    ]);

    $payload = ['order_id' => $pembayaran->pakasir_order_id, 'status' => 'completed', 'amount' => 150000];

    $this->postJson(route('pembayaran.webhook'), $payload)->assertOk();
    $this->postJson(route('pembayaran.webhook'), $payload)->assertOk();

    expect(Pembayaran::where('status', PembayaranStatus::Success)->count())->toBe(1);
    Event::assertDispatchedTimes(TagihanLunas::class, 1);
});

it('does not settle when the gateway says the payment is not completed', function () {
    Http::fake([
        'pakasir.test/api/transactiondetail*' => Http::response([
            'transaction' => ['status' => 'pending'],
        ]),
    ]);

    $tagihan = Tagihan::factory()->create(['status' => 'unpaid', 'jumlah' => 150000]);
    $pembayaran = Pembayaran::factory()->create([
        'tagihan_id' => $tagihan->id,
        'metode' => 'qris_pakasir',
        'status' => 'pending',
        'jumlah_bayar' => 150000,
        'pakasir_order_id' => 'TGH-'.$tagihan->id.'-notyet',
    ]);

    $this->postJson(route('pembayaran.webhook'), [
        'order_id' => $pembayaran->pakasir_order_id,
    ])->assertOk();

    expect($tagihan->fresh()->status)->toBe(TagihanStatus::Unpaid);
    expect($pembayaran->fresh()->status)->toBe(PembayaranStatus::Pending);
});

it('answers 200 to a webhook for an unknown order', function () {
    $this->postJson(route('pembayaran.webhook'), ['order_id' => 'TGH-999-nope'])
        ->assertOk();
});

it('renders the public invoice page and opens a qris', function () {
    $this->withoutVite();
    $tagihan = Tagihan::factory()->create(['status' => 'unpaid', 'jumlah' => 150000]);

    $this->get(route('publik.invoice', $tagihan->public_token))
        ->assertOk()
        ->assertSee($tagihan->nomor_tagihan)
        ->assertSee('Bayar Sekarang');

    expect(Pembayaran::where('tagihan_id', $tagihan->id)->count())->toBe(1);
});

it('shows lunas state on the public page for a paid tagihan', function () {
    $this->withoutVite();
    $tagihan = Tagihan::factory()->paid()->create();

    $this->get(route('publik.invoice', $tagihan->public_token))
        ->assertOk()
        ->assertSee('Tagihan Sudah Lunas');

    expect(Pembayaran::count())->toBe(0);
});

it('blocks the struk pdf until the tagihan is paid', function () {
    $tagihan = Tagihan::factory()->create(['status' => 'unpaid']);

    $this->get(route('publik.struk', $tagihan->public_token))->assertForbidden();
});

it('downloads the struk pdf once paid', function () {
    $tagihan = Tagihan::factory()->paid()->create();
    Pembayaran::factory()->create([
        'tagihan_id' => $tagihan->id,
        'metode' => 'transfer_manual',
        'status' => 'success',
        'dibayar_at' => now(),
    ]);

    $response = $this->get(route('publik.struk', $tagihan->public_token));
    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});
