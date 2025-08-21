<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            UserSeeder::class,
            SucursalSeeder::class,
            ProductoSeeder::class,
            InventarioSeeder::class,
            DimensionTiempoSeeder::class,
            VentaSeeder::class,
            TransactionSeeder::class,
        ]);
    }
}