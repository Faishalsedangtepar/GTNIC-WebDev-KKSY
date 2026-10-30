<?php

use App\Http\Controllers\DbTestController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/db-test', [DbTestController::class, 'index']);
Route::post('/db-test/run', [DbTestController::class, 'run']);

Route::get('/', fn () => redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // sementara: semua role masuk ke halaman admin dulu untuk tes
    Route::get('/dashboard', fn () => view('admin.admin'))->name('dashboard');
});