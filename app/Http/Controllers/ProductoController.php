<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductoResource;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductoController extends Controller
{
    public function index()
    {
        $productos = Producto::with('sucursal')->paginate(10);
        return view('productos.index', compact('productos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sucursal_id' => 'required|exists:sucursals,id',
            'codigo' => 'required|unique:productos',
            'nombre' => 'required|max:255',
            'descripcion' => 'nullable',
            'precio' => 'required|numeric|min:0',
            'costo' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'categoria' => 'required|max:255',
            'marca' => 'nullable|max:255',
            'atributos' => 'nullable|json'
        ]);

        $producto = Producto::create($validated);
        return new ProductoResource($producto);
    }

    public function show(Producto $producto)
    {
        return new ProductoResource($producto->load('sucursal', 'ventas', 'inventarios'));
    }

    public function update(Request $request, Producto $producto)
    {
        $validated = $request->validate([
            'sucursal_id' => 'sometimes|exists:sucursals,id',
            'codigo' => 'sometimes|unique:productos,codigo,' . $producto->id,
            'nombre' => 'sometimes|max:255',
            'descripcion' => 'nullable',
            'precio' => 'sometimes|numeric|min:0',
            'costo' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
            'categoria' => 'sometimes|max:255',
            'marca' => 'nullable|max:255',
            'atributos' => 'nullable|json'
        ]);

        $producto->update($validated);
        return new ProductoResource($producto);
    }

    public function destroy(Producto $producto)
    {
        $producto->delete();
        return response()->json(null, 204);
    }

    public function metrics(Producto $producto)
    {
        $ventas = DB::table('hecho_ventas')
            ->select(
                DB::raw('SUM(cantidad) as total_vendido'),
                DB::raw('SUM(monto_total) as total_ventas'),
                DB::raw('SUM(ganancia) as total_ganancia')
            )
            ->where('producto_id', $producto->id)
            ->first();

        return response()->json([
            'producto' => new ProductoResource($producto),
            'metrics' => $ventas
        ]);
    }
}
