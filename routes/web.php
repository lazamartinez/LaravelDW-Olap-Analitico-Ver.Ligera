<?php

use App\Http\Controllers\InventarioController;
use App\Http\Controllers\OLAPDashboardController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\SucursalController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard-olap', [OLAPDashboardController::class, 'index'])->name('olap.dashboard');
Route::resource('sucursales', SucursalController::class);
Route::resource('productos', ProductoController::class);
Route::resource('inventarios', InventarioController::class)->except(['update']);
Route::patch('/inventarios/{inventario}', [InventarioController::class, 'update']);
Route::post('/inventarios/transferir', [InventarioController::class, 'transferir'])->name('inventarios.transferir');
