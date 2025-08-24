<?php

namespace App\Http\Controllers;

use App\Services\OLAPService;
use Illuminate\Http\Request;
use App\Http\Resources\OLAP3DResource;

class OLAPController extends Controller
{
    protected $olapService;

    public function __construct(OLAPService $olapService)
    {
        $this->olapService = $olapService;
    }

    /**
     * Cubo 3D real
     */
    public function cube3D(Request $request)
    {
        try {
            $validated = $request->validate([
                'dimensions' => 'required|array|min:3|max:3',
                'measures' => 'required|array|min:1',
                'filters' => 'nullable|array'
            ]);

            $data = $this->olapService->get3DCubeData(
                $validated['dimensions'],
                $validated['measures'],
                $validated['filters'] ?? []
            );

            return response()->json([
                'success' => true,
                'data' => OLAP3DResource::collection($data)
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generando cubo OLAP',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cubo 3D de prueba (datos ficticios para test)
     */
    public function cube3DTest(Request $request)
    {
        $data = [
            ['x' => '2025-01', 'y' => 'Sucursal 1', 'z' => 100],
            ['x' => '2025-01', 'y' => 'Sucursal 2', 'z' => 150],
            ['x' => '2025-02', 'y' => 'Sucursal 1', 'z' => 120],
            ['x' => '2025-02', 'y' => 'Sucursal 2', 'z' => 180],
        ];

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Drill Down 3D
     */
    public function drillDown3D(Request $request)
    {
        try {
            $validated = $request->validate([
                'dimension' => 'required|string',
                'breakdown' => 'required|string',
                'coordinates' => 'required|array',
                'filters' => 'nullable|array'
            ]);

            $data = $this->olapService->drillDown3D(
                $validated['dimension'],
                $validated['breakdown'],
                $validated['coordinates'],
                $validated['filters'] ?? []
            );

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en drill down 3D',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Análisis de series temporales
     */
    public function timeSeriesAnalysis(Request $request)
    {
        try {
            $validated = $request->validate([
                'sucursal_id' => 'nullable|exists:sucursals,id',
                'producto_id' => 'nullable|exists:productos,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'granularity' => 'required|in:hourly,daily,weekly,monthly'
            ]);

            $data = $this->olapService->timeSeriesAnalysis(
                $validated['start_date'],
                $validated['end_date'],
                $validated['granularity'],
                $validated['sucursal_id'] ?? null,
                $validated['producto_id'] ?? null
            );

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en análisis de series temporales',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Análisis espacial
     */
    public function spatialAnalysis(Request $request)
    {
        try {
            $validated = $request->validate([
                'radius' => 'required|numeric|min:1',
                'center' => 'required|array',
                'center.lat' => 'required|numeric',
                'center.lng' => 'required|numeric',
                'metric' => 'required|in:ventas,ganancia,cantidad'
            ]);

            $data = $this->olapService->spatialAnalysis(
                $validated['center']['lat'],
                $validated['center']['lng'],
                $validated['radius'],
                $validated['metric']
            );

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en análisis espacial',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
