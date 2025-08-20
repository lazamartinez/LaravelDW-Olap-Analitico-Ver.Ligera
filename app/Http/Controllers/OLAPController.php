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

    public function cube3D(Request $request)
    {
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

        return OLAP3DResource::collection($data);
    }

    public function drillDown3D(Request $request)
    {
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

        return response()->json($data);
    }

    public function timeSeriesAnalysis(Request $request)
    {
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

        return response()->json($data);
    }

    public function spatialAnalysis(Request $request)
    {
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

        return response()->json($data);
    }
}