<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\CivilController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImportTemplateController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\QuickCountController;
use App\Http\Controllers\RoleController;
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
    Route::middleware('permission:dashboard.view')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    });

    // Civils management
    Route::middleware('permission:civil.view')->group(function () {
        Route::get('/civils', [CivilController::class, 'index'])->name('civils');
        Route::get('/civils/data', [CivilController::class, 'data'])->name('api.civils.data');
    });

    Route::middleware('permission:civil.create')->group(function () {
        Route::post('/civils', [CivilController::class, 'store'])->name('civils.store');
    });

    Route::middleware('permission:civil.update')->group(function () {
        Route::get('/civils/{civil}/edit', [CivilController::class, 'edit'])->name('civils.edit');
        Route::put('/civils/{civil}', [CivilController::class, 'update'])->name('civils.update');
    });

    Route::middleware('permission:civil.delete')->group(function () {
        Route::delete('/civils/{civil}', [CivilController::class, 'destroy'])->name('civils.destroy');
        Route::post('/civils/delete-bulk', [CivilController::class, 'destroyBulk'])->name('civils.destroyBulk');
    });

    Route::middleware('permission:civil.export')->group(function () {
        Route::post('/civils/export', [CivilController::class, 'export'])->name('civils.export');
    });

    Route::middleware('permission:civil.import')->group(function () {
        Route::post('/civils/import', [CivilController::class, 'import'])->name('civils.import');
    });

    // Quick Count TPS
    Route::middleware('permission:quick-count.view')->group(function () {
        Route::get('/quick-counts', [QuickCountController::class, 'index'])->name('quick-counts.index');
        Route::get('/quick-counts/data', [QuickCountController::class, 'data'])->name('quick-counts.data');
    });

    Route::middleware('permission:quick-count.create')->group(function () {
        Route::post('/quick-counts', [QuickCountController::class, 'store'])->name('quick-counts.store');
    });

    Route::middleware('permission:quick-count.update')->group(function () {
        Route::get('/quick-counts/{quickCount}/edit', [QuickCountController::class, 'edit'])->name('quick-counts.edit');
        Route::put('/quick-counts/{quickCount}', [QuickCountController::class, 'update'])->name('quick-counts.update');
    });

    Route::middleware('permission:quick-count.delete')->group(function () {
        Route::delete('/quick-counts/{quickCount}', [QuickCountController::class, 'destroy'])->name('quick-counts.destroy');
        Route::post('/quick-counts/delete-bulk', [QuickCountController::class, 'destroyBulk'])->name('quick-counts.destroyBulk');
    });

    Route::middleware('permission:quick-count.export')->group(function () {
        Route::match(['get', 'post'], '/quick-counts/export', [QuickCountController::class, 'export'])->name('quick-counts.export');
    });

    Route::middleware('permission:quick-count.import')->group(function () {
        Route::post('/quick-counts/import', [QuickCountController::class, 'import'])->name('quick-counts.import');
    });

    // Template, Progress, Tasks & Notifications
    Route::get('/imports/template/{module}', [ImportTemplateController::class, 'download'])->name('imports.template');
    Route::get('/imports/{import}', [CivilController::class, 'importProgress'])->name('civils.import.progress');
    Route::get('/imports/{import}/report', [CivilController::class, 'importReport'])->name('civils.import.report');
    Route::get('/exports/{export}', [CivilController::class, 'exportProgress'])->name('civils.export.progress');
    Route::get('/exports/{export}/download', [CivilController::class, 'exportDownload'])->name('civils.export.download');
    Route::get('/active-tasks', [CivilController::class, 'activeTasks'])->name('civils.active-tasks');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
    Route::get('/api/rws/{rw}/rts', [RwController::class, 'getRts'])->name('api.rws.rts');
    Route::get('/api/dashboard/stats', [DashboardController::class, 'stats'])->name('api.dashboard.stats');

    // Settings index
    Route::get('/settings', [SettingController::class, 'index'])->name('settings');

    // User management
    Route::middleware('permission:user.view')->group(function () {
        Route::get('/settings/users', [SettingController::class, 'users'])->name('settings.users');
        Route::get('/settings/users/data', [SettingController::class, 'usersData'])->name('settings.users.data');
    });

    Route::middleware('permission:user.create')->group(function () {
        Route::post('/settings/users', [SettingController::class, 'storeUser'])->name('settings.users.store');
    });

    Route::middleware('permission:user.update')->group(function () {
        Route::get('/settings/users/{user}/edit', [SettingController::class, 'editUser'])->name('settings.users.edit');
        Route::put('/settings/users/{user}', [SettingController::class, 'updateUser'])->name('settings.users.update');
    });

    Route::middleware('permission:user.delete')->group(function () {
        Route::delete('/settings/users/{user}', [SettingController::class, 'destroyUser'])->name('settings.users.destroy');
        Route::post('/settings/users/delete-bulk', [SettingController::class, 'destroyUsersBulk'])->name('settings.users.destroyBulk');
    });

    Route::middleware('permission:user.export')->group(function () {
        Route::match(['get', 'post'], '/settings/users/export', [SettingController::class, 'exportUsers'])->name('settings.users.export');
    });

    Route::middleware('permission:user.import')->group(function () {
        Route::post('/settings/users/import', [SettingController::class, 'importUsers'])->name('settings.users.import');
    });

    // Role management
    Route::middleware('permission:role.view')->group(function () {
        Route::get('/settings/roles', [RoleController::class, 'index'])->name('settings.roles');
        Route::get('/settings/roles/data', [RoleController::class, 'data'])->name('settings.roles.data');
        Route::get('/settings/roles/{role}/edit', [RoleController::class, 'edit'])->name('settings.roles.edit');
    });

    Route::middleware('permission:role.create')->group(function () {
        Route::post('/settings/roles', [RoleController::class, 'store'])->name('settings.roles.store');
    });

    Route::middleware('permission:role.update')->group(function () {
        Route::put('/settings/roles/{role}', [RoleController::class, 'update'])->name('settings.roles.update');
    });

    Route::middleware('permission:role.delete')->group(function () {
        Route::delete('/settings/roles/{role}', [RoleController::class, 'destroy'])->name('settings.roles.destroy');
    });

    // Permission management
    Route::middleware('permission:permission.view')->group(function () {
        Route::get('/settings/permissions', [PermissionController::class, 'index'])->name('settings.permissions');
        Route::get('/settings/permissions/data', [PermissionController::class, 'data'])->name('settings.permissions.data');
    });

    Route::middleware('permission:permission.sync')->group(function () {
        Route::post('/settings/permissions/sync', [PermissionController::class, 'sync'])->name('settings.permissions.sync');
    });

    // Master Candidate management
    Route::middleware('permission:candidate.view')->group(function () {
        Route::get('/settings/candidates', [CandidateController::class, 'index'])->name('settings.candidates');
        Route::get('/settings/candidates/data', [CandidateController::class, 'data'])->name('settings.candidates.data');
    });

    Route::middleware('permission:candidate.create')->group(function () {
        Route::post('/settings/candidates', [CandidateController::class, 'store'])->name('settings.candidates.store');
    });

    Route::middleware('permission:candidate.update')->group(function () {
        Route::get('/settings/candidates/{candidate}/edit', [CandidateController::class, 'edit'])->name('settings.candidates.edit');
        Route::put('/settings/candidates/{candidate}', [CandidateController::class, 'update'])->name('settings.candidates.update');
    });

    Route::middleware('permission:candidate.delete')->group(function () {
        Route::delete('/settings/candidates/{candidate}', [CandidateController::class, 'destroy'])->name('settings.candidates.destroy');
        Route::post('/settings/candidates/delete-bulk', [CandidateController::class, 'destroyBulk'])->name('settings.candidates.destroyBulk');
    });

    Route::middleware('permission:candidate.view')->group(function () {
        Route::match(['get', 'post'], '/settings/candidates/export', [CandidateController::class, 'export'])->name('settings.candidates.export');
        Route::get('/settings/candidates/exports/{export}', [CandidateController::class, 'exportProgress'])->name('settings.candidates.exportProgress');
    });

    Route::middleware('permission:candidate.create')->group(function () {
        Route::post('/settings/candidates/import', [CandidateController::class, 'import'])->name('settings.candidates.import');
        Route::get('/settings/candidates/imports/{import}', [CandidateController::class, 'importProgress'])->name('settings.candidates.importProgress');
    });

    // Master TPS management
    Route::middleware('permission:tps.view')->group(function () {
        Route::get('/settings/tps', [TpsController::class, 'index'])->name('settings.tps');
        Route::get('/settings/tps/data', [TpsController::class, 'data'])->name('settings.tps.data');
    });

    Route::middleware('permission:tps.create')->group(function () {
        Route::post('/settings/tps', [TpsController::class, 'store'])->name('settings.tps.store');
    });

    Route::middleware('permission:tps.update')->group(function () {
        Route::get('/settings/tps/{tp}/edit', [TpsController::class, 'edit'])->name('settings.tps.edit');
        Route::put('/settings/tps/{tp}', [TpsController::class, 'update'])->name('settings.tps.update');
    });

    Route::middleware('permission:tps.delete')->group(function () {
        Route::delete('/settings/tps/{tp}', [TpsController::class, 'destroy'])->name('settings.tps.destroy');
        Route::post('/settings/tps/delete-bulk', [TpsController::class, 'destroyBulk'])->name('settings.tps.destroyBulk');
    });

    Route::middleware('permission:tps.export')->group(function () {
        Route::match(['get', 'post'], '/settings/tps/export', [TpsController::class, 'export'])->name('settings.tps.export');
    });

    Route::middleware('permission:tps.import')->group(function () {
        Route::post('/settings/tps/import', [TpsController::class, 'import'])->name('settings.tps.import');
    });

    // Master RW management
    Route::middleware('permission:rw.view')->group(function () {
        Route::get('/settings/rws', [RwController::class, 'index'])->name('settings.rws');
        Route::get('/settings/rws/data', [RwController::class, 'data'])->name('settings.rws.data');
    });

    Route::middleware('permission:rw.create')->group(function () {
        Route::post('/settings/rws', [RwController::class, 'store'])->name('settings.rws.store');
    });

    Route::middleware('permission:rw.update')->group(function () {
        Route::get('/settings/rws/{rw}/edit', [RwController::class, 'edit'])->name('settings.rws.edit');
        Route::put('/settings/rws/{rw}', [RwController::class, 'update'])->name('settings.rws.update');
    });

    Route::middleware('permission:rw.delete')->group(function () {
        Route::delete('/settings/rws/{rw}', [RwController::class, 'destroy'])->name('settings.rws.destroy');
        Route::post('/settings/rws/delete-bulk', [RwController::class, 'destroyBulk'])->name('settings.rws.destroyBulk');
    });

    Route::middleware('permission:rw.export')->group(function () {
        Route::match(['get', 'post'], '/settings/rws/export', [RwController::class, 'export'])->name('settings.rws.export');
    });

    Route::middleware('permission:rw.import')->group(function () {
        Route::post('/settings/rws/import', [RwController::class, 'import'])->name('settings.rws.import');
    });

    // Master RT management
    Route::middleware('permission:rt.view')->group(function () {
        Route::get('/settings/rts', [RtController::class, 'index'])->name('settings.rts');
        Route::get('/settings/rts/data', [RtController::class, 'data'])->name('settings.rts.data');
    });

    Route::middleware('permission:rt.create')->group(function () {
        Route::post('/settings/rts', [RtController::class, 'store'])->name('settings.rts.store');
    });

    Route::middleware('permission:rt.update')->group(function () {
        Route::get('/settings/rts/{rt}/edit', [RtController::class, 'edit'])->name('settings.rts.edit');
        Route::put('/settings/rts/{rt}', [RtController::class, 'update'])->name('settings.rts.update');
    });

    Route::middleware('permission:rt.delete')->group(function () {
        Route::delete('/settings/rts/{rt}', [RtController::class, 'destroy'])->name('settings.rts.destroy');
        Route::post('/settings/rts/delete-bulk', [RtController::class, 'destroyBulk'])->name('settings.rts.destroyBulk');
    });

    Route::middleware('permission:rt.export')->group(function () {
        Route::match(['get', 'post'], '/settings/rts/export', [RtController::class, 'export'])->name('settings.rts.export');
    });

    Route::middleware('permission:rt.import')->group(function () {
        Route::post('/settings/rts/import', [RtController::class, 'import'])->name('settings.rts.import');
    });

    // Maintenance / System routes
    Route::get('/link-storage', [SettingController::class, 'linkStorage'])->name('link-storage');

    // Fallback route for storage files (serves files directly if symlink fails on shared hosting)
    // Route::get('/storage/{path}', function (string $path) {
    //     $filePath = storage_path('app/public/' . $path);

    //     if (! file_exists($filePath)) {
    //         abort(404);
    //     }

    //     $type = @mime_content_type($filePath) ?: 'application/octet-stream';

    //     return response()->file($filePath, [
    //         'Content-Type' => $type,
    //         'Cache-Control' => 'public, max-age=86400',
    //     ]);
    // })->where('path', '.*')->name('storage.fallback');

    Route::middleware('permission:migration.run')->group(function () {
        Route::get('/settings/general', [SettingController::class, 'general'])->name('settings.general');
        Route::post('/settings/migrate', [SettingController::class, 'migrate'])->name('settings.migrate');
        Route::post('/settings/patch-locations', [SettingController::class, 'patchLocations'])->name('settings.patch-locations');
        Route::post('/settings/sync-roles-permissions', [SettingController::class, 'syncRolesPermissions'])->name('settings.sync-roles-permissions');
        Route::post('/settings/link-storage', [SettingController::class, 'linkStorage'])->name('settings.link-storage');
    });
});