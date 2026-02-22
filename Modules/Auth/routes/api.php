<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\AuthController;
// ->middleware(['api' , 'auth:sanctum'])
Route::prefix('auth')->as('auth.')->group(function () {
    Route::get('/', [AuthController::class , 'all'])->name('all');
    Route::get('/login', [AuthController::class , 'login'])->name('login');
    Route::get('/register', [AuthController::class , 'register'])->name('register');
    Route::get('/logout', [AuthController::class , 'logout'])->name('logout');
    Route::get('/report', [AuthController::class , 'report'])->name('report');
});
