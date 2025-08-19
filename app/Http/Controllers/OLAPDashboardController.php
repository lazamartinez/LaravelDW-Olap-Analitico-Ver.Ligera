<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HechoVenta;
use App\Models\Sucursal;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OLAPDashboardController extends Controller
{
    public function index()
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

        // Ventas por tiempo (mes actual) -> corregido para PostgreSQL
        $ventasPorTiempo = HechoVenta::join('dimension_tiempos', 'hecho_ventas.tiempo_id', '=', 'dimension_tiempos.id')
            ->select(
                DB::raw("TO_CHAR(dimension_tiempos.fecha, 'YYYY-MM') as periodo"),
                DB::raw('SUM(monto_total) as total_ventas')
            )
            ->whereMonth('dimension_tiempos.fecha', now()->month)
            ->whereYear('dimension_tiempos.fecha', now()->year)
            ->groupBy('periodo')
            ->orderBy('periodo')
            ->get();

        $sucursalesActivas = Sucursal::where('activa', true)->count();
        $totalSucursales = Sucursal::count();

        return view('olap.dashboard', compact(
            'ventasHoy',
            'ventasMes',
            'gananciaTotal',
            'productosVendidos',
            'sucursalesVentas',
            'productosPopulares',
            'ventasPorTiempo',
            'sucursalesActivas',
            'totalSucursales'
        ));
    }
}
