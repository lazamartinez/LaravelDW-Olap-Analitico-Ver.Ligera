<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Usuario administrador
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@olap3d.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Usuarios de prueba
        User::factory(5)->create();
    }
}