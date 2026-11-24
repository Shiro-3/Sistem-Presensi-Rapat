<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RapatController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AkunController;
use App\Http\Controllers\PenggunaController;


/*
|--------------------------------------------------------------------------
| Publik
|--------------------------------------------------------------------------
*/
Route::get('/', [RapatController::class, 'index'])->name('rapat.index');

Route::get('/rapat-counts', [RapatController::class, 'counts'])
    ->middleware('throttle:60,1')
    ->name('rapat.counts');

Route::prefix('rapat/{slug}')
    ->where(['slug' => '[a-z0-9\-]+'])
    ->group(function () {
        Route::get('/presensi', [RapatController::class, 'showForm'])->name('rapat.form');
        Route::post('/presensi', [RapatController::class, 'storePresensi'])
            ->middleware('throttle:10,1')
            ->name('rapat.presensi.store');
    });

/*
|--------------------------------------------------------------------------
| Autentikasi
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.attempt');
    Route::get('/akun/password', [AkunController::class, 'edit'])->name('password.edit');
    Route::put('/akun/password', [AkunController::class, 'update'])
        ->middleware('throttle:5,1')
        ->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware(['auth', 'akun.aktif', 'ganti.password'])->group(function () {

    // ---------- Operasional: Admin dan Super Admin ----------
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/rapat/create', [AdminController::class, 'create'])->name('rapat.create');
    Route::post('/rapat', [AdminController::class, 'store'])->name('rapat.store');
    Route::get('/rapat/{id}', [AdminController::class, 'show'])->whereNumber('id')->name('rapat.show');
    Route::get('/rapat/{id}/pdf', [AdminController::class, 'exportPdf'])->whereNumber('id')->name('rapat.pdf');
    Route::get('/absensi/{id}/tanda-tangan', [AdminController::class, 'tandaTangan'])
        ->whereNumber('id')->name('absensi.ttd');
    Route::get('/absensi/{id}/foto', [AdminController::class, 'foto'])
        ->whereNumber('id')->name('absensi.foto');

    // Ubah password sendiri
    Route::get('/akun/password', [AkunController::class, 'edit'])->name('password.edit');
    Route::put('/akun/password', [AkunController::class, 'update'])
        ->middleware('throttle:5,1')->name('password.update');

    // ---------- Khusus Super Admin ----------
    Route::middleware('can:hapus-rapat')->group(function () {
        Route::delete('/rapat', [AdminController::class, 'destroyMany'])->name('rapat.destroy-many');
        Route::delete('/rapat/{id}', [AdminController::class, 'destroy'])->whereNumber('id')->name('rapat.destroy');
    });

    Route::middleware('can:kelola-pengguna')->prefix('pengguna')->name('pengguna.')->group(function () {
        Route::get('/', [PenggunaController::class, 'index'])->name('index');
        Route::get('/create', [PenggunaController::class, 'create'])->name('create');
        Route::post('/', [PenggunaController::class, 'store'])->name('store');
        Route::get('/{pengguna}/edit', [PenggunaController::class, 'edit'])->whereNumber('pengguna')->name('edit');
        Route::put('/{pengguna}', [PenggunaController::class, 'update'])->whereNumber('pengguna')->name('update');
        Route::put('/{pengguna}/password', [PenggunaController::class, 'resetPassword'])
            ->whereNumber('pengguna')->name('password');
    });
});