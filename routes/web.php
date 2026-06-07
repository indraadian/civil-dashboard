<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CivilController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;

// authentication pages
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
Route::get('/create-admin', [AuthController::class, 'createAdminUser'])->name('create-admin');

// 2. Civils
Route::middleware(['auth'])->group(function () {
    Route::get('/civils', [CivilController::class, 'index'])->name('civils');
    Route::get('/civils/data', [CivilController::class, 'data'])->name('api.civils.data');
    Route::get('/civils/{id}/edit', [CivilController::class, 'edit'])->name('civils.edit');
    Route::put('/civils/{id}', [CivilController::class, 'update'])->name('civils.update');

    Route::middleware('role:admin')->group(function () {
        Route::get('/civils/create', [CivilController::class, 'create'])->name('civils.create');
        Route::post('/civils', [CivilController::class, 'store'])->name('civils.store');
        Route::delete('/civils/{id}', [CivilController::class, 'destroy'])->name('civils.destroy');
        Route::post('/civils/delete-bulk', [CivilController::class, 'destroyBulk'])->name('civils.destroyBulk');
        Route::get('/civils/export', [CivilController::class, 'export'])->name('civils.export');
        Route::post('/civils/import', [CivilController::class, 'import'])->name('civils.import');
    });
});

// 2. dashboard
Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
});

// Route::middleware(['auth', 'role:admin,user'])->group(function () {
//     Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
// });

// {{-- Menampilkan tombol hanya untuk admin --}}
// @if(auth()->user()->role == 'admin')
//     <a href="{{ route('civils.create') }}" class="btn btn-primary">Tambah Data</a>
// @endif

// {{-- Menampilkan menu jika role adalah admin atau editor --}}
// @if(in_array(auth()->user()->role, ['admin', 'editor']))
//     <button class="btn btn-danger">Hapus Data</button>
// @endif