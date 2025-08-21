<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OLAPDashboardController;

// Ruta principal - Redirige al login
Route::get('/', function () {
    return redirect('/login');
});

// Rutas de autenticación
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Ruta del dashboard (protegida)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [OLAPDashboardController::class, 'index'])->name('dashboard');
});

// Otras rutas protegidas...
Route::middleware(['auth'])->group(function () {
    Route::get('/sucursales', function () {
        return view('sucursales');
    })->name('sucursales');
    
    Route::get('/productos', function () {
        return view('productos');
    })->name('productos');
    
    // ... más rutas
});