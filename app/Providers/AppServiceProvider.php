<?php

namespace App\Providers;

use App\Services\Whatsapp\LogWhatsappDriver;
use App\Services\Whatsapp\WhatsappService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Only the "log" stub driver exists today; real drivers (cloud_api,
        // gateway, chatcepat) are wired here once implemented.
        $this->app->bind(WhatsappService::class, LogWhatsappDriver::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());

        // super_admin bypasses all permission checks.
        Gate::before(function ($user, string $ability): ?bool {
            return $user->hasRole('super_admin') ? true : null;
        });
    }
}
