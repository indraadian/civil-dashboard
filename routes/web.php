<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CivilController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RtController;
use App\Http\Controllers\RwController;
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
Route::post('/register', [AuthController::class, 'showRegistrationForm'])->name('register.submit');

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
    Route::get('/imports/{import}/report', [CivilController::class, 'importReport'])->name('civils.import.report');
    Route::get('/exports/{export}', [CivilController::class, 'exportProgress'])->name('civils.export.progress');
    Route::get('/exports/{export}/download', [CivilController::class, 'exportDownload'])->name('civils.export.download');
    Route::get('/active-tasks', [CivilController::class, 'activeTasks'])->name('civils.active-tasks');

    // Notifications API
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');

    // Helper API for RW -> RT dropdowns
    Route::get('/api/rws/{rw}/rts', [RwController::class, 'getRts'])->name('api.rws.rts');

    // Admin & Super Admin operational routes
    Route::middleware(['role:super_admin|admin'])->group(function () {

        // Civils management
        Route::post('/civils', [CivilController::class, 'store'])->name('civils.store');
        Route::delete('/civils/{civil}', [CivilController::class, 'destroy'])->name('civils.destroy');
        Route::post('/civils/delete-bulk', [CivilController::class, 'destroyBulk'])->name('civils.destroyBulk');
        Route::post('/civils/export', [CivilController::class, 'export'])->name('civils.export');
        Route::post('/civils/import', [CivilController::class, 'import'])->name('civils.import');

        // Settings index
        Route::get('/settings', [SettingController::class, 'index'])->name('settings');

        // User management
        Route::get('/settings/users', [SettingController::class, 'users'])->name('settings.users');
        Route::get('/settings/users/data', [SettingController::class, 'usersData'])->name('settings.users.data');
        Route::post('/settings/users', [SettingController::class, 'storeUser'])->name('settings.users.store');
        Route::get('/settings/users/{user}/edit', [SettingController::class, 'editUser'])->name('settings.users.edit');
        Route::put('/settings/users/{user}', [SettingController::class, 'updateUser'])->name('settings.users.update');
        Route::delete('/settings/users/{user}', [SettingController::class, 'destroyUser'])->name('settings.users.destroy');
        Route::post('/settings/users/delete-bulk', [SettingController::class, 'destroyUsersBulk'])->name('settings.users.destroyBulk');

        // Master RW management
        Route::get('/settings/rws', [RwController::class, 'index'])->name('settings.rws');
        Route::get('/settings/rws/data', [RwController::class, 'data'])->name('settings.rws.data');
        Route::post('/settings/rws', [RwController::class, 'store'])->name('settings.rws.store');
        Route::get('/settings/rws/{rw}/edit', [RwController::class, 'edit'])->name('settings.rws.edit');
        Route::put('/settings/rws/{rw}', [RwController::class, 'update'])->name('settings.rws.update');
        Route::delete('/settings/rws/{rw}', [RwController::class, 'destroy'])->name('settings.rws.destroy');
        Route::post('/settings/rws/delete-bulk', [RwController::class, 'destroyBulk'])->name('settings.rws.destroyBulk');

        // Master RT management
        Route::get('/settings/rts', [RtController::class, 'index'])->name('settings.rts');
        Route::get('/settings/rts/data', [RtController::class, 'data'])->name('settings.rts.data');
        Route::post('/settings/rts', [RtController::class, 'store'])->name('settings.rts.store');
        Route::get('/settings/rts/{rt}/edit', [RtController::class, 'edit'])->name('settings.rts.edit');
        Route::put('/settings/rts/{rt}', [RtController::class, 'update'])->name('settings.rts.update');
        Route::delete('/settings/rts/{rt}', [RtController::class, 'destroy'])->name('settings.rts.destroy');
        Route::post('/settings/rts/delete-bulk', [RtController::class, 'destroyBulk'])->name('settings.rts.destroyBulk');
    });

    // Super Admin ONLY maintenance routes
    Route::middleware('role:super_admin')->group(function () {
        Route::get('/settings/general', [SettingController::class, 'general'])->name('settings.general');
        Route::post('/settings/migrate', [SettingController::class, 'migrate'])->name('settings.migrate');
        Route::post('/settings/patch-locations', [SettingController::class, 'patchLocations'])->name('settings.patch-locations');
    });
});