<?php

namespace App\Providers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole() || $this->app->environment('testing')) {
            return;
        }

        if (! filter_var(env('APP_AUTO_MIGRATE', false), FILTER_VALIDATE_BOOL)) {
            return;
        }

        $this->app->booted(function () {
            $cacheKey = 'app.auto-migrate.last-run';

            if (Cache::has($cacheKey)) {
                return;
            }

            try {
                $exitCode = Artisan::call('migrate', [
                    '--force' => true,
                    '--no-interaction' => true,
                ]);

                if ($exitCode === 0) {
                    Cache::put($cacheKey, now()->toDateTimeString(), now()->addMinutes(15));
                    Log::info('Auto migrations completed successfully.');
                } else {
                    Log::warning('Auto migrations failed.', ['exit_code' => $exitCode]);
                }
            } catch (\Throwable $e) {
                Log::error('Auto migrations error.', ['message' => $e->getMessage()]);
            }
        });
    }
}
