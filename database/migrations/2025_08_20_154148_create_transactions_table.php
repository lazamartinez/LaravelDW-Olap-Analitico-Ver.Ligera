<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('origen_sucursal_id')->constrained('sucursals')->onDelete('cascade');
            $table->foreignId('destino_sucursal_id')->constrained('sucursals')->onDelete('cascade');
            $table->string('codigo')->unique();
            $table->enum('estado', ['pendiente', 'en_transito', 'completada', 'cancelada'])->default('pendiente');
            $table->text('notas')->nullable();
            $table->enum('prioridad', ['low', 'medium', 'high'])->default('medium');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('transaction_producto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->onDelete('cascade');
            $table->foreignId('producto_id')->constrained()->onDelete('cascade');
            $table->integer('cantidad');
            $table->enum('estado', ['pendiente', 'en_camino', 'recibido'])->default('pendiente');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('transaction_producto');
        Schema::dropIfExists('transactions');
    }
}