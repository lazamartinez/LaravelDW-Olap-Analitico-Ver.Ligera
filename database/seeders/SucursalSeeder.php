<?php

namespace Database\Seeders;

use App\Models\Sucursal;
use Illuminate\Database\Seeder;

class SucursalSeeder extends Seeder
{
    public function run()
    {
        $sucursales = [
            [
                'nombre' => 'Sucursal Centro',
                'direccion' => 'Av. Principal 123',
                'ciudad' => 'Buenos Aires',
                'pais' => 'Argentina',
                'codigo_postal' => '1001',
                'telefono' => '+54 11 1234-5678',
                'email' => 'centro@olap3d.com',
                'latitud' => -34.603722,
                'longitud' => -58.381592,
                'configuracion' => json_encode(['port' => 8100, 'database' => 'sucursal_centro']),
                'activa' => true,
            ],
            [
                'nombre' => 'Sucursal Norte',
                'direccion' => 'Calle Norte 456',
                'ciudad' => 'Rosario',
                'pais' => 'Argentina',
                'codigo_postal' => '2000',
                'telefono' => '+54 341 987-6543',
                'email' => 'norte@olap3d.com',
                'latitud' => -32.94682,
                'longitud' => -60.63932,
                'configuracion' => json_encode(['port' => 8101, 'database' => 'sucursal_norte']),
                'activa' => true,
            ],
            [
                'nombre' => 'Sucursal Sur',
                'direccion' => 'Av. Sur 789',
                'ciudad' => 'La Plata',
                'pais' => 'Argentina',
                'codigo_postal' => '1900',
                'telefono' => '+54 221 555-1234',
                'email' => 'sur@olap3d.com',
                'latitud' => -34.92145,
                'longitud' => -57.95453,
                'configuracion' => json_encode(['port' => 8102, 'database' => 'sucursal_sur']),
                'activa' => true,
            ],
        ];

        foreach ($sucursales as $sucursal) {
            Sucursal::create($sucursal);
        }

        // Crear sucursales adicionales usando la factory
        \App\Models\Sucursal::factory(7)->create();
    }
}