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
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cotizacion_id')->unique()->constrained('cotizaciones')->onDelete('cascade');
            $table->string('codigo', 50)->unique();
            $table->integer('correlativo');
            $table->date('fecha_reserva');
            $table->string('estado', 20)->default('confirmada');
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('jefe_ucfchm', 150)->default('Sergio N. Rivera Céspedes');
            $table->text('observaciones')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
