<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dimension_tiempos', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->integer('dia');
            $table->integer('mes');
            $table->integer('anio');
            $table->string('trimestre');
            $table->string('semana');
            $table->string('dia_semana');
            $table->boolean('es_fin_de_semana');
            $table->boolean('es_feriado');
            $table->string('nombre_feriado')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dimension_tiempos');
    }
};
