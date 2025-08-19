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
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sucursal_id')->constrained()->onDelete('cascade');
            $table->string('codigo_transaccion')->unique();
            $table->dateTime('fecha_venta');
            $table->decimal('total', 12, 2);
            $table->decimal('impuestos', 10, 2);
            $table->decimal('descuentos', 10, 2)->default(0);
            $table->string('metodo_pago');
            $table->string('estado');
            $table->foreignId('empleado_id')->nullable()->constrained('users');
            $table->json('detalles')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
