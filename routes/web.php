<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoicePublikController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\PakasirWebhookController;
use App\Http\Controllers\PaketController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\PemutusanController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\TagihanController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    return redirect()->route(auth()->user()->isCustomer() ? 'portal.dashboard' : 'dashboard');
});

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.attempt');
    Route::get('register', [RegisterController::class, 'show'])->name('register');
    Route::post('register', [RegisterController::class, 'register'])->name('register.attempt');
});

// Public, unauthenticated payment surface.
Route::post('webhook/pakasir', PakasirWebhookController::class)->name('pembayaran.webhook');
Route::get('tagihan-publik/{token}', [InvoicePublikController::class, 'invoice'])->name('publik.invoice');
Route::get('tagihan-publik/{token}/struk', [InvoicePublikController::class, 'struk'])->name('publik.struk');

Route::middleware(['auth', 'active'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    /*
    |--- Staff area (admin) --------------------------------------------------
    */
    Route::middleware('role:admin')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::prefix('paket')->name('paket.')->controller(PaketController::class)->group(function () {
            Route::get('/', 'index')->name('index')->middleware('can:paket.view');
            Route::get('/data', 'data')->name('data')->middleware('can:paket.view');
            Route::get('/create', 'create')->name('create')->middleware('can:paket.create');
            Route::post('/', 'store')->name('store')->middleware('can:paket.create');
            Route::get('/{paket}/edit', 'edit')->name('edit')->middleware('can:paket.update');
            Route::put('/{paket}', 'update')->name('update')->middleware('can:paket.update');
            Route::delete('/{paket}', 'destroy')->name('destroy')->middleware('can:paket.delete');
            Route::patch('/{paket}/toggle', 'toggle')->name('toggle')->middleware('can:paket.update');
        });

        Route::prefix('pelanggan')->name('pelanggan.')->controller(PelangganController::class)->group(function () {
            Route::get('/', 'index')->name('index')->middleware('can:pelanggan.view');
            Route::get('/data', 'data')->name('data')->middleware('can:pelanggan.view');
            Route::get('/create', 'create')->name('create')->middleware('can:pelanggan.create');
            Route::post('/', 'store')->name('store')->middleware('can:pelanggan.create');
            Route::get('/{pelanggan}', 'show')->name('show')->middleware('can:pelanggan.view');
            Route::get('/{pelanggan}/edit', 'edit')->name('edit')->middleware('can:pelanggan.update');
            Route::put('/{pelanggan}', 'update')->name('update')->middleware('can:pelanggan.update');
            Route::patch('/{pelanggan}/activate', 'activate')->name('activate')->middleware('can:pelanggan.update');
            Route::delete('/{pelanggan}', 'destroy')->name('destroy')->middleware('can:pelanggan.delete');
        });

        Route::prefix('tagihan')->name('tagihan.')->controller(TagihanController::class)->group(function () {
            Route::get('/', 'index')->name('index')->middleware('can:tagihan.view');
            Route::get('/data', 'data')->name('data')->middleware('can:tagihan.view');
            Route::post('/generate-manual', 'generateManual')->name('generateManual')->middleware('can:tagihan.generate');
            Route::get('/{tagihan}', 'show')->name('show')->middleware('can:tagihan.view');
            Route::post('/{tagihan}/void', 'void')->name('void')->middleware('can:tagihan.void');
            Route::post('/{tagihan}/kirim-reminder', 'kirimReminder')->name('kirimReminder')->middleware('can:notifikasi.kirim');
        });

        Route::prefix('tagihan')->name('pembayaran.')->controller(PembayaranController::class)->group(function () {
            Route::post('/{tagihan}/bayar-qris', 'createQris')->name('createQris')->middleware('can:pembayaran.create_qris');
            Route::post('/{tagihan}/lunas-manual', 'konfirmasiManual')->name('konfirmasiManual')->middleware('can:pembayaran.konfirmasi');
            Route::get('/{tagihan}/cek-status', 'cekStatus')->name('cekStatus')->middleware('can:pembayaran.view');
        });

        Route::prefix('laporan')->name('laporan.')->controller(LaporanController::class)->middleware('can:laporan.view')->group(function () {
            Route::get('/pendapatan', 'pendapatan')->name('pendapatan');
            Route::get('/pendapatan/data', 'pendapatanData')->name('pendapatanData');
            Route::get('/pendapatan/export', 'pendapatanExport')->name('pendapatanExport')->middleware('can:laporan.export');

            Route::get('/tunggakan', 'tunggakan')->name('tunggakan');
            Route::get('/tunggakan/data', 'tunggakanData')->name('tunggakanData');
            Route::get('/tunggakan/export', 'tunggakanExport')->name('tunggakanExport')->middleware('can:laporan.export');

            Route::get('/pembayaran', 'pembayaran')->name('pembayaran');
            Route::get('/pembayaran/data', 'pembayaranData')->name('pembayaranData');
            Route::get('/pembayaran/export', 'pembayaranExport')->name('pembayaranExport')->middleware('can:laporan.export');

            Route::get('/pelanggan', 'pelanggan')->name('pelanggan');
            Route::get('/pelanggan/data', 'pelangganData')->name('pelangganData');
            Route::get('/pelanggan/export', 'pelangganExport')->name('pelangganExport')->middleware('can:laporan.export');
        });

        Route::prefix('notifikasi')->name('notifikasi.')->controller(NotifikasiController::class)->group(function () {
            Route::get('/', 'index')->name('index')->middleware('can:notifikasi.view');
            Route::get('/data', 'data')->name('data')->middleware('can:notifikasi.view');
            Route::post('/{log}/resend', 'resend')->name('resend')->middleware('can:notifikasi.kirim');
            Route::get('/template', 'template')->name('template')->middleware('can:notifikasi.template');
            Route::put('/template/{template}', 'templateUpdate')->name('templateUpdate')->middleware('can:notifikasi.template');
        });

        Route::prefix('pemutusan')->name('pemutusan.')->controller(PemutusanController::class)->middleware('can:pemutusan.kelola')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/data', 'data')->name('data');
            Route::post('/{pemutusan}/approve', 'approve')->name('approve');
            Route::post('/{pemutusan}/reject', 'reject')->name('reject');
        });

        Route::prefix('users')->name('user.')->controller(UserController::class)->group(function () {
            Route::get('/', 'index')->name('index')->middleware('can:user.view');
            Route::get('/data', 'data')->name('data')->middleware('can:user.view');
            Route::get('/create', 'create')->name('create')->middleware('can:user.create');
            Route::post('/', 'store')->name('store')->middleware('can:user.create');
            Route::get('/{user}/edit', 'edit')->name('edit')->middleware('can:user.update');
            Route::put('/{user}', 'update')->name('update')->middleware('can:user.update');
            Route::delete('/{user}', 'destroy')->name('destroy')->middleware('can:user.delete');
            Route::patch('/{user}/toggle', 'toggle')->name('toggle')->middleware('can:user.update');
            Route::post('/{user}/reset-password', 'resetPassword')->name('resetPassword')->middleware('can:user.update');
        });

        Route::prefix('pengaturan')->name('pengaturan.')->controller(PengaturanController::class)->group(function () {
            Route::get('/', 'index')->name('index')->middleware('can:pengaturan.view');

            Route::middleware('can:pengaturan.update')->group(function () {
                Route::put('/profil', 'updateProfil')->name('updateProfil');
                Route::put('/billing', 'updateBilling')->name('updateBilling');
                Route::put('/pembayaran', 'updatePembayaran')->name('updatePembayaran');
                Route::put('/whatsapp', 'updateWhatsapp')->name('updateWhatsapp');
                Route::put('/email', 'updateEmail')->name('updateEmail');
                Route::post('/whatsapp/tes', 'tesWhatsapp')->name('tesWhatsapp');
                Route::post('/email/tes', 'tesEmail')->name('tesEmail');
            });
        });
    });

    /*
    |--- Customer portal -----------------------------------------------------
    */
    Route::middleware('role:customer')->prefix('portal')->name('portal.')->controller(PortalController::class)->group(function () {
        Route::get('/', 'dashboard')->name('dashboard');
        Route::get('/langganan', 'langganan')->name('langganan');
        Route::put('/langganan', 'updateLangganan')->name('langgananUpdate');
        Route::get('/riwayat', 'riwayat')->name('riwayat');
        Route::get('/pemutusan', 'pemutusan')->name('pemutusan');
        Route::post('/pemutusan', 'storePemutusan')->name('pemutusanStore');
    });
});
