<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use Illuminate\Http\Request;

class SucursalController extends Controller
{
    public function startDockerContainer(Sucursal $sucursal)
    {
        // Validar que la sucursal no tenga ya un contenedor corriendo
        if ($sucursal->docker_container_id) {
            return response()->json(['message' => 'La sucursal ya tiene un contenedor en ejecución'], 400);
        }

        // Comando para iniciar el contenedor
        $dockerCommand = "docker run -d --name sucursal_{$sucursal->id} -p {$this->getNextAvailablePort()}:80 -e SUCURSAL_ID={$sucursal->id} sucursal-image";

        exec($dockerCommand, $output, $returnVar);

        if ($returnVar !== 0) {
            return response()->json(['message' => 'Error al iniciar el contenedor', 'error' => $output], 500);
        }

        // Obtener el ID del contenedor
        $containerId = $output[0] ?? null;

        if (!$containerId) {
            return response()->json(['message' => 'No se pudo obtener el ID del contenedor'], 500);
        }

        // Actualizar la sucursal con la información del contenedor
        $sucursal->update([
            'docker_container_id' => $containerId,
            'docker_image' => 'sucursal-image',
            'configuracion' => [
                'puerto' => $this->getNextAvailablePort(),
                'estado' => 'running'
            ]
        ]);

        return response()->json(['message' => 'Contenedor iniciado exitosamente', 'container_id' => $containerId]);
    }

    protected function getNextAvailablePort()
    {
        // Implementar lógica para encontrar un puerto disponible
        // Esto es un ejemplo simplificado
        $usedPorts = Sucursal::whereNotNull('configuracion->puerto')
            ->pluck('configuracion->puerto')
            ->toArray();

        $port = 8001;
        while (in_array($port, $usedPorts)) {
            $port++;
        }

        return $port;
    }

    public function stopDockerContainer(Sucursal $sucursal)
    {
        if (!$sucursal->docker_container_id) {
            return response()->json(['message' => 'La sucursal no tiene un contenedor en ejecución'], 400);
        }

        exec("docker stop {$sucursal->docker_container_id}", $output, $returnVar);

        if ($returnVar !== 0) {
            return response()->json(['message' => 'Error al detener el contenedor', 'error' => $output], 500);
        }

        $sucursal->update([
            'configuracion->estado' => 'stopped'
        ]);

        return response()->json(['message' => 'Contenedor detenido exitosamente']);
    }

    public function dockerContainerStatus(Sucursal $sucursal)
    {
        if (!$sucursal->docker_container_id) {
            return response()->json(['status' => 'not_created'], 200);
        }

        exec("docker inspect --format='{{.State.Status}}' {$sucursal->docker_container_id}", $output, $returnVar);

        if ($returnVar !== 0) {
            return response()->json(['status' => 'error'], 500);
        }

        return response()->json(['status' => $output[0] ?? 'unknown']);
    }
    public function index()
    {
        $sucursales = Sucursal::paginate(10);
        return view('sucursales.index', compact('sucursales'));
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'direccion' => 'required|string',
            'ciudad' => 'required|string',
            'pais' => 'required|string',
            'codigo_postal' => 'nullable|string',
            'telefono' => 'nullable|string',
            'email' => 'nullable|email',
            'activa' => 'sometimes|boolean'
        ]);

        // Convertir el checkbox "activa" a booleano
        $validated['activa'] = $request->has('activa');

        $sucursal = Sucursal::create($validated);

        return redirect()->route('sucursales.index')
                         ->with('success', 'Sucursal creada exitosamente');
    }

}
