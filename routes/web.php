<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CivilController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Civils — accessible by all authenticated users
    Route::get('/civils', [CivilController::class, 'index'])->name('civils');
    Route::get('/civils/data', [CivilController::class, 'data'])->name('api.civils.data');
    Route::get('/civils/{civil}/edit', [CivilController::class, 'edit'])->name('civils.edit');
    Route::put('/civils/{civil}', [CivilController::class, 'update'])->name('civils.update');

    // Import/Export progress polling (accessible by owner or admin)
    Route::get('/imports/{import}', [CivilController::class, 'importProgress'])->name('civils.import.progress');
    Route::get('/exports/{export}', [CivilController::class, 'exportProgress'])->name('civils.export.progress');
    Route::get('/exports/{export}/download', [CivilController::class, 'exportDownload'])->name('civils.export.download');

    // Admin-only routes
    Route::middleware('role:admin')->group(function () {

        // Civils management
        Route::post('/civils', [CivilController::class, 'store'])->name('civils.store');
        Route::delete('/civils/{civil}', [CivilController::class, 'destroy'])->name('civils.destroy');
        Route::post('/civils/delete-bulk', [CivilController::class, 'destroyBulk'])->name('civils.destroyBulk');
        Route::post('/civils/export', [CivilController::class, 'export'])->name('civils.export');
        Route::post('/civils/import', [CivilController::class, 'import'])->name('civils.import');

        // Settings
        Route::get('/settings', [SettingController::class, 'index'])->name('settings');
        Route::get('/settings/general', [SettingController::class, 'general'])->name('settings.general');
        Route::post('/settings/migrate', [SettingController::class, 'migrate'])->name('settings.migrate');

        // User management
        Route::get('/settings/users', [SettingController::class, 'users'])->name('settings.users');
        Route::post('/settings/users', [SettingController::class, 'storeUser'])->name('settings.users.store');
        Route::get('/settings/users/{user}/edit', [SettingController::class, 'editUser'])->name('settings.users.edit');
        Route::put('/settings/users/{user}', [SettingController::class, 'updateUser'])->name('settings.users.update');
        Route::delete('/settings/users/{user}', [SettingController::class, 'destroyUser'])->name('settings.users.destroy');
    });
});