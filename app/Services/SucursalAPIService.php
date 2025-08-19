<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Sucursal;
use App\Models\DynamicModel;

class SucursalAPIService
{
    public function callSucursal(Sucursal $sucursal, $endpoint, $method = 'get', $data = [])
    {
        $baseUrl = "http://host.docker.internal:{$sucursal->configuracion['port']}";
        
        return Http::withHeaders([
            'X-Sucursal-Secret' => $sucursal->api_secret,
            'Accept' => 'application/json'
        ])->{$method}("{$baseUrl}/api/{$endpoint}", $data);
    }

    public function syncGlobalData(Sucursal $sucursal)
    {
        // Sincronizar modelos dinámicos
        $dynamicModels = DynamicModel::all();
        $this->callSucursal($sucursal, 'dynamic-models/sync', 'post', [
            'models' => $dynamicModels->toArray()
        ]);

        // Sincronizar configuraciones globales (ejemplo básico)
        $globalConfig = [
            'settings' => [
                'tax_rate' => 0.16, // IVA general
                'currency' => 'MXN'
            ]
        ];
        $this->callSucursal($sucursal, 'config/sync', 'post', $globalConfig);
    }
}