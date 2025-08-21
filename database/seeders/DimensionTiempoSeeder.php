<?php

namespace Database\Seeders;

use App\Models\DimensionTiempo;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DimensionTiempoSeeder extends Seeder
{
    public function run()
    {
        $startDate = Carbon::now()->subYear();
        $endDate = Carbon::now();
        
        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            DimensionTiempo::create([
                'fecha' => $date->toDateString(),
                'dia' => $date->day,
                'mes' => $date->month,
                'anio' => $date->year,
                'trimestre' => ceil($date->month / 3),
                'semana' => $date->weekOfYear,
                'dia_semana' => $date->dayName,
                'es_fin_de_semana' => $date->isWeekend(),
                'es_feriado' => false,
            ]);
        }
    }
}