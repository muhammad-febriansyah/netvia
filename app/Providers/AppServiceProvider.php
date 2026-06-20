<?php

namespace App\Providers;

use App\Repositories\SettingRepository;
use App\Services\Whatsapp\LogWhatsappDriver;
use App\Services\Whatsapp\WhatsappService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Throwable;

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

        // admin (staff) bypasses all permission checks.
        Gate::before(function ($user, string $ability): ?bool {
            return $user->hasRole('admin') ? true : null;
        });

        $this->shareSiteIdentity();
    }

    /**
     * Expose company name + logo (from the settings table) to every view as $site.
     */
    private function shareSiteIdentity(): void
    {
        $site = null;

        View::composer('*', function ($view) use (&$site): void {
            if ($view->offsetExists('site')) {
                return;
            }

            if ($site === null) {
                $name = config('app.name', 'Netvia');
                $logo = null;

                try {
                    if (Schema::hasTable('settings')) {
                        $settings = app(SettingRepository::class);
                        $name = $settings->get('nama_perusahaan') ?: $name;
                        $logo = $settings->get('logo');
                    }
                } catch (Throwable) {
                    // Database not ready (e.g. during migrate) — fall back to defaults.
                }

                $site = ['nama_perusahaan' => $name, 'logo' => $logo];
            }

            $view->with('site', $site);
        });
    }
}
