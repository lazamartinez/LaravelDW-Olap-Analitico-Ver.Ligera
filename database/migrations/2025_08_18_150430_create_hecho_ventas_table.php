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
        Schema::create('hecho_ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sucursal_id')->constrained()->onDelete('cascade');
            $table->foreignId('producto_id')->constrained()->onDelete('cascade');
            $table->foreignId('tiempo_id')->constrained('dimension_tiempo')->onDelete('cascade');
            $table->integer('cantidad');
            $table->decimal('monto_total', 12, 2);
            $table->decimal('costo_total', 12, 2);
            $table->decimal('ganancia', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hecho_ventas');
    }
};
