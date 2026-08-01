<?php

namespace App\Providers;

use App\Events\ExportCompleted;
use App\Events\ExportFailed;
use App\Events\ImportCompleted;
use App\Events\ImportFailed;
use App\Listeners\HandleExportCompleted;
use App\Listeners\HandleExportFailed;
use App\Listeners\HandleImportCompleted;
use App\Listeners\HandleImportFailed;
use App\Models\CivilExport;
use App\Models\CivilImport;
use App\Policies\CivilExportPolicy;
use App\Policies\CivilImportPolicy;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
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
        // ── Super Admin Wildcard Access ──────────────────────────────────────────
        Gate::before(function ($user, $ability) {
            if ($user->isSuperAdmin()) {
                return true;
            }
        });

        // ── Policy Registration ────────────────────────────────────────────────
        Gate::policy(CivilImport::class, CivilImportPolicy::class);
        Gate::policy(CivilExport::class, CivilExportPolicy::class);

        // ── Event Listeners ────────────────────────────────────────────────────
        Event::listen(ImportCompleted::class, HandleImportCompleted::class);
        Event::listen(ImportFailed::class, HandleImportFailed::class);
        Event::listen(ExportCompleted::class, HandleExportCompleted::class);
        Event::listen(ExportFailed::class, HandleExportFailed::class);

        // ── Auto Migration (existing behavior) ────────────────────────────────
        if ($this->app->runningInConsole() || $this->app->environment('testing')) {
            return;
        }

        if (!filter_var(env('APP_AUTO_MIGRATE', false), FILTER_VALIDATE_BOOL)) {
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

