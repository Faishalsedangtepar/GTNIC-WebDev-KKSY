<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DbTestController;

Route::get('/db-test', [DbTestController::class, 'index']);
Route::post('/db-test/run', [DbTestController::class, 'run']);

Route::get('/', function () {
    return view('welcome');
});
