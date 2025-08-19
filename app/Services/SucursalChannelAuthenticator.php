<?php

namespace App\Services;

use App\Models\Sucursal;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class SucursalChannelAuthenticator
{
    public function authenticate(Request $request, $channelName)
    {
        // Verificar si el canal pertenece a una sucursal
        if (str_starts_with($channelName, 'sucursal_')) {
            $sucursalId = str_replace('sucursal_', '', $channelName);
            $sucursal = Sucursal::find($sucursalId);
            
            if (!$sucursal) {
                throw new AccessDeniedHttpException('Sucursal no encontrada');
            }
            
            // Verificar el token de la sucursal
            if ($request->header('X-Sucursal-Secret') !== $sucursal->api_secret) {
                throw new AccessDeniedHttpException('Credenciales inválidas');
            }
            
            return [
                'sucursal_id' => $sucursal->id,
                'channel_name' => $channelName,
                'auth' => true
            ];
        }
        
        return null;
    }
}