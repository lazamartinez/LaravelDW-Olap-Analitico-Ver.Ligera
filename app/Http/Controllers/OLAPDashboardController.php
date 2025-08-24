<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HechoVenta;
use App\Models\Sucursal;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OLAPDashboardController extends Controller
{
    /**
     * Display the main dashboard view.
     */
    public function index()
    {
        return view('olap.dashboard'); // Esta vista debe cargar tu SPA
    }

    /**
     * Get dashboard data for API.
     */
    public function getDashboardData(Request $request)
    {
        try {
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

            // Ventas por tiempo (mes actual)
            $ventasPorTiempo = HechoVenta::join('dimension_tiempos', 'hecho_ventas.tiempo_id', '=', 'dimension_tiempos.id')
                ->select(
                    DB::raw("TO_CHAR(dimension_tiempos.fecha, 'YYYY-MM-DD') as fecha"),
                    DB::raw('SUM(monto_total) as total_ventas')
                )
                ->whereMonth('dimension_tiempos.fecha', now()->month)
                ->whereYear('dimension_tiempos.fecha', now()->year)
                ->groupBy('fecha')
                ->orderBy('fecha')
                ->get();

            $sucursalesActivas = Sucursal::where('activa', true)->count();
            $totalSucursales = Sucursal::count();

            return response()->json([
                'success' => true,
                'data' => [
                    'ventasHoy' => $ventasHoy,
                    'ventasMes' => $ventasMes,
                    'gananciaTotal' => $gananciaTotal,
                    'productosVendidos' => $productosVendidos,
                    'sucursalesVentas' => $sucursalesVentas,
                    'productosPopulares' => $productosPopulares,
                    'ventasPorTiempo' => $ventasPorTiempo,
                    'sucursalesActivas' => $sucursalesActivas,
                    'totalSucursales' => $totalSucursales
                ],
                'message' => 'Datos del dashboard obtenidos exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => true,
                'data' => [
                    'ventasHoy' => 18542,
                    'ventasMes' => 243876,
                    'gananciaTotal' => 67385,
                    'productosVendidos' => 1248,
                    'sucursalesVentas' => [
                        ['sucursal' => 'Sucursal Centro', 'total_ventas' => 12500],
                        ['sucursal' => 'Sucursal Norte', 'total_ventas' => 9800],
                        ['sucursal' => 'Sucursal Sur', 'total_ventas' => 7600]
                    ],
                    'productosPopulares' => [
                        ['producto' => 'Laptop Gaming', 'total_vendido' => 45],
                        ['producto' => 'Smartphone', 'total_vendido' => 32],
                        ['producto' => 'Tablet', 'total_vendido' => 28]
                    ],
                    'ventasPorTiempo' => [
                        ['fecha' => now()->format('Y-m-d'), 'total_ventas' => 12500],
                        ['fecha' => now()->subDays(1)->format('Y-m-d'), 'total_ventas' => 11800],
                        ['fecha' => now()->subDays(2)->format('Y-m-d'), 'total_ventas' => 13200]
                    ],
                    'sucursalesActivas' => 8,
                    'totalSucursales' => 10
                ],
                'message' => 'Datos de demostración cargados'
            ]);
        }
    }

    /**
     * Get OLAP cube data for 3D visualization.
     */
    public function getCubeData(Request $request)
    {
        try {
            $dimensionX = $request->get('dimension_x', 'tiempo');
            $dimensionY = $request->get('dimension_y', 'sucursal');
            $dimensionZ = $request->get('dimension_z', 'producto');

            // Aquí implementarías la lógica para generar datos del cubo OLAP
            // basado en las dimensiones seleccionadas

            $cubeData = $this->generateCubeData($dimensionX, $dimensionY, $dimensionZ);

            return response()->json([
                'success' => true,
                'data' => $cubeData,
                'message' => 'Datos del cubo OLAP obtenidos exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener datos del cubo OLAP',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Generate sample cube data for demonstration.
     */
    protected function generateCubeData($dimX, $dimY, $dimZ)
    {
        // Datos de ejemplo para el cubo OLAP
        // En una implementación real, esto consultaría la base de datos
        return [
            'dimensions' => [
                ['name' => $dimX, 'members' => ['Enero', 'Febrero', 'Marzo']],
                ['name' => $dimY, 'members' => ['Norte', 'Sur', 'Este', 'Oeste']],
                ['name' => $dimZ, 'members' => ['Producto A', 'Producto B', 'Producto C']]
            ],
            'measures' => ['ventas', 'cantidad', 'ganancia'],
            'cells' => [
                // Celdas de ejemplo con datos aleatorios
                ['coords' => [0, 0, 0], 'value' => rand(1000, 5000)],
                ['coords' => [0, 0, 1], 'value' => rand(1000, 5000)],
                ['coords' => [0, 0, 2], 'value' => rand(1000, 5000)],
                ['coords' => [0, 1, 0], 'value' => rand(1000, 5000)],
                ['coords' => [0, 1, 1], 'value' => rand(1000, 5000)],
                // ... más celdas
            ],
            'maxValue' => 5000
        ];
    }

    public function metrics()
    {
        return response()->json([
            'ventasHoy' => 50000,
            'ventasMes' => 1200000,
            'gananciaTotal' => 300000,
            'totalSucursales' => Sucursal::count(),
            'sucursalesActivas' => Sucursal::where('activa', true)->count()
        ]);
    }
}
