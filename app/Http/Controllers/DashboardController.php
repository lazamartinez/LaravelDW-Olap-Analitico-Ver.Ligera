<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HechoVenta;
use App\Models\Sucursal;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function metrics()
    {
        // Métricas principales
        $ventasHoy = HechoVenta::whereHas('tiempo', function ($q) {
            $q->where('fecha', today());
        })->sum('monto_total');

        $ventasMes = HechoVenta::whereHas('tiempo', function ($q) {
            $q->whereMonth('fecha', now()->month)
                ->whereYear('fecha', now()->year);
        })->sum('monto_total');

        $gananciaTotal = HechoVenta::sum('ganancia');
        $productosVendidos = HechoVenta::sum('cantidad');

        // Ventas por sucursal
        $sucursalesVentas = HechoVenta::with('sucursal')
            ->select('sucursal_id', DB::raw('SUM(monto_total) as total_ventas'))
            ->groupBy('sucursal_id')
            ->orderByDesc('total_ventas')
            ->get()
            ->map(function ($item) {
                return [
                    'sucursal' => $item->sucursal->nombre,
                    'total_ventas' => $item->total_ventas
                ];
            });

        // Productos populares
        $productosPopulares = HechoVenta::with('producto')
            ->select('producto_id', DB::raw('SUM(cantidad) as total_vendido'))
            ->groupBy('producto_id')
            ->orderByDesc('total_vendido')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'producto' => $item->producto->nombre,
                    'total_vendido' => $item->total_vendido
                ];
            });

        $sucursalesActivas = Sucursal::where('activa', true)->count();
        $totalSucursales = Sucursal::count();

        return response()->json([
            'ventasHoy' => $ventasHoy,
            'ventasMes' => $ventasMes,
            'gananciaTotal' => $gananciaTotal,
            'productosVendidos' => $productosVendidos,
            'sucursalesVentas' => $sucursalesVentas,
            'productosPopulares' => $productosPopulares,
            'sucursalesActivas' => $sucursalesActivas,
            'totalSucursales' => $totalSucursales
        ]);
    }
}