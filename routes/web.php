<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaketController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

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
});
