<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->string('solicitud_arrendamiento_path')->nullable()->after('evento_id');
            $table->string('verificacion_habilitacion_path')->nullable()->after('solicitud_arrendamiento_path');
        });
    }

    public function down(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->dropColumn(['solicitud_arrendamiento_path', 'verificacion_habilitacion_path']);
        });
    }
};
