<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VpnController;
use App\Http\Controllers\AuthController;

// Rute Autentikasi Login & Logout
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
// Rute Lupa Password
Route::get('/forgot-password', [AuthController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');

// Rute Utama VPN (Dilindungi Middleware Auth)
Route::middleware(['auth'])->group(function () {
    Route::get('/', [VpnController::class, 'index'])->name('vpn.index');
    Route::post('/vpn/store', [VpnController::class, 'store'])->name('vpn.store');
    Route::put('/vpn/{id}', [VpnController::class, 'update'])->name('vpn.update');
    Route::delete('/vpn/{id}', [VpnController::class, 'destroy'])->name('vpn.destroy');
});
