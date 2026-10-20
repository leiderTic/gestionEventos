<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cotizacion_documentos', function (Blueprint $table) {
            $table->string('pdf_firmado_path')->nullable()->after('pdf_path');
            $table->timestamp('fecha_firma')->nullable()->after('pdf_firmado_path');
            $table->boolean('bloqueado')->default(false)->after('fecha_firma');
        });
    }

    public function down(): void
    {
        Schema::table('cotizacion_documentos', function (Blueprint $table) {
            $table->dropColumn(['pdf_firmado_path', 'fecha_firma', 'bloqueado']);
        });
    }
};
