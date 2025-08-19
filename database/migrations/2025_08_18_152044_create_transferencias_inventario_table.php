<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransferenciasInventarioTable extends Migration
{
    public function up()
    {
        Schema::create('transferencias_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained()->onDelete('cascade');
            $table->foreignId('sucursal_origen_id')->constrained('sucursals')->onDelete('cascade');
            $table->foreignId('sucursal_destino_id')->constrained('sucursals')->onDelete('cascade');
            $table->integer('cantidad');
            $table->string('motivo')->nullable();
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('transferencias_inventario');
    }
}