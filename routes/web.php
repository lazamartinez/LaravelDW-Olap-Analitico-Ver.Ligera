<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OLAPDashboardController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\VentaController;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Ruta principal - redirige al login
Route::get('/', function () {
    return redirect('/login');
});

// Ruta para obtener el usuario autenticado
Route::middleware(['auth'])->get('/user', function (Request $request) {
    return response()->json($request->user());
});

// 🔐 Autenticación con tu AuthController
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// 🔒 Rutas protegidas con middleware auth
Route::middleware(['auth'])->group(function () {
    // Dashboard principal
    Route::get('/dashboard', [OLAPDashboardController::class, 'index'])->name('dashboard');

    // Sucursales
    Route::resource('/sucursales', SucursalController::class);

    // Productos
    Route::resource('/productos', ProductoController::class);

    // Inventario
    Route::resource('/inventario', InventarioController::class);
    Route::post('/inventario/transferir', [InventarioController::class, 'transferir'])->name('inventario.transferir');

    // Transacciones
    Route::resource('/transacciones', TransactionController::class);
    Route::get('/transacciones/realtime', [TransactionController::class, 'realtimeFeed'])->name('transacciones.realtime');

    // Ventas
    Route::resource('/ventas', VentaController::class);
});
