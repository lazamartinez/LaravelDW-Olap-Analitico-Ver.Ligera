<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DimensionTiempo extends Model
{
    use HasFactory;

    protected $fillable = [
        'fecha',
        'dia',
        'mes',
        'anio',
        'trimestre',
        'semana',
        'dia_semana',
        'es_fin_de_semana',
        'es_feriado',
        'nombre_feriado'
    ];

    protected $casts = [
        'fecha' => 'date',
        'es_fin_de_semana' => 'boolean',
        'es_feriado' => 'boolean'
    ];

    public function hechoVentas()
    {
        return $this->hasMany(HechoVenta::class, 'tiempo_id');
    }
}