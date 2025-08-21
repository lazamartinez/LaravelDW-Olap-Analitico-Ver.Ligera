<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\Sucursal;
use App\Models\Producto;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function run()
    {
        $sucursales = Sucursal::all();
        $productos = Producto::all();

        // Crear 30 transacciones de prueba
        for ($i = 0; $i < 30; $i++) {
            $origen = $sucursales->random();
            
            // Asegurarse de que el destino sea diferente al origen
            do {
                $destino = $sucursales->random();
            } while ($destino->id === $origen->id);
            
            $transaction = Transaction::create([
                'origen_sucursal_id' => $origen->id,
                'destino_sucursal_id' => $destino->id,
                'codigo' => 'TRX-' . now()->format('Ymd') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'estado' => ['pendiente', 'en_transito', 'completada', 'cancelada'][rand(0, 3)],
                'notas' => 'Transferencia de productos entre sucursales',
                'prioridad' => ['low', 'medium', 'high'][rand(0, 2)],
                'user_id' => 1,
            ]);
            
            // Agregar productos a la transacción
            $productosTransaccion = $productos->random(rand(1, 4));
            
            foreach ($productosTransaccion as $producto) {
                $transaction->productos()->attach($producto->id, [
                    'cantidad' => rand(1, 10),
                    'estado' => ['pendiente', 'en_camino', 'recibido'][rand(0, 2)], // <- valores permitidos
                ]);
            }
        }
    }
}
