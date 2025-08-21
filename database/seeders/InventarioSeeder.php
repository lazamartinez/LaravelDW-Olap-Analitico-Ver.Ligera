<?php

namespace Database\Seeders;

use App\Models\Inventario;
use App\Models\Sucursal;
use App\Models\Producto;
use Illuminate\Database\Seeder;

class InventarioSeeder extends Seeder
{
    public function run()
    {
        $sucursales = Sucursal::all();
        $productos = Producto::all();

        foreach ($sucursales as $sucursal) {
            // Asignar 5-10 productos por sucursal
            $productosSucursal = $productos->random(rand(5, 10));
            
            foreach ($productosSucursal as $producto) {
                Inventario::create([
                    'sucursal_id' => $sucursal->id,
                    'producto_id' => $producto->id,
                    'cantidad' => rand(5, 50),
                    'minimo_stock' => rand(3, 10),
                    'ubicacion' => 'Estante ' . chr(65 + rand(0, 5)) . rand(1, 10),
                ]);
            }
        }
    }
}