<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cotizacion_documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cotizacion_id')->constrained('cotizaciones')->onDelete('cascade');
            $table->foreignId('plantilla_id')->nullable()->constrained('plantillas_documentos')->nullOnDelete();
            $table->string('codigo_tipo'); // RESPUESTA_ARRENDAMIENTO, RESPUESTA_SERVICIOS_ADICIONALES
            
            // Datos del formulario
            $table->date('fecha');
            $table->string('cite');
            $table->string('del')->nullable();
            $table->string('plazo')->default('tres (3) días hábiles');
            $table->string('telefono')->nullable();
            $table->string('correo_electronico')->nullable();
            $table->string('hoja_ruta')->nullable();
            $table->string('moscas')->nullable();

            // Snapshot de datos automáticos en el momento de generación
            $table->json('snapshot_datos')->nullable();
            
            // Archivo PDF generado
            $table->string('pdf_path')->nullable();

            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cotizacion_documentos');
    }
};
