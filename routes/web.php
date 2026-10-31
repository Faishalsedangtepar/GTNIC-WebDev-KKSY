<?php


use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request; 

Route::get('/', fn () => redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});


Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/dashboard', function (Request $request) {
        return match ($request->user()->role) {
            'admin'  => redirect()->route('admin.dashboard'),
            'kasir'  => redirect()->route('kasir.dashboard'),
            'gudang' => redirect()->route('stok.dashboard'),
            default  => abort(403, 'Role tidak dikenali.'),
        };
    })->name('dashboard');

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::view('/dashboard', 'admin.admin')->name('dashboard');
    });

    Route::middleware('role:kasir')->prefix('kasir')->name('kasir.')->group(function () {
        Route::view('/dashboard', 'kasir.kasir')->name('dashboard');
    });

    Route::middleware('role:gudang,admin')->prefix('stok')->name('stok.')->group(function () {
        Route::view('/dashboard', 'stok.stok')->name('dashboard');
    });
});
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/dashboard', function (Request $request) {
        return match ($request->user()->role) {
            'admin'  => redirect()->route('admin.dashboard'),
            'kasir'  => redirect()->route('kasir.dashboard'),
            'gudang' => redirect()->route('stok.dashboard'),
            default  => abort(403, 'Role tidak dikenali.'),
        };
    })->name('dashboard');

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::view('/dashboard', 'admin.admin')->name('dashboard');
    });

    Route::middleware('role:kasir')->prefix('kasir')->name('kasir.')->group(function () {
        Route::view('/dashboard', 'kasir.kasir')->name('dashboard');
    });

    Route::middleware('role:gudang,admin')->prefix('stok')->name('stok.')->group(function () {
        Route::view('/dashboard', 'stok.stok')->name('dashboard');
    });
});