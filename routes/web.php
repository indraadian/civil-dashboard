<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CivilController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImportTemplateController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\QuickCountController;
use App\Http\Controllers\RtController;
use App\Http\Controllers\RwController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TpsController;
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

    // Quick Count TPS
    Route::get('/quick-counts', [QuickCountController::class, 'index'])->name('quick-counts.index');
    Route::get('/quick-counts/data', [QuickCountController::class, 'data'])->name('quick-counts.data');
    Route::post('/quick-counts', [QuickCountController::class, 'store'])->name('quick-counts.store');
    Route::get('/quick-counts/{quickCount}/edit', [QuickCountController::class, 'edit'])->name('quick-counts.edit');
    Route::put('/quick-counts/{quickCount}', [QuickCountController::class, 'update'])->name('quick-counts.update');
    Route::delete('/quick-counts/{quickCount}', [QuickCountController::class, 'destroy'])->name('quick-counts.destroy');
    Route::post('/quick-counts/delete-bulk', [QuickCountController::class, 'destroyBulk'])->name('quick-counts.destroyBulk');

    Route::get('/imports/template/{module}', [ImportTemplateController::class, 'download'])->name('imports.template');
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
    Route::get('/api/dashboard/stats', [DashboardController::class, 'stats'])->name('api.dashboard.stats');

    // Master TPS (Viewable by all roles)
    Route::get('/settings/tps', [TpsController::class, 'index'])->name('settings.tps');
    Route::get('/settings/tps/data', [TpsController::class, 'data'])->name('settings.tps.data');

    // Admin & Super Admin operational routes
    Route::middleware(['role:admin'])->group(function () {

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
        Route::match(['get', 'post'], '/settings/rws/export', [RwController::class, 'export'])->name('settings.rws.export');
        Route::post('/settings/rws/import', [RwController::class, 'import'])->name('settings.rws.import');
        Route::get('/settings/rws/{rw}/edit', [RwController::class, 'edit'])->name('settings.rws.edit');
        Route::put('/settings/rws/{rw}', [RwController::class, 'update'])->name('settings.rws.update');
        Route::delete('/settings/rws/{rw}', [RwController::class, 'destroy'])->name('settings.rws.destroy');
        Route::post('/settings/rws/delete-bulk', [RwController::class, 'destroyBulk'])->name('settings.rws.destroyBulk');

        // Master RT management
        Route::get('/settings/rts', [RtController::class, 'index'])->name('settings.rts');
        Route::get('/settings/rts/data', [RtController::class, 'data'])->name('settings.rts.data');
        Route::post('/settings/rts', [RtController::class, 'store'])->name('settings.rts.store');
        Route::match(['get', 'post'], '/settings/rts/export', [RtController::class, 'export'])->name('settings.rts.export');
        Route::post('/settings/rts/import', [RtController::class, 'import'])->name('settings.rts.import');
        Route::get('/settings/rts/{rt}/edit', [RtController::class, 'edit'])->name('settings.rts.edit');
        Route::put('/settings/rts/{rt}', [RtController::class, 'update'])->name('settings.rts.update');
        Route::delete('/settings/rts/{rt}', [RtController::class, 'destroy'])->name('settings.rts.destroy');
        Route::post('/settings/rts/delete-bulk', [RtController::class, 'destroyBulk'])->name('settings.rts.destroyBulk');

        // User Management write actions & import/export
        Route::match(['get', 'post'], '/settings/users/export', [SettingController::class, 'exportUsers'])->name('settings.users.export');
        Route::post('/settings/users/import', [SettingController::class, 'importUsers'])->name('settings.users.import');

        // Master TPS write actions & import/export (Admin / Super Admin)
        Route::post('/settings/tps', [TpsController::class, 'store'])->name('settings.tps.store');
        Route::match(['get', 'post'], '/settings/tps/export', [TpsController::class, 'export'])->name('settings.tps.export');
        Route::post('/settings/tps/import', [TpsController::class, 'import'])->name('settings.tps.import');
        Route::get('/settings/tps/{tp}/edit', [TpsController::class, 'edit'])->name('settings.tps.edit');
        Route::put('/settings/tps/{tp}', [TpsController::class, 'update'])->name('settings.tps.update');
        Route::delete('/settings/tps/{tp}', [TpsController::class, 'destroy'])->name('settings.tps.destroy');
        Route::post('/settings/tps/delete-bulk', [TpsController::class, 'destroyBulk'])->name('settings.tps.destroyBulk');

        // Quick Count export & import (Admin / Super Admin)
        Route::match(['get', 'post'], '/quick-counts/export', [QuickCountController::class, 'export'])->name('quick-counts.export');
        Route::post('/quick-counts/import', [QuickCountController::class, 'import'])->name('quick-counts.import');
    });

    // Super Admin ONLY maintenance routes
    Route::middleware('role:super_admin')->group(function () {
        Route::get('/settings/general', [SettingController::class, 'general'])->name('settings.general');
        Route::post('/settings/migrate', [SettingController::class, 'migrate'])->name('settings.migrate');
        Route::post('/settings/patch-locations', [SettingController::class, 'patchLocations'])->name('settings.patch-locations');
    });
});