<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OLAPDashboardController;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\VentaController;

// Ruta principal redirige al login
Route::get('/', fn() => redirect('/login'));

// Login / Logout
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rutas protegidas con sesión Laravel
Route::middleware('auth')->group(function () {
    // Dashboard principal
    Route::get('/dashboard', [OLAPDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'metrics'])->name('dashboard.data');

    // Vistas Web CRUD (solo si las usás desde Blade)
    Route::resource('sucursales', SucursalController::class)->except(['store', 'update', 'destroy']);
    Route::resource('productos', ProductoController::class)->except(['store', 'update', 'destroy']);
    Route::resource('inventario', InventarioController::class)->except(['store', 'update', 'destroy']);
    Route::resource('transacciones', TransactionController::class)->except(['store', 'update', 'destroy']);
    Route::resource('ventas', VentaController::class)->except(['store', 'update', 'destroy']);
});

// Catch-all para SPA (excluye /api/*)
Route::get('/{any}', [OLAPDashboardController::class, 'index'])
    ->where('any', '^(?!api).*$');
